=== Marketplace for WooCommerce ===
Contributors: algoritmika,karzin,anbinder
Tags: woocommerce,marketplace,multivendor,vendors
Requires at least: 4.4
Tested up to: 4.7
Stable tag: 1.0.5
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Let users sell on your store

== Description ==

**Marketplace for WooCommerce** lets users sell on your store once they become vendors, earning commissions for their sales

**Check some of its features:**

* Vendors can customize their public pages
* Admins can setup the url slug of vendors public pages
* Admins can choose a custom label for vendors
* Users have to apply to become vendors
* Admins can setup vendors capabilities to upload files, view orders, publish products automatically and more
* Admins can block vendors at any time
* Setup commissions by percentage or fixed value,
* Commissions can be manually or automatically created on order complete, processing, you choose it.
* Vendor's products have a tab displaying info about the vendor, like its logo and description

== Frequently Asked Questions ==

= Where are the plugin's settings? =
Visit WooCommerce > Settings > Marketplace.

= Are there any widgets available? =
**Vendor products filter** - Filters Marketplace vendor products. It is only displayed on shop page

= How can I contribute? Is there a github repository? =
If you are interested in contributing - head over to the [Marketplace for WooCommerce plugin GitHub Repository](https://github.com/algoritmika/marketplace-for-woocommerce) to find out how you can pitch in.

= How can I help translating it? =
You can do it through [tranlslate.wordpress](https://translate.wordpress.org/projects/wp-plugins/marketplace-for-woocommerce)

== Installation ==

1. Upload the entire 'marketplace-for-woocommerce' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Start by visiting plugin settings at WooCommerce > Settings > Marketplace.

== Screenshots ==

== Changelog ==

= 1.0.5 - 16/10/2017 =
* Allow vendor fiels to be edited on frontend plugin

= 1.0.4 - 05/09/2017 =
* Fix bulk author changing
* Fix vendor url

= 1.0.3 - 23/08/2017 =
* Remove filter 'alg_mpwc_commission_value' to filter commission value
* Remove filter 'alg_mpwc_commission_currency' to filter commission currency
* Add action 'alg_mpwc_insert_commission' to run immediately after a commission is created
* Remove total value from commissions

= 1.0.2 - 21/08/2017 =
* Create option to manage product tab
* Create options to customize vendor's public page
* Create option to manage vendor link on product loop

= 1.0.1 - 17/08/2017 =
* Add currency to commission
* Add option to change product vendor/author
* Remove edit action from commissions bulk actions
* Add info about public page on admin settings if using ugly permalinks
* Create action 'alg_mpwc_vendor_admin_fields' for optionally adding vendor fields
* Create filter 'alg_mpwc_commission_value' to filter commission value
* Create filter 'alg_mpwc_commission_currency' to filter commission currency

= 1.0.0 - 18/05/2017 =
* Initial Release.

== Upgrade Notice ==

= 1.0.5 =
* Allow vendor fiels to be edited on frontend plugin