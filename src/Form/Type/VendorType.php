<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\OpenMarketplace\Form\Type;

use BitBag\OpenMarketplace\Entity\Vendor;
use BitBag\OpenMarketplace\Exception\ShopUserNotFoundException;
use BitBag\OpenMarketplace\Form\VendorImageType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Validator\Constraints\Valid;

final class VendorType extends AbstractResourceType
{
    private TokenStorageInterface $tokenStorage;

    private string $shopUserClassFQN;

    public function __construct(
        string $shopUserClassFQN,
        TokenStorageInterface $tokenStorage,
        string $dataClass,
        array $validationGroups = []
    ) {
        parent::__construct($dataClass, $validationGroups);

        $this->tokenStorage = $tokenStorage;
        $this->shopUserClassFQN = $shopUserClassFQN;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shopUser', EntityType::class, [
                'class' => $this->shopUserClassFQN,
            ])
            ->add('companyName', TextType::class, [
                'label' => 'open_marketplace.ui.company_name',
            ])
            ->add('taxIdentifier', TextType::class, [
                'label' => 'open_marketplace.ui.tax_identifier',
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'open_marketplace.ui.phone_number',
            ])
            ->add('image', VendorImageType::class, [
                'label' => false,
                'required' => false,
                'constraints' => [new Valid(['groups' => 'VendorLogo'])],
            ])
            ->add('vendorAddress', VendorAddressType::class, [
                'label' => 'open_marketplace.ui.company_address',
                'constraints' => [new Valid()],
            ])
            ->add('description', TextType::class, [
                'label' => 'open_marketplace.ui.description',
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
                $token = $this->tokenStorage->getToken();
                if (null === $token) {
                    throw new TokenNotFoundException('No token found.');
                }

                /** @var ShopUserInterface $user */
                $user = $token->getUser();
                if (!$user instanceof ShopUserInterface) {
                    throw new ShopUserNotFoundException('No user found.');
                }

                $form = $event->getForm();
                $form->get('shopUser')->setData($user);
                $event->setData($form);
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vendor::class,
            'validation_groups' => $this->validationGroups,
        ]);
    }
}
