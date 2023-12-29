<?php


namespace Dyson\SinglePageCheckout\Api;

/**
 * Cache variator interface.
 * Return cache key/identifier part.
 * @since 3.0.0
 */
interface CacheKeyPartProviderInterface
{
    /**
     * @return string
     */
    public function getKeyPart();
}
