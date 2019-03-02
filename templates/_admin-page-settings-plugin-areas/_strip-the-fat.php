<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
	exit;
}

$tabIdArea = 'wpacu-setting-strip-the-fat';
$styleTabContent = ($selectedTabArea === $tabIdArea) ? 'style="display: table-cell;"' : '';
?>
<div id="<?php echo $tabIdArea; ?>" class="wpacu-settings-tab-content" <?php echo $styleTabContent; ?>>
    <h2><span class="dashicons dashicons-info"></span> <?php _e('Prevent useless and often large CSS &amp; JS files increasing your total page size', WPACU_PLUGIN_TEXT_DOMAIN); ?></h2>
    <p class="wpacu-notice wpacu-warning" style="font-size: 13px;">Please read the following tips regarding the usage of <?php echo WPACU_PLUGIN_TITLE; ?> to avoid any inconveniences later on. This is useful if you haven't used a page speed booster plugin before or you're also using a caching plugin for the page caching feature. If you're already familiar with the plugin or read the explanations below, just mark this area as "read" using the slider button from the bottom of this page.</p>
    <p>The core functionality of <?php echo WPACU_PLUGIN_TITLE; ?>, as its name suggests, is to help you unload CSS Stylesheets (.css) &amp; JavaScript (.js) files through the <a href="<?php echo admin_url('admin.php?page=wpassetcleanup_assets_manager'); ?>">CSS &amp; JavaScript load manager</a> wherever they are loading in excess. This has always been the main purpose of this page speed booster plugin.</p>
    <p>It's recommended that you take this action first on whatever page you wish to optimize (e.g. homepage), before minification &amp; concatenation of the remaining loaded files (as you will eventually end up with less and smaller optimised files). They are extra features added to the plugin and are meant to further reduce the number of HTTP requests and also get a smaller page size as the minification will help with that.</p>
    <p>If you're already using other plugin for minification and concatenation, and you're happy with its configuration and decide to keep it, you can just use <?php echo WPACU_PLUGIN_TITLE; ?> for stripping the "fat" and the other plugin such as WP Rocket or Autoptimize will take the remaining files and optimize them. Don't enable minification/concatenation on both <?php echo WPACU_PLUGIN_TITLE; ?> and other caching plugin at the same time as this could lead to extra resources loaded and can generate conflicts or even duplicated files that will end up increasing the total page size.</p>

    <hr />
    <div style="margin: 20px 0 10px;"><strong style="font-size: 15px; line-height: 17px;">How are plugins such as WP Rocket, WP Fastest Cache, Autoptimize or W3 Total Cache working together with <?php echo WPACU_PLUGIN_TITLE; ?>?</strong></div>
    <p>Let's suppose you're optimising the homepage that has a total of 20 CSS/JS files loading and decided that 8 CSS &amp; JavaScript files are not needed there. Once they are prevented from loading (not deleted or altered in any way from their original source, this plugin doesn't do that), the remaining 12 files will be minified/combined (if you have this option enabled) by either <?php echo WPACU_PLUGIN_TITLE; ?>, WP Rocket or other plugin you decided to do this and saved into smaller and less files. This will end up in a decreased total page size, deferred unused CSS &amp; less HTTP requests resulting in a faster page load and a higher page speed performance score (via tools such as GTMetrix).</p>

    <hr />
    <div style="margin: 20px 0 10px;"><strong style="font-size: 15px; line-height: 17px;">Is a decrease in the total page size or a higher page speed score guaranteed?</strong></div>
    <p>As long as you will prevent useless files from loading, then you will for sure have a lighter &amp; faster website. If anything changes in your hosting configuration, the size of your images or any external scripts etc. that you're website is loading, then you could end up with a slower website and that is not dependent on <?php echo WPACU_PLUGIN_TITLE; ?> nor any other WordPress performance plugin as there are external things which will never depend entirely on a plugin.</p>

    <hr />
    <div style="margin: 20px 0 10px;"><strong style="font-size: 15px; line-height: 17px;">Can this plugin make the pages load slower?</strong></div>
    <p><?php echo WPACU_PLUGIN_TITLE; ?> doesn't add any extra files to load in the front-end view that will increase the number of HTTP requests in any way as it will defy its purpose. It's main task is to prevent other files from loading and cleaning up the HTML code. Moreover, by enabling concatenation (if your website is not using the HTTP/2 protocol), you will reduce the number of HTTP requests further. If you're using another plugin that also has an option for minification/concatenation and you have enabled the feature on both plugins (never do it), or haven't configured something the right way, you could end up with extra CSS/JS loaded that will eventually lead to a poorer page speed score and a slower website.</p>
    <p><?php echo WPACU_PLUGIN_TITLE; ?> will never alter (in any way) or delete CSS &amp; JS files from their original source (e.g. plugins, themes). Files created through minification/concatenation are cached and stored in <em>/wp-content/cache/asset-cleanup/</em> directory.</p>
    <hr />

    <label class="wpacu_switch">
        <input id="wpacu_wiki_read"
               type="checkbox"
			<?php echo (($data['wiki_read'] == 1) ? 'checked="checked"' : ''); ?>
               name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[wiki_read]"
               value="1" /> <span class="wpacu_slider wpacu_round"></span> </label>
    I understand how the plugin works and I will make sure to make proper tests (via "Test Mode" if necessary) after the changes I'm making. I'm aware that unloading the wrong CSS/JS files can break the layout and front-end functionality of the pages I'm optimising.
</div>