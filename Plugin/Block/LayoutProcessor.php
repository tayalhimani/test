<?php

namespace Dyson\SinglePageCheckout\Plugin\Block;

use Amasty\CheckoutCore\Model\Config;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Amasty\CheckoutCore\Model\Config\Source\CustomerRegistration;

class LayoutProcessor implements LayoutProcessorInterface
{
    const BILLING_ADDRESS_POSITION = 2;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Amasty\CheckoutGiftWrap\Model\Gift\Messages
     */
    private $giftMessages;

    /**
     * @var \Amasty\CheckoutCore\Api\FeeRepositoryInterface
     */
    private $feeRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Amasty\CheckoutDeliveryDate\Model\ConfigProvider
     */
    private $deliveryDate;

    /**
     * @var \Amasty\CheckoutDeliveryDate\Model\Delivery
     */
    private $delivery;

    /**
     * @var \Amasty\CheckoutDeliveryDate\Model\DeliveryDateProvider
     */
    private $deliveryDateProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\CheckoutCore\Plugin\Checkout\Block\Checkout\AttributeMerger
     */
    private $attributeMerger;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    private $subscriber;

    /**
     * @var \Amasty\CheckoutCore\Model\Config
     */
    private $checkoutConfig;

    /**
     * @var \Amasty\CheckoutCore\Model\Utility
     */
    private $utility;

    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * @var \Amasty\CheckoutCore\Model\ModuleEnable
     */
    private $moduleEnable;

    /**
     * @var LayoutWalkerFactory
     */
    private $walkerFactory;

    /**
     * @var LayoutWalker
     */
    private $walker;

    /**
     * @var \Amasty\CheckoutCore\Model\AdditionalFieldsManagement
     */
    private $additionalFieldsManagement;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var \Amasty\CheckoutStyleSwitcher\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Amasty\CheckoutGiftWrap\Model\ConfigProvider
     */
    private $giftConfigProvider;

    /**
     * @var \Dyson\SinglePageCheckout\Helper\Data
     */
    private $singlePageCheckoutHelper;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        CheckoutHelper $checkoutHelper,
        \Amasty\CheckoutGiftWrap\Model\Messages $giftMessages,
        \Amasty\CheckoutCore\Api\FeeRepositoryInterface $feeRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Amasty\CheckoutDeliveryDate\Model\ConfigProvider $deliveryDate,
        StoreManagerInterface $storeManager,
        \Amasty\CheckoutDeliveryDate\Model\Delivery $delivery,
        \Amasty\CheckoutCore\Plugin\Checkout\Block\Checkout\AttributeMerger $attributeMerger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Amasty\CheckoutCore\Model\Config $checkoutConfig,
        \Amasty\CheckoutCore\Model\Utility $utility,
        \Amasty\CheckoutCore\Model\ModuleEnable $moduleEnable,
        \Amasty\CheckoutCore\Block\Onepage\LayoutWalkerFactory $walkerFactory,
        \Amasty\CheckoutCore\Model\AdditionalFieldsManagement $additionalFieldsManagement,
        \Amasty\Base\Model\Serializer $serializer,
        \Amasty\CheckoutStyleSwitcher\Model\ConfigProvider $configProvider,
        \Amasty\CheckoutGiftWrap\Model\ConfigProvider $giftConfigProvider,
        \Amasty\CheckoutDeliveryDate\Model\DeliveryDateProvider $deliveryDateProvider,
        \Dyson\SinglePageCheckout\Helper\Data $singlePageCheckoutHelper
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->priceCurrency = $priceCurrency;
        $this->giftMessages = $giftMessages;
        $this->feeRepository = $feeRepository;
        $this->checkoutSession = $checkoutSession;
        $this->deliveryDate = $deliveryDate;
        $this->deliveryDateProvider = $deliveryDateProvider;
        $this->delivery = $delivery;
        $this->storeManager = $storeManager;
        $this->attributeMerger = $attributeMerger;
        $this->customerSession = $customerSession;
        $this->subscriber = $subscriber;
        $this->checkoutConfig = $checkoutConfig;
        $this->utility = $utility;
        $this->moduleEnable = $moduleEnable;
        $this->walkerFactory = $walkerFactory;
        $this->additionalFieldsManagement = $additionalFieldsManagement;
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
        $this->giftConfigProvider = $giftConfigProvider;
        $this->singlePageCheckoutHelper = $singlePageCheckoutHelper;
    }

    /**
     * @inheritdoc
     */
    public function process($jsLayout)
    {
        if ($this->singlePageCheckoutHelper->getCityEnableField()) {
            if (!isset($jsLayout['components']['checkoutProvider']['dictionaries']['city'])) {
                $jsLayout['components']['checkoutProvider']['dictionaries']['city'] = $this->getCityOptions();
            }
        }

        if (!$this->checkoutConfig->isEnabled()) {
            return $jsLayout;
        }

        /**
         * Add a new Continue to Payment button
         */
        $customField = [
            'component' => 'Magento_Ui/js/form/components/button',
            'config' => [
                'template' => 'Dyson_SinglePageCheckout/gv/continue-to-payment-template' // Merging AmastyCheckoutExtension
            ],
            'title' => __('Continue to payment'),
            'sortOrder' => 505,
            'actions' => []
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['continue_to_payment'] = $customField;;

        $this->walker = $this->walkerFactory->create(['layoutArray' => $jsLayout]);

        $this->setCheckoutTemplate();

       // if ($blockInfo = $this->checkoutConfig->getBlockInfo('block_order_summary')) {
            //$blockInfo = $this->serializer->unserialize($blockInfo);
           // $this->walker->setValue('{CHECKOUT}.>>.sidebar.>>.summary.config.summaryLabel', $blockInfo['value']);
       // }

        $this->setRequiredField();
        $this->processAdditionalStepLayout();

        if (!$this->checkoutConfig->getAdditionalOptions('discount')) {
            $this->walker->unsetByPath('{PAYMENT}.>>.afterMethods.>>.discount');
        }

        $this->processGiftLayout();
        $this->processShippingLayout();

       /* $this->walker->setValue(
            'components.checkoutProvider.amdiscount.isNeedToReloadShipping',
            $this->checkoutConfig->isReloadCheckoutShipping()
        );*/

        /**
         * Start of GV edits by SB
         */
        //Change to use GV templates whether or not the checkout items editable is enabled Merging for IN Market
        if ($this->singlePageCheckoutHelper->isPincodeModuleEnabled()) {
            if ($this->checkoutConfig->isCheckoutItemsEditable()) {
                $this->walker->setValue(
                    '{CART_ITEMS}.>>.details.component',
                    'Dyson_SinglePageCheckout/js/gv/checkout-summary-item-details'
                );
                $this->walker->setValue('{CART_ITEMS}.component', 'Dyson_SinglePageCheckout/js/gv/checkout-summary-cart-items');
            } else {
                $this->walker->setValue(
                    '{CART_ITEMS}.>>.details.component',
                    'Dyson_SinglePageCheckout/js/gv/checkout-summary-item-details'
                );
                $this->walker->setValue('{CART_ITEMS}.component', 'Dyson_SinglePageCheckout/js/gv/checkout-summary-cart-items');
            }
        }

        //Change totals templates and child js to GV ones
        // $this->walker->setValue(
        //     '{SIDEBAR}.>>.summary.>>.totals.config.template',
        //     'Dyson_AmastyCheckoutExtension/gv/summary-totals'
        // );

        //js to override existing templates
        // $this->walker->setValue(
        //     '{SIDEBAR}.>>.summary.>>.totals.children.subtotal.component',
        //     'Dyson_AmastyCheckoutExtension/js/gv/checkout-summary-subtotal'
        // );
        // $this->walker->setValue(
        //     '{SIDEBAR}.>>.summary.>>.totals.children.shipping.component',
        //     'Dyson_AmastyCheckoutExtension/js/gv/checkout-summary-shipping'
        // );
        // $this->walker->setValue(
        //     '{SIDEBAR}.>>.summary.>>.totals.children.grand-total.component',
        //     'Dyson_AmastyCheckoutExtension/js/gv/checkout-summary-grand-total'
        // );
        // $this->walker->setValue(
        //     '{SIDEBAR}.>>.summary.>>.totals.children.discount.component',
        //     'Dyson_AmastyCheckoutExtension/js/gv/summary-discount'
        // );

        //new element for edit order button
        // $this->walker->setValue(
        //     '{SIDEBAR}.>>.summary.>>.totals.children.edit-order.component',
        //     'Dyson_AmastyCheckoutExtension/js/gv/edit-order'
        // );
        // $this->walker->setValue(
        //     '{SIDEBAR}.>>.summary.>>.totals.children.edit-order.sortOrder',
        //     200
        // );
        /**
         * End of GV edits by SB
         */

        $this->walker->setValue(
            'components.checkoutProvider.config.defaultShippingMethod',
            $this->checkoutConfig->getDefaultShippingMethod()
        );
        $this->walker->setValue(
            'components.checkoutProvider.config.defaultPaymentMethod',
            $this->checkoutConfig->getDefaultPaymentMethod()
        );

        $this->walker->setValue(
            'components.checkoutProvider.config.minimumPasswordLength',
            $this->checkoutConfig->getMinimumPasswordLength()
        );

        $this->walker->setValue(
            'components.checkoutProvider.config.requiredCharacterClassesNumber',
            $this->checkoutConfig->getRequiredCharacterClassesNumber()
        );

        $this->agreementsMoveToReviewBlock();
        $this->moveDiscountToReviewBlock();
        $this->moveTotalToEnd();

        $fields = $this->walker->getValue('{SHIPPING_ADDRESS_FIELDSET}.>>');

        $this->prepareFields($fields);
        $this->sortFields($fields);

        $this->walker->setValue(
            '{SHIPPING_ADDRESS_FIELDSET}.>>',
            $fields
        );

        $this->processMultipleShippingAddress();

        $this->processBillingAddressRelocation();

        return $this->walker->getResult();
    }

    /**
     * Set onepage Checkout template
     */
    private function setCheckoutTemplate()
    {
        $layout = 'virtual';
        if (!$this->checkoutSession->getQuote()->isVirtual()) {
            $layout = $this->checkoutConfig->getLayoutTemplate();
        }
        $this->walker->setValue('{CHECKOUT}.config.template', 'Amasty_CheckoutCore/onepage/' . $layout);

        $this->walker->setValue('{CHECKOUT}.config.additionalClasses', $this->getAdditionalCheckoutClasses());
    }

    /**
     * @return string
     */
    protected function getAdditionalCheckoutClasses()
    {
        $position = $this->configProvider->getPlaceOrderPosition();
        $frontClasses = '';
        switch ($position) {
            case \Amasty\CheckoutStyleSwitcher\Model\Config\Source\PlaceButtonLayout::FIXED_TOP:
                $frontClasses .= ' am-submit-fixed -top';
                break;
            case \Amasty\CheckoutStyleSwitcher\Model\Config\Source\PlaceButtonLayout::FIXED_BOTTOM:
                $frontClasses .= ' am-submit-fixed -bottom';
                break;
            case \Amasty\CheckoutStyleSwitcher\Model\Config\Source\PlaceButtonLayout::SUMMARY:
                $frontClasses .= ' am-submit-summary';
                $this->walker->setValue('{SIDEBAR}.>>.place-button.component', 'Amasty_CheckoutStyleSwitcher/js/view/place-button');
                break;
        }

        return $frontClasses;
    }

    /**
     * Additional fields in the Summary Block (Review Block)
     */
    protected function processAdditionalStepLayout()
    {
        $fieldsValue = $this->additionalFieldsManagement->getByQuoteId($this->checkoutSession->getQuoteId());
        $this->processNewsletterLayout($fieldsValue);

        if (!$this->checkoutConfig->getAdditionalOptions('comment')) {
            $this->walker->unsetByPath('{ADDITIONAL_STEP}.>>.comment');
        } elseif ($fieldsValue->getComment()) {
            $this->walker->setValue('{ADDITIONAL_STEP}.>>.comment.default', $fieldsValue->getComment());
        }

        if ($this->checkoutConfig->getAdditionalOptions('create_account') === CustomerRegistration::NO
            || $this->checkoutSession->getQuote()->getCustomer()->getId() !== null
        ) {
            $this->walker->unsetByPath('{ADDITIONAL_STEP}.>>.register');
            $this->walker->unsetByPath('{ADDITIONAL_STEP}.>>.date_of_birth');
        } else {
            if (!$this->checkoutConfig->canShowDob()) {
                $this->walker->unsetByPath('{ADDITIONAL_STEP}.>>.date_of_birth');
            } elseif ($fieldsValue->getDateOfBirth()) {
                $this->walker->setValue('{ADDITIONAL_STEP}.>>.date_of_birth.default', $fieldsValue->getDateOfBirth());
            }
            if ($this->checkoutConfig->getAdditionalOptions('create_account') === CustomerRegistration::AFTER_PLACING) {
                $registerChecked = (bool)$this->checkoutConfig->getAdditionalOptions('create_account_checked');
                if ($fieldsValue->getRegister() !== null) {
                    $registerChecked = (bool)$fieldsValue->getRegister();
                }

                $this->walker->setValue('{ADDITIONAL_STEP}.>>.register.checked', $registerChecked);
                if ($registerChecked) {
                    $this->walker->setValue('{ADDITIONAL_STEP}.>>.register.value', $registerChecked);
                }

                $fieldsValue->setRegister($registerChecked);
            } else {
                $this->walker->unsetByPath('{ADDITIONAL_STEP}.>>.register');
                $registerChecked = true;
            }

            $this->walker->setValue('{ADDITIONAL_STEP}.>>.date_of_birth.visible', $registerChecked);
        }

        $fieldsValue->save();
    }

    /**
     * Visibility and status if the subscribe checkbox
     *
     * @param \Amasty\CheckoutCore\Model\AdditionalFields $fieldsValue
     */
    private function processNewsletterLayout($fieldsValue)
    {
        $newsletterConfig = (bool)$this->checkoutConfig->getAdditionalOptions('newsletter');

        if ($newsletterConfig && $this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            $this->subscriber->loadByCustomerId($customerId);
            $newsletterConfig = !$this->subscriber->isSubscribed();
        }

        if (!$newsletterConfig) {
            $this->walker->unsetByPath('{ADDITIONAL_STEP}.>>.subscribe');
        } else {
            $subscribeCheck = (bool)$this->checkoutConfig->getAdditionalOptions('newsletter_checked');
            if ($fieldsValue->getSubscribe() !== null) {
                $subscribeCheck = (bool)$fieldsValue->getSubscribe();
            }
            $this->walker->setValue('{ADDITIONAL_STEP}.>>.subscribe.checked', $subscribeCheck);
            if ($subscribeCheck) {
                $this->walker->setValue('{ADDITIONAL_STEP}.>>.subscribe.value', $subscribeCheck);
            }

            $fieldsValue->setSubscribe($subscribeCheck);
        }
    }

    /**
     * Gift Wrap and Gift Messages processor
     */
    private function processGiftLayout()
    {
        if (!$this->giftConfigProvider->isGiftWrapEnabled()) {
            $this->walker->unsetByPath('{GIFT_WRAP}');
        } else {
            $amount = $this->giftConfigProvider->getGiftWrapFee();

            $rate = $this->storeManager->getStore()->getBaseCurrency()->getRate(
                $this->storeManager->getStore()->getCurrentCurrency()
            );

            $amount *= $rate;

            $formattedPrice = $this->priceCurrency->format($amount, false);

            $this->walker->setValue('{GIFT_WRAP}.description', __('Gift wrap %1', $formattedPrice));
            $this->walker->setValue('{GIFT_WRAP}.fee', $amount);

            $fee = $this->feeRepository->getByQuoteId($this->checkoutSession->getQuoteId());

            if ($fee->getId()) {
                $this->walker->setValue('{GIFT_WRAP}.checked', true);
            }
        }

        if (empty($messages = $this->giftMessages->getGiftMessages())) {
            $this->walker->unsetByPath('{GIFT_MESSAGE_CONTAINER}');
        } else {
            $itemMessage = $quoteMessage = [
                'component' => 'uiComponent',
                'children' => [],
            ];
            $checked = false;

            /** @var \Magento\GiftMessage\Model\Message $message */
            foreach ($messages as $key => $message) {
                if ($message->getId()) {
                    $checked = true;
                }

                $node = $message
                    ->setData('item_id', $key)
                    ->toArray(['item_id', 'sender', 'recipient', 'message', 'title']);

                $node['component'] = 'Amasty_CheckoutGiftWrap/js/view/additional/gift-messages/message';
                if ($key) {
                    $itemMessage['children'][] = $node;
                } else {
                    $quoteMessage['children'][] = $node;
                }
            }
            $this->walker->setValue('{GIFT_MESSAGE_CONTAINER}.>>.checkbox.checked', $checked);
            $this->walker->setValue('{GIFT_MESSAGE_CONTAINER}.>>.item_messages', $itemMessage);
            $this->walker->setValue('{GIFT_MESSAGE_CONTAINER}.>>.quote_message', $quoteMessage);
        }
    }

    /**
     * Shipping address component, shipping and Delivery Date form processor
     */
    private function processShippingLayout()
    {
        /*
         * remove shipping information from sidebar,
         * on onestep checkout you already see shipping information
         */
        $this->walker->unsetByPath('{SIDEBAR}.>>.shipping-information');

        if (!$this->deliveryDate->isDeliveryDateEnabled() || $this->checkoutSession->getQuote()->isVirtual()) {
            $this->walker->unsetByPath('{AMCHECKOUT_DELIVERY_DATE}');
        } else {
            $this->walker->setValue(
                '{AMCHECKOUT_DELIVERY_DATE}.>>.date.amcheckout_days',
                $this->deliveryDate->getDeliveryDays()
            );

            if ($this->deliveryDate->isDeliveryDateRequired()) {
                $this->walker->setValue(
                    '{AMCHECKOUT_DELIVERY_DATE}.>>.date.validation.required-entry',
                    'true'
                );
            }

            $this->walker->setValue('{AMCHECKOUT_DELIVERY_DATE}.>>.date.required-entry', true);
            $this->walker->setValue(
                '{AMCHECKOUT_DELIVERY_DATE}.>>.time.options',
                $this->deliveryDate->getDeliveryHours()
            );
            $delivery = $this->deliveryDateProvider->findByQuoteId($this->checkoutSession->getQuoteId());

            $amcheckoutDelivery = [
                'date' => $delivery->getData('date'),
                'time' => $delivery->getData('time'),
                'comment' => $delivery->getData('comment'),
            ];
            $this->walker->setValue('components.checkoutProvider.amcheckoutDelivery', $amcheckoutDelivery);

            if (!$this->deliveryDate->isCommentEnabled()) {
                $this->walker->unsetByPath('{AMCHECKOUT_DELIVERY_DATE}.>>.comment');
            } else {
                $comment = (string)$this->deliveryDate->getDefaultComment();
                $this->walker->setValue('{AMCHECKOUT_DELIVERY_DATE}.>>.comment.placeholder', $comment);
            }
        }
    }

    /**
     * The method sets field as required
     */
    private function setRequiredField()
    {
        $attributeConfig = $this->attributeMerger->getFieldConfig();
        if (isset($attributeConfig['postcode'])) {
            $this->walker->setValue(
                '{SHIPPING_ADDRESS_FIELDSET}.>>.postcode.skipValidation',
                !$attributeConfig['postcode']->getData('required')
            );

            if ($this->walker->isExist('{SHIPPING_ADDRESS_FIELDSET}.>>.postcode.validation.required-entry')) {
                $this->walker->setValue(
                    '{SHIPPING_ADDRESS_FIELDSET}.>>.postcode.skipValidation',
                    !$this->walker->getValue('{SHIPPING_ADDRESS_FIELDSET}.>>.postcode.validation.required-entry')
                );
            }
        }
        $components = $this->walker->getValue('{SHIPPING_ADDRESS_FIELDSET}.>>');
        foreach ($attributeConfig as $field => $config) {
            if (isset($components[$field]) && !isset($components[$field]['skipValidation'])) {
               // $components[$field]['skipValidation'] = !$config->isRequired();
                //$components[$field]['validation']['required-entry'] = $config->isRequired();
            }
        }

        $this->walker->setValue('{SHIPPING_ADDRESS_FIELDSET}.>>', $components);
    }

    /**
     * The method moves to review block
     */
    private function agreementsMoveToReviewBlock()
    {
        $paymentListComponent = $this->walker->getValue('{PAYMENT}.>>.payments-list');
        if ($paymentListComponent) {
            $checkedAgreement = $this->checkoutConfig->isSetAgreements();
            $agreementsHasToMove = $this->checkoutConfig->getPlaceDisplayTermsAndConditions();

            if ($checkedAgreement && $agreementsHasToMove == Config::VALUE_ORDER_TOTALS) {
                $agreementComponentConfigs =
                    $paymentListComponent['children']['before-place-order']['children']['agreements'];
                $agreementComponent = ['agreements' => $agreementComponentConfigs];
                $additionalChildren = $this->walker->getValue('{ADDITIONAL_STEP}.>>');
                $additionalChildren =
                    $this->utility->arrayInsertBeforeKey($additionalChildren, 'comment', $agreementComponent);
                $this->walker->setValue('{ADDITIONAL_STEP}.>>', $additionalChildren);
                $this->walker->unsetByPath('{PAYMENT}.>>.payments-list.>>.before-place-order.>>.agreements');
                //replace agreement validation
                $this->walker->setValue(
                    '{PAYMENT}.>>.additional-payment-validators.>>.agreements-validator.component',
                    'Amasty_CheckoutCore/js/view/validators/agreement-validation'
                );
            }
        }
    }

    /**
     * The method moves discount inputs (coupons, rewards, etc.) to review block
     */
    private function moveDiscountToReviewBlock()
    {
        $summaryAdditional = [];
        $summaryAdditional['discount'] = $this->walker->getValue('{PAYMENT}.>>.afterMethods.>>.discount');
        $this->walker->unsetByPath('{PAYMENT}.>>.afterMethods.>>.discount');
        $summaryAdditional['rewards'] = $this->walker->getValue('{PAYMENT}.>>.afterMethods.>>.rewards');
        $this->walker->unsetByPath('{PAYMENT}.>>.afterMethods.>>.rewards');
        $summaryAdditional['gift-card'] = $this->walker->getValue('{PAYMENT}.>>.afterMethods.>>.gift-card');
        $this->walker->unsetByPath('{PAYMENT}.>>.afterMethods.>>.gift-card');

        $summaryAdditional = array_filter($summaryAdditional);
        $this->walker->setValue('{SIDEBAR}.>>.summary_additional.>>', $summaryAdditional);
    }

    /**
     * Move totals to the end of summary block
     */
    private function moveTotalToEnd()
    {
        $summary = $this->walker->getValue('{SIDEBAR}.>>.summary.>>');
        $totalsSection = $summary['totals'];
        unset($summary['totals']);
        $summary['totals'] = $totalsSection;
        $this->walker->setValue('{SIDEBAR}.>>.summary.>>', $summary);
    }

    /**
     * @param array $fields
     */
    private function prepareFields(&$fields)
    {
        foreach ($fields as $code => $field) {
            if ($code === 'customer_attributes_renderer' || $code === 'order-attributes-fields') {
                foreach ($field['children'] as $attributeCode => $attribute) {
                    $fields[$attributeCode] = $attribute;
                    if ($code === 'customer_attributes_renderer') {
                        $fields[$attributeCode]['sortOrder'] -= 2000;
                    }

                    $fields[$code]['fields'][] = $attributeCode;
                }

                unset($fields[$code]['children']);
            }
        }
    }

    /**
     * @param array $fields
     */
    private function sortFields(&$fields)
    {
        uasort($fields, function($firstField, $secondField) {
            if (isset($firstField['sortOrder']) && isset($secondField['sortOrder'])) {
                return $firstField['sortOrder'] - $secondField['sortOrder'];
            }
        });
    }

    /**
     * Change shipping address from grid to dropdown
     */
    private function processMultipleShippingAddress()
    {
        $shippingAddressConfig = (bool)$this->checkoutConfig->getMultipleShippingAddress();

        if ($shippingAddressConfig && $this->customerSession->isLoggedIn()) {
            $additionalFieldsets = $this->walker->getValue('{SHIPPING_ADDRESS_FIELDSET}');

            $shippingAddressData = [
                'displayArea' => 'address-list',
                'component' => 'Amasty_CheckoutCore/js/view/shipping-address/shipping-address',
                'provider' => 'checkoutProvider',
                'deps' => 'checkoutProvider',
                'dataScopePrefix' => 'shippingAddress'
            ];

            $this->walker->setValue(
                '{SHIPPING_ADDRESS}.>>.address-list',
                $shippingAddressData
            );

            $this->walker->setValue(
                '{SHIPPING_ADDRESS}.>>.address-list.>>.additional-fieldsets',
                $additionalFieldsets
            );
        }
    }

    /**
     * Transfer billing address from Payment to Shipping Address section
     */
    private function processBillingAddressRelocation()
    {
        if ($this->configProvider->getBillingAddressDisplayOn() == self::BILLING_ADDRESS_POSITION) {
            $billingAddress = $this->walker->getValue('{PAYMENT}.>>.afterMethods.>>.billing-address-form');
            $this->walker->setValue('{SHIPPING_ADDRESS}.>>.billing-address-form', $billingAddress);

            $afterMethodsChilds = $this->walker->getValue('{PAYMENT}.>>.afterMethods.>>');
            unset($afterMethodsChilds['billing-address-form']);
            $this->walker->setValue('{PAYMENT}.>>.afterMethods.>>', $afterMethodsChilds);
        }
    }

    /**
     * Get country options list.
     *
     * @return array
     */
    private function getCityOptions()
    {
        return  $this->singlePageCheckoutHelper->getCityOptions();
    }
}
