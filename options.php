<?php

if (is_user_logged_in() && is_admin()) {
    load_plugin_textdomain('spreadplugin', false, dirname(plugin_basename(__FILE__)) . '/languages');

    $adminSettings = $this->defaultOptions;

    if (isset($_POST['update-splg_options'])) { //save option changes
        foreach ($adminSettings as $key => $val) {
            if (isset($_POST[$key])) {
                if ($key == 'shop_zoomimagebackground') {
                    $_POST[$key] = str_replace('#', '', $_POST[$key]);
                }
                $adminSettings[$key] = trim($_POST[$key]);
            }
        }

        update_option('splg_options', $adminSettings);
    }

    $adminOptions = $this->getAdminOptions();

    $this->registerRewriteRules();
    $this->flushRewriteRules();?>
<style type="text/css">
.form-table td {
	vertical-align: top;
}
ul.sploplist {
	list-style: inherit !important;
}
</style>
<div class="wrap">

  <h2>Spreadplugin Plugin Options &raquo; Settings</h2>
  <div id="sprdplg-message" class="updated fade" style="display:none"></div>
  <div class="metabox-holder">
    <div class="meta-box-sortables ui-sortable">
      <div class="postbox">
        <div class="handlediv" title="Click to toggle"><br />
        </div>
        <h3 class="hndle">Spreadplugin
          <?php _e('Settings', 'spreadplugin');?>
        </h3>
        <div class="inside">
          <p>
            <?php _e('These settings will be used as default and can be overwritten by the extended shortcode.', 'spreadplugin');?>
          </p>
          <form action="options-general.php?page=splg_options&saved" method="post" id="splg_options_form" name="splg_options_form">
            <?php wp_nonce_field('splg_options');?>
            <table border="0" cellpadding="3" cellspacing="0" class="form-table">
              <tr>
                <td valign="top">
                  <?php _e('Shop id:', 'spreadplugin');?>
                </td>
                <td><input type="text" name="shop_id" value="<?php echo empty($adminOptions['shop_id']) ? 0 : $adminOptions['shop_id']; ?>" class="only-digit required" /></td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Shop source:', 'spreadplugin');?>
                </td>
                <td><select name="shop_source" id="shop_source" class="required">
                    <option value="net" <?php echo 'net' == $adminOptions['shop_source'] ? ' selected' : ''; ?>>Europe</option>
                    <option value="com" <?php echo 'com' == $adminOptions['shop_source'] ? ' selected' : ''; ?>>US/Canada/Australia/Brazil</option>
                  </select></td>
              </tr>
              <!-- <tr>
                <td valign="top">
                  <?php _e('Spreadshirt API Key:', 'spreadplugin');?>
                </td>
                <td><input type="text" name="shop_api" value="<?php echo $adminOptions['shop_api']; ?>" class="required" /></td>
              </tr> -->
              <!-- <tr>
                <td valign="top">
                  <?php _e('Spreadshirt API Secret:', 'spreadplugin');?>
                </td>
                <td><input type="text" name="shop_secret" value="<?php echo $adminOptions['shop_secret']; ?>" class="required" /></td>
              </tr> -->
              <tr>
                <td valign="top">
                  <?php _e('Limit articles per page:', 'spreadplugin');?>
                </td>
                <td><input type="text" name="shop_limit" value="<?php echo empty($adminOptions['shop_limit']) ? 10 : $adminOptions['shop_limit']; ?>" class="only-digit" /></td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Image size:', 'spreadplugin');?>
                </td>
                <td><select name="shop_imagesize" id="shop_imagesize">
                    <option value="190" <?php echo 190 == $adminOptions['shop_imagesize'] ? ' selected' : ''; ?>>190</option>
                    <option value="280" <?php echo 280 == $adminOptions['shop_imagesize'] ? ' selected' : ''; ?>>280 (recommended)</option>
                    <option value="600" <?php echo 600 == $adminOptions['shop_imagesize'] ? ' selected' : ''; ?>>600</option>
                  </select> px</td>
              </tr>
              <!-- <tr>
                <td valign="top"><?php _e('Article category:', 'spreadplugin');?></td>
                <td>Please see <strong>How do I get the category Id?</strong> in FAQ<br />
                  <br />
                  <input type="text" name="shop_category" value="<?php echo $adminOptions['shop_category']; ?>" class="only-digit" /></td>
              </tr> -->
              <tr>
                <td valign="top">
                  <?php _e('Social buttons:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_social" value="0" <?php echo 0 == $adminOptions['shop_social'] ? ' checked' : ''; ?> />
                  <?php _e('Disabled', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_social" value="1" <?php echo 1 == $adminOptions['shop_social'] ? ' checked' : ''; ?> />
                  <?php _e('Enabled', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Product linking:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_enablelink" value="0" <?php echo 0 == $adminOptions['shop_enablelink'] ? ' checked' : ''; ?> />
                  <?php _e('Disabled', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_enablelink" value="1" <?php echo 1 == $adminOptions['shop_enablelink'] ? ' checked' : ''; ?> />
                  <?php _e('Enabled', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Sort articles by:', 'spreadplugin');?>
                </td>
                <td><select name="shop_sortby" id="shop_sortby">
                    <option>place</option>
                    <?php if (!empty(self::$shopArticleSortOptions)) {
        foreach (self::$shopArticleSortOptions as $val) {
            ?>
                    <option value="<?php echo $val; ?>" <?php echo $adminOptions['shop_sortby'] == $val ? ' selected' : ''; ?>>
                      <?php echo $val; ?>
                    </option>
                    <?php
}
    }?>
                  </select></td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Target of checkout links:', 'spreadplugin');?>
                </td>
                <td>
                  <?php _e('Enter the name of your target iframe or frame, if available. Default is _blank (new window).', 'spreadplugin');?>
                  <br />
                  <br />
                  <input type="text" name="shop_linktarget" value="<?php echo empty($adminOptions['shop_linktarget']) ? '_self' : $adminOptions['shop_linktarget']; ?>" /></td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Use iframe for checkout:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_checkoutiframe" value="0" <?php echo 0 == $adminOptions['shop_checkoutiframe'] ? ' checked' : ''; ?> />
                  <?php _e('Opens in separate window', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_checkoutiframe" value="1" <?php echo 1 == $adminOptions['shop_checkoutiframe'] ? ' checked' : ''; ?> />
                  <?php _e('Opens an iframe in the page content', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Use designer:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_designer" value="0" <?php echo 0 == $adminOptions['shop_designer'] ? ' checked' : ''; ?> />
                  <?php _e('None', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_designer" value="1" <?php echo 1 == $adminOptions['shop_designer'] || 2 == $adminOptions['shop_designer'] ? ' checked' : ''; ?> />
                  <?php _e('Designer (Spreadshirt Tablomat)', 'spreadplugin');?>
                  <br />
                  <br />
                  <?php _e('Designer Shop Id', 'spreadplugin');?>
                  <input type="text" name="shop_designershop" value="<?php echo $adminOptions['shop_designershop']; ?>" class="only-digit" />
                  <br />
                  <?php _e('If you have a Designer Shop at Spreadshirt then enter its ID here to only show the designs of your Designer Shop, otherwise all Spreadshirt Marketplace designs are shown.', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Always show article description:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_showdescription" value="0" <?php echo 0 == $adminOptions['shop_showdescription'] ? ' checked' : ''; ?> />
                  <?php _e('Disabled', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_showdescription" value="1" <?php echo 1 == $adminOptions['shop_showdescription'] ? ' checked' : ''; ?> />
                  <?php _e('Enabled', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Show product description under article description:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_showproductdescription" value="0" <?php echo 0 == $adminOptions['shop_showproductdescription'] ? ' checked' : ''; ?> />
                  <?php _e('Disabled', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_showproductdescription" value="1" <?php echo 1 == $adminOptions['shop_showproductdescription'] ? ' checked' : ''; ?> />
                  <?php _e('Enabled', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Display price without and with tax:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_showextendprice" value="0" <?php echo 0 == $adminOptions['shop_showextendprice'] ? ' checked' : ''; ?> />
                  <?php _e('Disabled', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_showextendprice" value="1" <?php echo 1 == $adminOptions['shop_showextendprice'] ? ' checked' : ''; ?> />
                  <?php _e('Enabled', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Zoom image background color:', 'spreadplugin');?>
                </td>
                <td><input type="text" name="shop_zoomimagebackground" class="colorpicker" value="<?php echo empty($adminOptions['shop_zoomimagebackground']) ? '#FFFFFF' : '#' . $adminOptions['shop_zoomimagebackground']; ?>" data-default-color="#FFFFFF"
                    maxlength="7" /></td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('View:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_view" value="0" <?php echo 0 == $adminOptions['shop_view'] ? ' checked' : ''; ?> />
                  <?php _e('Grid view', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_view" value="1" <?php echo 1 == $adminOptions['shop_view'] ? ' checked' : ''; ?> />
                  <?php _e('List view', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_view" value="2" <?php echo 2 == $adminOptions['shop_view'] || '' == $adminOptions['shop_view'] ? ' checked' : ''; ?> />
                  <?php _e('Min view', 'spreadplugin');?> (Recommended)</td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Basket text or icon:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_basket_text_icon" value="0" <?php echo 0 == $adminOptions['shop_basket_text_icon'] || '' == $adminOptions['shop_basket_text_icon'] ? ' checked' : ''; ?> />
                  <?php _e('Text', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_basket_text_icon" value="1" <?php echo 1 == $adminOptions['shop_basket_text_icon'] ? ' checked' : ''; ?> />
                  <?php _e('Icon', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Infinity scrolling:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_infinitescroll" value="0" <?php echo 0 == $adminOptions['shop_infinitescroll'] ? ' checked' : ''; ?> />
                  <?php _e('Disabled', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_infinitescroll" value="1" <?php echo 1 == $adminOptions['shop_infinitescroll'] || '' == $adminOptions['shop_infinitescroll'] ? ' checked' : ''; ?> />
                  <?php _e('Enabled', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Lazy load:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_lazyload" value="0" <?php echo 0 == $adminOptions['shop_lazyload'] ? ' checked' : ''; ?> />
                  <?php _e('Disabled', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_lazyload" value="1" <?php echo 1 == $adminOptions['shop_lazyload'] || '' == $adminOptions['shop_lazyload'] ? ' checked' : ''; ?> />
                  <?php _e('Enabled', 'spreadplugin');?>
                  <br />
                  <br />
                  <?php _e('If active, load images on view (speed up page load).', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Zoom type:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_zoomtype" value="0" <?php echo 0 == $adminOptions['shop_zoomtype'] || '' == $adminOptions['shop_zoomtype'] ? ' checked' : ''; ?> />
                  <?php _e('Inner', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_zoomtype" value="1" <?php echo 1 == $adminOptions['shop_zoomtype'] ? ' checked' : ''; ?> />
                  <?php _e('Lens', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_zoomtype" value="2" <?php echo 2 == $adminOptions['shop_zoomtype'] ? ' checked' : ''; ?> />
                  <?php _e('Disabled', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Shop language:', 'spreadplugin');?>
                </td>
                <td><select name="shop_language" id="shop_language">
                    <option value="" <?php echo empty($adminOptions['shop_language']) ? ' selected' : ''; ?>>
                      <?php _e('Wordpress installation language (default)', 'spreadplugin');?>
                    </option>
                    <option value="da_DK" <?php echo 'da_DK' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Dansk</option>
                    <option value="de_DE" <?php echo 'de_DE' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Deutsch</option>
                    <option value="nl_NL" <?php echo 'nl_NL' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Dutch (Nederlands)</option>
                    <option value="fi_FI" <?php echo 'fi_FI' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Suomi</option>
                    <option value="es_ES" <?php echo 'es_ES' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Español</option>
                    <option value="fr_FR" <?php echo 'fr_FR' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>French</option>
                    <option value="it_IT" <?php echo 'it_IT' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Italiano</option>
                    <option value="nb_NO" <?php echo 'nb_NO' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Norsk</option>
                    <option value="nn_NO" <?php echo 'nn_NO' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Nynorsk</option>
                    <option value="pl_PL" <?php echo 'pl_PL' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Jezyk polski</option>
                    <option value="pt_PT" <?php echo 'pt_PT' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Português</option>
                    <option value="jp_JP" <?php echo 'jp_JP' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Japanese</option>
                    <option value="be_FR" <?php echo 'be_FR' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Belgium / French</option>
                    <option value="sv_SE" <?php echo 'sv_SE' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>Swedish</option>
                    <option value="en_GB" <?php echo 'en_GB' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>English (GB)</option>
                    <option value="us_US" <?php echo 'us_US' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>English (US)</option>
                    <option value="us_CA" <?php echo 'us_CA' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>English (CA)</option>
                    <option value="fr_CA" <?php echo 'fr_CA' == $adminOptions['shop_language'] ? ' selected' : ''; ?>>French (CA)</option>
                  </select></td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Anchor:', 'spreadplugin');?>
                </td>
                <td># <input type="text" name="shop_url_anchor" placeholder="<?php _e('splshop or similar', 'spreadplugin');?>" value="<?php echo empty($adminOptions['shop_url_anchor']) ? '' : $adminOptions['shop_url_anchor']; ?>" />
                  <br />
                  <?php _e('If you are using one page themes or want to specify an anchor to add with url, enter it here. Please avoid using the same anchor name as in your menu - some themes are blocking it.', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Product detail slug:', 'spreadplugin');?>
                </td>
                <td><input type="text" name="shop_url_productdetail_slug" placeholder="<?php _e('splproduct or similar', 'spreadplugin');?>" value="<?php echo empty($adminOptions['shop_url_productdetail_slug']) ? 'splproduct' : $adminOptions['shop_url_productdetail_slug']; ?>"
                    class="only-letters" />
                  <br />
                  <?php _e('Don\'t change if unknown! You could harm your site - dangerous.<br>Anyway, you could change the product detail link name here (SEO, Permalink).', 'spreadplugin');?>
                </td>
              </tr>
              <!-- <tr>
                <td valign="top"><?php _e('Separate Product Detail Page (experimental):', 'spreadplugin');?></td>
                <td><input type="text" name="shop_url_productdetail_page" placeholder="Only for experienced users." value="<?php echo empty($adminOptions['shop_url_productdetail_page']) ? '' : $adminOptions['shop_url_productdetail_page']; ?>" />
                  <small><br />
                  <?php _e('Experimental: For SEO you want to specify a separate page with separate text and not want to use the Shop page. Please enter the name for your separate detail page here if needed. The page must contain the shortcode [spreadplugin]! The pagename you might get from the url like .../detailpage/ whereas "detailpage" would be that name. (This behaviour will change in future to make it easier to configure!)', 'spreadplugin');?></small></td>
              </tr> -->
              <tr>
                <td valign="top">
                  <?php _e('Custom CSS');?>
                </td>
                <td><textarea style="width: 300px; height: 215px; background: #EEE;" name="shop_customcss" class="custom-css"><?php echo stripslashes(htmlspecialchars($adminOptions['shop_customcss'], ENT_QUOTES)); ?></textarea></td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Debug mode:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_debug" value="0" <?php echo 0 == $adminOptions['shop_debug'] || '' == $adminOptions['shop_debug'] ? ' checked' : ''; ?> />
                  <?php _e('Off', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_debug" value="1" <?php echo 1 == $adminOptions['shop_debug'] ? ' checked' : ''; ?> />
                  <?php _e('On', 'spreadplugin');?>
                  <br /> If active, all your spreadshirt/spreadplugin data could be exposed, so please be carefull with this option!</td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Sleep timer:', 'spreadplugin');?>
                </td>
                <td><input type="text" name="shop_sleep" value="<?php echo empty($adminOptions['shop_sleep']) ? 0 : intval($adminOptions['shop_sleep']); ?>" class="only-digit" />
                  <br />
                  <strong>Don't change this value, if you have no problems rebuilding your article cache, otherwise it would take very long!</strong> Changing this value is only neccessary if you are experiencing problems when rebuilding cache. Some
                  webspaces (e.g. godaddy.com) have request limits, which you can avoid by setting this value to for example 10.</td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Red Sky Theme one page (Custom Part) Workaround:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_rscuwo" value="0" <?php echo 0 == $adminOptions['shop_rscuwo'] || '' == $adminOptions['shop_rscuwo'] ? ' checked' : ''; ?> />
                  <?php _e('Off', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_rscuwo" value="1" <?php echo 1 == $adminOptions['shop_rscuwo'] ? ' checked' : ''; ?> />
                  <?php _e('On', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Change "Back to shop" link in checkout:', 'spreadplugin');?>
                </td>
                <td><input type="text" name="shop_backtoshopurl" style="min-width:300px" placeholder="<?php _e('http://www.example.com or empty if default', 'spreadplugin');?>" value="<?php echo empty($adminOptions['shop_backtoshopurl']) ? '' : $adminOptions['shop_backtoshopurl']; ?>" /></td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Product on stock check:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_stockstates" value="0" <?php echo 0 == $adminOptions['shop_stockstates'] || '' == $adminOptions['shop_stockstates'] ? ' checked' : ''; ?> />
                  <?php _e('Off', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_stockstates" value="1" <?php echo 1 == $adminOptions['shop_stockstates'] ? ' checked' : ''; ?> />
                  <?php _e('On', 'spreadplugin');?>
                </td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Claim Check 1:', 'spreadplugin');?>
                </td>
                <td><input type="text" name="shop_claimcheck1" placeholder="<?php _e('Delivery in 3-5 days', 'spreadplugin');?>" value="<?php echo empty($adminOptions['shop_claimcheck1']) ? '' : $adminOptions['shop_claimcheck1']; ?>" /> Small text
                  under the "Add to Basket" button</td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Claim Check 2:', 'spreadplugin');?>
                </td>
                <td><input type="text" name="shop_claimcheck2" placeholder="<?php _e('30 days return', 'spreadplugin');?>" value="<?php echo empty($adminOptions['shop_claimcheck2']) ? '' : $adminOptions['shop_claimcheck2']; ?>" /> Small text under
                  the "Add to Basket" button</td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Claim Check 3:', 'spreadplugin');?>
                </td>
                <td><input type="text" name="shop_claimcheck3" placeholder="<?php _e('Express shipment possible', 'spreadplugin');?>" value="<?php echo empty($adminOptions['shop_claimcheck3']) ? '' : $adminOptions['shop_claimcheck3']; ?>" /> Small text under
                  the "Add to Basket" button</td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Display payment options in cart:', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_cartpaymenticons" value="0" <?php echo 0 == $adminOptions['shop_cartpaymenticons'] || '' == $adminOptions['shop_cartpaymenticons'] ? ' checked' : ''; ?> />
                  <?php _e('Off', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_cartpaymenticons" value="1" <?php echo 1 == $adminOptions['shop_cartpaymenticons'] ? ' checked' : ''; ?> />
                  <?php _e('On', 'spreadplugin');?></td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Additional Modifications (used at mommyshirt):', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_additionalmods" value="0" <?php echo 0 == $adminOptions['shop_additionalmods'] || '' == $adminOptions['shop_additionalmods'] ? ' checked' : ''; ?> />
                  <?php _e('Off', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_additionalmods" value="1" <?php echo 1 == $adminOptions['shop_additionalmods'] ? ' checked' : ''; ?> />
                  <?php _e('On', 'spreadplugin');?></td>
              </tr>
              <tr>
                <td valign="top">
                  <?php _e('Open basket when customer has added new product to basket', 'spreadplugin');?>
                </td>
                <td><input type="radio" name="shop_openbasketonadd" value="0" <?php echo 0 == $adminOptions['shop_openbasketonadd'] || '' == $adminOptions['shop_openbasketonadd'] ? ' checked' : ''; ?> />
                  <?php _e('Off', 'spreadplugin');?>
                  <br />
                  <input type="radio" name="shop_openbasketonadd" value="1" <?php echo 1 == $adminOptions['shop_openbasketonadd'] ? ' checked' : ''; ?> />
                  <?php _e('On', 'spreadplugin');?></td>
              </tr>
            </table>
            <input type="submit" name="update-splg_options" id="update-splg_options" class="button-primary" value="<?php _e('Update settings', 'spreadplugin');?>" />
            <input type="button" onclick="javascript:rebuild();" class="button-primary" value="<?php _e('Rebuild cache', 'spreadplugin');?>" />
          </form>
        </div>
      </div>
      <div class="postbox">
        <div class="handlediv" title="Click to toggle"><br />
        </div>
        <h3 class="hndle">Shortcode Samples</h3>
        <div class="inside">
          <h4>
            <?php _e('Minimum required shortcode', 'spreadplugin');?>
          </h4>
          <p>[spreadplugin]</p>
          <h4>
            <?php _e('Sample shortcode with category', 'spreadplugin');?>
          </h4>
          <p>[spreadplugin shop_productcategory=&quot;Parameter EU or Parameter US&quot;]</p>
          <p>Possible values and shortcodes for pre-defined (Spreadshirt default) categories are:</p>
          <table role="grid">
            <thead>
              <tr role="row">
                <th>
                  <div>Parameter EU</div>
                </th>
                <th>
                  <div>Parameter US</div>
                </th>
                <th>
                  <div>Category</div>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr role="row">
                <td>D1</td>
                <td>D1</td>
                <td>Männer</td>
              </tr>
              <tr role="row">
                <td>D3</td>
                <td>D2</td>
                <td>Frauen</td>
              </tr>
              <tr role="row">
                <td>D4</td>
                <td>D3</td>
                <td>Kinder</td>
              </tr>
              <tr role="row">
                <td>D5</td>
                <td>D4</td>
                <td>Accessoires</td>
              </tr>
              <tr role="row">
                <td>D19</td>
                <td>D18</td>
                <td>Hüllen</td>
              </tr>
              <tr role="row">
                <td>Q1</td>
                <td>Q1</td>
                <td>T-Shirts</td>
              </tr>
              <tr role="row">
                <td>Q2</td>
                <td>Q2</td>
                <td>Hoodies</td>
              </tr>
              <tr role="row">
                <td>Q3</td>
                <td>Q3</td>
                <td>Jacken/Westen</td>
              </tr>
              <tr role="row">
                <td>Q4</td>
                <td>Q4</td>
                <td>Poloshirts</td>
              </tr>
              <tr role="row">
                <td>Q5</td>
                <td>Q5</td>
                <td>Langarmshirts</td>
              </tr>
              <tr role="row">
                <td>Q6</td>
                <td>Q6</td>
                <td>Tank Tops</td>
              </tr>
              <tr role="row">
                <td>Q7</td>
                <td>Q7</td>
                <td>Hosen und Shorts</td>
              </tr>
              <tr role="row">
                <td>Q8</td>
                <td>Q8</td>
                <td>Sportbekleidung</td>
              </tr>
              <tr role="row">
                <td>Q9</td>
                <td>Q9</td>
                <td>Unterwäsche</td>
              </tr>
              <tr role="row">
                <td>P46</td>
                <td>P47</td>
                <td>Caps/Mützen</td>
              </tr>
              <tr role="row">
                <td>P56</td>
                <td>n/a</td>
                <td>Schals</td>
              </tr>
              <tr role="row">
                <td>P47</td>
                <td>P48</td>
                <td>Taschen und Rucksäcke</td>
              </tr>
              <tr role="row">
                <td>P48</td>
                <td>P49</td>
                <td>Schürzen</td>
              </tr>
              <tr role="row">
                <td>P54</td>
                <td>n/a</td>
                <td>Regenschirme</td>
              </tr>
              <tr role="row">
                <td>P53</td>
                <td>n/a</td>
                <td>Kuscheltiere</td>
              </tr>
              <tr role="row">
                <td>P49</td>
                <td>P110</td>
                <td>Tassen und Zubehör</td>
              </tr>
              <tr role="row">
                <td>P50</td>
                <td>P50</td>
                <td>Buttons und Anstecker</td>
              </tr>
              <tr role="row">
                <td>P58</td>
                <td>P54</td>
                <td>Sonstige</td>
              </tr>
              <tr role="row">
                <td>P113</td>
                <td>P120</td>
                <td>iPhone-Hüllen</td>
              </tr>
              <tr role="row">
                <td>P111</td>
                <td>P118</td>
                <td>Samsung-Hüllen</td>
              </tr>
              <tr role="row">
                <td>P117</td>
                <td>P122</td>
                <td>iPad-Hüllen</td>
              </tr>
              <tr role="row">
                <td>n/a</td>
                <td>P124</td>
                <td>iPod-Hüllen</td>
              </tr>
              <tr role="row">
                <td colspan="1">I123456</td>
                <td colspan="1"><span>I123456</span></td>
                <td colspan="1">Produkte mit Design 123456</td>
              </tr>
            </tbody>
          </table>
          <p>&nbsp;</p>
          <h4>
            <?php _e('Use one of the following shortcode extensions to overwrite or extend each single page.', 'spreadplugin');?> (only for experienced users) </h4>
          <p>
            <?php

    $_plgop = '';
    foreach ($adminOptions as $k => $v) {
        if (!in_array($k, array('shop_infinitescroll', 'shop_customcss', 'shop_debug', 'shop_sleep', 'shop_url_productdetail_slug', 'shop_url_productdetail_page'))) {
            $_plgop .= $k . '="' . $v . '"<br>';
        }
    }

    echo trim($_plgop);?>
          </p>
          <p>&nbsp;</p>
        </div>
      </div>
    </div>
  </div>
  <p>If you experience any problems or have suggestions, feel free to leave a message on <a href="http://wordpress.org/support/plugin/wp-spreadplugin" target="_blank">wordpress</a>.<br />
  </p>
  <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EZLKTKW8UR6PQ" target="_blank"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" alt="Jetzt einfach, schnell und sicher online bezahlen mit PayPal." /></a>
  <p>All donations valued greatly</p>
</div>
<script language='javascript' type='text/javascript'>
  function setMessage(msg) {
    jQuery("#sprdplg-message").append(msg); //.html(msg)
    jQuery("#sprdplg-message").show();
  }

  function rebuildItem(listcontent, cur1, cur2) {
    if (cur2 == 0) {
      setMessage((typeof listcontent[cur1].title !== 'undefined' ? "<h3>" + listcontent[cur1].title + "</h3>" : "") + "<p>Rebuilding Page " + (cur1 + 1) + " of " + listcontent.length + "...</p>");
    }
    if (cur2 >= listcontent[cur1].items.length) {
      setMessage("Done<br>");
      // storing items
      jQuery.ajax({
        url: "<?php echo admin_url('admin-ajax.php'); ?>",
        type: "POST",
        data: "action=rebuildCache&do=save&_pageid=" + listcontent[cur1].id + "&_ts=" + (new Date()).getTime(),
        timeout: 360000,
        cache: false,
        success: function(result) {
          //console.debug(result);
          setMessage("<p>Successfully stored page " + cur1 + "</p>");
        },
        error: function(request, status, error) {
          setMessage("<p>Error " + request.status + " storing page " + cur1 + "</p>");
        }
      });
      // next page
      cur1 = cur1 + 1;
      if (listcontent[cur1]) {
        rebuildItem(listcontent, cur1, 0);
      }
      return;
    }
    setMessage("Rebuilding Item " + (cur2 + 1) + " of " + listcontent[cur1].items.length + " (" + listcontent[cur1].items[cur2].articlename + ") <img src='" + listcontent[cur1].items[cur2].previewimage + "' width='32' height='32'>... ");
    jQuery.ajax({
      url: "<?php echo admin_url('admin-ajax.php'); ?>",
      type: "POST",
      data: "action=rebuildCache&do=rebuild&_pageid=" + listcontent[cur1].id + "&_articleid=" + listcontent[cur1].items[cur2].articleid + "&_producttypeid=" + listcontent[cur1].items[cur2].producttypeid + "&_appearanceid=" + listcontent[cur1].items[
        cur2].appearanceid + "&_productid=" + listcontent[cur1].items[cur2].productid + "&_viewid=" + listcontent[cur1].items[cur2].viewid + "&_pos=" + listcontent[cur1].items[cur2].place + "&_ts=" + (new Date()).getTime(),
      success: function(result) {
        setMessage(result + ' <br>');
        // next item
        cur2 = cur2 + 1;
        rebuildItem(listcontent, cur1, cur2);
      },
      error: function(request, status, error) {
        setMessage("Request not performed error " + request.status + '. Try next<br>');
        // skip to next item
        cur2 = cur2 + 1;
        rebuildItem(listcontent, cur1, cur2);
      }
    });
  }

  function rebuild() {
    jQuery('html, body').animate({
      scrollTop: 0
    }, 800);
    setMessage("<p>Reading pages. Please wait...</p>");
    jQuery.ajax({
      url: "<?php echo admin_url('admin-ajax.php'); ?>",
      type: "POST",
      data: "action=rebuildCache&do=getlist" + "&_ts=" + (new Date()).getTime(),
      timeout: 360000,
      cache: false,
      dataType: 'json',
      success: function(result) {
        var list = result;
        if (!list) {
          setMessage("<p>No pages found.</p>");
          return;
        }
        var curr1 = 0;
        var curr2 = 0;
        rebuildItem(list, curr1, curr2);
      },
      error: function(request, status, error) {
        setMessage("Getlist not performed error '" + error + " (" + request.status + ")'. Please check the browser console for more informations." + '<br>');
        console.log("Got following error message: " + request.responseText);
      }
    });
  }
  jQuery('.only-digit').keyup(function() {
    if (/\D/g.test(this.value)) {
      // Filter non-digits from input value.
      this.value = this.value.replace(/\D/g, '');
    }
  });
  jQuery('.only-letters').keyup(function() {
    if (/[^a-z]/gi.test(this.value)) {
      // Filter non-letters from input value.
      this.value = this.value.replace(/[^a-z]/gi, '');
    }
  });
  // select different locale if north america is set
  jQuery('#shop_locale').change(function() {
    var sel = jQuery(this).val();
    if (sel == 'us_US' || sel == 'us_CA' || sel == 'fr_CA') {
      jQuery('#shop_source').val('com');
    } else {
      jQuery('#shop_source').val('net');
    }
  });
  // bind to the form's submit event
  jQuery('#splg_options_form').submit(function() {
    var isFormValid = true;
    jQuery("#splg_options_form .required").each(function() {
      if (jQuery.trim(jQuery(this).val()).length == 0) {
        jQuery(this).parent().addClass("highlight");
        isFormValid = false;
      } else {
        jQuery(this).parent().removeClass("highlight");
      }
    });
    // Formularprüfung
    if (!isFormValid) {
      setMessage("<p><?php _e('Please fill in the highlighted fields!', 'spreadplugin');?></p>");
    } else {
      return true;
    }
    return false;
  });
  // add color picker
  jQuery(document).ready(function() {
    jQuery('.colorpicker').wpColorPicker();
  });
</script>
<?php
if (isset($_GET['saved'])) {
        /*echo '<script language="javascript">rebuild();</script>';*/
        echo '<script language="javascript">setMessage("<p>' . __('Successfully saved settings. Please click rebuild cache if necessary.', 'spreadplugin') . '</p>");</script>';
    }
}?>
