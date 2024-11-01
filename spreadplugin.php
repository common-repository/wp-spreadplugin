<?php
/**
 * Plugin Name:     WP-Spreadplugin
 * Plugin URI:      https://wordpress.org/plugins/wp-spreadplugin/
 * Version:         4.8.9
 * Author:          Thimo Grauerholz
 * Author URI:      https://www.mommyshirt.com/
 * Donate link:     https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EZLKTKW8UR6PQ
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:     spreadplugin
 * Domain Path:     /languages
 * Description:     This plugin uses the Spreadshirt API to list articles and let your customers order articles of your Spreadshirt shop using Spreadshirt order process.
 */

if (!defined('SPREADPLUGIN_AGENT')) {
    define('SPREADPLUGIN_AGENT', 'WP-Spreadplugin/4.8.9 (https://www.mommyshirt.com/; thimo@grauerholz.de)');
}

@ini_set('user_agent', SPREADPLUGIN_AGENT);

// disabled w3tc
// define('DONOTCACHEPAGE', true);
// define('DONOTCACHEDB', true);
// define('DONOTMINIFY', true);
// define('DONOTCDN', true);
// define('DONOTCACHEOBJECT', true);

/*
 * WP_Spreadplugin class
 */
if (!class_exists('WP_Spreadplugin')) {
    class WP_Spreadplugin
    {
        public static $shopOptions;
        public static $shopArticleSortOptions = array(
            'name',
            'price',
            'recent',
            //'weight',
        );
        public $defaultOptions = array(
            'shop_id' => '',
            'shop_locale' => '',
            // 'shop_api' => '',
            'shop_source' => '',
            // 'shop_secret' => '',
            'shop_limit' => '',
            'shop_social' => 0,
            'shop_enablelink' => '',
            'shop_productcategory' => '',
            'shop_sortby' => '',
            'shop_linktarget' => '',
            'shop_checkoutiframe' => 0,
            'shop_designershop' => '',
            'shop_designsbackground' => '',
            'shop_showdescription' => '',
            'shop_showproductdescription' => '',
            'shop_imagesize' => '',
            'shop_showextendprice' => '',
            'shop_zoomimagebackground' => '',
            'shop_infinitescroll' => '',
            'shop_customcss' => '',
            'shop_article' => '',
            'shop_article_detail' => '',
            'shop_view' => '',
            'shop_zoomtype' => '',
            'shop_lazyload' => '',
            'shop_language' => '',
            'shop_basket_text_icon' => '',
            'shop_debug' => '',
            'shop_sleep' => '',
            'shop_designer' => '',
            'shop_url_anchor' => '',
            'shop_url_productdetail_slug' => '',
            'shop_url_productdetail_page' => '',
            'shop_rscuwo' => '',
            'shop_backtoshopurl' => '',
            'shop_stockstates' => '',
            'shop_modelids' => '',
            'shop_category' => '',
            'shop_claimcheck1' => '',
            'shop_claimcheck2' => '',
            'shop_claimcheck3' => '',
            'shop_cartpaymenticons' => 0,
            'shop_topic' => '',
            'shop_idea' => '',
            'shop_additionalmods' => '',
            'shop_openbasketonadd' => 0,
        );

        protected static $worksWithLocale = true;
        protected static $userAgent = SPREADPLUGIN_AGENT;
        protected static $shopCache = 0; // Shop article cache - never expires

        public function __construct()
        {
            add_action('init', array(
                &$this,
                'startSession',
            ), 1);

            // load default languages
            add_action('plugins_loaded', array($this, 'loadDefaultLanguage'));

            add_action('wp_logout', array(
                &$this,
                'endSession',
            ));
            add_action('wp_login', array(
                &$this,
                'endSession',
            ));

            add_shortcode('spreadplugin', array(
                $this,
                'Spreadplugin',
            ));

            add_shortcode('spreadplugin-designer', array(
                $this,
                'SpreadpluginDesigner',
            ));

            // Ajax actions
            /*
             * add_action('wp_ajax_nopriv_mergeBasket', array( &$this,'mergeBaskets' )); add_action('wp_ajax_mergeBasket', array( &$this,'mergeBaskets' ));
             */
            add_action('wp_ajax_nopriv_myAjax', array(
                &$this,
                'doAjax',
            ));
            add_action('wp_ajax_myAjax', array(
                &$this,
                'doAjax',
            ));
            add_action('wp_ajax_nopriv_myCart', array(
                &$this,
                'doCart',
            ));
            add_action('wp_ajax_myCart', array(
                &$this,
                'doCart',
            ));
            add_action('wp_ajax_nopriv_myDelete', array(
                &$this,
                'doCartItemDelete',
            ));
            add_action('wp_ajax_myDelete', array(
                &$this,
                'doCartItemDelete',
            ));
            add_action('wp_ajax_rebuildCache', array(
                &$this,
                'doRebuildCache',
            ));

            add_action('wp_enqueue_scripts', array(
                &$this,
                'enqueueSomes',
            ));
            add_action('wp_head', array(
                &$this,
                'loadHead',
            ));
            add_action('wp_footer', array(
                &$this,
                'loadFoot',
            ));

            add_action('init', array(
                &$this,
                'addQueryVars',
            ));

            // add_action('after_switch_theme', array(&$this,'registerRewriteRules'));
            add_action('init', array(&$this, 'registerRewriteRules'));

            // Yoast SEO and SEO filters
            add_filter('wpseo_opengraph_url', array($this, 'overwriteWpSeoUrl'));
            add_filter('wpseo_canonical', array($this, 'overwriteWpSeoUrl'));
            add_filter('wpseo_metadesc', array($this, 'overwriteWpSeoDesc'));
            add_filter('wpseo_title', array($this, 'overwriteWpSeoTitle'));
            add_filter('wpseo_opengraph_type', array($this, 'overwriteWpSeoType'));
            add_filter('wpseo_opengraph_image', array($this, 'seoImageUrlHandler'));

            // Rank Math filters
            add_filter('rank_math/opengraph/url', array($this, 'overwriteWpSeoUrl'));
            add_filter('rank_math/frontend/canonical', array($this, 'overwriteWpSeoUrl'));
            add_filter('rank_math/frontend/description', array($this, 'overwriteRankMathDesc'));
            add_filter('rank_math/frontend/title', array($this, 'overwriteWpSeoTitle'));
            add_filter('rank_math/opengraph/type', array($this, 'overwriteWpSeoType'));
            add_filter('rank_math/opengraph/twitter/image', array($this, 'seoImageUrlHandler'));
            add_filter('rank_math/opengraph/facebook/image', array($this, 'seoImageUrlHandler'));

            add_filter('body_class', array($this, 'addBodyClassDetailPage'));

            // additional mods
            add_action('bcn_after_fill', array($this, 'addStaticBc'));
            add_filter('accordions_filter_content', array($this, 'removeAccordionsDetailPage'));
            add_filter('accordions_filter_title', array($this, 'removeAccordionsDetailPage'));
            add_filter('the_content', array($this, 'removeTitleDetailPage'));
            add_filter('the_content', array($this, 'removeClassesOnDetailPage'));

            // admin check
            if (is_admin()) {
                // Regenerate cache after activation of the plugin
                // register_activation_hook(__FILE__, array(&$this,'helperClearCacheQuery'));
                register_activation_hook(__FILE__, array(&$this, 'registerRewriteRules'));
                register_deactivation_hook(__FILE__, array(&$this, 'flushRewriteRules'));

                // add Admin menu
                add_action('admin_menu', array(
                    &$this,
                    'addPluginPage',
                ));
                // add Plugin settings link
                add_filter('plugin_action_links', array(
                    &$this,
                    'addPluginSettingsLink',
                ), 10, 2);

                add_action('admin_enqueue_scripts', array(
                    &$this,
                    'enqueueAdminJs',
                ));

                add_action('admin_init', array(&$this, 'privacyDeclarations'), 20);

                /*    // Place different and check for additional caches | Translate it
            $articleData = WP_Spreadplugin::getCacheArticleData();
            if (count($articleData) == 0 || $articleData == false) {
            add_action( 'admin_bar_menu', function() {
            global $wp_admin_bar;
            $wp_admin_bar->add_menu( array(
            'id'        => 'spreadshirt-cache-bar',
            'title'        => '<span style="background-color:red;padding:2px;border: 1px white solid;border-radius:5px;"> Spreadshirt Cache veraltet! </span>',
            'href'        =>    '/wp-admin/options-general.php?page=splg_options',
            'parent'    => 'top-secondary',
            'meta'        => array(
            'class'        => '',
            'title'        =>'Der Cache ist nicht aktuell..',
            ),
            ) );
            }, 1000 );
            }
            unset($articleData);
             */
            }
        }

        /**
         * Returns an instance of this class.
         */
        public static function get_instance()
        {
            if (null == self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * PHP 4 Compatible Constructor.
         */
        public function WP_Spreadplugin()
        {
            $this->__construct();
        }

        /**
         * Function Spreadplugin.
         *
         * @return string article display
         */
        public function Spreadplugin($atts)
        {
            $articleCleanData = array(); // Array with article informations for sorting and filtering
            $articleCleanDataComplete = array(); // Array with article informations for sorting and filtering
            $articleData = array();

            // get admin options (default option set on admin page)
            $conOp = $this->getAdminOptions();

            // shortcode overwrites admin options (default option set on admin page) if available
            $arrSc = shortcode_atts($this->defaultOptions, $atts);

            // replace options by shortcode if set
            if (!empty($arrSc)) {
                foreach ($arrSc as $key => $option) {
                    if ('' != $option) {
                        $conOp[$key] = $option;
                    }
                }
            }

            // setting defaults if needed
            self::$shopOptions = $conOp;
            self::$shopOptions['shop_source'] = (empty($conOp['shop_source']) ? 'net' : $conOp['shop_source']);
            self::$shopOptions['shop_limit'] = (empty($conOp['shop_limit']) ? 10 : intval($conOp['shop_limit']));
            self::$shopOptions['shop_locale'] = ''; // Workaround for older versions of this plugin
            self::$shopOptions['shop_imagesize'] = (0 == intval($conOp['shop_imagesize']) ? 190 : intval($conOp['shop_imagesize']));
            self::$shopOptions['shop_zoomimagebackground'] = (empty($conOp['shop_zoomimagebackground']) ? 'FFFFFF' : str_replace('#', '', $conOp['shop_zoomimagebackground']));
            self::$shopOptions['shop_infinitescroll'] = ('' == $conOp['shop_infinitescroll'] ? 1 : $conOp['shop_infinitescroll']);
            self::$shopOptions['shop_zoomtype'] = ('' == $conOp['shop_zoomtype'] ? 0 : $conOp['shop_zoomtype']);
            self::$shopOptions['shop_lazyload'] = ('' == $conOp['shop_lazyload'] ? 1 : $conOp['shop_lazyload']);
            self::$shopOptions['shop_debug'] = ('' == $conOp['shop_debug'] ? 0 : $conOp['shop_debug']);

            // Overwrite defaults if set (old vals)
            self::$shopOptions['shop_designer'] = (2 == self::$shopOptions['shop_designer'] ? self::$shopOptions['shop_designer'] = 1 : self::$shopOptions['shop_designer']);

            // Disable Zoom on min view, because of the new view - not on details page
            if (2 == self::$shopOptions['shop_view'] && !get_query_var(self::$shopOptions['shop_url_productdetail_slug'])) {
                self::$shopOptions['shop_zoomtype'] = 2;
            }

            if (get_query_var('productCategory')) {
                $c = get_query_var('productCategory');
                self::$shopOptions['shop_productcategory'] = $c;
            }

            if (!empty(self::$shopOptions['shop_productcategory'])) {
                self::$shopOptions['shop_productcategory'] = htmlspecialchars_decode(self::$shopOptions['shop_productcategory']);
            }

            // Workaround for some content editors
            if (!empty(self::$shopOptions['shop_productcategory'])) {
                self::$shopOptions['shop_productcategory'] = str_replace(array('"', '&quot;'), '', self::$shopOptions['shop_productcategory']);
            }

            if (get_query_var('articleSortBy')) {
                $c = urldecode(get_query_var('articleSortBy'));
                self::$shopOptions['shop_sortby'] = $c;
            }

            // check
            if (!empty(self::$shopOptions['shop_id'])) {
                $paged = (get_query_var('pagesp') ? get_query_var('pagesp') : 1);

                $offset = ($paged - 1) * self::$shopOptions['shop_limit'];

                // get article data
                $articleData = self::getCacheArticleData();

                if (1 == self::$shopOptions['shop_debug']) {
                    echo 'Stored Article Data RAW (0):<br>';
                    print_r($articleData);
                }

                // Add all those articles with no own designs and other cases - maybe overwrite them
                if (!empty($articleData)) {
                    foreach ($articleData as $arrDesigns) {
                        if (!empty($arrDesigns)) {
                            foreach ($arrDesigns as $articleId => $arrArticle) {
                                $articleCleanData[$articleId] = $arrArticle;
                                $urlified = self::urlify($arrArticle['name'] . '-' . $articleId);
                                $articleCleanDataComplete[$urlified] = $articleCleanDataComplete[$articleId] = $arrArticle;
                                $urlified2 = self::urlify($arrArticle['name'] . ' - ' . $arrArticle['productname'] . '-' . $articleId);
                                $articleCleanDataComplete[$urlified2] = $articleCleanDataComplete[$articleId] = $arrArticle;
                            }
                        }
                    }

                    if (1 == self::$shopOptions['shop_debug']) {
                        echo 'With some cases (2):<br>';
                        print_r($articleCleanData);
                    }
                }

                // filter
                if (is_array($articleCleanData)) {
                    // Single product
                    if (isset(self::$shopOptions['shop_article']) && self::$shopOptions['shop_article'] > 0 && array_key_exists(self::$shopOptions['shop_article'], $articleCleanData)) {
                        $articleCleanData = array(
                            self::$shopOptions['shop_article'] => $articleCleanData[self::$shopOptions['shop_article']],
                        );
                    }
                }

                /*
                 * 2014-06-22 Changed from place to id, place is not set anymore (and sort direction to desc) 2014-07-20 Changed back to place and sort direction asc, because place added again
                 */
                @uasort($articleCleanData, function ($a, $b) {
                    return ($a['place'] < $b['place']) ? -1 : 1;
                });

                if (!empty(self::$shopOptions['shop_sortby']) && is_array($articleCleanData) && in_array(self::$shopOptions['shop_sortby'], self::$shopArticleSortOptions)) {
                    if ('recent' == self::$shopOptions['shop_sortby']) {
                        krsort($articleCleanData);
                    } elseif ('price' == self::$shopOptions['shop_sortby']) {
                        uasort($articleCleanData, function ($a, $b) {
                            return ($a['pricebrut'] < $b['pricebrut']) ? -1 : 1;
                        });
                        // } elseif ('weight' == self::$shopOptions['shop_sortby']) {
                        //     uasort($articleCleanData, create_function('$a,$b', "return (\$a['weight'] > \$b['weight'])?-1:1;"));
                    } else {
                        uasort(
                            $articleCleanData,
                            function ($a, $b) {
                                return strnatcmp($a[htmlspecialchars(self::$shopOptions['shop_sortby'])], $b[htmlspecialchars(self::$shopOptions['shop_sortby'])]);
                            }
                        );
                    }
                }

                // pagination
                if (!empty(self::$shopOptions['shop_limit']) && is_array($articleCleanData)) {
                    $cArticleNext = count(array_slice($articleCleanData, $offset + self::$shopOptions['shop_limit'], self::$shopOptions['shop_limit'], true));
                    $articleCleanData = array_slice($articleCleanData, $offset, self::$shopOptions['shop_limit'], true);
                }

                // Start output
                $output = (!empty($conOp['shop_url_anchor']) ? '<a name="' . $conOp['shop_url_anchor'] . '"></a>' : '');

                // check if curl is enabled
                $output .= (function_exists('curl_version') ? '' : '<span class="error">Curl seems to be disabled. In order to use Shop functionality, it should be enabled</span>');
                // wrapper for integrated designer
                if (1 == self::$shopOptions['shop_designer']) {
                    $output .= '
					<div id="spreadplugin-designer-wrapper"><div id="spreadplugin-designer" class="spreadplugin-designer spreadplugin-clearfix"></div></div>
					';
                }

                // Start div
                $output .= '
				<div id="spreadplugin-items" class="spreadplugin-items spreadplugin-clearfix">
				';

                // display
                if (empty($articleData)) {
                    $output .= '<br>No articles in Shop. Please rebuild cache.';
                } else {
                    // Listing product

                    if (!get_query_var(self::$shopOptions['shop_url_productdetail_slug']) && empty(self::$shopOptions['shop_article_detail'])) {
                        // add spreadplugin-menu
                        $output .= '<div id="spreadplugin-menu" class="spreadplugin-menu">';

                        // add product categories
                        // $output .= '<select name="productCategory" id="productCategory">';
                        // $output .= '<option value="">'.__('Product category', 'spreadplugin').'</option>';
                        // if (isset($typesData)) {
                        //     foreach ($typesData as $t => $v) {
                        //         $output .= '<option value="'.str_replace('+', '%20', urlencode($t)).'"'.($t == self::$shopOptions['shop_productcategory'] ? ' selected' : '').'>'.$t.'</option>';
                        //     }
                        // }
                        // $output .= '</select> ';

                        // add sorting
                        $output .= '<div class="style-select"><select name="articleSortBy" id="articleSortBy">';
                        $output .= '<option value="">' . __('Sort by', 'spreadplugin') . '</option>';
                        $output .= '<option value="name"' . ('name' == self::$shopOptions['shop_sortby'] ? ' selected' : '') . '>' . __('name', 'spreadplugin') . '</option>';
                        $output .= '<option value="price"' . ('price' == self::$shopOptions['shop_sortby'] ? ' selected' : '') . '>' . __('price', 'spreadplugin') . '</option>';
                        $output .= '<option value="recent"' . ('recent' == self::$shopOptions['shop_sortby'] ? ' selected' : '') . '>' . __('recent', 'spreadplugin') . '</option>';
                        //$output .= '<option value="weight"'.('weight' == self::$shopOptions['shop_sortby'] ? ' selected' : '').'>'.__('weight', 'spreadplugin').'</option>';
                        $output .= '</select></div>';

                        // url not needed here, but just in case if js won't work for some reason
                        $output .= '<div id="checkout" class="spreadplugin-checkout"><span></span> <a href="' . (!empty($_SESSION['checkoutUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']]) ? $_SESSION['checkoutUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']] : '') . '" target="' . self::$shopOptions['shop_linktarget'] . '" id="basketLink" class="spreadplugin-checkout-link' . (1 == self::$shopOptions['shop_basket_text_icon'] ? ' button' : '') . '">' . (0 == self::$shopOptions['shop_basket_text_icon'] ? __('Basket', 'spreadplugin') : '') . '</a></div>';
                        $output .= '<div id="spreadplugin-cart" class="spreadplugin-cart"></div>';

                        $output .= '</div>';

                        $output .= '<div id="spreadplugin-list">';

                        // Article view
                        if (!empty($articleCleanData)) {
                            switch (self::$shopOptions['shop_view']) {
                                case 1:
                                    foreach ($articleCleanData as $articleId => $arrArticle) {
                                        $output .= $this->displayListArticles($articleId, $arrArticle, self::$shopOptions['shop_zoomimagebackground']);
                                    }
                                    break;
                                case 2:
                                    foreach ($articleCleanData as $articleId => $arrArticle) {
                                        $output .= $this->displayMinArticles($articleId, $arrArticle, self::$shopOptions['shop_zoomimagebackground']);
                                    }
                                    break;
                                default:
                                    foreach ($articleCleanData as $articleId => $arrArticle) {
                                        $output .= $this->displayArticles($articleId, $arrArticle, self::$shopOptions['shop_zoomimagebackground']);
                                    }
                                    break;
                            }
                        }

                        $output .= '</div>';

                        $output .= '<div id="pagination">';
                        if ($cArticleNext > 0) {
                            $output .= '<a href="' . $this->prettyPagesUrl() . '">' . __('next', 'spreadplugin') . '</a>';
                        }
                        $output .= '</div>';
                    } else {
                        if (!empty($articleCleanDataComplete[self::replaceUnsecure(get_query_var(self::$shopOptions['shop_url_productdetail_slug']))]) || !empty($articleCleanDataComplete[self::replaceUnsecure(self::$shopOptions['shop_article_detail'])])) {
                            $desiredArticle = empty(self::$shopOptions['shop_article_detail']) ? self::replaceUnsecure(get_query_var(self::$shopOptions['shop_url_productdetail_slug'])) : self::replaceUnsecure(self::$shopOptions['shop_article_detail']);

                            // display product page
                            $output .= '<div id="spreadplugin-list" class="spreadplugin-detail-content">';

                            // checkout
                            // add simple spreadplugin-menu
                            $output .= '<div id="spreadplugin-menu" class="spreadplugin-menu">';
                            $output .= '<a href="javascript:history.back();" class="btn-back">' . __('Back', 'spreadplugin') . '</a>';
                            $output .= '<div id="checkout" class="spreadplugin-checkout"><span></span> <a href="' . (!empty($_SESSION['checkoutUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']]) ? $_SESSION['checkoutUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']] : '') . '" target="' . self::$shopOptions['shop_linktarget'] . '" id="basketLink" class="spreadplugin-checkout-link' . (1 == self::$shopOptions['shop_basket_text_icon'] ? ' button' : '') . '">' . (0 == self::$shopOptions['shop_basket_text_icon'] ? __('Basket', 'spreadplugin') : '') . '</a></div>';
                            $output .= '<div id="cart" class="spreadplugin-cart"></div>';
                            $output .= '</div>';

                            // product
                            $output .= $this->displayDetailPage($desiredArticle, $articleCleanDataComplete[$desiredArticle], self::$shopOptions['shop_zoomimagebackground']);

                            $output .= '</div>';
                        } else {
                            status_header(404);
                            nocache_headers();
                            include get_query_template('404');
                            die();
                        }
                    }
                }

                // End div
                $output .= '</div>';

                // Shipment Table
                if (!empty($shipmentData)) {
                    $output .= '<div id="spreadplugin-shipment-wrapper">
					<table class="shipment-table">';
                    foreach ($shipmentData as $c => $v) {
                        $output .= '<tr>';
                        $output .= '<th colspan="2">' . $c . '</th>';
                        $output .= '</tr>';
                        foreach ($v as $m => $d) {
                            $output .= '<tr>';
                            $output .= '<td>' . __('Order Value', 'spreadplugin') . '<br>';
                            if (0 == $d['value-to']) {
                                $output .= __('over', 'spreadplugin') . ' ';
                            }
                            if ($d['value-from'] > 0) {
                                $output .= self::formatPrice($d['value-from'], '') . ' ';
                            }
                            if ($d['value-to'] > 0) {
                                $output .= __('up to', 'spreadplugin') . ' ' . self::formatPrice($d['value-to'], '');
                            }
                            $output .= ' </td>';
                            $output .= '<td>' . self::formatPrice($d['price'], '') . '</td>';
                            $output .= '</tr>';
                        }
                    }
                    $output .= '</table>
					</div>';
                }

                return $output;
            }
        }

        public function SpreadpluginDesigner($atts)
        {
            // shortcode overwrites admin options (default option set on admin page) if available

            return '<div id="spreadplugin-designer-wrapper"><div id="spreadplugin-designer" class="spreadplugin-designer spreadplugin-clearfix"></div></div><script>jQuery(function() {
              spreadshirt.create("sketchomat",{

				shopId: ajax_object.designerShopId,
				target: document.getElementById(\'spreadplugin-designer\'),
				platform: ajax_object.designerPlatform,
				locale: ajax_object.designerLocale,
				width: ajax_object.designerWidth,
        cssUrl: ajax_object.cssSketchomatLocation,
        ' . (!empty($atts['designid']) ? 'designId: ' . $atts['designid'] . ',' : '')
                . (!empty($atts['appearanceid']) ? 'appearanceId: ' . $atts['appearanceid'] . ',' : '')
                . (!empty($atts['producttypeid']) ? 'productTypeId: ' . $atts['producttypeid'] . ',' : '')
                . (!empty($atts['viewid']) ? 'viewId: ' . $atts['viewid'] . ',' : '') . '

				addToBasket: function(basketItem, callback) {

					var data = {
						article: basketItem.product.id,
						size: basketItem.size.id,
						appearance: basketItem.appearance.id,
						quantity: basketItem.quantity,
						shopId: basketItem.shopId,
						action: \'myAjax\',
						type: \'1\' // type switch for using articleId as productId
					}

					jQuery.post(ajax_object.ajaxLocation,data,function(json) {

						if (json.c.m == 1) {
							// return success to confomat
							callback && callback();
						} else {
							// return failure to confomat
							callback && callback(true);
						}

						// Refresh shopping cart

							// from function refreshCart START
							jQuery(\'.spreadplugin-checkout-link\').attr(\'href\', json.c.u);
							jQuery(\'.spreadplugin-checkout-link\').removeAttr(\'title\');
							jQuery(\'.spreadplugin-checkout span\').text(json.c.q);
							jQuery(\'.spreadplugin-cart-checkout a\').attr(\'href\', json.c.u);

							jQuery.get(ajax_object.ajaxLocation,\'action=myCart\',function (data) {
								jQuery(\'.spreadplugin-cart\').html(data);


								// checkout in an iframe in page
								if (ajax_object.pageCheckoutUseIframe == 1) {
											jQuery(\'.spreadplugin-cart-checkout a\').click(function(event) {
														event.preventDefault();

														var checkoutLink = jQuery(this).attr(\'href\');

														if (typeof checkoutLink !== "undefined" && checkoutLink.length > 0) {

															jQuery(\'#spreadplugin-items #pagination\').remove();
															jQuery(\'#spreadplugin-items #spreadplugin-menu\').remove();
															jQuery(window).unbind(\'.infscr\');

															jQuery(\'#spreadplugin-list\').html(\'<iframe style="z-index:10002" id="checkoutFrame" frameborder="0" width="900" height="2000" scroll="yes">\');
															jQuery(\'#spreadplugin-list #checkoutFrame\').attr(\'src\', checkoutLink);

															jQuery(\'html, body\').animate({
																				scrollTop : jQuery("#spreadplugin-items #checkoutFrame").offset().top
																			}, 2000);

														}
													});

								}


								jQuery(\'.cart-row a.deleteCartItem\').click(function(e) {
									e.preventDefault;
									jQuery(this).closest(\'.cart-row\').show().fadeOut(\'slow\');

									// &\'+sid+\'
									jQuery.post(ajax_object.ajaxLocation,\'action=myDelete&id=\'+jQuery(this).closest(\'.cart-row\').data(\'id\'),function() {
											$.post(ajax_object.ajaxLocation,\'action=myAjax\',function(json) {
												refreshCart(json);
											}, \'json\');
										});

								});

								// hide cart when user clicks close
								jQuery(\'.spreadplugin-cart-close\').click(function(e) {
									e.preventDefault();
									jQuery(".spreadplugin-cart").hide();
								});

							});
							// from function refreshCart END

					}, \'json\');
				}

			}, function(err, app) {
			if (err) {
				// something went wrong
				console.log(err);
			} else {
				// cool I can control the application (see below)
				// app.setProductTypeId(6);
			}
			});
    });</script>';
        }

        public static function isJson($string)
        {
            json_decode($string);

            return JSON_ERROR_NONE == json_last_error();
        }

        /**
         * Function loadHead.
         */
        public function loadHead()
        {
            $conOp = $this->getAdminOptions();

            if (!empty($conOp['shop_customcss'])) {
                echo '
				<style type="text/css">
				' . stripslashes($conOp['shop_customcss']) . '
				</style>
				';
            }
        }

        /**
         * Function loadFoot.
         */
        public function loadFoot()
        {
        }

        public function enqueueSomes()
        {
            global $post;

            $this->reparseShortcodeData(get_query_var('pageid') ? intval(get_query_var('pageid')) : null);

            // Respects SSL, Style.css is relative to the current file
            wp_enqueue_style('spreadplugin', plugins_url('/css/spreadplugin.css', __FILE__));
            wp_enqueue_style('magnific_popup_css', plugins_url('/css/magnific-popup.css', __FILE__));

            //wp_enqueue_style('dashicons');

            // Scrolling
            if (self::$shopOptions['shop_infinitescroll'] == 1 || self::$shopOptions['shop_infinitescroll'] == '') {
                wp_enqueue_script('infinite_scroll', plugins_url('/js/jquery.infinitescroll.min.js', __FILE__), array('jquery'));
            }

            // Fancybox
            wp_enqueue_script('magnific_popup', plugins_url('/js/jquery.magnific-popup.min.js', __FILE__), array('jquery'));

            // Zoom
            wp_enqueue_script('zoom', plugins_url('/js/jquery.elevateZoom-2.5.5.min.js', __FILE__), array('jquery'));

            // lazyload
            if (self::$shopOptions['shop_lazyload'] == 1 || self::$shopOptions['shop_lazyload'] == '') {
                wp_enqueue_script('lazyload', plugins_url('/js/jquery.lazyload.min.js', __FILE__), array('jquery'));
            }

            // isotope
            wp_enqueue_script('isotope', plugins_url('/js/isotope.pkgd.min.js', __FILE__), array('jquery'));

            // sketchomat
            if (self::$shopOptions['shop_designer'] > 0) {
                wp_enqueue_script('sketchomat', plugins_url('/js/spreadshirt.min.js', __FILE__), array('jquery'));
            }

            // Spreadplugin
            wp_enqueue_script('spreadplugin', plugins_url('/js/spreadplugin.min.js', __FILE__), array('jquery'));

            // translate ajax_object in js
            wp_localize_script('spreadplugin', 'ajax_object', array(
                'textHideDesc' => esc_attr__('Hide article description', 'spreadplugin'),
                'textShowDesc' => esc_attr__('Show article description', 'spreadplugin'),
                'textProdHideDesc' => esc_attr__('Hide product description', 'spreadplugin'),
                'textProdShowDesc' => esc_attr__('Show product description', 'spreadplugin'),
                'loadingImage' => plugins_url('/img/loading.gif', __FILE__),
                'loadingMessage' => esc_attr__('Loading...', 'spreadplugin'),
                'loadingFinishedMessage' => esc_attr__('You have reached the end', 'spreadplugin'),
                'pageLink' => self::prettyPermalink(),
                'pageCheckoutUseIframe' => self::$shopOptions['shop_checkoutiframe'],
                'textButtonAdd' => esc_attr__('Add to basket', 'spreadplugin'),
                'textButtonAdded' => esc_attr__('Adding...', 'spreadplugin'),
                'textButtonFailed' => esc_attr__('Add failed', 'spreadplugin'),
                'ajaxLocation' => admin_url('admin-ajax.php') . '?pageid=' . get_the_ID() . '&nonce=' . wp_create_nonce('spreadplugin'),
                'infiniteScroll' => (self::$shopOptions['shop_infinitescroll'] == 1 || self::$shopOptions['shop_infinitescroll'] == '' ? 1 : 0),
                'lazyLoad' => (1 == self::$shopOptions['shop_lazyload'] || '' == self::$shopOptions['shop_lazyload'] ? 1 : 0),
                'zoomConfig' => (0 == self::$shopOptions['shop_zoomtype'] ? array('zoomType' => 'inner', 'cursor' => 'crosshair', 'easing' => true) : array('zoomType' => 'lens', 'lensShape' => 'round', 'lensSize' => 150)),
                'zoomActivated' => (2 == self::$shopOptions['shop_zoomtype'] ? 0 : 1),
                'designerShopId' => (self::$shopOptions['shop_designershop'] > 0 ? self::$shopOptions['shop_designershop'] : self::$shopOptions['shop_id']),
                'designerTargetId' => 'spreadplugin-designer',
                'designerPlatform' => ('net' == self::$shopOptions['shop_source'] ? 'EU' : 'NA'),
                'designerLocale' => (empty(self::$shopOptions['shop_language']) ? get_locale() : self::$shopOptions['shop_language']),
                'designerWidth' => 750,
                'designerBasketId' => (!empty($_SESSION['basketId'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']]) ? $_SESSION['basketId'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']] : ''),
                'prettyUrl' => ('' != get_option('permalink_structure') ? 1 : 0),
                'imagesize' => self::$shopOptions['shop_imagesize'],
                'cssSketchomatLocation' => plugins_url('/css/spreadplugin-sketchomat-inline.css', __FILE__),
                'openBasketOnAdd' => (int) !empty(self::$shopOptions['shop_openbasketonadd']),
            ));
        }

        public function enqueueAdminJs()
        {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
        }

        public function startSession()
        {
            $status = session_status();

            if (PHP_SESSION_NONE === $status && !session_id()) {
                @session_start();
                @session_write_close();
            }
        }

        // public function closeSession()
        // {
        //     session_write_close();
        // }

        public function endSession()
        {
            @session_destroy();
        }

        /**
         * Function doAjax.
         *
         * does all the ajax
         *
         * @return string json
         */
        public function doAjax()
        {

            $_m = '';

            // $this->startSession();
            @session_start();
            $basketsUrl = '';

            if (!wp_verify_nonce($_GET['nonce'], 'spreadplugin')) {
                die('Security check');
            }

            $this->reparseShortcodeData(get_query_var('pageid') ? intval(get_query_var('pageid')) : intval($_GET['pageid']));

            // create an new basket if not exist
            if (!isset($_SESSION['basketUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']])) {
                // gets basket
                $apiUrl = 'https://api.spreadshirt.' . self::$shopOptions['shop_source'] . '/api/v1/baskets?mediaType=json&locale=' . self::$shopOptions['shop_language'];
                $stringJsonShop = wp_remote_post($apiUrl, array(
                    'timeout' => 120,
                    'user-agent' => self::$userAgent,
                    "body" => json_encode(array(
                        'currencyId' => 1,
                        'countryId' => 1,
                        'basketItems' => array(),
                    )),
                ));
                if (!empty($stringJsonShop->errors)) {
                    die('Error getting basket.');
                }

                $stringJsonShop = wp_remote_retrieve_body($stringJsonShop);
                $objShop = self::isJson($stringJsonShop) ? json_decode($stringJsonShop, false) : false;

                if (!is_object($objShop)) {
                    die('Basket not loaded');
                }

                // get the checkout url
                $checkoutUrl = self::checkout($objShop->href);

                // Workaround
                $checkoutUrl = self::workaroundLangUrl($checkoutUrl);

                // saving to session
                $_SESSION['basketUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']] = $objShop->href;
                $_SESSION['checkoutUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']] = $checkoutUrl;
                $_SESSION['basketId'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']] = $objShop->id;
                // $this->closeSession();
            }

            // add an article to the basket
            if (isset($_POST['size'], $_POST['appearance'], $_POST['quantity'])) {
                // article data to be sent to the basket resource
                $data = array(
                    'productId' => $_POST['article'],
                    'size' => $_POST['size'],
                    'appearance' => $_POST['appearance'],
                    'quantity' => $_POST['quantity'],
                    'shopId' => self::$shopOptions['shop_id'],
                    'type' => (!empty($_POST['type']) ? (int) $_POST['type'] : ''),
                    'ideaId' => $_POST['ideaId'],
                    'defaultAppearance' => $_POST['defaultAppearance'],
                    'sellableId' => $_POST['sellableId'],
                );

                // add to basket
                $_m = self::addBasketItem($_SESSION['basketUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']], $data);
            }

            $intInBasket = self::getInBasketQuantity(self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']);

            echo json_encode(array(
                'c' => array(
                    'u' => $_SESSION['checkoutUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']],
                    'q' => intval($intInBasket),
                    'm' => $_m,
                ),
            ));
            die();
        }

        /**
         * Overwrite wpseo.
         */
        public function overwriteWpSeoUrl($str)
        {
            $slugOptions = $this->getAdminOptions();

            if (get_query_var($slugOptions['shop_url_productdetail_slug'])) {
                return $this->prettyProductUrl(self::replaceUnsecure(get_query_var($slugOptions['shop_url_productdetail_slug'])));
            }

            return $str;
        }

        /**
         * SEO Description prepare.
         */
        public function seoDescriptionHandler($str, $trail = true)
        {
            $slugOptions = $this->getAdminOptions();
            $articleData = self::getCacheArticleData();
            $string = '';

            if (get_query_var($slugOptions['shop_url_productdetail_slug'])) {
                $articleId = self::replaceUnsecure(get_query_var($slugOptions['shop_url_productdetail_slug']));

                if (!empty($articleData)) {
                    foreach ($articleData as $arrDesigns) {
                        if (!empty($arrDesigns)) {
                            foreach ($arrDesigns as $_articleId => $arrArticle) {
                                $urlified = self::urlify($arrArticle['name'] . '-' . $_articleId);
                                $urlified2 = self::urlify($arrArticle['name'] . ' - ' . $arrArticle['productname'] . '-' . $_articleId);

                                if ($articleId == $_articleId || $articleId == $urlified || $articleId == $urlified2) {
                                    $string = htmlspecialchars(trim((!empty($arrArticle['description']) ? $arrArticle['description'] : '') . ' ' . $arrArticle['productshortdescription']), ENT_QUOTES) . ($trail ? ' | ' : '');
                                }
                            }
                        }
                    }
                }
            }

            return $string . $str;
        }

        /**
         * Overwrite wpseo.
         */
        public function overwriteWpSeoDesc($str)
        {
            return $this->seoDescriptionHandler($str, true);
        }

        /**
         * Overwrite Rank Math Description.
         */
        public function overwriteRankMathDesc($str)
        {
            return $this->seoDescriptionHandler($str, false);
        }

        /**
         * Overwrite wpseo.
         */
        public function overwriteWpSeoTitle($str)
        {
            $slugOptions = $this->getAdminOptions();
            $articleData = self::getCacheArticleData();
            $string = '';

            if (get_query_var($slugOptions['shop_url_productdetail_slug']) && $slugOptions['shop_additionalmods'] == 1) {
                $temp = explode(' | ', $str);
                $str = (!empty($temp) ? end($temp) : $str);
            }

            if (get_query_var($slugOptions['shop_url_productdetail_slug'])) {
                $articleId = self::replaceUnsecure(get_query_var($slugOptions['shop_url_productdetail_slug']));

                if (!empty($articleData)) {
                    foreach ($articleData as $arrDesigns) {
                        if (!empty($arrDesigns)) {
                            foreach ($arrDesigns as $_articleId => $arrArticle) {
                                $urlified = self::urlify($arrArticle['name'] . '-' . $_articleId);
                                $urlified2 = self::urlify($arrArticle['name'] . ' - ' . $arrArticle['productname'] . '-' . $_articleId);

                                if (($articleId == $_articleId || $articleId == $urlified || $articleId == $urlified2) && !empty($arrArticle['name'])) {
                                    $string = htmlspecialchars($arrArticle['name'] . (!empty($arrArticle['productname']) ? ' - ' . $arrArticle['productname'] : ''), ENT_QUOTES) . ' | ';
                                }
                            }
                        }
                    }
                }
            }

            return $string . $str;
        }

        /**
         * Overwrite wpseo.
         */
        public function overwriteWpSeoType($type = '')
        {
            global $post;

            $slugOptions = $this->getAdminOptions();
            //Check for plugin usage

            if (!empty($post->post_content) && has_shortcode($post->post_content, 'spreadplugin')) {
                if (get_query_var($slugOptions['shop_url_productdetail_slug'])) {
                    return 'product.item';
                }

                return 'product';
            }

            return $type;
        }

        /**
         * SEO og:image handler.
         */
        public function seoImageUrlHandler($img = '')
        {
            $slugOptions = $this->getAdminOptions();
            $articleData = self::getCacheArticleData();

            if (get_query_var($slugOptions['shop_url_productdetail_slug'])) {
                $articleId = self::replaceUnsecure(get_query_var($slugOptions['shop_url_productdetail_slug']));

                if (!empty($articleData)) {
                    foreach ($articleData as $arrDesigns) {
                        if (!empty($arrDesigns)) {
                            foreach ($arrDesigns as $_articleId => $article) {
                                $urlified = self::urlify($article['name'] . '-' . $_articleId);
                                $urlified2 = self::urlify($article['name'] . ' - ' . $article['productname'] . '-' . $_articleId);

                                // Slow ...
                                if ($articleId == $_articleId || $articleId == $urlified || $articleId == $urlified2) {
                                    $imgSrc = '//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . urlencode($article['productid']) . '/views/' . $article['view'] . ',width=800,height=800,appearanceId=' . $article['appearance'] . ',typeId=' . $article['type'];

                                    if (!empty(self::$shopOptions['shop_modelids'])) {
                                        $modelId = self::returnModelId($article, self::$shopOptions);
                                        $imgSrc .= ',modelId=' . $modelId . ',crop=list,version=' . time();
                                    } elseif (!empty($article['viewModelId'])) {
                                        $imgSrc .= ',modelId=' . $article['viewModelId'] . ',crop=list,version=' . time();
                                    }

                                    return 'https:' . $imgSrc;
                                }
                            }
                        }
                    }
                }
            }

            return $img;
        }

        public function addBodyClassDetailPage($classes)
        {
            $slugOptions = $this->getAdminOptions();

            if (get_query_var($slugOptions['shop_url_productdetail_slug'])) {
                $classes[] = 'spreadplugin-detail-page';
            }

            return $classes;
        }

        /**
         * Admin.
         */
        public function addPluginPage()
        {
            // Create menu tab
            add_options_page('Set Spreadplugin options', 'Spreadplugin', 'manage_options', 'splg_options', array(
                $this,
                'pageOptions',
            ));
        }

        // call page options
        public function pageOptions()
        {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            // display options page
            include plugin_dir_path(__FILE__) . '/options.php';
        }

        // Rebuild Cache Ajax Call
        public function doRebuildCache()
        {
            global $wpdb;

            $res = array();

            $action = $_POST['do'];
            @session_start();

            if ('getlist' == $action) {
                // $this->startSession();
                // delete transient cache
                $wpdb->query('DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "_transient_%spreadplugin%cache%"');
                $_SESSION['_tac'] = array();

                // read posts/pages,... with shortcode
                $result = $wpdb->get_results('SELECT distinct ' . $wpdb->posts . '.id,post_title FROM ' . $wpdb->posts . ' left join ' . $wpdb->postmeta . ' on ' . $wpdb->postmeta . '.post_id =' . $wpdb->posts . ".id WHERE post_type <> 'revision' and post_status <> 'trash' and (post_content like '%[spreadplugin %' or post_content like '%[spreadplugin]%' or ((meta_value like '%[spreadplugin %' or meta_value like '%[spreadplugin]%') and (meta_key = 'panels_data' or meta_key = 'tie_builder' or meta_key = 'ct_builder')))");

                if ($result) {
                    foreach ($result as $item) {
                        $items = array();
                        $_items = array();
                        $this->reparseShortcodeData($item->id);

                        // get raw article data for later usage
                        $_items = $this->getRawArticleData($item->id);

                        if (is_object($_items) && !empty($_items->article)) {
                            $i = 0;
                            foreach ($_items->article as $article) {
                                $productId = '';

                                if (preg_match('/compositions\/([^\/,]*)/', $article->imageUrl, $productMatches)) {
                                    $productId = $productMatches[1];
                                }
                                if (preg_match('/products\/([^\/,]*)/', $article->imageUrl, $productMatches) && empty($productId)) {
                                    $productId = $productMatches[1];
                                }

                                $items[] = array(
                                    'articleid' => $article->id,
                                    'producttypeid' => $article->productTypeId,
                                    'appearanceid' => $article->appearanceId,
                                    'viewid' => $article->viewId,
                                    'productid' => $productId,
                                    'previewimage' => $article->imageUrl,
                                    'articlename' => $article->altText,
                                    'sellableid' => $article->sellableId,
                                    'ideaid' => $article->ideaId,
                                    'place' => $i,
                                );

                                ++$i;
                            }
                        }

                        $res[] = array(
                            'id' => $item->id,
                            'title' => $item->post_title,
                            'items' => $items,
                        );
                    }
                }

                // $this->closeSession();
                die(json_encode($res));
            } elseif ('rebuild' == $action) {
                $_pageid = intval($_POST['_pageid']);
                $_articleid = self::replaceUnsecure($_POST['_articleid']);
                $_productTypeId = (int) $_POST['_producttypeid'];
                $_appearanceId = (int) $_POST['_appearanceid'];
                $_productId = !empty($_POST['_productid']) ? self::replaceUnsecure($_POST['_productid']) : '';
                $_ideaId = !empty($_POST['_ideaid']) ? self::replaceUnsecure($_POST['_ideaid']) : '';
                $_sellableId = !empty($_POST['_sellableid']) ? self::replaceUnsecure($_POST['_sellableid']) : '';
                $_viewId = (int) $_POST['_viewid'];
                $_pos = intval($_POST['_pos']);
                $this->reparseShortcodeData($_pageid);
                $_mId = md5($_productId . '_' . $_articleid . '_' . $_productTypeId . '_' . $_appearanceId . '_' . self::$shopOptions['shop_language'] . '.' . self::$shopOptions['shop_source'] . '.' . self::$shopOptions['shop_id']);
                $_n = false;

                // read only when not already read, to save time and resources.
                if (empty($_SESSION['_tac'][$_mId])) {
                    $_articleData = $this->getSingleArticleData($_pageid, $_articleid, $_productTypeId, $_appearanceId, $_productId, $_viewId, $_pos, $_ideaId, $_sellableId);
                    $_SESSION['_tac'][$_mId] = $_articleData;
                } else {
                    $_articleData = $_SESSION['_tac'][$_mId];
                    $_n = true;
                }

                // sleep timer, for some users reaching their request limits - 20 sec will avoid it.
                if (!empty(self::$shopOptions['shop_sleep']) && self::$shopOptions['shop_sleep'] > 0) {
                    sleep(self::$shopOptions['shop_sleep']);
                }

                if (!empty($_articleData)) {
                    // store each article in a session for later use
                    $temp[$_articleData['designid']][$_articleData['id'] . '_' . $_articleData['type'] . '_' . $_articleData['appearance']] = $_articleData;

                    // Skip session and write directly to transient
                    $oldTransient = get_transient('spreadplugin4-article-cache-' . $_pageid);

                    set_transient('spreadplugin4-article-cache-' . $_pageid, array_merge((empty($oldTransient) ? array() : get_transient('spreadplugin4-article-cache-' . $_pageid)), $temp), self::$shopCache);

                    // $this->closeSession();
                    die('Done' . ($_n ? ' - already known' : ''));
                }

                // $this->closeSession();
                die('Error: ' . $_articleData);
            } elseif ('save' == $action) {
                die('done');
            }
        }

        /**
         * Add Settings link to plugin.
         */
        public function addPluginSettingsLink($links, $file)
        {
            static $this_plugin;
            if (!$this_plugin) {
                $this_plugin = plugin_basename(__FILE__);
            }

            if ($file == $this_plugin) {
                $settings_link = '<a href="options-general.php?page=splg_options">' . __('Settings', 'spreadplugin') . '</a>';
                array_unshift($links, $settings_link);
            }

            return $links;
        }

        // Convert hex to rgb values
        public function hex2rgb($hex)
        {
            if (3 == strlen($hex)) {
                $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
            } else {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            }

            return array(
                $r,
                $g,
                $b,
            );
            // returns an array with the rgb values
        }

        // read admin options
        public function getAdminOptions()
        {
            $scOptions = $this->defaultOptions;
            $splgOptions = get_option('splg_options');
            if (!empty($splgOptions)) {
                foreach ($splgOptions as $key => $option) {
                    $scOptions[$key] = $option;
                }
            }

            // set defaults
            if (empty($scOptions['shop_url_productdetail_slug'])) {
                $scOptions['shop_url_productdetail_slug'] = 'sproduct';
            }
            if ('' == $scOptions['shop_stockstates']) {
                $scOptions['shop_stockstates'] = 1;
            }

            if ('com' == $scOptions['shop_source'] && empty($scOptions['shop_language'])) {
                $scOptions['shop_language'] = 'us_US';
            }

            return $scOptions;
        }

        /**
         * re-parse the shortcode to get the authentication details
         * read page config and admin options.
         *
         * @TODO find a different way
         */
        public function reparseShortcodeData($pageId = 0)
        {
            $pageId = (0 == $pageId && get_query_var('pageid') ? intval(get_query_var('pageid')) : $pageId);
            $pageContent = '';

            // Check if panel contains spreadplugin code
            $pageData = get_post_meta($pageId, 'panels_data', true);
            if (!empty($pageData) && !empty($pageData['widgets'][0]['text']) && false !== stripos($pageData['widgets'][0]['text'], '[spreadplugin')) {
                $pageContent = $pageData['widgets'][0]['text'];
            }

            // use page content
            if (empty($pageContent)) {
                $pageData = get_page($pageId);
                if (!empty($pageData->post_content) && false !== stripos($pageData->post_content, '[spreadplugin')) {
                    $pageContent = $pageData->post_content;
                }
            }

            // get admin options (default option set on admin page)
            $conOp = $this->getAdminOptions();

            // shortcode overwrites admin options (default option set on admin page) if available
            preg_match("/\[spreadplugin[^\]]*\]/", $pageContent, $matches);

            // Overwrite default options if available
            if (!empty($matches[0])) {
                $pageContent = str_replace('[spreadplugin', '', str_replace(']', '', $matches[0]));

                $arrSc = shortcode_parse_atts($pageContent);

                // replace options by shortcode if set
                if (!empty($arrSc)) {
                    foreach ($arrSc as $key => $option) {
                        if ('' != $option) {
                            $conOp[$key] = $option;
                        }
                    }
                }
            }

            self::$shopOptions = $conOp;

            // Disable Zoom on min view, because of the new view - not on details page
            if (2 == self::$shopOptions['shop_view'] && !get_query_var($conOp['shop_url_productdetail_slug'])) {
                self::$shopOptions['shop_zoomtype'] = 2;
            }

            // Workaround for old product categories
            if (!empty(self::$shopOptions['shop_productcategory'])) {
                if (self::$shopOptions['shop_source'] === 'net') {
                    switch (self::$shopOptions['shop_productcategory']) {
                        case 'Männer':
                        case 'Men':
                            self::$shopOptions['shop_productcategory'] = 'D1';
                            break;
                        case 'Frauen':
                        case 'Women':
                            self::$shopOptions['shop_productcategory'] = 'D3';
                            break;
                        case 'Kinder & Babys':
                        case 'Kids & Babys':
                            self::$shopOptions['shop_productcategory'] = 'D4';
                            break;
                        case 'Accessoires':
                            self::$shopOptions['shop_productcategory'] = 'D5';
                            break;
                        case 'Hüllen':
                            self::$shopOptions['shop_productcategory'] = 'D19';
                            break;
                    }
                } else {
                    switch (self::$shopOptions['shop_productcategory']) {
                        case 'Men':
                            self::$shopOptions['shop_productcategory'] = 'D1';
                            break;
                        case 'Women':
                            self::$shopOptions['shop_productcategory'] = 'D2';
                            break;
                        case 'Kids & Babies':
                            self::$shopOptions['shop_productcategory'] = 'D3';
                            break;
                        case 'Accessoires':
                            self::$shopOptions['shop_productcategory'] = 'D4';
                            break;
                    }
                }
            }

            // overwrite translation if language available and set
            $this->overwriteLanguageIfNeeded();
        }

        // build cart
        public function doCart()
        {
            // $this->startSession();
            if (!wp_verify_nonce($_GET['nonce'], 'spreadplugin')) {
                die('Security check');
            }
            @session_start();

            $this->reparseShortcodeData(get_query_var('pageid') ? intval(get_query_var('pageid')) : intval($_GET['pageid']));

            // create an new basket if not exist
            if (isset($_SESSION['basketUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']])) {
                $basketItems = self::getBasket($_SESSION['basketUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']]);
                $currency = '';
                $currencyItem = '';
                $currencyRow = '';

                if (!empty($basketItems->currency->id)) {
                    $stringTypeJson = wp_remote_get('https://api.spreadshirt.net/api/v1/currencies/' . $basketItems->currency->id . '?mediaType=json', array('timeout' => 120, 'user-agent' => self::$userAgent));
                    $stringTypeJson = wp_remote_retrieve_body($stringTypeJson);
                    $objCurrencyData = self::isJson($stringTypeJson) ? json_decode($stringTypeJson, false) : false;
                    if (!empty($objCurrencyData->isoCode)) {
                        $currency = $objCurrencyData->isoCode;
                    }
                }

                // $priceSum = 0;
                $intSumQuantity = 0;

                echo '<div class="spreadplugin-cart-contents">';

                if (!empty($basketItems->basketItems)) {

                    //echo '<pre>' . print_r($basketItems) . '</pre>';
                    foreach ($basketItems->basketItems as $item) {
                        $productEntry = array_search('product', array_column($item->element->properties, 'key'));
                        $appearanceEntry = array_search('appearance', array_column($item->element->properties, 'key'));
                        $sizeEntry = array_search('sizeLabel', array_column($item->element->properties, 'key'));

                        if ($currencyItem !== $currencyRow || $currencyRow === '') {
                            $stringTypeJson = wp_remote_get('https://api.spreadshirt.net/api/v1/currencies/' . $item->priceItem->currency->id . '?mediaType=json', array('timeout' => 120, 'user-agent' => self::$userAgent));
                            $stringTypeJson = wp_remote_retrieve_body($stringTypeJson);
                            $objCurrencyData = self::isJson($stringTypeJson) ? json_decode($stringTypeJson, false) : false;
                            if (!empty($objCurrencyData->isoCode)) {
                                $currencyItem = $objCurrencyData->isoCode;
                            }
                        }

                        echo '<div class="cart-row" data-id="' . $item->id . '">
						<div class="cart-delete"><a href="javascript:;" class="deleteCartItem" title="' . __('Remove', 'spreadplugin') . '"><i></i></a></div>
						<div class="cart-preview"><img src="//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . $item->element->properties[$productEntry]->value . '/views/,width=280,height=280,appearanceId=' . $item->element->properties[$appearanceEntry]->value . '"></div>
						<div class="cart-description"><strong>' . $item->description . '</strong><br>' . __('Size', 'spreadplugin') . ': ' . $item->element->properties[$sizeEntry]->value . '<br>' . __('Quantity', 'spreadplugin') . ': ' . $item->quantity . '</div>
						<div class="cart-price"><strong>' . self::formatPrice($item->priceItem->vatIncluded * $item->quantity, $currencyItem) . '</strong></div>
						</div>';

                        // $priceSum +=  $item->priceItem->vatIncluded *  $item->quantity;
                        $intSumQuantity += $item->quantity;
                        $currencyRow = $currencyItem;
                    }
                }

                echo '</div>';
                echo '<div class="spreadplugin-cart-total">

                <div class="total-excl-shipping">' . __('Total (excl. Shipping)', 'spreadplugin') . '<strong class="price">' . self::formatPrice($basketItems->priceItems->display, $currency) . '</strong></div>
                <div class="total-incl-shipping">' . __('Total (incl. Shipping)', 'spreadplugin') . '<strong class="price">' . self::formatPrice($basketItems->priceTotal->display, $currency) . '</strong></div>

                </div>';

                echo '<div class="spreadplugin-cart-claims"><div class="spreadplugin-cart-claims-body">
                ';
                if (!empty(self::$shopOptions['shop_claimcheck1'])) {
                    echo '<div class="claims-row"><span class="claims-check">✓</span> ' . self::$shopOptions['shop_claimcheck1'] . '</div>';
                }
                if (!empty(self::$shopOptions['shop_claimcheck2'])) {
                    echo '<div class="claims-row"><span class="claims-check">✓</span> ' . self::$shopOptions['shop_claimcheck2'] . '</div>';
                }
                if (!empty(self::$shopOptions['shop_claimcheck3'])) {
                    echo '<div class="claims-row"><span class="claims-check">✓</span> ' . self::$shopOptions['shop_claimcheck3'] . '</div>';
                }
                echo '</div></div>';

                echo '<div class="spreadplugin-cart-close"><a href="#">' . __('Close', 'spreadplugin') . '</a></div>';

                if ($intSumQuantity > 0) {
                    echo '<div id="cart-checkout" class="spreadplugin-cart-checkout"><a href="' . $_SESSION['checkoutUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']] . '" target="' . self::$shopOptions['shop_linktarget'] . '">' . __('Proceed to checkout', 'spreadplugin') . '</a></div>';
                } else {
                    echo '<div id="cart-checkout" class="spreadplugin-cart-checkout"><a title="' . __('Basket is empty', 'spreadplugin') . '">' . __('Proceed to checkout', 'spreadplugin') . '</a></div>';
                }

                echo '<div class="spreadplugin-cart-checkout-privacy">' . __('With a click "Proceed to checkout" you will be redirected to Spreadshirt. At this moment the <a href="https://service.spreadshirt.com/hc/en-gb/articles/115000991325/" rel="nofollow" target="_blank">GTC</a> and <a href="https://service.spreadshirt.com/hc/en-gb/articles/115000978409/" rel="nofollow" target="_blank">Privacy</a> of Spreadshirt are applied.', 'spreadplugin') . '</div>';

                if (self::$shopOptions['shop_cartpaymenticons'] == 1) {
                    echo '<div class="spreadplugin-cart-payment"><div class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 213.18" fill="currentColor" width="56px" height="22px">
<path d="M440.69,57.57A5.19,5.19,0,0,1,445,65.71l-99.6,143.76a8.69,8.69,0,0,1-7.11,3.71H308.32a5.19,5.19,0,0,1-4.24-8.18l31-43.77-33-96.81A5.19,5.19,0,0,1,307,57.57h29.44a8.66,8.66,0,0,1,8.29,6.17l17.5,58.47,41.32-60.85a8.65,8.65,0,0,1,7.15-3.79h30Zm-200.23,58c1.3-8.24-.49-15.72-5.06-21.07s-11.4-8.12-19.86-8.12c-17,0-30.71,11.79-33.39,28.67-1.39,8.27.26,15.71,4.67,20.93s11.37,8,20,8c17.24,0,30.75-11.43,33.63-28.46Zm41.53-58a5.19,5.19,0,0,1,5.13,6L271,165.56a8.64,8.64,0,0,1-8.54,7.3H235.63a5.19,5.19,0,0,1-5.13-6l1.33-8.28s-14.71,17-41.26,17c-15.46,0-28.43-4.45-37.52-15.14-9.9-11.64-13.94-28.32-11.1-45.78,5.47-35,33.59-59.94,66.51-59.94,14.37,0,28.75,3.14,35.21,12.5l2.08,3,1.3-8.32a5.19,5.19,0,0,1,5.13-4.38H282Zm-178.82.71c1.24-7.84.25-13.52-3-17.35-5.47-6.39-16.08-6.39-27.31-6.39H68.53a5.18,5.18,0,0,0-5.12,4.38l-6.58,41.7h9.39c16.5,0,33.56,0,37-22.34ZM96,0c20.82,0,36.5,5.5,45.34,15.9,8,9.46,10.72,23,7.94,40.15-6.17,39.32-29.83,59.15-70.82,59.15H58.77a8.64,8.64,0,0,0-8.54,7.3l-6.79,43a8.64,8.64,0,0,1-8.54,7.3H5.18a5.19,5.19,0,0,1-5.12-6L25.22,7.3A8.64,8.64,0,0,1,33.77,0H96ZM760.94,4.39A5.19,5.19,0,0,1,766.07,0h28.74a5.19,5.19,0,0,1,5.13,6L774.75,165.55a8.64,8.64,0,0,1-8.54,7.3H740.53a5.19,5.19,0,0,1-5.12-6L760.94,4.38ZM684.3,115.57c1.3-8.24-.49-15.72-5.06-21.07s-11.4-8.12-19.86-8.12c-17,0-30.71,11.79-33.39,28.67-1.39,8.27.26,15.71,4.67,20.93s11.37,8,20,8c17.24,0,30.75-11.43,33.63-28.46Zm41.53-58a5.19,5.19,0,0,1,5.13,6l-16.11,102a8.64,8.64,0,0,1-8.54,7.3H679.46a5.19,5.19,0,0,1-5.13-6l1.33-8.28s-14.71,17-41.26,17c-15.46,0-28.43-4.45-37.52-15.14-9.9-11.64-13.94-28.32-11.1-45.78,5.47-35,33.59-59.94,66.51-59.94,14.37,0,28.75,3.14,35.2,12.5l2.09,3L690.9,62A5.19,5.19,0,0,1,696,57.57h29.8ZM547,58.28c1.24-7.84.25-13.52-3-17.35-5.47-6.39-16.08-6.39-27.31-6.39h-4.31a5.18,5.18,0,0,0-5.12,4.38l-6.58,41.7H510c16.5,0,33.56,0,37-22.34ZM539.83,0c20.82,0,36.5,5.5,45.34,15.9,8,9.46,10.72,23,7.94,40.15-6.17,39.32-29.83,59.15-70.82,59.15H502.6a8.64,8.64,0,0,0-8.54,7.3l-7.14,45.24a6.05,6.05,0,0,1-6,5.1H449a5.19,5.19,0,0,1-5.12-6L469.06,7.3A8.64,8.64,0,0,1,477.6,0h62.23Z"></path>
</svg>

          </div>
          <div class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 258.6" fill="currentColor" width="56px" height="22px">
<path d="M134.77,25.4l22.66,115.23C130.86,66.8,71.86,27.74,0,9.37L.77,4.3h104.7c14.06,0,26.16,5.06,29.3,21.1Zm29.3,150L227.34,4.3H296.1l-102,250.4H125.77L73,55.46c37.5,15.24,70.7,48.83,84.4,85.17l6.63,34.77ZM346.5,255.07H281.63L322.26,4.3H387.1L346.5,255.07ZM523.43,0a161.14,161.14,0,0,1,58.2,10.55l-9,54.3-5.86-3.1c-11.73-5.47-27.34-9.77-48.43-9.4-25.4,0-37.1,11-37.1,21.5,0,11.7,13.66,19.93,36.3,31.23,37.13,18,54.7,39.46,54.7,68C571.5,225,527.34,259,459.37,258.6c-29.3,0-57-6.26-72.26-13.3l9-56.24,8.6,4.3c20.7,9.76,34.77,12.9,60.94,12.9,18.36,0,38.66-7.43,38.66-24.23,0-10.93-8.2-19.13-33.6-31.23-24.6-12.13-57-32-56.63-68C414.46,34,459.37,0,523.43,0ZM747.66,4.3,800,255.07H739.83c-5.83-28.9-7.8-37.5-7.8-37.5h-82.8s-2.74,6.66-13.7,37.5h-68L663.66,25.4C670.3,9,682,4.3,697.66,4.3ZM667.57,166H721.5s-2.76-12.5-14.86-72.26L702,72.27c-3.14,9.37-9,24.23-8.6,23.83C673,151.57,667.57,166,667.57,166Z"></path>
</svg>

          </div>
          <div class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 480" fill="currentColor" width="56px" height="22px">
<path d="M560,0C692.55,0,800,107.45,800,240S692.55,480,560,480a239.06,239.06,0,0,1-160-61.16A239.09,239.09,0,0,1,240,480C107.45,480,0,372.54,0,240S107.45,0,240,0A239.06,239.06,0,0,1,400,61.16,239.05,239.05,0,0,1,560,0ZM99,297.42h23.78l19.8-114.85H105L79.23,253.86V182.57H43.58L23.78,297.43H47.54L63.38,210.3v87.13H81.2l31.68-87.13L99,297.43ZM209.9,273.66c1.29-7.93,2.63-16.5,4-25.75a94.24,94.24,0,0,0,2-17.82q0-27.72-33.67-27.72-11.88,0-27.72,5.94c-1.33,7.92-2.68,14.54-4,19.8,10.56-2.64,19.11-4,25.75-4,10.55,0,15.84,2.66,15.84,7.92V238h-9.9c-13.21,0-24.46,3.3-33.67,9.9-6.63,5.29-9.9,13.86-9.9,25.75,0,7.92,2,14.54,5.94,19.8,4,4,9.21,5.94,15.84,5.94q17.81,0,25.74-11.88v9.91h19.8v-5.94a130,130,0,0,0,4-17.82Zm71.29-47.53,4-21.76c-4-1.3-11.88-2-23.76-2q-35.64,0-35.64,31.68,0,17.81,17.82,25.74a25.24,25.24,0,0,1,9.9,5.94,8.24,8.24,0,0,1,2,5.94c0,5.29-4.66,7.92-13.86,7.92-4,0-11.23-1.3-21.78-4-1.33,9.24-2.68,16.52-4,21.78,5.24,1.32,13.85,2,25.74,2q37.57,0,37.62-31.68,0-15.84-17.82-25.75l-7.92-4c-1.32-1.3-2-3.28-2-5.94,0-5.26,4-7.92,11.88-7.92s13.85.68,17.82,2Zm51.49,2,2-23.78H322.77l2-13.86H301l-9.9,63.37a93.09,93.09,0,0,0-4,25.75q0,19.81,19.8,19.8,11.88,0,17.82-2l4-21.78a17.63,17.63,0,0,1-7.92,2c-5.29,0-7.92-2.63-7.92-7.92v-7.92c1.29-1.3,2.27-4,3-7.92a61.72,61.72,0,0,0,1-7.92,75,75,0,0,0,2-17.82h13.86ZM409.9,259.8a124.85,124.85,0,0,0,2-23.77c0-10.55-2.67-18.47-7.92-23.76q-9.95-9.89-25.75-9.9-17.83,0-29.7,15.84-11.89,13.87-11.88,39.6,0,41.59,41.59,41.59c10.56,0,19.11-1.3,25.75-4l4-23.76q-13.87,7.93-25.74,7.92t-17.82-5.94c-2.68-2.63-4-7.24-4-13.86H409.9ZM449.51,242c4-7.92,8.57-11.19,13.87-9.9,5.24-17.14,8.57-26.4,9.9-27.72q-5.95-4-11.88,2-5.95,2-11.88,11.88l2-13.86H427.73q-4,31.69-11.88,87.13l-2,5.94h25.75q3.95-33.65,9.9-55.45Zm89.11,53.47,4-25.76q-11.88,5.95-21.78,5.94a18.76,18.76,0,0,1-15.84-7.92q-5.95-5.95-5.94-19.8,0-21.77,7.92-31.68,7.86-11.88,23.76-11.88,9.89,0,21.78,5.94l4-23.76q-17.83-5.95-27.72-5.94-25.75,0-39.6,19.8-15.88,19.81-15.84,49.51,0,23.76,11.88,35.64Q497,299.41,516.83,299.4c9.21,0,16.49-1.3,21.78-4ZM626.74,238a57.44,57.44,0,0,0,1-7.93q0-27.72-33.67-27.72-11.88,0-27.72,5.94c-2.68,7.92-4,14.54-4,19.8,10.56-2.64,19.11-4,25.75-4,10.55,0,15.84,2.66,15.84,7.92,0,2.67-.69,4.65-2,5.94h-9.9a53.1,53.1,0,0,0-31.68,9.9c-6.63,5.29-9.9,13.86-9.9,25.75,0,7.92,2,14.54,5.94,19.8,4,4,9.21,5.94,15.84,5.94q17.83,0,25.75-11.88v9.91h19.8c1.3-4,2.64-11.54,4-22.77s2.64-20.11,4-26.74a58.87,58.87,0,0,1,1-9.9Zm38.61,4c4-7.92,8.57-11.19,13.87-9.9,1.29-11.88,4.61-21.1,9.9-27.72-2.68-1.3-6.63-.65-11.88,2q-5.95,2-11.88,11.88a44.83,44.83,0,0,0,2-13.86H643.56q-4,43.57-11.88,87.13l-2,5.94h25.75q5.93-43.53,9.9-55.45Zm67.32,55.45h23.77l19.8-114.85H750.49l-4,33.67q-11.88-11.88-23.76-11.88-17.83,0-27.72,15.84-13.87,15.88-13.86,41.59c0,9.24,3.26,18.51,9.9,27.72,4,6.62,11.18,9.9,21.78,9.9,9.21,0,16.49-3.27,21.78-9.9l-2,7.92ZM162.38,269.7c0-9.22,6.59-13.86,19.8-13.86h6q0,11.88-5.94,17.82-4,5.95-11.88,5.94c-5.29,0-7.92-3.27-7.92-9.9Zm223.76-43.57a6,6,0,0,1,2,4l2,4V240H362.38q3.95-15.83,15.84-15.84h4a5.6,5.6,0,0,0,4,2ZM574.26,269.7c0-9.22,6.6-13.86,19.8-13.86H600q0,11.88-5.94,17.82-4,5.95-11.88,5.94c-5.29,0-7.92-3.27-7.92-9.9Zm152.48-43.57c9.21,0,13.86,5.94,13.87,17.82q0,15.87-5.94,23.76c-4,5.29-8.61,7.92-13.86,7.92q-11.89,0-11.88-17.82c0-10.55,1.29-17.82,4-21.78q5.95-9.89,13.86-9.9Z"></path>
</svg>

          </div>
          <div class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 218.6" fill="currentColor" width="56px" height="22px">
<path d="M177.7,69.7H123c0-9-1.1-15-3.2-18.1c-3.3-4.5-12.3-6.8-27-6.8c-14.3,0-23.8,1.3-28.4,3.9s-6.9,8.3-6.9,17.1c0,8,2.1,13.2,6.2,15.7c2.9,1.8,6.8,2.8,11.7,3.1l11,0.8c23.7,1.6,38.4,2.7,44.3,3.3c18.7,1.9,32.3,6.9,40.7,14.9c6.6,6.3,10.6,14.5,11.8,24.7c0.8,6.8,1.1,13.7,1.1,20.6c0,17.6-1.7,30.5-5,38.7c-6.1,15-19.8,24.5-41.1,28.4c-8.9,1.7-22.6,2.5-40.9,2.5c-30.7,0-52-1.8-64.1-5.5c-14.8-4.5-24.6-13.6-29.3-27.3C1.3,178.2,0,165.5,0,147.7h54.7c0,2,0,3.5,0,4.6c0,9.5,2.7,15.6,8.2,18.3c4.3,2,9,3,13.7,3h20.1c10.3,0,16.8-0.5,19.7-1.6c5-2,8.3-5.2,9.9-9.6c0.9-3.4,1.3-7,1.2-10.5c0-9.6-3.5-15.5-10.5-17.6c-2.6-0.8-14.8-2-36.6-3.4c-17.5-1.2-29.6-2.5-36.4-3.7C26,123.9,14.1,117,8.1,106.5c-5.2-8.9-7.9-22.3-7.9-40.3c0-13.7,1.4-24.7,4.2-33s7.4-14.6,13.7-18.8c9.2-6.6,21-10.5,35.4-11.6c12-1,25.2-1.6,39.8-1.6c23,0,39.3,1.3,49.1,3.9c23.8,6.4,35.7,24.3,35.7,53.8C178.3,61.3,178.1,64.9,177.7,69.7 M418.5,218.6V0h109.6c15,0,26.5,1.2,34.4,3.7c18,5.6,30.1,17.2,36.3,34.7c3.2,9.2,4.8,23,4.8,41.4c0,22.2-1.8,38.1-5.3,47.8c-7,19.2-21.5,30.2-43.3,33.1c-2.6,0.4-13.5,0.8-32.8,1.1l-9.8,0.3h-35.1v56.4L418.5,218.6z M477.3,111.5H514c11.6-0.4,18.7-1.3,21.2-2.7c3.5-1.9,5.8-5.7,7-11.5c0.9-5.6,1.2-11.3,1.1-17.1c0-9.2-0.7-16.1-2.2-20.6c-2.1-6.3-7.2-10.1-15.2-11.5c-1.6-0.2-5.4-0.3-11.4-0.3h-37.2C477.3,47.8,477.3,111.5,477.3,111.5z M729.2,180.8h-78.7l-10.6,37.8h-60.9L644.8,0h88.8L800,218.6h-59.6L729.2,180.8z M717.5,138.2l-27.6-94.8l-26.8,94.8H717.5z M410.7,31.6c-38.4-36-96.5-40.7-140.2-11.4c-17.8,11.9-31.7,28.8-40,48.5h-24.7l-0.2,0.5L191,100.5l-0.6,1.3h32.3c-0.2,2.8-0.3,5.4-0.3,7.8c0,3.2,0.2,6.4,0.4,9.7h-15.6l-0.2,0.5l-14.6,31.3l-0.6,1.3h39.6c17.6,40,57.6,65.8,102.1,65.8c22.2,0.1,44-6.5,62.5-18.8l0.4-0.3v-41.4l-1.6,1.9c-27.8,31.8-76.1,35-107.9,7.2c-4.8-4.2-9.1-9.1-12.7-14.4H354l0.2-0.5l14.6-31.3l0.6-1.3H261.3c-0.5-3.5-0.7-7.1-0.7-10.6c0-2.3,0.1-4.6,0.3-6.9h116.9l0.2-0.5L392.5,70l0.6-1.3H271.8c21.8-36.2,68.8-47.8,105-26 c7.6,4.6,14.3,10.4,19.9,17.3l1,1.2l0.6-1.4l12.7-27.2l0.3-0.6L410.7,31.6L410.7,31.6z"></path>
</svg>

          </div></div>';
                }
            }

            // $this->closeSession();
            die();
        }

        // delete cart
        public function doCartItemDelete()
        {
            // $this->startSession();
            if (!wp_verify_nonce($_GET['nonce'], 'spreadplugin')) {
                die('Security check');
            }
            @session_start();

            $this->reparseShortcodeData(get_query_var('pageid') ? intval(get_query_var('pageid')) : intval($_GET['pageid']));

            // create an new basket if not exist
            if (isset($_SESSION['basketUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']])) {
                // uuid test
                if (preg_match('/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/', $_POST['id'])) {
                    self::deleteBasketItem($_SESSION['basketUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']], $_POST['id']);
                }
            }
            // $this->closeSession();
            die();
        }

        public static function mmToIn($val)
        {
            return number_format($val * 0.0393701, 1);
        }

        public function addQueryVars()
        {
            global $wp;
            $slugOptions = $this->getAdminOptions();

            $wp->add_query_var('productCategory');
            $wp->add_query_var('articleSortBy');
            $wp->add_query_var('productSubCategory');
            $wp->add_query_var('pagesp');
            $wp->add_query_var($slugOptions['shop_url_productdetail_slug']);
        }

        /**
         * flushRewriteRules()
         * Flush the rewrite rules, which forces the regeneration with new rules.
         * return void.
         **/
        public function flushRewriteRules()
        {
            //global $wp_rewrite;

            flush_rewrite_rules();
        }

        /**
         * flushRewriteRules()
         * Flush the rewrite rules, which forces the regeneration with new rules.
         * return void.
         **/
        public function registerRewriteRules()
        {
            $frontPageId = get_option('page_on_front');

            $slugOptions = $this->getAdminOptions();
            $pageName4 = (!empty($slugOptions['shop_url_productdetail_page']) ? $slugOptions['shop_url_productdetail_page'] : '$matches[4]');
            $pageName2 = (!empty($slugOptions['shop_url_productdetail_page']) ? $slugOptions['shop_url_productdetail_page'] : '$matches[2]');
            $pageName1 = (!empty($slugOptions['shop_url_productdetail_page']) ? $slugOptions['shop_url_productdetail_page'] : '$matches[1]');

            // Produkt detail pages
            add_rewrite_tag('%' . $slugOptions['shop_url_productdetail_slug'] . '%', '([^&]+)');

            // Produkt detail pages if html in url
            add_rewrite_rule('([a-zA-Z]{2})/' . $slugOptions['shop_url_productdetail_slug'] . '/([^&]+)\.html/?$', 'index.php?lang=$matches[1]&' . $slugOptions['shop_url_productdetail_slug'] . '=$matches[2]', 'top');
            add_rewrite_rule('(.?.+?)/' . $slugOptions['shop_url_productdetail_slug'] . '/([^&]+)\.html/?$', 'index.php?pagename=' . $pageName1 . '&' . $slugOptions['shop_url_productdetail_slug'] . '=$matches[2]', 'top');
            add_rewrite_rule($slugOptions['shop_url_productdetail_slug'] . '/([^&]+)\.html/?$', 'index.php?page_id=' . $frontPageId . '&' . $slugOptions['shop_url_productdetail_slug'] . '=$matches[1]', 'top');

            // Produkt detail pages common
            add_rewrite_rule('([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(.?.+?)/' . $slugOptions['shop_url_productdetail_slug'] . '/([^&]+)/?$', 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=' . $pageName4 . '&' . $slugOptions['shop_url_productdetail_slug'] . '=$matches[5]', 'top');
            add_rewrite_rule('([a-zA-Z]{2})/(.?.+?)/' . $slugOptions['shop_url_productdetail_slug'] . '/([^&]+)/?$', 'index.php?lang=$matches[1]&pagename=' . $pageName2 . '&' . $slugOptions['shop_url_productdetail_slug'] . '=$matches[3]', 'top');
            add_rewrite_rule('([a-zA-Z]{2})/' . $slugOptions['shop_url_productdetail_slug'] . '/([^&]+)/?$', 'index.php?lang=$matches[1]&' . $slugOptions['shop_url_productdetail_slug'] . '=$matches[2]', 'top');
            add_rewrite_rule('(.?.+?)/' . $slugOptions['shop_url_productdetail_slug'] . '/([^&]+)/?$', 'index.php?pagename=' . $pageName1 . '&' . $slugOptions['shop_url_productdetail_slug'] . '=$matches[2]', 'top');
            add_rewrite_rule($slugOptions['shop_url_productdetail_slug'] . '/([^&]+)/?$', 'index.php?page_id=' . $frontPageId . '&' . $slugOptions['shop_url_productdetail_slug'] . '=$matches[1]', 'top');

            // Pagination
            add_rewrite_tag('%pagesp%', '([0-9]{1,})');

            // Pagination if html in url
            add_rewrite_rule('([a-zA-Z]{2})/(.?.+?)/pagesp/([0-9]{1,})\.html/?$', 'index.php?lang=$matches[1]&pagename=$matches[2]&pagesp=$matches[3]', 'top');
            add_rewrite_rule('([a-zA-Z]{2})/pagesp/([0-9]{1,})\.html/?$', 'index.php?lang=$matches[1]&pagesp=$matches[2]', 'top');
            add_rewrite_rule('(.?.+?)/pagesp/([0-9]{1,})\.html/?$', 'index.php?pagename=$matches[1]&pagesp=$matches[2]', 'top');
            add_rewrite_rule('pagesp/([0-9]{1,})\.html/?$', 'index.php?page_id=' . $frontPageId . '&pagesp=$matches[1]', 'top');

            // Pagination common
            add_rewrite_rule('([a-zA-Z]{2})/(.?.+?)/pagesp/([0-9]{1,})/?$', 'index.php?lang=$matches[1]&pagename=$matches[2]&pagesp=$matches[3]', 'top');
            add_rewrite_rule('([a-zA-Z]{2})/pagesp/([0-9]{1,})/?$', 'index.php?lang=$matches[1]&pagesp=$matches[2]', 'top');
            add_rewrite_rule('(.?.+?)/pagesp/([0-9]{1,})/?$', 'index.php?pagename=$matches[1]&pagesp=$matches[2]', 'top');
            add_rewrite_rule('pagesp/([0-9]{1,})/?$', 'index.php?page_id=' . $frontPageId . '&pagesp=$matches[1]', 'top');
        }

        public function privacyDeclarations()
        {
            if (function_exists('wp_add_privacy_policy_content')) {
                $content = '<h2>' . __('What personal data we collect and why we collect it') . '</h2>' .
                '<p>' .
                __(
                    'This plugin uses Spreadshirt API to create a unique basket for each visitor. This process doesn\'t use personalized data, so no IP address or browser information is transfered to Spreadshirt. The unique basket ID and checkout URL is stored in a browser cookie until the browser is closed (session). Adding an article to the basket using the "Add to basket" button, the plugin submits the desired article, color, size, basket ID and shop ID to Spreadshirt API which stores the information in the basket. If the visitor clicks on checkout, a link to the checkout process of Spreadshirt with the unique basket ID will be opened. At this point the visitor leaves this page and enters the pages of Spreadshirt and so the privacy policy of Spreadshirt. During basket creation and adding articles to the basket, no personalized data is somehow transfered to Spreadshirt or stored in this plugin.'
                )
                    . '</p>' .
                    '<p class="wp-policy-help"><strong>Deutsch:</strong><br>Dieses Plugin nutzt die Schnittstelle von Spreadshirt um einen eindeutigen Warenkorb für jeden Besucher zu erstellen. Dieser Prozess übermittelt und nutzt keine personenbezogenen Daten wie IP-Adresse oder Browser-Informationen, somit werden diese auch nicht an Spreadshirt übertragen. Der eindeutige Warenkorb ID und URL werden solange in einem Cookie gespeichert, bis der Browser geschlossen wird (Sitzung). Wird ein Artikel zum Warenkorb hinzugefügt, übermittelt das Plugin den gewünschten Artikel, Farbe, Größe, Warenkorb ID und Shop ID an die Schnittstelle von Spreadshirt, wo diese Daten dann gespeichert werden. Klickt der Besucher dann auf kaufen, wird er, mittels eines vorher generierten Links, an Spreadshirt weitergeleitet. Dieser Link enthält die vorher erstellte Warenkorb ID, um den Warenkorb eindeutig zu identifizieren. Ab diesem Punkt verlässt der Besucher diese Seite und wird auf die Seiten von Spreadshirt weitergeleitet, wo die Datenschutzbestimmungen von Spreadshirt gelten. Während der Erstellung des Warenkorbs und des Hinzufügens von Artikeln in den Warenkorb, werden keinerlei personenbezogene Daten an Spreadshirt weitergeleitet oder in diesem Plugin gespeichert.</p>';
                wp_add_privacy_policy_content(
                    'WP-Spreadplugin',
                    $content
                );
            }
        }

        public function overwriteLanguageIfNeeded()
        {
            // overwrite translation if language attribute available and set
            if (!empty(self::$shopOptions['shop_language'])) {
                $_ol = plugin_dir_path(__FILE__) . 'languages/' . 'spreadplugin' . '-' . self::$shopOptions['shop_language'] . '.mo';

                if (file_exists($_ol)) {
                    load_textdomain('spreadplugin', $_ol);
                }
            }
        }

        public function loadDefaultLanguage()
        {
            load_plugin_textdomain('spreadplugin', false, plugin_dir_path(__FILE__) . 'languages/');
        }

        /**
         * Overwrite title.
         */
        public function removeTitleDetailPage($content)
        {
            $slugOptions = $this->getAdminOptions();

            if (get_query_var($slugOptions['shop_url_productdetail_slug']) && $slugOptions['shop_additionalmods'] == 1) {
                return preg_replace('/<h1\sclass\=\"cat-title\">.*?<\/h1>/', '', $content);
            }

            return $content;
        }

        /**
         * Overwrite title.
         */
        public function removeClassesOnDetailPage($content)
        {
            $slugOptions = $this->getAdminOptions();

            if (get_query_var($slugOptions['shop_url_productdetail_slug']) && !empty($content)) {
                $dom = new DOMDocument();
                //https://php.kkbox.codes/manual/en/domdocument.savehtml.php#119767 but doesn't work here
                @$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NODEFDTD);
                $xPath = new DOMXPath($dom);
                $nodes = $xPath->query('//div[contains(@class,"spreadplugin-remove-on-detail")]');

                for ($i = $nodes->length; --$i >= 0;) {
                    $node = $nodes->item($i);
                    $node->parentNode->removeChild($node);
                }

                // if ($nodes->item(0)) {
                //     $nodes->item(0)->parentNode->removeChild($nodes->item(0));
                // }

                return str_replace(array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $dom->saveHTML());
            }

            return $content;
        }

        /**
         * We're going to pop off the paged breadcrumb and add in our own thing.
         *
         * @param object $trail the breadcrumb_trail object after it has been filled
         */
        public function addStaticBc($breadcrumb_trail)
        {
            $slugOptions = $this->getAdminOptions();
            $articleData = self::getCacheArticleData();
            $articleCleanData = array();

            if (get_query_var($slugOptions['shop_url_productdetail_slug']) && $slugOptions['shop_additionalmods'] == 1) {
                $articleId = self::replaceUnsecure(get_query_var($slugOptions['shop_url_productdetail_slug']));

                if (!empty($articleData)) {
                    foreach ($articleData as $arrDesigns) {
                        if (!empty($arrDesigns)) {
                            foreach ($arrDesigns as $_articleId => $arrArticle) {
                                $urlified = self::urlify($arrArticle['name'] . '-' . $_articleId);
                                $urlified2 = self::urlify($arrArticle['name'] . ' - ' . $arrArticle['productname'] . '-' . $_articleId);

                                if ($articleId == $_articleId || $articleId == $urlified || $articleId == $urlified2) {
                                    $breadcrumb = new bcn_breadcrumb(htmlspecialchars($arrArticle['name'], ENT_QUOTES), null, array('home'), null);
                                    array_splice($breadcrumb_trail->breadcrumbs, 0, 1, array($breadcrumb));
                                }
                            }
                        }
                    }
                }
            }
        }

        /**
         * [removeAccordionsDetailPage description].
         *
         * @param [type] $content [description]
         *
         * @return [type] [description]
         */
        public function removeAccordionsDetailPage($content)
        {
            $slugOptions = $this->getAdminOptions();

            if ($slugOptions['shop_additionalmods'] == 1) {
                if (get_query_var($slugOptions['shop_url_productdetail_slug'])) {
                    return '';
                }
            }

            return $content;
        }

        /**
         * Function createAuthHeader.
         *
         * Creates authentification header
         *
         * @param string $method [POST,GET]
         * @param string $url
         *
         * @return string
         */
        // public static function createAuthHeader($method, $url)
        // {
        //     $time = time() * 1000;

        //     $data = "${method} ${url} ${time}";
        //     $sig = sha1("${data} " . self::$shopOptions['shop_secret']);

        //     return 'Authorization: SprdAuth apiKey="' . self::$shopOptions['shop_api'] . "\", data=\"${data}\", sig=\"${sig}\"";
        // }

        /**
         * Function oldHttpRequest.
         *
         * creates the curl requests, until I get a fix for the wordpress request problems
         *
         * @param $url
         * @param $header
         * @param $method
         * @param $data
         * @param $len
         *
         * @return string bool
         */
        public static function oldHttpRequest($url, $header = null, $method = 'GET', $data = null, $len = null)
        {
            switch ($method) {
                case 'GET':

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

                    break;

                case 'POST':

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

                    break;

                case 'PUT':

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

                    curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

                    break;

                case 'DELETE':
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

                    break;
            }

            $result = curl_exec($ch);
            $info = curl_getinfo($ch);
            $status = isset($info['http_code']) ? $info['http_code'] : null;
            @curl_close($ch);

            if (in_array($status, array(
                200,
                201,
                204,
                403,
                406,
            ))) {
                return $result;
            }

            return false;
        }

        /**
         * Function getCacheArticleData.
         *
         * @return array Article data
         */
        protected static function getCacheArticleData()
        {
            return get_transient('spreadplugin4-article-cache-' . get_the_ID());
        }

        /**
         * function parseArticleData
         * Retrieves article data and collect.
         */
        protected function getRawArticleData($pageId)
        {
            $page = 1;
            $return = new stdClass();
            $return->article = array();
            $topic = '';
            $idea = '';

            $queryString = '';
            if (!empty(self::$shopOptions['shop_productcategory'])) {
                $queryString = urlencode(self::$shopOptions['shop_productcategory']);
            }
            if (!empty(self::$shopOptions['shop_category'])) {
                $catAddition = '';
                if (self::$shopOptions['shop_category'][0] !== 'T') {
                    $catAddition = 'T';
                }
                $queryString .= urlencode($catAddition . self::$shopOptions['shop_category']);
            }

            if (!empty(self::$shopOptions['shop_topic'])) {
                $topic = urlencode(strip_tags(trim(self::$shopOptions['shop_topic'])));
            }
            if (!empty(self::$shopOptions['shop_idea'])) {
                $idea = urlencode(strip_tags(trim(self::$shopOptions['shop_idea'])));
            }

            do {
                // read all available products
                $apiUrl = 'https://' . self::$shopOptions['shop_id'] . '.myspreadshop.' . self::$shopOptions['shop_source'] . '/' . self::$shopOptions['shop_id'] . '/shopData/list?size=&color=&collection=' . $topic . (!empty($idea) ? '&idea=' . $idea : '') . '&page=' . $page . '&query=' . $queryString . '&locale=' . (empty(self::$shopOptions['shop_language']) ? get_locale() : self::$shopOptions['shop_language']);

                $stringTypeJson = wp_remote_get($apiUrl, array('timeout' => 120, 'user-agent' => self::$userAgent));
                $stringTypeJson = wp_remote_retrieve_body($stringTypeJson);
                $objArticlesBase = self::isJson($stringTypeJson) ? json_decode($stringTypeJson, false) : false;

                if (!empty($objArticlesBase)) {
                    foreach ($objArticlesBase->articles as $article) {
                        $return->article[] = $article;
                    }
                }

                $page = $objArticlesBase->page + 1;
            } while ($objArticlesBase->page <= $objArticlesBase->numberOfPages && $objArticlesBase->numberOfPages > 0);

            return $return;
        }

        /**
         * function getSingleArticleData
         * Retrieves article data and save into cache.
         */
        protected function getSingleArticleData($pageId, $articleId, $productTypeId, $appearanceId, $productId, $viewId, $place, $ideaId, $sellableId)
        {
            $articleData = array();

            $apiUrlBase = 'https://' . self::$shopOptions['shop_id'] . '.myspreadshop.' . self::$shopOptions['shop_source'] . '/' . self::$shopOptions['shop_id'];
            $apiUrlBase .= '/shopData/detail?ideaId=' . $articleId . '&productTypeId=' . $productTypeId . '&appearanceId=' . $appearanceId . '&mediaType=json&locale=' . (empty(self::$shopOptions['shop_language']) ? get_locale() : self::$shopOptions['shop_language']);

            $stringTypeJson = wp_remote_get($apiUrlBase, array('timeout' => 120, 'user-agent' => self::$userAgent));
            $stringTypeJson = wp_remote_retrieve_body($stringTypeJson);
            $article = self::isJson($stringTypeJson) ? json_decode($stringTypeJson, false) : false;

            if (!is_object($article)) {
                return 'Article empty (object)';
            }

            if (!empty($article)) {
                $viewModelId = 0;

                if (!empty($article->mainImageUrls[0])) {
                    if (preg_match('/modelId\/([^\/,]*)/', $article->mainImageUrls[0], $productMatches)) {
                        $viewModelId = $productMatches[1];
                    }
                    if (preg_match('/modelId=(\d+)/', $article->mainImageUrls[0], $productMatches) && empty($viewModelId)) {
                        $viewModelId = $productMatches[1];
                    }
                }

                $articleData['name'] = $article->articleName;
                $articleData['appearance'] = (int) $article->selectedAppearanceId;
                $articleData['view'] = (int) $viewId;
                $articleData['viewModelId'] = $viewModelId;
                $articleData['type'] = (int) $article->productTypeId;
                $articleData['id'] = $articleId;
                $articleData['description'] = $article->designDescription;
                // $articleData['designTags'] = $article->designTags;
                //$articleData['pricenet'] = (float) $objArticlePriceData->vatExcluded;
                $articleData['pricebrut'] = $article->price;
                $articleData['currencycode'] = $article->currency;
                $articleData['productname'] = $article->productTypeName;
                $articleData['productshortdescription'] = $article->productTypeShortDescription;
                $articleData['productdescription'] = $article->productTypeLongDescription;
                $articleData['place'] = $place;
                $articleData['designid'] = $article->designId;
                $articleData['productid'] = $productId;
                $articleData['sellableProductId'] = null;
                $articleData['sellableId'] = !empty($article->sellableId) ? $article->sellableId : '';
                $articleData['ideaId'] = !empty($article->ideaId) ? $article->ideaId : '';

                // replace to use stock states || weiter unten ist neuer
                // sizes
                if (!empty($article->productTypeSizes->sizes)) {
                    foreach ($article->productTypeSizes->sizes as $val) {
                        $articleData['sizes'][$val->id]['onStock'] = $val->inStock;
                        $articleData['sizes'][$val->id]['name'] = $val->name;
                        $articleData['sizes'][$val->id]['measures'][0]['name'] = $val->name;
                        $articleData['sizes'][$val->id]['measures'][0]['value'] = $val->measureValues[0];
                        $articleData['sizes'][$val->id]['measures'][1]['name'] = $val->name;
                        $articleData['sizes'][$val->id]['measures'][1]['value'] = $val->measureValues[1];
                    }
                }

                if (!empty($article->appearances)) {
                    foreach ($article->appearances as $appearance) {
                        if ($article->selectedAppearanceId == $appearance->id) {
                            $articleData['default_bgc'] = $appearance->colors[0];
                        }

                        $articleData['appearances'][$appearance->id]['color'] = $appearance->colors[0];
                        $articleData['appearances'][$appearance->id]['onStock'] = $appearance->inStock;
                    }
                }
                // replace end

                if (!empty($article->mainImageUrls)) {
                    foreach ($article->mainImageUrls as $view) {
                        $viewId = 0;
                        $modelId = 0;

                        if (preg_match('/views\/([^\/,]*)/', $view, $productMatches)) {
                            $viewId = $productMatches[1];
                        }
                        if (preg_match('/viewId=(\d+)/', $view, $productMatches) && empty($viewId)) {
                            $viewId = $productMatches[1];
                        }
                        if (preg_match('/modelId\/([^\/,]*)/', $view, $productMatches)) {
                            $modelId = $productMatches[1];
                        }
                        if (preg_match('/modelId=(\d+)/', $view, $productMatches) && empty($modelId)) {
                            $modelId = $productMatches[1];
                        }
                        $articleData['views'][$viewId] = array('modelId' => $modelId);
                    }
                }

                return $articleData;
            }

            return 'Article empty';
        }

        /**
         * Function displayArticles.
         *
         * Displays the articles
         *
         * @return html
         */
        protected function displayArticles($id, $article, $backgroundColor = '', $isotope = true)
        {
            $imgSrc = '//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . urlencode($article['productid']) . '/views/' . $article['view'] . ',width=' . self::$shopOptions['shop_imagesize'] . ',height=' . self::$shopOptions['shop_imagesize'] . ',appearanceId=' . $article['appearance'] . ',typeId=' . $article['type'];

            if (!empty(self::$shopOptions['shop_modelids'])) {
                $modelId = self::returnModelId($article, self::$shopOptions);
                $imgSrc .= ',modelId=' . $modelId . ',crop=list,version=' . time();
            } elseif (!empty($article['viewModelId'])) {
                $imgSrc .= ',modelId=' . $article['viewModelId'] . ',crop=list,version=' . time();
            }

            $output = '<div class="spreadplugin-article spreadplugin-clearfix grid-view' . (true == $isotope ? ' spreadplugin-item' : '') . '" id="article_' . $id . '" style="width:' . (self::$shopOptions['shop_imagesize'] + 7) . 'px">';
            $output .= '<a name="' . $id . '"></a>';
            $output .= '<h3>' . (!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '') . (!empty($article['productname']) ? '<span class="product-name-addon"> ' . htmlspecialchars($article['productname'], ENT_QUOTES) . '</span>' : '') . '</h3>';
            $output .= '<form method="post" id="form_' . $id . '">';

            // edit article button
            if (1 == self::$shopOptions['shop_designer']) {
                $output .= ' <div class="edit-wrapper-integrated" data-designid="' . $article['designid'] . '" data-productid="' . (!empty($article['sellableProductId']) ? $article['sellableProductId'] : $article['productid']) . '" data-viewid="' . $article['view'] . '" data-appearanceid="' . $article['appearance'] . '" data-producttypeid="' . $article['type'] . '"><i></i></div>';
            }

            // display preview image
            $output .= '<div class="image-wrapper">';
            $output .= '<img src="';

            if (0 == self::$shopOptions['shop_lazyload']) {
                $output .= $imgSrc;
            } else {
                $output .= plugins_url('/img/blank.gif', __FILE__);
            }

            $output .= '" alt="' . (!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '') . (!empty($article['productname']) ? htmlspecialchars(' - ' . $article['productname'], ENT_QUOTES) : '') . '" id="previewimg_' . $id . '" data-zoom-image="//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . urlencode($article['productid']) . '/views/' . $article['view'] . ',width=800,height=800' . (!empty($backgroundColor) ? ',backgroundColor=' . $backgroundColor : '') . '" class="preview lazyimg" data-original="' . $imgSrc . '" />';
            $output .= '</div>';

            // add a select with available sizes
            if (isset($article['sizes']) && is_array($article['sizes'])) {
                $output .= '<div class="size-wrapper spreadplugin-clearfix"><span>' . __('Size', 'spreadplugin') . ':</span> <select class="size-select" name="size">';

                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<option value="' . $k . '"' . (is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? ' disabled="disabled" title="' . __('Out of stock', 'spreadplugin') . '"' : '') . '>' . (!empty($v['name']) ? $v['name'] : $k) . '</option>';
                }

                $output .= '</select></div>';
            }

            if (1 == self::$shopOptions['shop_enablelink']) {
                $output .= '<div class="details-wrapper2 spreadplugin-clearfix"><a href="' . $this->prettyProductUrl(self::urlify($article['name'] . '-' . $id)) . '">' . __('Details', 'spreadplugin') . '</a></div>';
            }

            $output .= '<div class="separator"></div>';

            // add a list with availabel product colors
            if (isset($article['appearances']) && is_array($article['appearances'])) {
                $output .= '<div class="color-wrapper spreadplugin-clearfix"><span>' . __('Color', 'spreadplugin') . ':</span> <ul class="colors" name="color">';

                foreach ($article['appearances'] as $k => $v) {
                    $output .= '<li value="' . $k . '"><div class="spreadplugin-color-item" style="background-color:' . (!empty($v['color']) ? strtoupper($v['color']) : '') . '" title="' . (is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? __('Out of stock', 'spreadplugin') : '') . '" class="' . (is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? 'spreadplugin-not-on-stock' : '') . '"></div></li>';
                }

                $output .= '</ul></div>';
            }

            // add a list with available product views
            if (isset($article['views']) && is_array($article['views'])) {
                $output .= '<div class="views-wrapper"><ul class="views" name="views">';

                foreach ($article['views'] as $k => $v) {
                    $output .= '<li value="' . $k . '"><img src="//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . urlencode($article['productid']) . '/views/' . $article['view'];
                    $output .= ',width=100,height=100,appearanceId=' . $article['appearance'] . ',typeId=' . $article['type'] . ',viewId=' . $k . '" class="previewview" alt="" id="viewimg_' . $id . '" /></li>';
                }

                $output .= '</ul></div>';
            }

            // Short product description
            $output .= '<div class="separator"></div>';
            $output .= '<div class="product-name">';
            $output .= htmlspecialchars($article['productname'], ENT_QUOTES);
            $output .= '</div>';

            // Show description link if not empty
            if (!empty($article['description'])) {
                $output .= '<div class="separator"></div>';

                if (0 == self::$shopOptions['shop_showdescription']) {
                    $output .= '<div class="description-wrapper"><div class="header"><a>' . __('Show article description', 'spreadplugin') . '</a></div><div class="description">' . htmlspecialchars($article['description'], ENT_QUOTES) . '</div></div>';
                } else {
                    $output .= '<div class="description-wrapper">' . htmlspecialchars($article['description'], ENT_QUOTES) . '</div>';
                }
            }

            // Show product description link if set
            if (1 == self::$shopOptions['shop_showproductdescription']) {
                $output .= '<div class="separator"></div>';

                if (0 == self::$shopOptions['shop_showdescription']) {
                    $output .= '<div class="product-description-wrapper"><div class="header"><a>' . __('Show product description', 'spreadplugin') . '</a></div><div class="description">' . $article['productdescription'] . '</div></div>';
                } else {
                    $output .= '<div class="product-description-wrapper">' . $article['productdescription'] . '</div>';
                }
            }

            $output .= '<input type="hidden" value="' . $article['appearance'] . '" id="appearance" name="appearance" />';
            $output .= '<input type="hidden" value="' . $article['view'] . '" id="view" name="view" />';
            $output .= '<input type="hidden" value="' . $article['sellableId'] . '" id="sellableId" name="sellableId" />';
            $output .= '<input type="hidden" value="' . $article['ideaId'] . '" id="ideaId" name="ideaId" />';
            $output .= '<input type="hidden" value="' . $article['appearance'] . '" id="defaultAppearance" name="defaultAppearance" />';
            $output .= '<input type="hidden" value="' . (!empty($article['sellableProductId']) ? $article['sellableProductId'] : $article['productid']) . '" id="article" name="article" />';
            $output .= '<input type="hidden" value="1" id="type" name="type" />';

            $output .= '<div class="separator"></div>';
            $output .= '<div class="price-wrapper">';
            if (1 == self::$shopOptions['shop_showextendprice']) {
                //$output .= '<span id="price-without-tax">'.__('Price (without tax):', 'spreadplugin').' '.self::formatPrice($article['pricenet'], $article['currencycode']).'<br /></span>';
                $output .= '<span id="price-with-tax">' . __('Price (with tax):', 'spreadplugin') . ' ' . self::formatPrice($article['pricebrut'], $article['currencycode']) . '</span>';
                $output .= '<br><div class="additionalshippingcosts">';
                $output .= __('excl. <a class="shipping-window">Shipping</a>', 'spreadplugin');
                $output .= '</div>';
            } else {
                $output .= '<div class="price-container"><div class="price-slug">' . __('Price:', 'spreadplugin') . '</div> <div class="price">' . self::formatPrice($article['pricebrut'], $article['currencycode']) . '</div></div>';
            }
            $output .= '</div>';

            $output .= '<input type="text" value="1" class="quantity" name="quantity" maxlength="4" />';

            // order buttons
            $output .= '<input type="submit" name="submit" value="' . __('Add to basket', 'spreadplugin') . '" /><br>';

            // Social buttons
            if (true == self::$shopOptions['shop_social']) {
                $output .= '
				<ul class="soc-icons">
				<li><a target="_blank" data-color="#5481de" class="fb" href="//www.facebook.com/sharer.php?u=' . urlencode($this->prettyProductUrl($id)) . '&t=' . rawurlencode(get_the_title()) . '" title="Facebook"><i></i></a></li>
				<li><a target="_blank" data-color="#06ad18" class="goog" href="//plus.google.com/share?url=' . urlencode($this->prettyProductUrl($id)) . '" title="Google"><i></i></a></li>
				<li><a target="_blank" data-color="#2cbbea" class="twt" href="//twitter.com/home?status=' . rawurlencode(get_the_title()) . ' - ' . urlencode($this->prettyProductUrl($id)) . '" title="Twitter"><i></i></a></li>
				<li><a target="_blank" data-color="#e84f61" class="pin" href="//pinterest.com/pin/create/button/?url=' . rawurlencode($this->prettyProductUrl($id)) . '&media=' . rawurlencode('https://image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . $article['productid'] . '/views/' . $article['view'] . ',width=' . self::$shopOptions['shop_imagesize'] . ',height=' . self::$shopOptions['shop_imagesize'] . '') . ',width=' . self::$shopOptions['shop_imagesize'] . ',height=' . self::$shopOptions['shop_imagesize'] . '&description=' . (!empty($article['description']) ? htmlspecialchars($article['description'], ENT_QUOTES) : 'Product') . '" title="Pinterest"><i></i></a></li>
				</ul>
				';
            }

            $output .= '
					</form>
					</div>';

            return $output;
        }

        /**
         * Function displayListArticles.
         *
         * Displays the articles
         *
         * @return html
         */
        protected function displayListArticles($id, $article, $backgroundColor = '', $isotope = true)
        {
            $imgSrc = '//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . urlencode($article['productid']) . '/views/' . $article['view'] . ',width=' . self::$shopOptions['shop_imagesize'] . ',height=' . self::$shopOptions['shop_imagesize'] . ',appearanceId=' . $article['appearance'] . ',typeId=' . $article['type'];

            if (!empty(self::$shopOptions['shop_modelids'])) {
                $modelId = self::returnModelId($article, self::$shopOptions);
                $imgSrc .= ',modelId=' . $modelId . ',crop=list,version=' . time();
            } elseif (!empty($article['viewModelId'])) {
                $imgSrc .= ',modelId=' . $article['viewModelId'] . ',crop=list,version=' . time();
            }

            $output = '<div class="spreadplugin-article list-view' . (true == $isotope ? ' spreadplugin-item' : '') . '" id="article_' . $id . '">';
            $output .= '<a name="' . $id . '"></a>';
            $output .= '<form method="post" id="form_' . $id . '"><div class="articleContentLeft">';

            // edit article button
            if (1 == self::$shopOptions['shop_designer']) {
                $output .= ' <div class="edit-wrapper-integrated" data-designid="' . $article['designid'] . '" data-productid="' . (!empty($article['sellableProductId']) ? $article['sellableProductId'] : $article['productid']) . '" data-viewid="' . $article['view'] . '" data-appearanceid="' . $article['appearance'] . '" data-producttypeid="' . $article['type'] . '"><i></i></div>';
            }

            // display preview image
            $output .= '<div class="image-wrapper">';
            $output .= '<img src="';

            if (0 == self::$shopOptions['shop_lazyload']) {
                $output .= $imgSrc;
            } else {
                $output .= plugins_url('/img/blank.gif', __FILE__);
            }

            $output .= '" alt="' . (!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '') . (!empty($article['productname']) ? htmlspecialchars(' - ' . $article['productname'], ENT_QUOTES) : '') . '" id="previewimg_' . $id . '" data-zoom-image="//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . urlencode($article['productid']) . '/views/' . $article['view'] . ',width=800,height=800' . (!empty($backgroundColor) ? ',backgroundColor=' . $backgroundColor : '') . '" class="preview lazyimg" data-original="' . $imgSrc . '" />';
            $output .= '</div>';

            // Short product description
            $output .= '<div class="product-name">';
            $output .= htmlspecialchars($article['productname'], ENT_QUOTES);
            $output .= '</div>';

            if (1 == self::$shopOptions['shop_enablelink']) {
                $output .= '<div class="details-wrapper2 spreadplugin-clearfix"><a href="' . $this->prettyProductUrl(self::urlify($article['name'] . '-' . $id)) . '">' . __('Details', 'spreadplugin') . '</a></div>';
            }

            $output .= '</div><div class="articleContentRight"><h3>' . (!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '') . (!empty($article['productname']) ? '<span class="product-name-addon"> ' . htmlspecialchars($article['productname'], ENT_QUOTES) . '</span>' : '') . '</h3>';

            // Show description link if not empty
            if (!empty($article['description'])) {
                if (0 == self::$shopOptions['shop_showdescription']) {
                    $output .= '<div class="description-wrapper"><div class="header"><a>' . __('Show article description', 'spreadplugin') . '</a></div><div class="description">' . htmlspecialchars($article['description'], ENT_QUOTES) . '</div></div>';
                } else {
                    $output .= '<div class="description-wrapper">' . htmlspecialchars($article['description'], ENT_QUOTES) . '</div>';
                }
            }

            // add a select with available sizes
            if (isset($article['sizes']) && is_array($article['sizes'])) {
                $output .= '<div class="size-wrapper spreadplugin-clearfix"><span>' . __('Size', 'spreadplugin') . ':</span> <select class="size-select" name="size">';

                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<option value="' . $k . '"' . (is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? ' disabled="disabled" title="' . __('Out of stock', 'spreadplugin') . '"' : '') . '>' . (!empty($v['name']) ? $v['name'] : $k) . '</option>';
                }

                $output .= '</select></div>';
            }

            // add a list with availabel product colors
            if (isset($article['appearances']) && is_array($article['appearances'])) {
                $output .= '<div class="color-wrapper spreadplugin-clearfix"><span>' . __('Color', 'spreadplugin') . ':</span> <ul class="colors" name="color">';

                foreach ($article['appearances'] as $k => $v) {
                    $output .= '<li value="' . $k . '"><div class="spreadplugin-color-item" style="background-color:' . (!empty($v['color']) ? strtoupper($v['color']) : '') . '" title="' . (is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? __('Out of stock', 'spreadplugin') : '') . '" class="' . (is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? 'spreadplugin-not-on-stock' : '') . '"></div></li>';
                }

                $output .= '</ul></div>';
            }

            // add a list with available product views
            if (isset($article['views']) && is_array($article['views'])) {
                $output .= '<div class="views-wrapper spreadplugin-clearfix"><ul class="views" name="views">';

                foreach ($article['views'] as $k => $v) {
                    $output .= '<li value="' . $k . '"><img src="//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . urlencode($article['productid']) . '/views/' . $article['view'];
                    $output .= ',width=100,height=100,appearanceId=' . $article['appearance'] . ',typeId=' . $article['type'] . ',viewId=' . $k . '" class="previewview" alt="" id="viewimg_' . $id . '" /></li>';
                }

                $output .= '</ul></div>';
            }

            $output .= '<input type="hidden" value="' . $article['appearance'] . '" id="appearance" name="appearance" />';
            $output .= '<input type="hidden" value="' . $article['view'] . '" id="view" name="view" />';
            $output .= '<input type="hidden" value="' . $article['sellableId'] . '" id="sellableId" name="sellableId" />';
            $output .= '<input type="hidden" value="' . $article['ideaId'] . '" id="ideaId" name="ideaId" />';
            $output .= '<input type="hidden" value="' . $article['appearance'] . '" id="defaultAppearance" name="defaultAppearance" />';
            $output .= '<input type="hidden" value="' . (!empty($article['sellableProductId']) ? $article['sellableProductId'] : $article['productid']) . '" id="article" name="article" />';
            $output .= '<input type="hidden" value="1" id="type" name="type" />';

            $output .= '<div class="price-wrapper spreadplugin-clearfix">';
            if (1 == self::$shopOptions['shop_showextendprice']) {
                $output .= '<span id="price-with-tax">' . __('Price (with tax):', 'spreadplugin') . ' ' . self::formatPrice($article['pricebrut'], $article['currencycode']) . '</span>';
                $output .= '<br><div class="additionalshippingcosts">';
                $output .= __('excl. <a class="shipping-window">Shipping</a>', 'spreadplugin');
                $output .= '</div>';
            } else {
                $output .= '<div class="price-container"><div class="price-slug">' . __('Price:', 'spreadplugin') . '</div> <div class="price">' . self::formatPrice($article['pricebrut'], $article['currencycode']) . '</div></div>';
            }
            $output .= '</div>';

            $output .= '<input type="text" value="1" class="quantity" name="quantity" maxlength="4" />';

            // order buttons
            $output .= '<input type="submit" name="submit" value="' . __('Add to basket', 'spreadplugin') . '" /><br>';

            // Social buttons
            if (true == self::$shopOptions['shop_social']) {
                $output .= '
				<ul class="soc-icons">
				<li><a target="_blank" data-color="#5481de" class="fb" href="//www.facebook.com/sharer.php?u=' . urlencode($this->prettyProductUrl($id)) . '&t=' . rawurlencode(get_the_title()) . '" title="Facebook"><i></i></a></li>
				<li><a target="_blank" data-color="#06ad18" class="goog" href="//plus.google.com/share?url=' . urlencode($this->prettyProductUrl($id)) . '" title="Google"><i></i></a></li>
				<li><a target="_blank" data-color="#2cbbea" class="twt" href="//twitter.com/home?status=' . rawurlencode(get_the_title()) . ' - ' . urlencode($this->prettyProductUrl($id)) . '" title="Twitter"><i></i></a></li>
				<li><a target="_blank" data-color="#e84f61" class="pin" href="//pinterest.com/pin/create/button/?url=' . rawurlencode($this->prettyProductUrl($id)) . '&media=' . rawurlencode('https://image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . $article['productid'] . '/views/' . $article['view'] . ',width=' . self::$shopOptions['shop_imagesize'] . ',height=' . self::$shopOptions['shop_imagesize'] . '') . ',width=' . self::$shopOptions['shop_imagesize'] . ',height=' . self::$shopOptions['shop_imagesize'] . '&description=' . (!empty($article['description']) ? htmlspecialchars($article['description'], ENT_QUOTES) : 'Product') . '" title="Pinterest"><i></i></a></li>
				</ul>
				';
            }

            $output .= '
			</div>
			</form>
			</div>';

            return $output;
        }

        /**
         * Function displayMinArticles.
         *
         * Displays the articles
         *
         * @return html
         */
        protected function displayMinArticles($id, $article, $backgroundColor = '', $isotope = true)
        {
            $imgSrc = '//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . urlencode($article['productid']) . '/views/' . $article['view'] . ',width=' . (self::$shopOptions['shop_imagesize'] == '280' ? '600' : self::$shopOptions['shop_imagesize']) . ',height=' . (self::$shopOptions['shop_imagesize'] == '280' ? '600' : self::$shopOptions['shop_imagesize']) . ',appearanceId=' . $article['appearance'] . ',typeId=' . $article['type'];

            if (!empty(self::$shopOptions['shop_modelids'])) {
                $modelId = self::returnModelId($article, self::$shopOptions);
                $imgSrc .= ',modelId=' . $modelId . ',crop=list,version=' . time();
            } elseif (!empty($article['viewModelId'])) {
                $imgSrc .= ',modelId=' . $article['viewModelId'] . ',crop=list,version=' . time();
            }

            $output = '<div class="spreadplugin-article spreadplugin-clearfix min-view' . (true == $isotope ? ' spreadplugin-item' : '') . '" id="article_' . $id . '" style="width:' . (self::$shopOptions['shop_imagesize'] + 7) . 'px">';
            $output .= '<a name="' . $id . '"></a>';
            $output .= '<form method="post" id="form_' . $id . '">';

            // edit article button
            if (1 == self::$shopOptions['shop_designer']) {
                $output .= ' <div class="edit-wrapper-integrated" data-designid="' . $article['designid'] . '" data-productid="' . (!empty($article['sellableProductId']) ? $article['sellableProductId'] : urlencode($article['productid'])) . '" data-viewid="' . $article['view'] . '" data-appearanceid="' . $article['appearance'] . '" data-producttypeid="' . $article['type'] . '"><i></i></div>';
            }

            // display preview image
            $output .= '<div class="image-wrapper">';
            if (!empty($article['created']) && strtotime($article['created']) > strtotime('-1 month')) {
                $output .= '<div class="new-badge">' . __('New', 'spreadplugin') . '</div>';
            }
            $output .= '<a href="' . $this->prettyProductUrl(self::urlify($article['name'] . ' - ' . $article['productname'] . '-' . $id)) . '"><img src="';

            if (0 == self::$shopOptions['shop_lazyload']) {
                $output .= $imgSrc;
            } else {
                $output .= plugins_url('/img/blank.gif', __FILE__);
            }

            $output .= '" alt="' . (!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '') . (!empty($article['productname']) ? htmlspecialchars(' - ' . $article['productname'], ENT_QUOTES) : '') . '" id="previewimg_' . $id . '" data-zoom-image="//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . urlencode($article['productid']) . '/views/' . $article['view'] . ',width=800,height=800' . (!empty($backgroundColor) ? ',backgroundColor=' . $backgroundColor : '') . '" class="preview lazyimg" data-original="' . $imgSrc . '" /></a>';
            $output .= '</div>';

            $output .= '<div class="product-name">' . (!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '') . (!empty($article['productname']) ? '<span class="product-name-addon"> ' . htmlspecialchars($article['productname'], ENT_QUOTES) . '</span>' : '') . '</div>';

            $output .= '<div class="price-wrapper">' . self::formatPrice($article['pricebrut'], $article['currencycode']) . '</div>';

            $output .= '<div class="actions">';

            // add a select with available sizes
            if (isset($article['sizes']) && is_array($article['sizes'])) {
                $output .= '<div class="size-wrapper spreadplugin-clearfix"><span>' . __('Size', 'spreadplugin') . ':</span> <select class="size-select" name="size">';

                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<option value="' . $k . '"' . (is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? ' disabled="disabled" title="' . __('Out of stock', 'spreadplugin') . '"' : '') . '>' . (!empty($v['name']) ? $v['name'] : $k) . '</option>';
                }

                $output .= '</select></div>';
            }

            // add a list with availabel product colors
            if (isset($article['appearances']) && is_array($article['appearances']) && count($article['appearances']) > 1) {
                $output .= '<div class="color-wrapper spreadplugin-clearfix"><span>' . __('Color', 'spreadplugin') . ':</span> <ul class="colors" name="color">';

                foreach ($article['appearances'] as $k => $v) {
                    $output .= '<li value="' . $k . '"><div class="spreadplugin-color-item" style="background-color:' . (!empty($v['color']) ? strtoupper($v['color']) : '') . '" title="' . (is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? __('Out of stock', 'spreadplugin') : '') . '" class="' . (is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? 'spreadplugin-not-on-stock' : '') . '"></div></li>';
                }

                $output .= '</ul></div>';
            }

            $output .= '<input type="hidden" value="' . $article['appearance'] . '" id="appearance" name="appearance" />';
            $output .= '<input type="hidden" value="' . $article['view'] . '" id="view" name="view" />';
            $output .= '<input type="hidden" value="' . $article['sellableId'] . '" id="sellableId" name="sellableId" />';
            $output .= '<input type="hidden" value="' . $article['ideaId'] . '" id="ideaId" name="ideaId" />';
            $output .= '<input type="hidden" value="' . $article['appearance'] . '" id="defaultAppearance" name="defaultAppearance" />';
            $output .= '<input type="hidden" value="' . (!empty($article['sellableProductId']) ? $article['sellableProductId'] : $article['productid']) . '" id="article" name="article" />';
            $output .= '<input type="hidden" value="1" id="type" name="type" />';

            $output .= '<div class="add-basket-wrapper spreadplugin-clearfix"><button type="submit" name="submit" class="add-basket-button" value=""><i></i></button></div>';

            // order buttons
            $output .= '<input type="hidden" value="1" class="quantity" name="quantity" />';

            $output .= '
			</div>
			</form>
			</div>';

            return $output;
        }

        protected static function reformatBasketItemsForPutting($basketItems)
        {

            return array_map(function ($row) {
                $appearanceEntry = array_search('appearance', array_column($row->element->properties, 'key'));
                $sizeEntry = array_search('size', array_column($row->element->properties, 'key'));

                return array(
                    'id' => $row->id,
                    'quantity' => $row->quantity,
                    'element' => array(
                        'id' => $row->element->id,
                        'type' => $row->element->type,
                        'properties' => array(

                            array(
                                'key' => 'size',
                                'value' => $row->element->properties[$sizeEntry]->value,
                            ),

                            array(
                                'key' => 'appearance',
                                'value' => $row->element->properties[$appearanceEntry]->value,
                            ),
                        ),
                        'shop' => array(
                            'id' => $row->shop->id,
                        ),
                    ),
                );
            }, $basketItems);

        }

        /**
         * Function Add basket item.
         *
         * @param $basketUrl
         * @param $namespaces
         * @param array $data
         */
        protected static function addBasketItem($basketUrl, $data)
        {
            // $basketItemsUrl = $basketUrl . '/items?locale=' . (empty(self::$shopOptions['shop_language']) ? get_locale() : self::$shopOptions['shop_language']);

            // 2019-01-22 Fetch always Product ID as its no more in cache (Sprd Telco)
            // if (!empty($data['ideaId'])) {
            //     $apiUrlBase = 'https://api.spreadshirt.' . self::$shopOptions['shop_source'] . '/api/v1/ideas/' . self::replaceUnsecure($data['ideaId']) . '/sellables/' . self::replaceUnsecure($data['sellableId']) . '/product?appearanceId=' . (int) $data['appearance'] . '&mediaType=json&locale=' . (empty(self::$shopOptions['shop_language']) ? get_locale() : self::$shopOptions['shop_language']);

            //     $stringTypeJson = wp_remote_get($apiUrlBase, array('timeout' => 120, 'user-agent' => self::$userAgent));
            //     $stringTypeJson = wp_remote_retrieve_body($stringTypeJson);
            //     $sellableProduct = self::isJson($stringTypeJson) ? json_decode($stringTypeJson) : false;

            //     if (!empty($sellableProduct->id)) {
            //         $data['articleId'] = $sellableProduct->id;
            //     }
            // }

            $currentBasketItems = self::getBasket($basketUrl);
            $basketItems = array();
            $productHref = (!empty($data['productId']) ? 'shops/' . (int) $data['shopId'] . '/products/' . self::replaceUnsecure($data['productId']) : '');

            if (empty($data['productId']) && empty($data['sellableId'])) {
                return "0";
            }

            if (!empty($currentBasketItems->basketItems)) {
                $basketItems = self::reformatBasketItemsForPutting($currentBasketItems->basketItems);
            }

            $basketItems = array_filter(json_decode(json_encode($basketItems), false), function ($row) use ($data) {

                $appearanceEntry = array_search('appearance', array_column($row->element->properties, 'key'));
                $sizeEntry = array_search('size', array_column($row->element->properties, 'key'));

                if ($row->element->id == self::replaceUnsecure($data['sellableId']) && $row->element->properties[$sizeEntry]->value == $data['size'] && $row->element->properties[$appearanceEntry]->value == $data['appearance']) {
                    return false;
                }
                return true;

            });

            if (!empty($data['sellableId'])) {
                $basketItems[] = array(
                    'quantity' => (int) $data['quantity'],
                    'element' => array(
                        'id' => self::replaceUnsecure($data['sellableId']),
                        'type' => 'sprd:sellable',
                        'properties' => array(

                            array(
                                'key' => 'size',
                                'value' => (int) $data['size'],
                            ),

                            array(
                                'key' => 'appearance',
                                'value' => (int) $data['appearance'],
                            ),
                        ),
                        'shop' => array(
                            'id' => (int) $data['shopId'],
                        ),
                    ),
                );
            } else {
                $basketItems[] = array(
                    'quantity' => (int) $data['quantity'],
                    'element' => array(
                        'id' => "",
                        'type' => 'sprd:product',
                        'href' => $productHref,
                        'properties' => array(

                            array(
                                'key' => 'size',
                                'value' => (int) $data['size'],
                            ),

                            array(
                                'key' => 'appearance',
                                'value' => (int) $data['appearance'],
                            ),
                        ),
                        'shop' => array(
                            'id' => (int) $data['shopId'],
                        ),
                    ),
                );

            }

            $basketContents = array(
                'basketItems' => $basketItems,
            );

            $header = array();
            // $header[] = self::createAuthHeader('PUT', $basketUrl);
            $header[] = 'Content-Type: application/x-www-form-urlencoded';

            $result = self::oldHttpRequest($basketUrl . '?mediaType=json', $header, 'PUT', json_encode($basketContents));

            if ($result) {
                return '1';
            }

            return '0';
        }

        /**
         * Function delete basket item.
         *
         * @param $basketUrl
         * @param $namespaces
         * @param array $data
         */
        protected static function deleteBasketItem($basketUrl, $itemId)
        {

            $currentBasketItems = self::getBasket($basketUrl);
            $basketItems = array();

            if (!empty($currentBasketItems->basketItems)) {
                $basketItems = self::reformatBasketItemsForPutting($currentBasketItems->basketItems);

            }

            $basketItems = array_filter(json_decode(json_encode($basketItems), false), function ($row) use ($itemId) {

                if ($row->id == $itemId) {
                    return false;
                }
                return true;

            });

            $basketContents = array(
                'basketItems' => array_values($basketItems),
            );

            $header = array();
            // $header[] = self::createAuthHeader('PUT', $basketUrl);
            $header[] = 'Content-Type: application/x-www-form-urlencoded';

            self::oldHttpRequest($basketUrl . '?mediaType=json', $header, 'PUT', json_encode($basketContents));

            // TODO: Add some responses and refresh then...
            // if ($result) {
            //     return '1';
            // }

            // return '0';

        }

        /**
         * Function Checkout.
         *
         * @param $basketUrl
         * @param $namespaces
         *
         * @return string $checkoutUrl
         */
        protected static function checkout($basketUrl)
        {
            $checkoutUrl = '';

            $basketCheckoutUrl = $basketUrl . '/checkout?mediaType=json';
            $header = array();
            // $header[] = self::createAuthHeader('GET', $basketCheckoutUrl);
            $header[] = 'Content-Type: application/json';
            $result = self::oldHttpRequest($basketCheckoutUrl, $header, 'GET');
            $response = self::isJson($result) ? json_decode($result, false) : false;

            if (!empty($response->href)) {
                $checkoutUrl = $response->href;
            } else {
                die('ERROR: Can\'t get checkout url.');
            }

            return $checkoutUrl;
        }

        /**
         * Function getBasket.
         *
         * retrieves the basket
         *
         * @param string $basketUrl
         *
         * @return object $basket
         */
        protected static function getBasket($basketUrl)
        {
            $header = array();
            $basket = '';

            if (!empty($basketUrl)) {
                // $header[] = self::createAuthHeader('GET', $basketUrl . '?locale=' . (empty(self::$shopOptions['shop_language']) ? get_locale() : self::$shopOptions['shop_language']));
                $header[] = 'Content-Type: application/json';
                $result = self::oldHttpRequest($basketUrl . '?locale=' . (empty(self::$shopOptions['shop_language']) ? get_locale() : self::$shopOptions['shop_language']) . '&mediaType=json', $header, 'GET');

                $basket = self::isJson($result) ? json_decode($result, false) : '';
            }

            return $basket;
        }

        /**
         * Function getInBasketQuantity.
         *
         * retrieves quantity of articles in basket
         *
         * @return int $intInBasket Quantity of articles
         */
        protected static function getInBasketQuantity($source)
        {
            $intInBasket = 0;
            @session_start();

            if (isset($_SESSION['basketUrl'][$source])) {
                $basketContents = self::getBasket($_SESSION['basketUrl'][$source]);

                if (!empty($basketContents->basketItems)) {
                    foreach ($basketContents->basketItems as $item) {
                        $intInBasket += $item->quantity;
                    }
                }
            }

            return $intInBasket;
        }

        /**
         * Function displayArticles.
         *
         * Displays the articles
         *
         * @return html
         */
        protected function displayDetailPage($id, $article, $backgroundColor = '')
        {
            $_toInches = false;
            if ('en_US' == self::$shopOptions['shop_language'] || 'en_GB' == self::$shopOptions['shop_language'] || 'us_US' == self::$shopOptions['shop_language'] || 'us_CA' == self::$shopOptions['shop_language'] || 'fr_CA' == self::$shopOptions['shop_language']) {
                $_toInches = true;
            }

            $sku = $article['id'] . '_' . $article['type'] . '_' . $article['appearance'];

            /**
             * Google Microdata.
             **
             * Disabled Elements:
             * "logo": "https://mosaic01.ztat.net/nvg/media/brandxl/7ee03a8e-534f-4a83-a574-8d3fafc5da34.jpg",
             * "manufacturer": "Nike Performance",
             * "color": "neonpink",
             * "aggregateRating": {
             *   "@type": "AggregateRating",
             *   "bestRating": 5,
             *   "ratingCount": 39,
             *   "ratingValue": "4.4615383",
             *   "reviewCount": 39,
             *   "worstRating": 1
             * },.
             */
            $output = '<script type="application/ld+json">
            {
              "@context": "http://schema.org",
              "@type": "Product",
              "image": "https://image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . urlencode($article['productid']) . '/views/' . $article['view'] . ',width=600,height=600",
              "itemCondition": "http://schema.org/NewCondition",
              "name": "' . (!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '') . (!empty($article['productname']) ? htmlspecialchars(' - ' . $article['productname'], ENT_QUOTES) : '') . '",
              "description": "' . htmlspecialchars(trim((!empty($article['description']) ? $article['description'] : '') . ' ' . $article['productshortdescription']), ENT_QUOTES) . '",
              "brand": "Spreadshirt",
              "sku": "' . $sku . '",
              "offers": [';

            $offerJson = '';
            if (!empty($article['sizes'])) {
                foreach ($article['sizes'] as $k => $v) {
                    $offerJson .= '
                        {
                          "@type": "Offer",
                          "availability": "http://schema.org/InStock",
                          "price": "' . $article['pricebrut'] . '",
                          "priceCurrency": "' . $article['currencycode'] . '",
                          "name": "' . $v['name'] . '",
                          "sku": "' . $sku . '-' . str_pad($k, 4, 0, STR_PAD_LEFT) . '"
                        },';
                }

                $output .= trim($offerJson, ', ');
            }

            $output .= '
              ]

            }
            </script>';

            $output .= '<div class="spreadplugin-article-detail spreadplugin-item" id="article_' . $id . '">';
            $output .= '<a name="' . $id . '"></a>';
            $output .= '<form method="post" id="form_' . $id . '">
            <div class="articleContentLeft">';

            // edit article button
            if (1 == self::$shopOptions['shop_designer']) {
                $output .= ' <div class="edit-wrapper-integrated" data-designid="' . $article['designid'] . '" data-productid="' . (!empty($article['productid']) ? $article['productid'] : '') . '" data-viewid="' . $article['view'] . '" data-appearanceid="' . $article['appearance'] . '" data-producttypeid="' . $article['type'] . '"><i></i></div>';
            }

            $imgSrc = '//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . urlencode($article['productid']) . '/views/' . $article['view'] . ',width=800,height=800,appearanceId=' . $article['appearance'] . ',typeId=' . $article['type'];

            if (!empty(self::$shopOptions['shop_modelids'])) {
                $modelId = self::returnModelId($article, self::$shopOptions);
                $imgSrc .= ',modelId=' . $modelId . ',crop=list,version=' . time();
            } elseif (!empty($article['viewModelId'])) {
                $imgSrc .= ',modelId=' . $article['viewModelId'] . ',crop=list,version=' . time();
            }

            // display preview image
            $output .= '<div class="image-wrapper">';
            $output .= '<img src="' . $imgSrc . '" class="preview" style="height:280px"  alt="' . (!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '') . (!empty($article['productname']) ? htmlspecialchars(' - ' . $article['productname'], ENT_QUOTES) : '') . '" id="previewimg_' . $id . '" data-zoom-image="' . $imgSrc . (!empty($backgroundColor) ? ',backgroundColor=' . $backgroundColor : '') . '" />';
            $output .= '</div>';

            // add a list with available product views
            if (isset($article['views']) && is_array($article['views'])) {
                $output .= '<div class="views-wrapper"><ul class="views" name="views">';

                foreach ($article['views'] as $k => $v) {
                    $output .= '<li value="' . $k . '" data-view-type="products"><img src="//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . urlencode($article['productid']) . '/views/' . $k;
                    $output .= ',width=100,height=100,appearanceId=' . $article['appearance'] . ',typeId=' . $article['type'] . '" class="previewview" alt="" id="viewimg_' . $id . '" /></li>';
                }

                $output .= '<li value="1" data-view-type="compositions"><img src="//image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/compositions/' . urlencode($article['productid']) . '/views/1';
                $output .= ',width=100,height=100,appearanceId=' . $article['appearance'] . ',typeId=' . $article['type'] . '" class="previewview" alt="" id="viewimg_' . $id . '" /></li>';

                $output .= '</ul></div>';
            }

            // Short product description
            $output .= '<div class="product-name">';
            $output .= htmlspecialchars($article['productname'], ENT_QUOTES);
            $output .= '</div>';

            $output .= '</div><div class="articleContentRight"><h1>' . (!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '') . (!empty($article['productname']) ? '<span class="product-name-addon"> ' . htmlspecialchars($article['productname'], ENT_QUOTES) . '</span>' : '') . '</h1>';

            // Show description link if not empty
            if (!empty($article['description'])) {
                $output .= '<div class="description-wrapper spreadplugin-clearfix">' . htmlspecialchars($article['description'], ENT_QUOTES) . '</div>';
            }

            // Show product description
            $output .= '<div class="product-description-wrapper spreadplugin-clearfix"><strong>' . __('Product details', 'spreadplugin') . '</strong><div>' . $article['productshortdescription'] . '</div></div>';

            $output .= '<div style="font-size: smaller; padding-bottom:10px">#<span>' . $sku . '</span></div>';

            // add a list with availabel product colors
            if (isset($article['appearances']) && is_array($article['appearances'])) {
                $output .= '<div class="color-wrapper spreadplugin-clearfix"><span>' . __('Color', 'spreadplugin') . ':</span> <ul class="colors" name="color">';

                foreach ($article['appearances'] as $k => $v) {
                    $output .= '<li value="' . $k . '"><div class="spreadplugin-color-item" style="background-color:' . (!empty($v['color']) ? strtoupper($v['color']) : '') . '" title="' . (is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? __('Out of stock', 'spreadplugin') : '') . '" class="' . (is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? 'spreadplugin-not-on-stock' : '') . '"></div></li>';
                }

                $output .= '</ul></div>';
            }

            // add a select with available sizes
            if (isset($article['sizes']) && is_array($article['sizes'])) {
                $output .= '<div class="size-wrapper"><span>' . __('Size', 'spreadplugin') . ':</span> <div class="style-select-size"><select class="size-select" name="size">';

                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<option value="' . $k . '"' . (is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? ' disabled="disabled" title="' . __('Out of stock', 'spreadplugin') . '"' : '') . '>' . (!empty($v['name']) ? $v['name'] : $k) . '</option>';
                }

                $output .= '</select></div></div>';
            }

            $output .= '<div class="quantity-wrapper"><span>' . __('Quantity:', 'spreadplugin') . '</span> <input type="text" value="1" class="quantity" name="quantity" maxlength="4" /></div>';

            $output .= '<input type="hidden" value="' . $article['appearance'] . '" id="appearance" name="appearance" />';
            $output .= '<input type="hidden" value="' . $article['view'] . '" id="view" name="view" />';
            $output .= '<input type="hidden" value="' . $article['sellableId'] . '" id="sellableId" name="sellableId" />';
            $output .= '<input type="hidden" value="' . $article['ideaId'] . '" id="ideaId" name="ideaId" />';
            $output .= '<input type="hidden" value="' . $article['appearance'] . '" id="defaultAppearance" name="defaultAppearance" />';
            $output .= '<input type="hidden" value="' . (!empty($article['sellableProductId']) ? $article['sellableProductId'] : $article['productid']) . '" id="article" name="article" />';
            $output .= '<input type="hidden" value="1" id="type" name="type" />';

            // $output .= '<div class="separator"></div>';
            $output .= '<div class="price-wrapper spreadplugin-clearfix">';
            if (1 == self::$shopOptions['shop_showextendprice']) {
                //$output .= '<span id="price-without-tax">'.__('Price (without tax):', 'spreadplugin').' '.self::formatPrice($article['pricenet'], $article['currencycode']).'<br /></span>';
                $output .= '<span id="price-with-tax">' . __('Price (with tax):', 'spreadplugin') . ' ' . self::formatPrice($article['pricebrut'], $article['currencycode']) . '</span>';
                $output .= '<br><div class="additionalshippingcosts">';
                $output .= __('excl. <a class="shipping-window">Shipping</a>', 'spreadplugin');
                $output .= '</div>';
            } else {
                $output .= '<div class="price-container"><div class="price-slug">' . __('Price:', 'spreadplugin') . '</div> <div class="price">' . self::formatPrice($article['pricebrut'], $article['currencycode']) . '</div></div>';
            }
            $output .= '</div>';

            // order buttons
            $output .= '<input type="submit" name="submit" value="' . __('Add to basket', 'spreadplugin') . '" />';

            $output .= '<div class="addtocart-claims">';
            if (!empty(self::$shopOptions['shop_claimcheck1'])) {
                $output .= '<div class="claims-row"><span class="claims-check">✓</span> ' . self::$shopOptions['shop_claimcheck1'] . '</div>';
            }
            if (!empty(self::$shopOptions['shop_claimcheck2'])) {
                $output .= '<div class="claims-row"><span class="claims-check">✓</span> ' . self::$shopOptions['shop_claimcheck2'] . '</div>';
            }
            $output .= '</div>';

            $output .= '<br>';

            // Social buttons
            if (true == self::$shopOptions['shop_social']) {
                $output .= '
				<ul class="soc-icons">
				<li><a target="_blank" data-color="#5481de" class="fb" href="//www.facebook.com/sharer.php?u=' . urlencode($this->prettyProductUrl($id)) . '&t=' . rawurlencode(get_the_title()) . '" title="Facebook"><i></i></a></li>
				<li><a target="_blank" data-color="#06ad18" class="goog" href="//plus.google.com/share?url=' . urlencode($this->prettyProductUrl($id)) . '" title="Google"><i></i></a></li>
				<li><a target="_blank" data-color="#2cbbea" class="twt" href="//twitter.com/home?status=' . rawurlencode(get_the_title()) . ' - ' . urlencode($this->prettyProductUrl($id)) . '" title="Twitter"><i></i></a></li>
				';
                $output .= '<li><a target="_blank" data-color="#e84f61" class="pin" href="//pinterest.com/pin/create/button/?url=' . rawurlencode($this->prettyProductUrl($id)) . '&media=' . rawurlencode('https://image.spreadshirtmedia.' . self::$shopOptions['shop_source'] . '/image-server/v1/products/' . $article['productid'] . '/views/' . $article['view'] . ',width=280,height=280') . ',width=' . self::$shopOptions['shop_imagesize'] . ',';
                $output .= 'height=' . self::$shopOptions['shop_imagesize'] . '&description=' . (!empty($article['description']) ? htmlspecialchars($article['description'], ENT_QUOTES) : 'Product') . '" title="Pinterest"><i></i></a></li>
				</ul>
				';
            }
            $output .= '
			</div>
			</form>
			';

            $output .= '
<div id="spreadplugin-tabs_wrapper">
	<div id="spreadplugin-tabs_content_container">
  <h2 class="spreadplugin-details-headline">' . __('Product Details', 'spreadplugin') . '</h2>
  <div id="tab3" class="spreadplugin-tab_content">
    <p>' . $article['productdescription'] . '</p>
  </div>
		<div id="tab2" class="spreadplugin-tab_content">
			<img alt="" src="https://image.spreadshirtmedia.net/image-server/v1/productTypes/' . $article['type'] . '/variants/size,width=130,height=130">

			<table class="assort_sizes">
			<thead>
			<tr>
			<th>' . __('Size', 'spreadplugin') . '</th>
			';

            if (isset($article['sizes']) && is_array($article['sizes'])) {
                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<th>' . (!empty($v['name']) ? $v['name'] : $k) . '</th>';
                }
            }

            $output .= '
			</tr>
			</thead>
			<tbody>
			<tr>
			<td>' . __('Dimension', 'spreadplugin') . ' A (' . ($_toInches ? 'inch' : 'cm') . ')</td>
			';

            if (isset($article['sizes']) && is_array($article['sizes'])) {
                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<td>' . (!empty($v['measures'][0]['value']) ? ($_toInches ? ($v['measures'][0]['value']) : $v['measures'][0]['value']) : $k) . '</td>';
                }
            }

            $output .= '
			</tr>
			<tr class="even">
			<td>' . __('Dimension', 'spreadplugin') . ' B (' . ($_toInches ? 'inch' : 'cm') . ')</td>
			';

            if (isset($article['sizes']) && is_array($article['sizes'])) {
                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<td>' . (!empty($v['measures'][1]['value']) ? ($_toInches ? ($v['measures'][1]['value']) : $v['measures'][1]['value']) : $k) . '</td>';
                }
            }

            $output .= '
			</tr>
			</tbody>
			</table>
			';

            $output .= '
		</div>
    <div id="tab1" class="spreadplugin-tab_content">
    			<p><img alt="" src="https://image.spreadshirtmedia.net/image-server/v1/productTypes/' . $article['type'] . '/variants/detail,width=560,height=150"></p>
    		</div>
	';

            if (!empty($article['printtypename'])) {
                $output .= '
			<div id="tab4" class="spreadplugin-tab_content">
				<p><strong>' . $article['printtypename'] . '</strong></p>
				<p>' . $article['printtypedescription'] . '</p>
			</div>
			';
            }
            $output .= '</div>
</div>
			';

            $output .= '</div>';

            return $output;
        }

        protected static function formatPrice($price, $currency)
        {
            return empty(self::$shopOptions['shop_language']) || 'en_US' == self::$shopOptions['shop_language'] || 'en_GB' == self::$shopOptions['shop_language'] || 'us_US' == self::$shopOptions['shop_language'] || 'us_CA' == self::$shopOptions['shop_language'] || 'fr_CA' == self::$shopOptions['shop_language'] ? "<span class=\"currency\">${currency}</span> " . number_format($price, 2, '.', '') : number_format($price, 2, ',', '.') . " <span class=\"currency\">${currency}</span>";
        }

        // Workaround for checkout language | the new checkout needs locale urgently
        protected static function workaroundLangUrl($url)
        {
            $_langCodeArr = @explode('_', (empty(self::$shopOptions['shop_language']) ? get_locale() : self::$shopOptions['shop_language']));
            $_langCode = $_langCodeArr[0];
            $langUrl = '';
            $checkoutUrl = $url; // failover, if no checkout url set

            if (!empty($_langCode)) {
                if (false === strpos($url, 'spreadshirt.com')) {
                    switch ($_langCode) {
                        case 'en':
                            $langUrl = 'spreadshirt.co.uk';
                            break;
                        case 'nb':
                            $langUrl = 'spreadshirt.no';
                            break;
                        case 'fr':
                            $langUrl = 'spreadshirt.fr';
                            break;
                        case 'de':
                            $langUrl = 'spreadshirt.de';
                            break;
                        case 'nl':
                            $langUrl = 'spreadshirt.nl';
                            break;
                        case 'fi':
                            $langUrl = 'spreadshirt.fi';
                            break;
                        case 'es':
                            $langUrl = 'spreadshirt.es';
                            break;
                        case 'it':
                            $langUrl = 'spreadshirt.it';
                            break;
                        case 'nn':
                            $langUrl = 'spreadshirt.no';
                            break;
                        case 'pl':
                            $langUrl = 'spreadshirt.pl';
                            break;
                        case 'sv':
                        case 'se':
                            $langUrl = 'spreadshirt.se';
                            break;
                        case 'pt':
                            break;
                        case 'pl':
                            break;
                        case 'be':
                            $langUrl = 'spreadshirt.be';
                            break;
                    }
                } else {
                    if ('CA' == $_langCodeArr[1]) {
                        $langUrl = 'spreadshirt.ca';
                    }
                }

                if (!empty($langUrl)) {
                    $checkoutUrl = str_replace(array('spreadshirt.net', 'spreadshirt.com'), $langUrl, $url);
                }
            }

            // Spreadshirt offers an customized checkout for some users, heres the workaround
            if (false === stripos($url, 'shopId')) {
                $checkoutUrl .= '&shopId=' . (int) self::$shopOptions['shop_id'];
            }

            // back to shop link
            if (!empty(self::$shopOptions['shop_backtoshopurl'])) {
                $checkoutUrl .= '&continueShoppingLink=' . urlencode(self::$shopOptions['shop_backtoshopurl']);
                $checkoutUrl .= '&emptyBasketUrl=' . urlencode(self::$shopOptions['shop_backtoshopurl']);
            }

            return $checkoutUrl;
        }

        protected function prettyProductUrl($id)
        {
            $myPermalink = self::prettyPermalink();
            $slugOptions = $this->getAdminOptions();
            $permalinkRemoveHtml = (strpos(get_permalink(), '.html') !== false ? str_replace('.html', '', get_permalink()) : get_permalink());

            if (1 == $slugOptions['shop_rscuwo']) {
                if ('' != get_option('permalink_structure')) {
                    // using pretty permalinks, append to url
                    $url = user_trailingslashit($slugOptions['shop_url_productdetail_slug'] . '/' . $id) . (strpos(get_permalink(), '.html') !== false ? '.html' : '') . (!empty($slugOptions['shop_url_anchor']) ? '#' . $slugOptions['shop_url_anchor'] : '');
                } else {
                    $url = '?' . $slugOptions['shop_url_productdetail_slug'] . '=' . $id . (!empty($slugOptions['shop_url_anchor']) ? '#' . $slugOptions['shop_url_anchor'] : '');
                }
            } else {
                if ('' != get_option('permalink_structure')) {
                    // using pretty permalinks, append to url
                    $url = user_trailingslashit($permalinkRemoveHtml . ('/' != substr($permalinkRemoveHtml, -1) ? '/' : '') . $slugOptions['shop_url_productdetail_slug'] . '/' . $id) . (strpos(get_permalink(), '.html') !== false ? '.html' : '') . (!empty($slugOptions['shop_url_anchor']) ? '#' . $slugOptions['shop_url_anchor'] : '');
                } else {
                    $url = add_query_arg($slugOptions['shop_url_productdetail_slug'], $id, $myPermalink) . (!empty($slugOptions['shop_url_anchor']) ? '#' . $slugOptions['shop_url_anchor'] : '');
                }
            }

            return $url;
        }

        protected function prettyPagesUrl()
        {
            $myPermalink = self::prettyPermalink();
            $slugOptions = $this->getAdminOptions();
            $permalinkRemoveHtml = (strpos(get_permalink(), '.html') !== false ? str_replace('.html', '', get_permalink()) : get_permalink());

            $paged = (get_query_var('pagesp') ? get_query_var('pagesp') : 1);

            if (1 == $slugOptions['shop_rscuwo']) {
                if ('' != get_option('permalink_structure')) {
                    // using pretty permalinks, append to url
                    $url = user_trailingslashit('pagesp/' . ($paged + 1)) . (strpos(get_permalink(), '.html') !== false ? '.html' : '') . (!empty($slugOptions['shop_url_anchor']) ? '#' . $slugOptions['shop_url_anchor'] : '');
                } else {
                    $url = '?pagesp=' . ($paged + 1) . (!empty($slugOptions['shop_url_anchor']) ? '#' . $slugOptions['shop_url_anchor'] : '');
                }
            } else {
                if ('' != get_option('permalink_structure')) {
                    // using pretty permalinks, append to url
                    $url = user_trailingslashit($permalinkRemoveHtml . ('/' != substr(get_permalink(), -1) ? '/' : '') . 'pagesp/' . ($paged + 1)) . (strpos(get_permalink(), '.html') !== false ? '.html' : '') . (!empty($slugOptions['shop_url_anchor']) ? '#' . $slugOptions['shop_url_anchor'] : '');
                } else {
                    $url = add_query_arg(array('pagesp' => $paged + 1), $myPermalink) . (!empty($slugOptions['shop_url_anchor']) ? '#' . $slugOptions['shop_url_anchor'] : '');
                }
            }

            return $url;
        }

        protected static function prettyPermalink()
        {
            $frontPageId = get_option('page_on_front');

            if (get_the_ID() == $frontPageId) {
                $myPermalink = _get_page_link($frontPageId);
            } else {
                $myPermalink = get_permalink();
            }

            return $myPermalink;
        }

        protected static function getRidOfHttp($s)
        {
            return str_replace('http://', '//', $s);
        }

        protected function returnModelId($article, $options)
        {
            $typeToModel = array();
            $shortcodeValArray = @explode(',', $options['shop_modelids']);
            if (!empty($shortcodeValArray)) {
                foreach ($shortcodeValArray as $v) {
                    if (!empty($v)) {
                        $s = @explode(':', $v);
                        if (!empty($s)) {
                            $typeToModel[$s[0]] = (int) $s[1];
                        }
                    }
                }
            }

            if (array_key_exists($article['type'] . '-' . $article['appearance'], $typeToModel)) {
                $modelId = $typeToModel[$article['type'] . '-' . $article['appearance']];
            } elseif (array_key_exists($article['type'], $typeToModel)) {
                $modelId = $typeToModel[$article['type']];
            } else {
                $modelId = 1;
            }

            return $modelId;
        }

        protected static function replaceUnsecure($t)
        {
            return preg_replace('/[^A-Za-z0-9%:_-]/', '', $t);
        }

        protected static function urlify($string)
        {
            // replace non letter or digits by -
            $text = preg_replace('~[^\pL\d]+~u', '-', $string);

            // transliterate
            // if (extension_loaded('iconv')) {
            //     $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
            // }
            $text = remove_accents($text); // use wordpress function

            // remove unwanted characters
            $text = preg_replace('~[^-\w]+~', '', $text);

            // trim
            $text = trim($text, '-');

            // remove duplicated - symbols
            $text = preg_replace('~-+~', '-', $text);

            // lowercase
            $text = strtolower($text);

            if (empty($text)) {
                return 'na';
            }

            return $text;
        }
    } // END class WP_Spreadplugin

    new WP_Spreadplugin();
}

// Widget
class SpreadpluginBasketWidget extends WP_Widget
{
    public function __construct()
    {
        $widget_ops = array(
            'classname' => 'spreadplugin_basket_widget',
            'description' => __('Widget to display basket contents everywhere', 'spreadplugin'),
        );

        // Instantiate the parent object
        parent::__construct(
            'spreadplugin_basket_widget',
            __('Spreadplugin Basket', 'spreadplugin'),
            $widget_ops);

    }

    public function widget($args, $instance)
    {
        load_plugin_textdomain('spreadplugin', false, dirname(plugin_basename(__FILE__)) . '/languages');

        $output = '<div class="spreadplugin-checkout widget"><span></span> <a class="spreadplugin-checkout-link' . (!empty(WP_Spreadplugin::$shopOptions['shop_basket_text_icon']) && WP_Spreadplugin::$shopOptions['shop_basket_text_icon'] == 1 ? ' button' : '') . '">' . (empty(WP_Spreadplugin::$shopOptions['shop_basket_text_icon']) || 0 == WP_Spreadplugin::$shopOptions['shop_basket_text_icon'] ? __('Basket', 'spreadplugin') : '') . '</a></div>
<div id="spreadplugin-widget-cart" class="spreadplugin-cart"></div>';

        echo $output;
    }

    public function update($new_instance, $old_instance)
    {
        // Save widget options
    }

    public function form($instance)
    {
        // Output admin widget options form

    }
}

add_action('widgets_init', function () {
    register_widget('SpreadpluginBasketWidget');
});

// Widget
class SpreadpluginCouponWidget extends WP_Widget
{
    public function __construct()
    {
        // Instantiate the parent object
        parent::__construct(
            'spreadplugin_coupon_widget',
            __('Spreadplugin Coupon', 'spreadplugin'),
            array('description' => __('Widget to display coupon contents everywhere', 'spreadplugin'))
        );
    }

    public function widget($args, $instance)
    {
        load_plugin_textdomain('spreadplugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
        // get admin options (default option set on admin page)

        $output = '';

        if (!empty(WP_Spreadplugin::$shopOptions['shop_id'])) {
            $couponUrl = 'https://api.spreadshirt.' . WP_Spreadplugin::$shopOptions['shop_source'] . '/api/v1/shops/' . WP_Spreadplugin::$shopOptions['shop_id'] . '/currentPromotion';

            $header = array();
            // $header[] = WP_Spreadplugin::createAuthHeader('GET', $couponUrl);
            $result = WP_Spreadplugin::oldHttpRequest($couponUrl, $header, 'GET');
            $dateFormat = get_option('date_format');
//             $result = '{
            //     "description": "5% off everything",
            //     "validUntil": "2019-10-29T23:59:59",
            //     "code": "88S1C3S73E"
            // }';

            if (!empty($result) && WP_Spreadplugin::isJson($result)) {
                $json = json_decode($result, false);
                echo '<div class="spreadplugin-coupon-container">
<div class="spreadplugin-coupon-description">' . (!empty($json) && !empty($json->description) ? $json->description : '') . '</div>
<div class="spreadplugin-coupon-validuntil">' . (!empty($json) && !empty($json->validUntil) ? mysql2date($dateFormat, $json->validUntil) : '') . '</div>
<div class="spreadplugin-coupon-code">Code: ' . (!empty($json) && !empty($json->code) ? $json->code : '') . '</div>
</div>';
            }
        }

        echo $output;
    }

    public function update($new_instance, $old_instance)
    {
        // Save widget options
    }

    public function form($instance)
    {
        // Output admin widget options form
    }
}

add_action('widgets_init', function () {
    register_widget('SpreadpluginCouponWidget');
});

// function prefix_plugin_update_message($data, $response)
// {
//     if (isset($data['upgrade_notice'])) {
//         printf(
//             '<div class="update-message">%s</div>',
//             wpautop($data['upgrade_notice'])
//         );
//     }
// }
// add_action('wp-spreadplugin/spreadplugin.php', 'prefix_plugin_update_message', 10, 2);

/**
 * This function runs when WordPress completes its upgrade process
 * It iterates through each plugin updated to see if ours is included.
 *
 * @param $upgrader_object Array
 * @param $options Array
 */
function wp_spreadplugin_upgrade_completed($upgrader_object, $options)
{
    // The path to our plugin's main file
    $our_plugin = plugin_basename(__FILE__);
    // If an update has taken place and the updated type is plugins and the plugins element exists
    if ($options['action'] == 'update' && $options['type'] == 'plugin' && isset($options['plugins'])) {
        // Iterate through the plugins being updated and check if ours is there
        foreach ($options['plugins'] as $plugin) {
            if ($plugin == $our_plugin) {
                // Set a transient to record that our plugin has just been updated
                set_transient('wp_spreadplugin_updated', 1);
            }
        }
    }
}
add_action('upgrader_process_complete', 'wp_spreadplugin_upgrade_completed', 10, 2);

/**
 * Show a notice to anyone who has just updated this plugin
 * This notice shouldn't display to anyone who has just installed the plugin for the first time.
 */
function wp_spreadplugin_display_update_notice()
{
    // Check the transient to see if we've just updated the plugin
    if (get_transient('wp_spreadplugin_updated')) {
        echo '<div class="notice notice-success"><strong>WP-Spreadplugin</strong>: ' . __("Thanks for updating. It's recommended to rebuild cache after update.", 'spreadplugin') . '</div>';
        delete_transient('wp_spreadplugin_updated');
    }
}
add_action('admin_notices', 'wp_spreadplugin_display_update_notice');
