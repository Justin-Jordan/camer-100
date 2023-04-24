<?php

declare(strict_types=1);

namespace spec\BitBag\OpenMarketplace\Remover;

use BitBag\OpenMarketplace\Entity\VendorAddressInterface;
use BitBag\OpenMarketplace\Entity\VendorProfileUpdateInterface;
use BitBag\OpenMarketplace\Remover\ProfileUpdateRemover;
use BitBag\OpenMarketplace\Remover\ProfileUpdateRemoverInterface;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

final class ProfileUpdateRemoverSpec extends ObjectBehavior
{
    public function let(
        EntityManagerInterface $entityManager
    ): void {
        $this->beConstructedWith($entityManager);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProfileUpdateRemover::class);
        $this->shouldImplement(ProfileUpdateRemoverInterface::class);
    }

    public function it_removes_profile_update(
        EntityManagerInterface $entityManager,
        VendorProfileUpdateInterface $vendorProfileUpdate
    ): void {
        $vendorProfileUpdate->getVendorAddress()
            ->willReturn(null);

        $entityManager->remove($vendorProfileUpdate)
            ->shouldBeCalledOnce();

        $entityManager->flush()
            ->shouldBeCalledOnce();

        $this->removePendingUpdate($vendorProfileUpdate);
    }

    public function it_removes_profile_update_and_address_update(
        EntityManagerInterface $entityManager,
        VendorProfileUpdateInterface $vendorProfileUpdate,
        VendorAddressInterface $vendorProfileAddressUpdate
    ): void {
        $vendorProfileUpdate->getVendorAddress()
            ->willReturn($vendorProfileAddressUpdate);

        $entityManager->remove($vendorProfileAddressUpdate)
            ->shouldBeCalledOnce();

        $entityManager->remove($vendorProfileUpdate)
            ->shouldBeCalledOnce();

        $entityManager->flush()
            ->shouldBeCalledOnce();

        $this->removePendingUpdate($vendorProfileUpdate);
    }
}
