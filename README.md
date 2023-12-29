# Dyson Amasty Checkout Extension

Dyson_AmastyCheckoutExtension is an extension of an Amasty module, built to enable an 
easier method of updating files that we may frequently need to change/update.

Almost all the files in this module have an original counterpart in the Amasry_Checkout.

####Installing
For this module to work, the Amasty_Checkout, Amasty_Base and Amasty_Geoip 
modules must be integrated in app/code at the same time. The code for these files can 
be found in the folder amastysrc in this module. 

When installing this module, copy the Amasty folder in amastysrc to your app/code folder
in the market you're working in. 

Almost all customization of the Amasty module is in the module via plugin/xml changes,
giving us our own templates and javascript files to edit, instead of editing the original
files from the Amasty plugin itself.

Main exception is the Amasty/Checkout/view/frontend/web/template/onepage/2columns.html 
file which has been edited by G&V but remains in the core module by Amasty.

####Styling

The styling of all the components is done in dyson-theme-leap so you will need to composer 
require the necessary branch / commit in order to get the checkout to look as it should. 
Initial dev work has been done in the branch feature/jc/AIe9yhN1/2107-2818-single-page-checkout

##Dev Work

####Customising fields

Editing the order of the checkout fields should be done in the Amasty admin section for all
fields that exist in the Magento core (first name, last name, telephone etc.). However,
custom fields, and editing other elemtns of core fields should remain edited in the CustomCheckoutFields module found in the individual
market's app/code/Dyson/ folder. In the LayoutProcessor file of this module we will continue
to create and edit custom fields for the market on a bespoke basis.

####Templates / JS changes
If a change request comes in which requires the modification of a template file we don't 
have in this module, but exists in the Amasty core file, we will need to make a copy in 
our file and make sure that the Amasty reference to it's original file is switched to our 
new one.

The main location this is done in is in Plugin/Block/LayoutProcessor.php in our module.
Hopefully, most changes required will be in templates referenced in this file, so all you
need to do is create a new template or js file in our module and change the reference
in the LayoutProcessor.



