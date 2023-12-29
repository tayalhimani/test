<?php


namespace Dyson\SinglePageCheckout\Cache\Wrappers;

/**
 * Checkout layout processors abstract cache wrapper.
 * Used by DI virtual type.
 * @since 3.0.0
 */
class LayoutProcessorCacheWrapper implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @var \Dyson\SinglePageCheckout\Cache\Type
     */
    private $cacheModel;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    private $objectManager;

    /**
     * @var string
     */
    private $processorClassName;

    /**
     * @var bool
     */
    private $isCacheable;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Dyson\SinglePageCheckout\Api\CacheKeyPartProviderInterface[]
     */
    private $cacheVariators;

    /**
     * @var array
     */
    private $cacheTags = [\Magento\Framework\App\Cache\Type\Config::CACHE_TAG];

    /**
     * @param \Dyson\SinglePageCheckout\Cache\Type $cacheModel
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param string $processorClass
     * @param \Dyson\SinglePageCheckout\Api\CacheKeyPartProviderInterface[] $cacheVariators
     * @param bool $isCacheable
     */
    public function __construct(
        \Dyson\SinglePageCheckout\Cache\Type $cacheModel,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        string $processorClass = '',
        array $cacheVariators = [],
        bool $isCacheable = true
    ) {
        $this->cacheModel = $cacheModel;
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
        $this->processorClassName = $processorClass;
        $this->cacheVariators = $cacheVariators;
        $this->isCacheable = $isCacheable;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout)
    {
        if (!$this->isCacheable) {
            return $this->getProcessorObject()->process($jsLayout);
        }
        $data = $this->cacheModel->load($this->getCacheKey());
        if ($data === false) {
            $jsLayout = $this->getProcessorObject()->process($jsLayout);
            $this->cacheModel->save($this->serializer->serialize($jsLayout), $this->getCacheKey(), $this->cacheTags);
        } else {
            $jsLayout = $this->serializer->unserialize($data);
        }

        return $jsLayout;
    }

    /**
     * @return string
     */
    private function getCacheKey(): string
    {
        $key = 'layoutProc|' . $this->processorClassName;
        /** @var \Dyson\SinglePageCheckout\Api\CacheKeyPartProviderInterface $keyPartObject */
        foreach ($this->cacheVariators as $keyPartObject) {
            $key .= '|' . $keyPartObject->getKeyPart();
        }

        return $key;
    }

    /**
     * @return \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
     */
    private function getProcessorObject()
    {
        return $this->objectManager->get($this->processorClassName);
    }
}
