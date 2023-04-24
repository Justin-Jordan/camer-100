<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\OpenMarketplace\Controller\Action\Vendor\ProductListing;

use BitBag\OpenMarketplace\Entity\ProductListing\ProductDraftInterface;
use BitBag\OpenMarketplace\Entity\ShopUserInterface;
use BitBag\OpenMarketplace\Exception\VendorNotFoundException;
use BitBag\OpenMarketplace\Factory\ProductListingFromDraftFactoryInterface;
use BitBag\OpenMarketplace\Form\ProductListing\ProductType;
use BitBag\OpenMarketplace\Repository\ProductListing\ProductDraftRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\EventDispatcherInterface;
use Sylius\Bundle\ResourceBundle\Controller\FlashHelperInterface;
use Sylius\Bundle\ResourceBundle\Controller\NewResourceFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\RedirectHandlerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CreateProductAction extends AbstractController
{
    private MetadataInterface $metadata;

    private RequestConfigurationFactoryInterface $requestConfigurationFactory;

    private NewResourceFactoryInterface $newResourceFactory;

    private FactoryInterface $factory;

    private RedirectHandlerInterface $redirectHandler;

    private FlashHelperInterface $flashHelper;

    private EventDispatcherInterface $eventDispatcher;

    private ProductListingFromDraftFactoryInterface $productListingFromDraftFactory;

    private ProductDraftRepositoryInterface $productDraftRepository;

    private ImageUploaderInterface $imageUploader;

    public function __construct(
        MetadataInterface $metadata,
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        NewResourceFactoryInterface $newResourceFactory,
        FactoryInterface $factory,
        ProductListingFromDraftFactoryInterface $productListingFromDraftFactory,
        RedirectHandlerInterface $redirectHandler,
        FlashHelperInterface $flashHelper,
        EventDispatcherInterface $eventDispatcher,
        ProductDraftRepositoryInterface $productDraftRepository,
        ImageUploaderInterface $imageUploader
    ) {
        $this->requestConfigurationFactory = $requestConfigurationFactory;
        $this->newResourceFactory = $newResourceFactory;
        $this->factory = $factory;
        $this->metadata = $metadata;
        $this->redirectHandler = $redirectHandler;
        $this->flashHelper = $flashHelper;
        $this->eventDispatcher = $eventDispatcher;
        $this->productListingFromDraftFactory = $productListingFromDraftFactory;
        $this->productDraftRepository = $productDraftRepository;
        $this->imageUploader = $imageUploader;
    }

    public function __invoke(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        /** @var ProductDraftInterface $newResource */
        $newResource = $this->newResourceFactory->create($configuration, $this->factory);

        $form = $this->createForm(ProductType::class, $newResource);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            /** @var ProductDraftInterface $productDraft */
            $productDraft = $form->getData();

            foreach ($newResource->getImages() as $image) {
                $image->setOwner($newResource);
                $this->imageUploader->upload($image);
            }
            foreach ($productDraft->getAttributes() as $attribute) {
                $attribute->setSubject($productDraft);
            }

            $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::CREATE, $configuration, $newResource);

            if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                throw new HttpException($event->getErrorCode(), $event->getMessage());
            }
            if ($event->isStopped()) {
                $this->flashHelper->addFlashFromEvent($configuration, $event);

                $eventResponse = $event->getResponse();
                if (null !== $eventResponse) {
                    return $eventResponse;
                }

                return $this->redirectHandler->redirectToIndex($configuration, $newResource);
            }

            /** @var ShopUserInterface $user */
            $user = $this->getUser();
            $vendor = $user->getVendor();

            if (null === $vendor) {
                throw new VendorNotFoundException('Vendor not found.');
            }

            $productDraft = $this->productListingFromDraftFactory->createNew($productDraft, $vendor);

            $this->productDraftRepository->save($productDraft);
            $this->addFlash('success', 'open_marketplace.ui.product_listing_created');

            return $this->redirectToRoute('open_marketplace_vendor_product_listing_index');
        }

        return new Response(
            $this->renderView('Vendor/ProductListing/create_form.html.twig', [
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $newResource,
                $this->metadata->getName() => $newResource,
                'form' => $form->createView(),
            ])
        );
    }
}
