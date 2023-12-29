# Dyson_SinglePageCheckout

## Summary

Dyson_SinglePageCheckout modifies the behaviour of Amasty_Checkout (Amasty One Page Checkout). 

This module provides the following functionality:

- Accordian-based checkout step system.

*Throughout this doc 'soft enabled' and 'soft disabled' refers to system config setting for Amasty One Step Checkout, that is enabled or disabled, i.e. the module itself is enabled in both states.*

## Dependencies

The main Amasty Checkout source is found in `app/code` and should not be altered. This module has hard dependencies on the following modules:

- `Amasty_Checkout`
- `Amasty_Base`
- `Amasty_Geoip`


## Knockout component and template replacements

Order of component and template replacement implementations, from ideal to less-than-ideal:

1. Layout XML - Favoured if possible - most discoverable method. `amasty_checkout.xml` layout handle is only loaded if Amasty Checkout module is soft enabled.
2. LayoutProcessor plug - Less favoured, too many modules with LayoutProcessors can be     
3. Via requirejs-config.js - Least favourable because it's not very discoverable and hard to trace, but sometimes this is the only option. 

### Safe fallback if Amasty Checkout is 'soft' disabled

Although the module depends on these modules mentioned above, there can be eventualities where in an emergency Amasty Checkout may be 'soft' disabled on a production site (i.e. the system config setting, not the module). For this reason any code complementing Amasty Checkout must really be wrapped in a conditional check that the config is set to enabled to make changes. This allows us to make changes non-destructively and allow to fall-back to Magento Checkout if disabled.

Any component overrides to stock Magento components and component templates must be included conditionally depending on Amasty Checkout's soft enabled/disabled state.

Checking the Amasty Checkout soft enabled state:

#### JS
There's a global attached to the window that can be used for requirejs-config.js for mixins/components/templates or anywhere:

`var amasty_enabled = !window.amasty_checkout_disabled;`

#### PHP

Via Amasty's Checkout config:
- Inject: `\Amasty\Checkout\Model\Config $checkoutConfig`
- Enabled check: `$this->checkoutConfig->isEnabled()`