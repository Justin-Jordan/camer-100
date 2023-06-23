<?php

namespace BitBag\OpenMarketplace\Entity;

use Dedi\SyliusSEOPlugin\Domain\SEO\Adapter\ReferenceableInterface;
use Dedi\SyliusSEOPlugin\Domain\SEO\Adapter\ReferenceableTrait;
use Dedi\SyliusSEOPlugin\Domain\SEO\Adapter\SeoAwareChannelInterface;
use Dedi\SyliusSEOPlugin\Domain\SEO\Adapter\SeoAwareChannelTrait;
use Dedi\SyliusSEOPlugin\Entity\SEOContent;
use Sylius\Component\Core\Model\Channel as BaseChannel;

class Channel extends BaseChannel implements ReferenceableInterface, SeoAwareChannelInterface
{
    use ReferenceableTrait;
    use SeoAwareChannelTrait;

    protected function createReferenceableContent(): ReferenceableInterface
    {
        return new SEOContent();
    }
}