=== Marketplace for WooCommerce ===
Contributors: wpcodefactory, anbinder, karzin, omardabbas, kousikmukherjeeli
Tags: woocommerce, marketplace, multivendor, vendors
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.5.7
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Let users sell on your store.

== Description ==

**Marketplace for WooCommerce** lets users sell on your store once they become vendors, earning commissions for their sales.

**Check some of its features:**

* Vendors can customize their public pages.
* Admins can setup the url slug of vendors public pages.
* Admins can choose a custom label for vendors.
* Users have to apply to become vendors.
* Admins can setup vendors capabilities to upload files, view orders, publish products automatically and more.
* Admins can block vendors at any time.
* Setup commissions by percentage or fixed value.
* Commissions can be manually or automatically created on order complete, processing, you choose it.
* Vendor's products have a tab displaying info about the vendor, like its logo and description.

== Frequently Asked Questions ==

= Where are the plugin's settings? =

Visit "WooCommerce > Settings > Marketplace".

= Are there any widgets available? =

**Vendor products filter** - Filters Marketplace vendor products. It is only displayed on shop page.

= How can I contribute? Is there a GitHub repository? =

If you are interested in contributing - head over to the [Marketplace for WooCommerce plugin GitHub Repository](https://github.com/algoritmika/marketplace-for-woocommerce) to find out how you can pitch in.

= How can I help translating it? =

You can do it through [translate.wordpress](https://translate.wordpress.org/projects/wp-plugins/marketplace-for-woocommerce).

= What are the shortcodes available? =

**`[alg_mpwc_vendor_img]`**: Vendor image, as store's logo or vendor gravatar, most probably used on **Vendors > Product loop info > Info's content** option. Shortcode params:

* **`img_type`**: Type of image returned. Values allowed: `gravatar` or `store_logo`.
* **`gravatar_size`**: Size of image if `img_type` is set as `gravatar`.
* **`logo_style`**: Image style if `img_type` is set as `store_logo`.
* **`vendor_id`**: Id from vendor.
* **`post_id`**: Id from product.

= What are the filters available? =

**`alg_mpwc_loop_vendor_info_hook`**: Manages where the vendor info, provided by the **Vendors > Product loop info > Info's content** option, will be displayed on product loop.

Default value:

`woocommerce_after_shop_loop_item`

Example 1:

`add_filter( 'alg_mpwc_loop_vendor_info_hook', function ( $hook ) {
	$hook = 'woocommerce_before_shop_loop_item_title';
	return $hook;
} );`


== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > Marketplace".

== Screenshots ==

== Changelog ==

= 1.5.7 - 27/03/2024 =
* Fix - Dynamic property php warning is being triggered multiple times.
* WC tested up to: 8.7.

= 1.5.6 - 24/11/2023 =
* Update change log.

= 1.5.5 - 24/11/2023 =
* WC tested up to: 8.3.
* Tested up to: 6.4.

= 1.5.4 - 12/10/2022 =
* Fix - Array to string conversion in `class-alg-mpwc-vendor-role.php` on line 433.
* WC tested up to: 7.0.

= 1.5.3 - 08/06/2022 =
* Dev - Add more safe-checks when sending commissions emails.
* WC tested up to: 6.5.
* Tested up to: 6.0.

= 1.5.2 - 08/03/2022 =
* Dev - Add `alg_mpwc_send_commission_notification_email` filter.
* WC tested up to: 6.3.

= 1.5.1 - 16/02/2022 =
* Fix - Error: Call to a member function get_formatted_name() on bool.
* Create `alg_mpwc_commission_notification_email_to` filter.
* Tested up to: 5.9.
* WC tested up to: 6.2.

= 1.5.0 - 10/01/2022 =
* Dev - Vendors Options - Registration - Checkbox text - Now checkbox is not displayed, if checkbox text is empty.
* Dev - Vendors Options - Product Loop Info - Info's content - Admin settings field's type changed to the `textarea`.
* Dev - Code refactoring.
* WC tested up to: 6.0.

= 1.4.9 - 01/12/2021 =
* Dev - Add `alg_mpwc_loop_vendor_info_hook_priority` to setup the priority hook where the vendor info will be displayed on product loop.

= 1.4.8 - 30/11/2021 =
* Dev - Add `alg_mpwc_loop_vendor_info_hook` to setup where the vendor info will be displayed on product loop.

= 1.4.7 - 29/11/2021 =
* Dev - Create `[alg_mpwc_vendor_img]` shortcode.
* Dev - Vendors - Product loop info - Create "Info's content" option.

= 1.4.6 - 26/11/2021 =
* Fix - Remove bulk actions dropdown from vendors orders admin page.
* Fix - Own vendor post statuses are showing wrong results on admin.
* Fix - Links from vendor do not work in some environments.
* WC tested up to: 5.9.

= 1.4.5 - 04/10/2021 =
* Fix - Vendor can't access some content via ajax on frontend.
* WC tested up to: 5.7.

= 1.4.4 - 21/09/2021 =
* Fix - Commissions - Compute discounts.
* Fix - Checkout - "SyntaxError: Unexpected end of JSON input".

= 1.4.3 - 31/08/2021 =
* Fix - Properly flushing rewrite rules on plugin activation now.
* Fix - Commissions - Fix commission total amount style in value column.

= 1.4.2 - 30/08/2021 =
* Fix - Vendor can't see his own product on admin.
* Dev - Vendors - Create `alg_mpwc_post_types_allowed_to_vendor_on_admin` filter to manage the post types allowed to vendor on admin. Default to `array( 'acf-field-group', 'acf-field' )`.
* Dev - `[vendor_rating]` shortcode added.
* Dev - Vendors - Public Page - Rating - `%vendor_id%` placeholder added.
* Dev - Vendors - Public Page - Rating - Class in the default value updated (from `alg_mpwc_vendor_rating` to `alg-mpwc-vendor-rating`).
* Dev - Vendors - Product Tab - "Content" option added.

= 1.4.1 - 25/08/2021 =
* Fix - Remove WooCommerce menu from admin for vendors.
* Fix - Improve methods of preventing vendor role from accessing not allowed content.
* Dev - Setup auto deploy on GitHub.

= 1.4.0 - 25/08/2021 =
* Fix - Properly flushing rewrite rules on plugin activation now.
* Fix - Vendor caps fixed.
* Dev - Vendors - Public Page - "Rating" options added.
* WC tested up to: 5.6.

= 1.3.6 - 17/08/2021 =
* Fix - Flushing rewrite rules on version update now.
* Fix - Flushing rewrite rules on saved settings now.
* Fix - Admin settings description fixed (in "Vendors > Registration").
* Dev - Minor code refactoring and clean up.

= 1.3.5 - 10/08/2021 =
* Fix - Warnings from CMB2 and cmb-field-select2 Composer packages in php 8.0.
* Fix - Check if WooCommerce exists.
* Simplify composer setup.
* WC tested up to: 5.5.
* Tested up to: 5.8.

= 1.3.4 - 23/12/2020 =
* Fix - `Alg_MPWC_Vendor_Role()` - `manages_media_deleting()` - Fixed.
* WC tested up to: 4.8.
* Tested up to: 5.6.

= 1.3.3 - 08/10/2020 =
* Dev - Orders - "Related Commissions" meta box - Now checking if related commission post exists (and "No related commissions found" message added).
* Dev - Composer update.
* Tested up to: 5.5.
* WC tested up to: 4.5.

= 1.3.2 - 03/03/2020 =
* Fix - Vendor emails - Not sending empty table emails anymore (in case of zero order total and "Create zero commissions" option disabled).

= 1.3.1 - 26/02/2020 =
* Fix - Composer - `webdevstudios/cmb2` replaced with `cmb2/cmb2`

= 1.3.0 - 26/02/2020 =
* Fix - Vendors - "Call to undefined function get_editable_roles()" error fixed when saving settings.
* Fix - Commission admin settings - Possible "Trying to get property 'display_name' of non-object" notice fixed.
* Dev - Orders - "Related Commissions" meta box added.
* Dev - Commissions - "Create zero commissions" option added.
* Dev - Admin Settings - Descriptions updated etc.
* Dev - Duplicate call to `autoload.php` removed from `alg_mpwc_start_plugin()`.
* Dev - Minor code refactoring and clean up.
* Dev - Composer dependencies updated.
* Domain path changed from `languages` to `langs`.
* Requires at least: 5.0.
* Tested up to: 5.3.
* WC tested up to: 3.9.

= 1.2.7 - 02/07/2019 =
* Fix - `create_function()` calls removed.
* Dev - Composer updated.
* Tested up to: 5.2.

= 1.2.6 - 06/05/2019 =
* Tested up to: 5.1
* WC tested up to: 3.6
* Fix missing menu on author pages

= 1.2.5 - 28/10/2018 =
* Fix commission currency totals on emails
* Add {order_date} template variable

= 1.2.4 - 28/10/2018 =
* Fix commissions query on emails
* Show correct commission currency on emails

= 1.2.3 - 26/10/2018 =
* Create option to send email to vendors

= 1.2.2 - 24/10/2018 =
* Create option to group commissions by author
* Add option to create commissions for each item separately
* Reorganize commissions settings

= 1.2.1 - 08/08/2018 =
* Change the way 'blocked vendors' work as it is overwriting meta_query. Now blocked vendors are added to a option called 'alg_mpwc_blocked_users' and it's added to 'author__not_in' wp_query parameter in order to hide their products

= 1.2.0 - 04/08/2018 =
* Additional check added in `fix_empty_variation_product_price()`.
* My account "Marketplace" tab content updated.
* Commission manager - If commissions is not paid, changing its status to `refunded` (instead of `need-refund`).
* Commissions - "Default commission status" option added.
* Commission status taxonomy - "Reserved" status added.

= 1.1.12 - 10/06/2018 =
* Put input and span on the same line on 'Apply for becoming a vendor'

= 1.1.11 - 01/05/2018 =
* Add action 'alg_mpwc_vendor_marketplace_tab_content' for tab content

= 1.1.10 - 25/04/2018 =
* Update plugin

= 1.1.9 - 25/04/2018 =
* Fix empty variation product price

= 1.1.8 - 24/04/2018 =
* Add new option to allow vendors to access admin

= 1.1.7 - 23/04/2018 =
* Fix string 'See your public page'

= 1.1.6 - 17/04/2018 =
* Fix products filter on commissions admin page

= 1.1.5 - 16/04/2018 =
* Improve the regexp solution to filter products on commissions
* Fix white space on template

= 1.1.4 - 29/03/2018 =
* Add "WC tested up to"
* Update "Tested up to"
* Add "WC requires at least"

= 1.1.3 - 29/03/2018 =
* Fix variations authorship
* Fix commissions totals

= 1.1.2 - 22/12/2017 =
* Add refund commissions status (need refund and refunded)
* Add option to bulk update refund commissions status
* Add screen option to exclude refund commissions from totals
* Add screen option to ignore pagination to calculate commissions totals

= 1.1.1 - 14/12/2017 =
* Fix commissions total

= 1.1.0 - 08/12/2017 =
* Add option to recalculate commissions
* Display commissions total

= 1.0.10 - 07/12/2017 =
* Fix missing file

= 1.0.9 - 07/12/2017 =
* Fix missing file
* "Tested up to" updated

= 1.0.8 - 06/12/2017 =
* Fix plugin name

= 1.0.7 - 06/12/2017 =
* Fix missing file
= 1.1.0 - 08/12/2017 =
* Add option to recalculate commissions
* Display commissions total

= 1.0.6 - 01/12/2017 =
* Add product filter on commissions list page on admin

= 1.0.5 - 06/11/2017 =
* Allow vendor fiels to be edited on frontend plugin
* Fix commissions status not being created automatically
* Add option to redirect vendors to admin on login
* Remove all dashboard widgets
* Add filter "alg_mpwc_commission_fixed_value" for commission with fixed values

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

= 1.0.0 =
This is the first release of the plugin.
