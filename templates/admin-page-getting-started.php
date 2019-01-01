<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}
?>
<div class="wpacu-wrap">
    <div class="about-wrap wpacu-about-wrap">
        <h1>Welcome to Asset CleanUp <?php echo WPACU_PLUGIN_VERSION; ?></h1>
        <p class="about-text wpacu-about-text">
            Thank you for installing this page speed booster plugin! Prepare to make your WordPress website faster &amp; lighter by removing the useless CSS &amp; JavaScript files from your pages. For maximum performance, Asset CleanUp works best when used with a caching plugin or a 3rd party solution such as Varnish.
            <img src="<?php echo WPACU_PLUGIN_URL; ?>/assets/images/wpacu-logo-transparent-bg-v1.png" alt="" />
        </p>

        <h2 class="nav-tab-wrapper wp-clearfix">
            <a href="<?php echo admin_url('admin.php?page=wpassetcleanup_getting_started&wpacu_for=how-it-works'); ?>" class="nav-tab <?php if ($data['for'] === 'how-it-works') { ?>nav-tab-active<?php } ?>">How it works</a>
            <a href="<?php echo admin_url('admin.php?page=wpassetcleanup_getting_started&wpacu_for=benefits-fast-pages'); ?>" class="nav-tab <?php if ($data['for'] === 'benefits-fast-pages') { ?>nav-tab-active<?php } ?>">Benefits of fast loading pages</a>
            <a href="<?php echo admin_url('admin.php?page=wpassetcleanup_getting_started&wpacu_for=start-optimization'); ?>" class="nav-tab <?php if ($data['for'] === 'start-optimization') { ?>nav-tab-active<?php } ?>">Start Optimization</a>
        </h2>

        <div class="about-wrap-content">
            <?php if ($data['for'] === 'how-it-works') { ?>
                <p>Often, our WordPress websites are loaded with elements that are not needed to load on specific pages or even everywhere. These assets (CSS &amp; JavaScript files) as well as inline code are adding up to the total size of the page, thus taking more time for the page to load.</p>
                <p>This could end up in a slow website that leads to page abandonment, poor ranking in Google search and sometimes conflict JavaScript errors where too many scripts are loading and one of them (or more) have poorly written code that is not autonomous and badly interacts with other code.</p>
                <hr />
                <p class="area-title">What Asset CleanUp really does?</p>
                <p>Asset CleanUp is not a caching plugin and doesn't just make the page faster once you install &amp; activate it. You need to select the assets that are not needed to load on your website which will in the end reduce considerably the number of HTTP requests and optimize the front-end side of your pages.</p>
                <p>Once the setup is completed, the pages will have a better speed score (this can be tested using tools such as GTMetrix) and combined with a caching plugin (such as <a target="_blank" href="https://gabelivan.com/visit/wp-rocket">WP Rocket</a>), it will improve the page speed even more. <span style="font-size: 17px;">🚀</span></p>
                <hr />
                <p class="area-title">Example (Stripping ~66% of "crap") <span style="font-size: 22px;">✨</span></p>
                <p>Let's suppose you have a page where 30 files (CSS &amp; JS) are loaded. All have a total size of 1.5 MB. Using Asset CleanUp, you can reduce the number to 12 files by unloading the other 18 files which are useless on the page. You've reduced the total size to 0.7 MB, this resulting in less time in downloading the assets, thus the page will load faster. If you also use a caching plugin and combined and minify the remaining 12 files, the total assets size becomes smaller to 0.5 MB. In the end, <strong>the assets will load 3 times faster and improve your page speed score</strong>. Moreover, the HTML source code will be cleaner and easier to go through in case you're a developer and need to do any debugging or just check something in the code.</p>
                <hr />
                <p class="area-title">Not sure how to configure it? <span style="font-size: 22px;">🤔</span></p>
                <p>No problem! You can enable "Test Mode" and any changes you make, will only be visible for you (the logged-in administrator), while the regular visitors will see the pages as if the plugin is not active. Once all is good, you can disable "Test Mode" (thus applying the settings to everyone), clear the page caching (if using a plugin or a server-side solution such as Varnish) and check out the page speed score. <a target="_blank" href="https://assetcleanup.com/docs/?p=84">Read more</a></p>
            <?php } elseif ($data['for'] === 'benefits-fast-pages') { ?>
                <p class="area-title">Higher search ranking</p>
                <p>Since 2010, there has been a signal in Google search ranking algorithms: site speed, which reflects how quickly a website responds to web requests.</p>
                <p>Speeding up websites is important — not just to site owners, but to all Internet users. Faster sites create happy users and Google has seen in their internal studies that when a site responds slowly, visitors spend less time there. But faster sites don't just improve user experience; recent data shows that improving site speed also reduces operating costs. Like Google, their users place a lot of value in speed — that's why they've decided to take site speed into account in their search rankings. They use a variety of sources to determine the speed of a site relative to other sites.
                <p><span class="dashicons dashicons-video-alt3"></span> <a href="https://www.youtube.com/watch?v=SO4YuDAkplU" target="_blank">How does Google determine page speed?</a></p>
                <hr />

                <p class="area-title">Visitor Experience</p>
                <p>For a customer (it's likely happened to you too) that wants to purchase something online, it's very frustrating to land on slow loading website. A blazing fast website, will keep your visitors happy, engaged, which will directly influence conversions. If a visitor doesn't get what he wants in a time he/she thinks it's reasonable, they will probably head to another website belonging to a competitor. As today's users expect a fast and streamlined web experience, you're losing business if you neglect this often overlooked aspect.</p>
                <hr />

                <p class="area-title">Better Developer Experience</p>
                <p>As developers, we often go through the HTML source code of the website, access the server (e.g. Apache, NGINX) logs that has the HTTP requests, and have to sometimes solve code conflict problems (e.g. between plugins) due to poorly written code. By preventing unnecessary files to load, having less HTTP requests, and cleaner HTML code, you will be able to easily go through the code (which is smaller), your log files will take less space on the server, will be easier to backup and analyse, and by having less JavaScript files loading, you will be reduce the changes of getting less JS errors that could interfere with the functionality of your website.</p>
                <hr />

                <p class="area-title">Higher Revenue</p>
                <p>Just about any major retailer is taking site speed as a very important factor for increasing conversions. According to Strangeloop, 57% of online customers will leave a website after waiting 3 seconds for the page to load. Moreover, 80% of those people will not return to that page. Some of them will tell others about their negative experience. This has a direct impact on the conversion rate, revenue and brand image.</p>

                <p style="margin-bottom: 0;"><em>"78% of users say they've felt STRESS OR ANGER while using a slow website."</em></p>
                <p style="margin-top: 5px; margin-bottom: 0;"><em>"44% of users say that slow online transaction make them ANXIOUS about the success of the transaction."</em></p>
                <p style="margin-top: 5px;"><em>"4% of people have THROWN THEIR PHONE while using a slow mobile site."</em></p>

	            <?php add_thickbox(); ?>
                <div id="wpacu-brain-slow-website-info" style="display:none;">
                    <img alt="" style="width: 100%;"
                         src="<?php echo WPACU_PLUGIN_URL; ?>/assets/images/your-brain-on-a-slow-website-infographic.jpg" />
                </div>

                <span class="dashicons dashicons-format-image"></span> <a href="#TB_inline?&width=1024&height=550&inlineId=wpacu-brain-slow-website-info"
                   class="thickbox">View "This Is Your Brain On A Slow Website" Infographic</a>
             <?php } elseif ($data['for'] === 'start-optimization') { ?>
                <p>The first step that needs to be taken is to check the list of your active plugins that load CSS &amp; JavaScript, as well as the active theme, and determine which .css &amp; .js assets are not needed on the pages you want to optimize.</p>

                <p class="area-title">Common Example: "Contact Form 7" plugin</p>
                <p>At the time of writing this (January 1, 2019), the plugin loads 2 files everywhere (site-wide), when most of the WordPress websites only use them in the contact page. These files are:</p>
                <ul>
                    <li><em>/wp-content/plugins/contact-form-7/includes/css/styles.css?ver=5.1.1</em> (Stylesheet File)</li>
                    <li><em>/wp-content/plugins/contact-form-7/includes/js/scripts.js?ver=5.1.1</em> (JavaScript File)</li>
                </ul>

                <p>Moreover, the JavaScript file has an inline code associated with it, which looks something like this:</p>

                <pre><code>&lt;script type=&#39;text/javascript&#39;&gt;
/* &lt;![CDATA[ */
var wpcf7 = {&quot;apiSettings&quot;:{&quot;root&quot;:&quot;https:\/\/www.yourdomain.com\/wp-json\/contact-form-7\/v1&quot;,&quot;namespace&quot;:&quot;contact-form-7\/v1&quot;},&quot;cached&quot;:&quot;1&quot;};
/* ]]&gt; */
&lt;/script&gt;
</code></pre>

                <p style="margin-top: 0;">These extra files loading, as well as the HTML code used to call them, not to mention the inline code associated with the JS file, add up to the total size of the page: the number of HTTP requests and the HTML source code size (this is a minor thing, but when dealing with tens of files, it adds up).</p>

                <p>Just like "Contact Form 7", there are plenty of other files that are loading from plugins and the active theme which shouldn't be loaded in many pages. Think about pages that have mostly text such as "Terms and Conditions", "Privacy Policy" or the "404 (Not Found)" page. These ones can be stripped by a lot of "crap" which will boost the speed score and offer a better visitor experience.</p>

                <p>Once you unload the right (the ones you know are not useful) files and test everything (via "Test Mode" to make sure your visitors will not be affected in case you break any page functionality), you can clear the cache if you're using a caching plugin and test your page speed score in GTMetrix or other similar tool that measures the page load. You will see an improvement. <span style="vertical-align: bottom; font-size: 20px; line-height: 20px;">😀️</span></p>
            <?php } ?>
        </div>
    </div>
</div>