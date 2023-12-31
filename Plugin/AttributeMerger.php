<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CheckoutCore
 */


namespace Dyson\SinglePageCheckout\Plugin;

use Amasty\CheckoutCore\Model\Field;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Amasty\CheckoutCore\Api\Data\QuoteCustomFieldsInterface;
use Amasty\CheckoutCore\Api\Data\CustomFieldsConfigInterface;
use Amasty\CheckoutCore\Plugin\Checkout\Block\Checkout\LayoutProcessor;
use Amasty\CheckoutCore\Model\FieldsDefaultProvider;
use Amasty\CheckoutCore\Model\Config as ConfigProvider;
use Magento\Eav\Model\Config;
use Amasty\GoogleAddressAutocomplete\Model\ConfigProvider as GoogleAddressConfig;

/**
 * Class AttributeMerger
 */
class AttributeMerger
{
    protected $fieldsConfig = null;

    /**
     * @var Field
     */
    private $fieldSingleton;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LayoutProcessor
     */
    private $layoutProcessorPlugin;

    /**
     * @var FieldsDefaultProvider
     */
    private $fieldsDefaultProvider;

    /**
     * @var ConfigProvider
     */
    private $checkoutConfig;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Field $fieldSingleton,
        StoreManagerInterface $storeManager,
        LayoutProcessor $layoutProcessorPlugin,
        FieldsDefaultProvider $fieldsDefaultProvider,
        ConfigProvider $checkoutConfig,
        Config $eavConfig,
        GoogleAddressConfig $configProvider
    ) {
        $this->fieldSingleton = $fieldSingleton;
        $this->storeManager = $storeManager;
        $this->layoutProcessorPlugin = $layoutProcessorPlugin;
        $this->fieldsDefaultProvider = $fieldsDefaultProvider;
        $this->checkoutConfig = $checkoutConfig;
        $this->eavConfig = $eavConfig;
        $this->configProvider = $configProvider;
    }

    /**
     * @return array
     */
    public function getDefaultData()
    {
        return $this->fieldsDefaultProvider->getDefaultData();
    }

    /**
     * @return Field[]
     */
    public function getFieldConfig()
    {
        if ($this->fieldsConfig === null) {
            $this->fieldsConfig = $this->fieldSingleton->getConfig(
                $this->storeManager->getStore()->getId()
            );
        }

        return $this->fieldsConfig;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $subject
     * @param array $elements
     * @param string $providerName name of the storage container used by UI component
     * @param string $dataScopePrefix
     * @param array $fields
     *
     * @return array|null
     * @see \Magento\Checkout\Block\Checkout\AttributeMerger:getFieldConfig to understand wth is going on here
     *
     */
    public function beforeMerge(
        \Magento\Checkout\Block\Checkout\AttributeMerger $subject,
                                                         $elements,
                                                         $providerName,
                                                         $dataScopePrefix,
        array $fields = []
    ) {
        if (!$this->checkoutConfig->isEnabled()) {
            return null;
        }

        $defaultData = $this->getDefaultData();
        $fieldConfig = $this->getFieldConfig();
        $inheritedAttributes = $this->fieldSingleton->getInheritedAttributes();

        foreach ($elements as $attributeCode => &$attributeConfig) {
            if (isset($defaultData[$attributeCode])) {
                $attributeConfig['default'] = $defaultData[$attributeCode];
            }

            if (isset($inheritedAttributes[$attributeCode])) {
                $parent = $inheritedAttributes[$attributeCode];
                $attributeConfig = $this->prepareInheritedAttributeConfig($attributeConfig, $parent, $fieldConfig);
            }

            if (isset($fieldConfig[$attributeCode]) && $attributeCode != 'country_id') {
                $field = $fieldConfig[$attributeCode];

                if (!(int)$field->getData('enabled')) {
                    unset($elements[$attributeCode]);
                    unset($fields[$attributeCode]);
                    if ($attributeCode === 'region') {
                        unset($elements['region_id']);
                        unset($fields['region_id']);
                    }
                    continue;
                }

                /** @var \Amasty\Checkout\Model\Field $field */
                $this->layoutProcessorPlugin->setOrder($attributeCode, $field->getData('sort_order'));
                $attributeConfig = $this->prepareExtraAttributeConfig($attributeConfig, $field);
            }
        }
        unset($attributeConfig);
        if (isset($elements['telephone'])) {
            $fields['telephone']['component'] = 'Dyson_SinglePageCheckout/js/form/element/telephone';
        }
        if (isset($elements['postcode'])) {
            $fields['postcode']['component'] = 'Dyson_SinglePageCheckout/js/form/element/delivery-message';
        }

        if (isset($elements['postcode'])) {
            $fields['postcode']['config']['elementTmpl'] = 'Dyson_SinglePageCheckout/form/element/postcode';
        }

        $elements = $this->addCustomFields($fieldConfig, $elements, $dataScopePrefix);

        return [$elements, $providerName, $dataScopePrefix, $fields];
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $subject
     * @param array $config
     *
     * @return array
     */
    public function afterMerge(\Magento\Checkout\Block\Checkout\AttributeMerger $subject, $config)
    {
        if (!$this->checkoutConfig->isEnabled()) {
            return $config;
        }
        $fieldConfig = $this->getFieldConfig();
        $defaultData = $this->getDefaultData();

        foreach ($config as $code => $configItem) {
            if (isset($fieldConfig[$code])) {
                $config[$code]['sortOrder'] = $fieldConfig[$code]->getData('sort_order');
            }
        }

        if (isset($config['postcode']) && isset($fieldConfig['postcode'])) {
            $config['postcode']['skipValidation'] = !$fieldConfig['postcode']->getData('required');
        }

        if (isset($config['region_id'])) {
            $config['region_id']['component'] = 'Amasty_CheckoutCore/js/form/element/region';
            if (!empty($defaultData['region_id'])) {
                $config['region_id']['default'] = $defaultData['region_id'];
            }
        }

        if (isset($config['street']) && $this->configProvider->isAddressSuggestionEnabled()) {
            $config['street']['children'][0]['component'] = 'Amasty_GoogleAddressAutocomplete/js/form/element/autocomplete';
        }

        return $config;
    }

    /**
     * @param array $fieldConfig
     * @param array $elements
     * @param string $dataScopePrefix
     *
     * @return array
     */
    private function addCustomFields($fieldConfig, $elements, $dataScopePrefix)
    {
        if (!strpos($dataScopePrefix, '.custom_attributes') !== false) {
            $countOfCustomFields = CustomFieldsConfigInterface::COUNT_OF_CUSTOM_FIELDS;
            $index = CustomFieldsConfigInterface::CUSTOM_FIELD_INDEX;

            for ($index; $index <= $countOfCustomFields; $index++) {
                $customFieldIndex = 'custom_field_' . $index;

                if (isset($fieldConfig[$customFieldIndex]) && $fieldConfig[$customFieldIndex]->getEnabled() == 1) {
                    $field = $fieldConfig[$customFieldIndex];

                    $customAttributeName = CustomAttributesDataInterface::CUSTOM_ATTRIBUTES
                        . '.' . $customFieldIndex . '_' . $dataScopePrefix;

                    $elements[$customAttributeName] = $field->getData();
                    $elements[$customAttributeName]['visible'] = '1';
                    $elements[$customAttributeName]['formElement'] = 'input';
                    $elements[$customAttributeName]['dataType'] = 'text';
                    $elements[$customAttributeName]['sortOrder'] = $field->getSortOrder();
                    $elements[$customAttributeName]['validation']['required-entry'] = (bool)$field->getRequired();
                }
            }
        }

        return $elements;
    }

    /**
     * @param array $attributeConfig
     * @param string $parent
     * @param array $fieldConfig
     *
     * @return mixed
     */
    private function prepareInheritedAttributeConfig($attributeConfig, $parent, $fieldConfig)
    {
        if (isset($fieldConfig[$parent])) {
            $attributeConfig['sortOrder'] = $fieldConfig[$parent]->getData('sort_order');
            $attributeConfig['visible'] = $fieldConfig[$parent]->getData('enabled');

            if ($fieldConfig[$parent]->getData('label') != $fieldConfig[$parent]->getData('default_label')) {
                $attributeConfig['label'] = $fieldConfig[$parent]->getData('label');
            }
        }

        return $attributeConfig;
    }

    /**
     * @param array $attributeConfig
     * @param \Amasty\Checkout\Model\Field $field
     *
     * @return mixed
     */
    private function prepareExtraAttributeConfig($attributeConfig, $field)
    {
        $attributeConfig['sortOrder'] = $field->getData('sort_order');
        $attributeConfig['visible'] = $field->getData('enabled');
        $attributeConfig['required'] = $field->getData('required');
        $attributeConfig['validation']['required-entry'] = (bool)$field->getData('required');

        $label = $field->getData('label');

        if ($label != $field->getData('default_label')) {
            $attributeConfig['label'] = $label;
        }

        return $attributeConfig;
    }
}
