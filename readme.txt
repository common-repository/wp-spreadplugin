=== WP-Spreadplugin ===
Contributors: pr3ss-play
Author: Thimo Grauerholz
Tags: spreadshirt,wordpress,plugin,shop,store,shirt,t-shirt,integration,online store,online shop
Requires at least: 3.3
Tested up to: 6.2
Stable tag: 4.8.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin uses the Spreadshirt API to list articles and let your customers order articles of your Spreadshirt shop using Spreadshirt order process.

== Description ==

This plugin uses the Spreadshirt API to display the contents of your Spreadshirt Shop or Spreadshop via Spreadshirts’ API. It is made for SEO compatibility, so each article has its unique URL and uses Google structured data.

The basket is api-driven, so until you or the customer clicks checkout the customer stays on your website. Only after clicking the checkout button the customer is redirected to the basket of your Spreadshirt Shop / Spreadshop. The whole payment and order process is handled by Spreadshirt.

Using the plugin is quite easy!
You only need to fill the settings, add the shortcode `[spreadplugin]` to a new or existing page or post, click `Rebuild Cache` and your shop is ready!

**Current features**
* Compatible with old and new partner area (api) of Spreadshirt
* Uses Spreadshirts' own basket
* Designer Sketchomat already integrated to allow customers to customize your products (optional)
* Own product pages with custom URLs (SEO)
* Basket Widget
* Enhanced filter with extended shortcodes
* Choose color and sizes
* Multi-Language support
* Social buttons
* Enhanced zoom
* Infinity Scrolling
* Unique canonical, title, meta description with enabled **Yoast SEO** and **Rank Math**
and many more...

**What do you need**

* Wordpress
* Spreadshirt shop

**Demo**

https://www.mommyshirt.com/tshirts-fuer-frauen/

== Installation ==

1. Install using wordpress plugin installer
2. Activate the plugin through the **Plugins** menu in WordPress
3. Edit default settings using **Spreadplugin Settings**
4. Create a new site or edit an existing site
5. Insert shortcode `[spreadplugin]`
6. Go back to **Spreadplugin Settings** and click **Rebuild cache**. Please wait until the cache has been rebuild
7. Done

== Frequently asked questions ==

= I want to use a different currency. Is this possible? =

Please use a different country or language setting and click `Rebuild Cache`

= How to display one pre-defined category per page? =

1. Please see `How to display one category per page? (Custom categories)`
2. Use `shop_productcategory` and one of your category names as values. See possible values in Spreadplugin Settings page under: `Sample shortcode with category`

= How to disable the social buttons? =

Add or change in the [spreadplugin] code the value from `shop_social="1"` to `shop_social="0"` or use the settings page.

= How to default sort? =

Add or change in the `[spreadplugin]` code the value from `shop_sortby=""` to `shop_sortby="name"`. Available sort options are name, price, recent. Or use the settings page.

= It shows old articles =

Please go to the settings page in the admin panel of the plugin and click "Rebuild cache".

= I want to use more than one shop on the same website =

Please use the extended shortcode.
This will overwrite the default plugin settings just for the page, where you have added this shortcode.

= The infinity scroll always repeats all of my articles on and on and on and.. =

This might be a problem resulting of a special URL structure (permalinks). In this case, please have a look at your wordpress settings -> permalinks.
If you don't want to change this setting to another one, please let me know the structure to check it.

= How can I change the language of different shop instances? =

If you change the language in your wordpress installation, the language of the plugin changes, too. Well, but you can change the language only for the plugin by selecting your language in the spreadplugin options, now. If you have multiple pages with different shops on it and want to use a different language on each page, please use the shortcode and extend your already used shortcode by - for example `shop_language="de_DE"` - possible values are: de_DE, en_GB, fr_FR, nl_NL, nn_NO, nb_NO, da_DK, it_IT. Your new shortcode could look like this: `[spreadplugin shop_language="de_DE"]`

= After updating wordpress, detail page doesn't work anymore! =

Please save Spreadplugin settings again.

= Display Designer only =

Paste `[spreadplugin-designer]` into your desired page, that's it. You may also use the Spreadplugin Basket widget to display shopping cart contents.

= Can I use different shops? =

Sure, just use `[spreadplugin shop_id="XXX"]` on one page and `[spreadplugin shop_id="ZZZ"]` on another and `Rebuild Cache` afterwards.

= I have created a custom topic (Themen in german) for my products, how can I display the content? =

Use `[spreadplugin shop_topic="XXX"]` whereas XXX stands for your topic id. If you navigate to the topic in your Spreadshop, you can see the url in your browser changes to something like `?collection=XXX&`. The `XXX` is your topic id.

= How can I remove some elements on product detail pages? =

Add following class to the html element you want to be removed: `spreadplugin-remove-on-detail`. You can also enclose more than one element with a div for example.

= I want to show a design only =

Use `[spreadplugin shop_idea="XXX"]` whereas XXX stands for your idea id. If you navigate to the topic in your Spreadshop, you can see the url in your browser changes to something like `idea=XXX&`. The `XXX` is your design/idea id.

= I want to display the designer with pre-defined products or... =

Use `[spreadplugin-designer designid="XXX" appearanceid="XXX" producttypeid="XXX" viewid="XXX"]` you can use or arrange the properties as you like. For example to just show gray pullovers use: `[spreadplugin-designer appearanceid="363" producttypeid="5"]`.

= I want to display the detail page of a single article =

Use `[spreadplugin shop_article_detail="XXX"]` to display the detail page of a single product. As value `XXX` use the detail page url like `baby-wickel-world-champion-baby-bio-langarm-body-5d77BAa2e447425742X4bfd1-816-1`.

== Screenshots ==

1. Article view
2. Settings page

== Known Bugs ==

* If using W3TC, please disable page cache for shop pages.

== Upgrade Notice ==

= 4.1.3 =
Please rebuild cache.

= 4.0.2 =
Please rebuild cache.

= 4.0.0 =
For compatiblity reasons, there are new product categories. Please change them accordingly.

== Changelog ==

= 4.8.9 =
Adjustments for new Spreadshirt API

= 4.8.8 =
Google structured data fix

= 4.8.7 =
Google structured data fix

= 4.8.6 =
Bugfix

= 4.8.5 =
Bugfix

= 4.8.4 =
API endpoints changed

= 4.8.3 =
API endpoints changed

= 4.8.2 =
API Key and Secret are not required anymore.

= 4.8.1 =
Designer was unable to add product to basket.

= 4.8.0 =
Switched to JSON query and responses for Spreadshirt requests. XML API is deprecated.

= 4.7.5 =
Use wordpress session fixes

= 4.7.3 =
Use wordpress diacritics functions

= 4.7.2 =
Stickers couldn't be displayed

= 4.7.0 =
`shop_article_detail` shortcode attribute allows you to specify a single article. It display the detail page instead of `shop_article` which displays the kategory view.

= 4.6.9 =
Currency for basek item rows

= 4.6.8 =
If you want to exclude elements from detail page, enclose them with `<div class="spreadplugin-remove-on-detail">...</div>` and enable. This applies now to all occurencies.

= 4.6.7 =
- New setting added to open basket on every "Add to basket"
- Added cart total with shipping costs

= 4.6.6 =
Yoast fix, thanks to cmolyn.

= 4.6.5 =
Spreadshirt API changes

= 4.6.4 =
Use Designer with shortcodes - see faq

= 4.6.3 =
Deprecated messages

= 4.6.1 =
Added product name to title again

= 4.6.0 =
Added coupon widget so you can display coupon code

= 4.5.2 =
Updated to latest spreadshirt code

= 4.5.1 =
Bugfix in shopping cart

= 4.5.0 =
Added idea to show just one idea with `shop_idea="5dXXabfd5fd3eXXXb4dbf4cf"`

= 4.4.10 =
Adjusted to spreadshirt changes

= 4.4.9 =
Added empty basket url, if basket is empty, customer gets redirected to website.

= 4.4.8 =
Bugfixes

= 4.4.7 =
Some scripts are disabled if functions aren't used

= 4.4.6 =
Cache fix

= 4.4.5 =
Health-check fix

= 4.4.4 =
Encoding fix

= 4.4.2 =
Title meta improvement

= 4.4.1 =
Basket item language fix

= 4.4.0 =
Basket scrollbar is now sticky to avoid flickering.
If you want to exclude elements from detail page, enclose them with `<div class="spreadplugin-remove-on-detail">...</div>` and enable

= 4.2.8 =
PHP warnings reduced

= 4.2.7 =
Minor update to allow addons

= 4.2.6 =
Inch calculation removed

= 4.2.2 =
Added more style options.

= 4.2.1 =
Fatal Bugfix. Sorry!

= 4.2.0 =
Use .html in url if permalink '/%postname%.html' enabled. Tested with e.g. Plugin '.html after URL' https://de.wordpress.org/plugins/html-after-url/

= 4.1.9 =
Bugfixes

= 4.1.8 =
Added new view for detail page

= 4.1.7.4 =
Display custom themes, topics of products

= 4.1.7 =
Added Support for Rank Math - thanks to René!

= 4.1.6 =
Yoast SEO fix

= 4.1.5 =
Javascript code improvements

= 4.1.4 =
CSS improvements

= 4.1.3 =
CSS improvements and split article name from product name

= 4.1.2 =
SEO improvements

= 4.1.1 =
Bugfix

= 4.1.0 =
Added additional input fields for "Add to basket" button on detail page

= 4.0.9 =
Changed background image color to show only on zoom images.

= 4.0.8 =
Adjusted the api requests

= 4.0.7 =
Added Canadian Dollars. Please choose CA in Spreadplugin Options

= 4.0.6 =
Google structured data fix

= 4.0.5 =
Improved Yoast SEO compatiblity

= 4.0.4 =
SEO friendly URLs: Article name is now included in the url.

= 4.0.3 =
Reenabled custom shop categories again. Please rebuild cache. You can use [spreadplugin shop_category="XXX"] and put any string in there, which you can find by navigating your Spreadshop and looking at the query string in the address bar of the browser behind ?q=XXX

= 4.0.2 =
API changes at Spreadshirt

= 4.0.1 =
Bugfix

= 4.0.0 =
Now works with new partner area

= 3.12.3 =
CURL changes

= 3.12.0 =
Bugfix

= 3.11.7 =
Bugfix

= 3.11.6 =
Minor changes

= 3.11.5 =
Added translation for "Loading..." text

= 3.11.4 =
Badge for new articles (added in -1 month)

= 3.11.3 =
Added model images. Use shortcode `shop_modelids="PRODUCTTYPEID-APPEARANCEID:MODELID,..."`. Possible ModelIds can be inspected on Spreadshirt Marketplace images at attribute modelId. Please see `How to display the model images` in FAQ.

= 3.11.2 =
MagnificPopup disabled as it is not supported anymore

= 3.11.0 =
Bugfix

= 3.10.8 =
Add the suggested privacy policy text to the policy postbox

= 3.10.7 =
Replaced Tablomat with the new Sketchomat

= 3.10.6 =
Updates to Spreadshirt API

= 3.10.4 =
Bugfix in SEO

= 3.10.3 =
* Improved SEO with Yoast SEO
* Disabled experimental feature

= 3.10.2 =
Prepared for API changes in 2018 (https://www.spreadshirt.com/blog/2017/12/21/changes-api-usage/)

= 3.10.1 =
Added CSS Classes for price and currency. If you like, you can now hide the currency.

= 3.10.0 =
Experimental feature for improved SEO (separate detail page). If you have questions, feel free to post in the forum.

= 3.9.44 =
* Added compatibility for tielabs page builder

= 3.9.43 =
* Added "widget" class to widget

= 3.9.42 =
* Spreadplugin Designer refreshes Cart now when loaded via [spreadplugin-designer] shortcode and basket embedded as widget

= 3.9.41 =
* Added new shortcode `[spreadplugin-designer]` to display designer only.

= 3.9.40 =
* Prepare for changes in spreadshirt api beginning 28th feburary 2017

= 3.9.39 =
* Hide available colors of products which are out of stock, use following Custom CSS: .spreadplugin-not-on-stock {display:none}

= 3.9.38 =
* Changed price in min view to net price

= 3.9.36 =
* Increased "Rebuild cache" speed

= 3.9.35 =
* enabled filters and sorting again

= 3.9.33 =
* Added Permalink rules for language in URL

= 3.9.31 =
* "Product on stock check" option in Spreadplugin settings added. Now stock check can be switched on or off. If on and product out of stock, you aren't able to order it. If you disable this option, you are able to order it regardless of the stock status.

= 3.9.30 =
* Removed fly-over animation in min-view

= 3.9.29 =
* Enabled appearance change for those who didn't rebuild cache yet

= 3.9.28 =
* Stock states enabled again, rebuild cache is highly recommended

= 3.9.24 =
* Enabled compatiblity with W3 Total Cache plugin by disabling it for shop pages

= 3.9.23 =
* CSS changes

= 3.9.21 =
* Disabled isotope when in Design view

= 3.9.20 =
* Added isotope for better grid display
* Added reading of all products in shop
* Added color selection in min-view. Can be disabled by CSS using .spreadplugin-article .color-wrapper {display:none}
* Added new icons

= 3.9.10 =
* Bugfix reading articles when having EU and US shop

= 3.9.9 =
* Option to change "Back to shop" link in Spreadshirt checkout

= 3.9.8.10 =
* Permalink added for blog post day and name structure

= 3.9.8.9 =
* Bugfix

= 3.9.8.8 =
* Language Bugfix
* Bugfix for using with WPBakery Visual Composer

= 3.9.8.6 =
* Bugfix for image swapper

= 3.9.8.5 =
* Permalinks update hook changed

= 3.9.8.3 =
* Language fix

= 3.9.8.2 =
* Added article price check to use fallback when reading articles with/without language setting.

= 3.9.8.1 =
* Fix for american language / use default language us_US if none set.

= 3.9.8 =
* Siteorigin Pagebuilder workaround added
* Fix for importing wrong/old articles

= 3.9.7.9 =
* Minor language and additional bugfixes

= 3.9.7.7 =
* Single article bugfix

= 3.9.7.6 =
* SSL restrictions removed

= 3.9.7.2 =
* Checkout fixes
* Checkout uses shopId now for some users having customized spreadshirt checkout with logo on top. See http://www.spreadshirt.net/-C9397 for more informations.

= 3.9.7.1 =
* Anchor added so you can jump directly to spreadplugin shop if set
* Basket Widget integrated and not display separatly in plugin list
* Temporarly removed category and sorting boxes, sorry but will return later

= 3.9.7 =
* Code optimizations
* Pretty urls for detail pages. If you are having problems with pretty urls, please disable and enable spreadplugin again.
* Bugfix when on home page

= 3.9.6.4 =
* Function doAjax didn't read shop_language shortcode

= 3.9.6.3 =
* If shop_language is set in page, it's getting read now

= 3.9.6.1 =
* Basket auto refresh interval disabled

= 3.9.6 =
* Added separate basket widget

= 3.9.5.2 =
* Url fix

= 3.9.5 =
* Bugfix in Tablomat

= 3.9.4.1 =
* Bugfix

= 3.9.4 =
* Updated integrated designer "Tablomat" to use the shop basket.
* Removed iframe Designer

= 3.9.3 =
* Bugfix for environments disabled wordpress globals (wp_query)

= 3.9.2 =
* Added close button for basket as user requested

= 3.9.1 =
* Read one article one when using shop_article

= 3.9 =
* Added new tag (shop_article) to just display one article

= 3.8.9 =
* Bugfix with not applied filters

= 3.8.8 =
* Bugfix with doubled content

= 3.8.7.9 =
* Added limit of max read articles. Now you can reduce the quantity of articles to be read.
* Bugfix for Users using Plugin "Page Builder by SiteOrigin"

= 3.8.7.8 =
* Added japanese thanks to schlafcola.de

= 3.8.7.7 =
* Added spanish, portuguese translation, thanks to schlafcola.de

= 3.8.7.6 =
* bugfixes

= 3.8.7.4 =
* Norway bugfix

= 3.8.7.3 =
* Minor Bugfixes

= 3.8.7 =
* Switched to tablomat to use with designer shop (premium). Please choose "Show Spreadshirt designs in the designer" in "Apperance" -> "Settings", if you don't want to display Spreadshirt Marketplace designs.

= 3.8.6.7 =
* Fixed english checkout link

= 3.8.6.6 =
* Fixed problems with the new checkout of spreadshirt

= 3.8.6.5 =
* Renamed some CSS

= 3.8.6.4 =
* Removed the caching block

= 3.8.6.3 =
* Tried to reduce caching problems with other plugins

= 3.8.6.2 =
* One XSS vulnerability fixed

= 3.8.6 =
* CSS fixes

= 3.8.5 =
* Changes in Spreadshirt API
* Added polish

= 3.8.4 =
* Added Brazil and Australia for US/Canada

= 3.8.3 =
* Minor Bugfixes

= 3.8.2 =
* Responsive detail page

= 3.8.1 =
* Minor Bugfixes

= 3.8 =
* Bugfix release. In some cases, not all articles are displayed and increased debugging.

= 3.7.9 =
* Bugfixing / tried sync from basket to designer again

= 3.7.8 =
* Bugfixing
* Modified minimal-view and added more effects. See it at http://www.alsterwasser-fisch.com/

= 3.7.7 =
* Sort by place is default again. Place is set via article order in api, which should represent your shop sorting.
* Zoom can be disabled completly

= 3.7.6 =
* Added shipping costs popup, please rebuild cache!

= 3.7.5b =
* Added new integrated designer shop called confomat (by spreadshirt). Choose from options between none, integrated designer shop (but shows your chosen design and marketplace designs, if you click on designs tab) and premium (if you have a premium account at spreadshirt with a designer shop activated)
* This is a beta release!

= 3.7.3 =
* Bugfix: On some newly created shops, the articles didn't get loaded completly.
* Bugfix: Error when reading articles with category definded.

= 3.7.1 =
* Bugfix

= 3.7 =
* New code for building caches. The cache is not build on first page load anymore. You have to click on "Rebuild cache" or "Save settings" to trigger cache building. Otherwise the product pages stay empty.

= 3.6.2 =
* Code modifications
* Small debug mode added. Please enable only if you experience problems (Settings menu)

= 3.6.1 =
* Bugfix in minimal view, Basket won't open.

= 3.6 =
* Added new minimal view - please be sure to improve the display by adding css
* Added new configuration option `shop_basket_text_icon` to enable or disable basket icon
* Some minor changes
* Support me by code improvements - if you've got some

= 3.5.8 =
* Language of the plugin is now selectable / changeable through shortcode. On questions please see faq
* Small error message when adding a product failes.
* Bugfixes

= 3.5.6.3 =
* Some minor fixes
* Added span tags for size and color label to disable them via css

= 3.5.6.1 =
* Inches on detail pages for US/CA
* Bugfixes

= 3.5.6 =
* Get rid of some error messages

= 3.5.5.6 =
* Https fixes

= 3.5.5.5 =
* Minor bug fix to get rid of php notices

= 3.5.5.3 =
* Print technique for detail page added

= 3.5.5.2 =
* Language fix french

= 3.5.5.1 =
* Added option to enable or disable lazy load

= 3.5.5 =
* New option to change the zoom behaviour. Please see spreadplugin options at Zoom type.

= 3.5.4 =
* New detail page. Added product details, size table...

= 3.5.3.4 =
* Changed URL style of detail page, so woocommerce installations are not harmed :)

= 3.5.3.3 =
* Bugfix: In some cases the detail pages of a product is empty

= 3.5.3.2 =
* Bugfix in Product detail pages

= 3.5.3.1 =
* Bugfix in shop URL

= 3.5.3 =
* Checkout-Language workaround added and set to shop country

= 3.5.2 =
* Replaced fancyBox 2 with magnific-popup

= 3.5 =
* Bugfixes

= 3.4.2 =
* Bugfixes

= 3.4.1 =
* Minor improvements

= 3.4 =
* Added close basket, when click outside
* Added new option to display product description under article
* Minor enhancements

= 3.3.1 =
* Italian translation added

= 3.3 =
* Beautiful flyover to basket animation

= 3.2 =
* Depending on stock state the size and color of a product will be hidden (beta) / removed

= 3.1.5 =
* Speed improvements by adding lazy image loading (only loads images when in viewport)

= 3.1.4 =
* Solved session basket problem: On some server configurations, the session couldn't be reused, so there was created a new session and so the basket contents were lost.
* Minor Bugfixes

= 3.1.3 =
* Bugfix: Translation problem in detail page fixed

= 3.1.2 =
* Bugfix: Pagination did not work in some conditions

= 3.1.1 =
* Bugfix: Basket has shown wrong prices, when quantity is higher than one

= 3.1 =
* Added own product pages (detail pages)

= 3.0.1 =
* Added new shortcode for displaying only specific designs in specific pages. Please refer faq.

= 3.0 =
* Basket added
* Minor bugfixes

= 2.9.6 =
* Minor bugfixes
* Added Text for german locale 'zzgl. Versandkosten' if extended price is choosen

= 2.9.5.1 =
Norwegian language added

= 2.9.5 =
* JS now loaded at the shortcode call.
* Timeout limit removed

= 2.9.4 =
Pagination bugfix: In some cases, the pagination doesn't work and always shows the first page.

= 2.9.3 =
Price format changed for USD

= 2.9.2 =
Bugfix: InfiniteScroll was not disabled correctly - it did show Javascript errors.

= 2.9.1 =
Sometimes no articles were displayed, when no designs are available. This has now been fixed.

= 2.9 =
* Add your own custom css in admin interface. This won't be overwritten by any spreadplugin update.
* Disable infinite scrolling in admin interface.
* Added edit article button, if designer shop id is added.

= 2.8.2 =
Bugfix: Wrong view was used as default

= 2.8.1 =
* Color picker for zoom image background / choose background color
* Default sorting changed

= 2.8 =
* Enhanced article zoom
* Price with tax now displayed only. Can be changed in options/settings page.
* Two images sizes now available (190 & 280 pixel)

= 2.7.6 =
Enhanced cache call method to get always newest API file, when deleting spreadplugin cache.

= 2.7.5 =
Bugfix: Script tried to display descriptions of the designs, which is currently not available from spreadshirt

= 2.7.4 =
Article description can now be displayed always. Use `shop_showdescription="1"` to enable or use settings.

= 2.7.3 =
Edit article now opens in fancybox when set `shop_checkoutiframe="2"`. It opens in a separate window by default, now. Thanks to grillwear-shop.de

= 2.7.2 =
* Extended admin page
* An must have update!

= 2.7.1 =
* Fix for `Better WP Security` users, which were unable to display the options page in some circumstances.

= 2.7 =
* New kick-ass retina social buttons
* Clean-up release
* Maybe the last release

= 2.6.2 =
* Bugfixes (Sorting reverted, can't solve it yet)

= 2.6.1 =
* Bugfixes (incl. sorting bug - hopefully ;-))
* Added short product description e.g. Women�s Classic T-Shirt, Men�s Classic T-Shirt,...

= 2.6 =
* New social buttons, if you don't like, just replace in the image directory with your own. See FAQ
* Speed improvements (got rid of facebook and twitter implementations)

= 2.5 =
* Settings page will now be used for all default settings. If you configure default settings, you'll just need the minimum shortcode `[spreadplugin]`.
If you extend your shortcode with additional settings, they will be used! All existing shortcodes may stay untouched at least your shop_locale is not empty.
If you receive a locale error, please add shop_locale="us_US" to your shortcode. Please refer `http://wordpress.org/extend/plugins/wp-spreadplugin/installation/`.
* Bugfix
* Please save your old css file. If it's from Version 2.2.x you can reuse it in 2.5.

= 2.2.1 =
* Sorting added 'weight'

= 2.2 =
* New sticky toolbar
* CSS & JS fixes

= 2.1.3 =
* Updated the design view
* SSL improvements - thanks goes to Marcus from sozenshirts.de for that note
* Minor bugfixes

= 2.1.2 =
Bugfix: Infinity scrolling doesn't work sometimes when in designs view

= 2.1 =
* Settings page added to regenerate cache because **Cache doesn't regenerate itself anymore** due to performance

= 2.0 =
* Added a new shortcode variable to by display designs by default. To enable change shop_display="0"` to shop_display="1"`.
Sample (active): http://lovetee.de/shop/
Sample (disabled/article view): http://lovetee.de/shop-articles/
* Added Pinterest / Thanks to shirtarrest.com
* Article category sub-filter
* CSS fixes

= 1.9.4 =
Click on zoom image doesn't open a separate window anymore. The article description is now displayed in a modal window on the website.

= 1.9.3 =
Edit for articles added, if you have a designer shop. Activate by changing shop_designershop="0" to shop_designershop="[DESIGNERSHOPID]".

= 1.9.2 =
* Bugfix: JS script took each form to submit an article to the basket :)
* Style: Changed some styles to fit most environments

= 1.9.1 =
Fancybox added to display checkout in a modal window. Activate by adding or changing `shop_checkoutiframe="0"` to `shop_checkoutiframe="2"`

= 1.9 =
* Ajax driven shop (Add products to the basket without reloading the whole content)
* Internal article cache extended to 2 hours. If you want to change, have a look at row 46 in spreadplugin.php and change the value.

= 1.8.4 =
Price formatting added

= 1.8.3 =
Compatibility update for using with 'Simple Facebook Connect'-Plugin

= 1.8.2a =
Dutch language added (nl_NL)

= 1.8.2 =
Compatibility update for < PHP 5

= 1.8.1 =
Translation for sorting added

= 1.8 =
* Added a new shortcode variable to sort the articles by default. To enable change shop_sortby=""` to shop_sortby="[name, price, recent]"`.
* Sorting select box added

= 1.7.4 =
* Custom url structures now possible

= 1.7.3 =
* Added a new shortcode variable to open the checkout window in an iframe. To enable change shop_checkoutiframe="0"` to shop_checkoutiframe="1"`.
* Bugfix

= 1.7.1 =
* Added a new shortcode variable to change the link targets. To enable change `shop_linktarget=""` to shop_linktarget="YOUR_IFRAME_NAME"`.
* You may hide the product category field by adding a style in the plugin css. E.g. .spreadplugin-items #productCategory {display:none}

= 1.7 =
* Own cache added (updates every 8 hours) - speed improvements.
* Product category now accessable
* Shortcode added for direct calls of a category. Add ` shop_productcategory=""` and fill with field value e.g. Women => ` shop_productcategory="Women"`

= 1.6.5 =
* Debugging things, no need to update

= 1.6.4 =
* Each article image has now a link to it's spreadshirt product details website. Use the shortcode to enable ` shop_enablelink="1"` or disable `shop_enablelink="0"` this behaviour (default is enabled).

= 1.6.1 =
* Added a new shortcode variable to disable social media buttons. Enable ` shop_social="1"` / Disable `shop_social="0"`

= 1.6 =
* Define a category to display with `shop_category=""`. Please have a look at the faq for getting the category id. In v2 I hope to have an admin interface which will help you with the configuration.

= 1.5 =
* Shows detailed product description when hovering the article image (mouseover)

= 1.4.2 =
* Zoom image now shows right color of views

= 1.4.1 =
* Size of views increased

= 1.4 =
* Different views of the article available (front, back, left, right)

= 1.3 =
* Language improvements (Thanks to Steve for helping me with french :))

= 1.2.9 =
* Skipping some errors when spreadshirt articles are no more readable (link dead?)

= 1.2.8 =
* Removed some error messages when in Wordpress debug mode

= 1.2.7 =
* I missed the multi language features of Twitter and Facebook, so sorry!

= 1.2.6 =
* Added Twitter share button. It pushes description text if available, else it just says 'Product'. Additionally, it says @URL to product.

= 1.2.5 =
* Added Facebook like button

= 1.2.2 =
* Enabled some error messages

= 1.2 =
* Spreadshirts "Free Color Selection"/Color limitation is now processed

= 1.1.3 =
* Show hide prices using stylesheet
* French language improvements

= 1.1 =
* jQuery compatibility improvements
* Currency display fix

= 1.0 =
