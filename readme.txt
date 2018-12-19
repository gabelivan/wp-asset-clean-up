=== Asset CleanUp: Page Speed Booster ===
Contributors: gabelivan
Tags: pagespeed, page speed, dequeue, performance, gtmetrix
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7GJZCW6RD8ECS
Requires at least: 4.0
Tested up to: 5.0.1
Stable tag: 1.2.8.9
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Make your website load FASTER by preventing specific scripts (.JS) & styles (.CSS) from loading on pages/posts and home page. Works best in addition to a cache plugin!

== Description ==
* Make your web pages load FASTER with **Asset CleanUp: Page Speed Booster**
* Faster page load = Happier Visitors = More Conversions

There are often times when you are using a theme and a number of plugins which are enabled and run on the same page. However, you don't need to use all of them and to improve the speed of your website and make the HTML source code cleaner (convenient for debugging purposes), it's better to prevent those styles and scripts from loading.

For instance, you might use a plugin that generates contact forms and it loads its assets (.CSS and .JS files) in every page of your website instead of doing it only in the /contact page (if that's the only place where you need it).

"Asset CleanUp" scans your page and detects all the assets that are loaded. All you have to do when editing a page/post is just to select the ones you DO NOT wish to load.

The plugin works best in combination with a cache plugin such as [WP Rocket](https://gabelivan.com/visit/wp-rocket).

= This plugin's benefits include =
* Decreases number of HTTP requests loaded (important for faster page load)
* Reduces the HTML code of the actual page (that's even better if GZIP compression is enabled)
* Makes source code easier to scan in case you're a developer and want to search for something
* Remove possible conflicts between plugins/theme (e.g. 2 JavaScript files that are loading from different plugins and they interfere one with another)
* Better performance score if you test your URL on websites such as GTmetrix, PageSpeed Insights, Pingdom Website Speed Test
* Google will love your website more as it would be faster and fast page load is nowadays a factor in search ranking
* Your server access log files (e.g the Apache ones) will be easier to scan and would take less space on your server

Plugin works with WordPress Multisite Network enabled!

> <strong>Asset CleanUp Pro</strong><br />
> This plugin is the lite version of Asset CleanUp Pro that comes with more features including managing assets (CSS and JS fies) on all WordPress pages, apply "async" and "defer" on loaded JavaScript files which would boost the speed score even higher, remove query strings from static resources (.css & .js), cleanup head section of the website. <a href="https://gabelivan.com/items/wp-asset-cleanup-pro/?utm_source=wp_org_lite&utm_medium=inside_quote">Click here to purchase Asset CleanUp Pro!</a>

= NOTES =
People that have tested the plugin are so far happy with it and I want to keep a good reputation for it. In case something is not working for you or have any suggestions, please write to me on the forum and I will be happy to assist you. **BEFORE rating this plugin**, please check the following post http://chrislema.com/theres-wrong-way-give-plugin-feedback-wordpress-org/ and then use your common sense when writing the feedback :)

= GO PRO =
Give Asset CleanUp a try! If you want to unlock more features, you can <a href="https://gabelivan.com/items/wp-asset-cleanup-pro/?utm_source=wp_org_lite&utm_medium=go_pro">Upgrade to the Pro version</a>.

== Installation ==
* If you're planning to use the Lite version of the plugin:

1. Go to "Plugins" -> "Add New" -> "Upload Plugin" and attach the downloaded ZIP archive from the plugin's page or use the "Search plugins..." form on the right side and look for "asset cleanup"
2. Install and activate the plugin (if server's PHP version is below 5.3, it will show you an error and activation will not be made).
3. Edit any Page / Post / Custom Post Type and you will see a meta box called "Asset CleanUp" which will load the list of all the loaded .CSS and .JS files. Alternatively, you will be able to manage the assets list in the front-end view as well (at the bottom of the pages) if you've enabled "Manage in the Front-end?" in plugin's settings page.
4. To unload the assets for the home page, go to "Asset CleanUp" menu on the left panel of the Dashboard and click "Home Page".

* I have purchased the Pro version. How to do the upgrade?
1. Go to "Plugins" -> "Installed Plugins", deactivate and then delete "Asset CleanUp: Page Speed Booster" (no worries, any settings applied would be preserved)
2. Go to "Plugins" -> "Add New" -> "Upload Plugin"; You will notice an upload form and an "Install Now" submit button. Download the ZIP file you received in your purchase email receipt (example: wp-asset-clean-up-pro-v1.0.8.7.zip), attach it to the form and install the new upgraded plugin.
3. Finally, click "Activate Plugin"! That's it :)

== Frequently Asked Questions ==
= What PHP version is required for this plugin to work? =

5.3+ - I strongly recommend you to use PHP 7+, if you're website is compatible with it, as it's much faster and it will make a big difference in terms of back-end speed.

= How do I know if my website’s page loading speed is slow and needs improvement? =
There are various ways to check the speed of a website and this is in relation to the following: front-end (the part of the website visible to your visitors), back-end (PHP code, server-side optimization), hosting company, CDN (Content Delivery Network) setup, files loaded (making sure CSS, JS, Images, Fonts, and other elements are properly optimized when processed by the visitor’s browser).

Check out <a href="https://gtmetrix.com/" target="_blank">https://gtmetrix.com/</a> to do an analysis of your website and see the overall score your website gets in PageSpeed and YSlow.

= What is an asset and which are the assets this plugin is dealing with? =

Web assets are elements such as CSS, JavaScript, Fonts, and image files that make the front-end which is the look and functionality of your website that is processed by the browser you are using (e.g. Google Chrome. Mozilla Firefox, Safari, Internet Explorer, Opera etc.). Asset CleanUp deals with CSS and JavaScript assets which are enqueued in WordPress by your theme and other plugins.

= Is this plugin a caching one?

No, Asset CleanUp does not do any page caching. It just helps you unload .css and .js that you choose as not needed from specific pages (or all pages). This, combined with an existing caching plugin, will make your website pages load faster and get a higher score in speed checking tools such as GTMetrix (Google PageSpeed and YSlow).

= Has this plugin been tested with other caching / speed booster plugins?

Yes, this plugin was tested with W3 Total Cache, WP Rocket and Autoptimize and should work with any caching plugin as any page should be cached only after the page (HTML Source) was rendered and all the enqueueing / dequeueing was already completed (from either the plugins or the theme).

= I've noticed scripts and styles that are loaded on the page, but they do not show in the "Asset CleanUp" list when editing the page or no assets are showing at all. Why is that? =

There are a few known reasons why you might see different or no assets loading for management:

- Those assets weren't loaded properly into WordPress by the theme/plugin author as they were likely hardcoded and not enqueued the WordPress way. Here's a tutorial that will help you understand better the enqueuing process: http://www.wpbeginner.com/wp-tutorials/how-to-properly-add-javascripts-and-styles-in-wordpress/

- You're using a cache plugin that is caching pages even when you're logged in which is something I don't recommend as you might have conflicts with other plugins as well (e.g. in W3 Total Cache, you can enable/disable this) or that plugin is caching pages even when a POST request is made to them (which is not a good practice as there are many situations in which a page should not be cached). That could happen if you're using "WP Remote POST" method (from version 1.2.4.4) of retrieving the assets in the Dashboard.

- You might have other functions or plugins (e.g. Plugin Organizer) that are loading prior to Asset CleanUp. Note that Plugin Organizer has a file that is in “mu-plugins” which will load prior to any plugin you have in “plugins”, thus, if you have disabled specific plugins through “Plugin Organizer” in some pages, their assets will obviously not show in the assets list as they are not loaded at all in the first place.

If none of these apply to you and you just don't see assets that should definitely show there, please open a support ticket.

= How can I access all the features? =

You can get access to more features, priority support and automatic updates by <a href="https://gabelivan.com/items/wp-asset-cleanup-pro/?utm_source=wp_org_lite&utm_medium=inside_faq">Upgrading to the Pro version</a>.

= jQuery and jQuery Migrate are often loading on pages/post. Are they always needed? =

Well known jQuery library is being used by many themes and plugins so it's recommended to keep it on. jQuery Migrate was created to simplify the transition from older versions of jQuery. It restores deprecated features and behaviors so that older code will still run properly on jQuery 1.9 and later.

However, there are cases when you might not need jQuery at all in a page. If that's the case, feel free to unload it. Make sure you properly test the page afterwards, including testing it for mobile view.

= Is the plugin working with WordPress Multisite Network? =

Yes, the plugin has been tested for WordPress Multisite and all its settings are applied correctly to any of the sites that you will be updating.

= When editing a post/page, I can see the message "We're getting the loaded scripts and styles for this page. Please wait...", but nothing loads! Why? =

The plugin makes AJAX calls to retrieve the data from the front-end page with 100% accuracy. Possible reasons why nothing is shown despite the wait might be:

- Your internet connection cut off after you loaded the edit post/post (before the AJAX calls were triggered). Make sure to check that and refresh the page if it's back on - it happened to me a few times

- There could be a conflict between plugins or your theme and something is interfering with the script that is retrieving the assets

- You are loading the WordPress Dashboard through HTTPS, but you are forcing the front-end to load via HTTP. Although Asset CleanUp auto-corrects the retrieval URL (e.g. if you're logged in the Dashboard securely via HTTPS, it will attempt to fetch the assets through HTTPS too), there could be cases where another plugin or .htaccess forces a HTTP connection only for the public view. Due to Same Origin Policy (read more here: https://developer.mozilla.org/En/Same_origin_policy_for_JavaScript), you can't make plain HTTP AJAX calls from HTTPS connections. If that's the case, try to enable "WP Remote POST" as a retrieval method in the plugin's settings if you want to manage the assets in the Dashboard.

- You're using plugins such as Wordfence that block the AJAX request. At this time, if that's the case, it's best to enable managing assets in the front-end view (Settings).

In this case, it's advisable to enable "Manage in the Front-end?" in "Settings" of "Asset CleanUp", thus making the list to show at the bottom of the posts, pages and front-page only for the logged in users with admin privileges.

Although I've written the code to ensure maximum compatibility, there are factors which are not up to the quality of the plugin that could interfere with it.
In case the assets are not loading for you, please write me on the forum and I will be happy to assist you!

= I do not know or I'm not sure which assets to unload on my pages. What should I do? =

With the recently released "Test Mode" feature, you can safely unload assets on your web pages without affecting the pages' functionality for the regular visitors. It will unload CSS & JavaScript files that you selected ONLY for yourself (logged-in administrator). That's recommended in case you have any doubts about whether you should applying a specific setting or unload any asset. Once you've been through the trial and error and your website is lighter, you can deactivate "Test Mode" and the changes will apply for everyone. Then, test the page speed score of your website :)

== Screenshots ==
1. When editing a post/page (custom post type as well) a meta box will load with the asset list
2. Styles (.CSS) loaded for the home page when accessing the "Asset CleanUp" Dashboard's menu
3. Scripts (.JS) loaded for the home page having an alert message when accessing the "Asset CleanUp" Dashboard's menu
4. Scripts (.JS) are selected for site-wide unload

== Changelog ==
= 1.2.8.9 =
* Only trigger specific actions when necessary to avoid the use of extra server resources
* Make sure "ver" query string is stripped on request only for the front-end view; Avoid removing the license info from the database when resetting everything (unless the admin chooses to remove the license info too for a complete uninstall)
* Updated the way temporary data is stored (from $_SESSION to WordPress transient) for more effective use of server resources

= 1.2.8.8 =
* Option to clean any license data after everything is reset in case the Pro version was used before on the same website
* Removed "Opt In" option from the non-sensitive diagnostic tracking until it will get replaced with a smoother version

= 1.2.8.7 =
* Bug Fix: When settings are reset to their default values via "Tools", make sure 'jQuery Migrate' and 'Comment Reply' are loading again if added in the bulk (site-wide) unload list (as by default they were not unloaded)

= 1.2.8.6 =
* Better support for WordPress 5.0 when updating a post/page within the Dashboard
* On new plugin installations, "Hide WordPress Core Files From The Assets List?" is enabled by default
* Added option for users to opt in to security and feature updates notifications, and non-sensitive diagnostic tracking
* Added "Tools" page which allows you to reset all settings or reset everything
* Bug Fix: Notice error was printing when there was no source file for specific handles that are loading inline code (e.g. woocommerce-inline)

= 1.2.8.5 =
* Option to hide WordPress core files from the management list to avoid applying settings to any of them by mistake (showing the core files for unload, async or defer are mostly useful for advanced developers in particular situations)
* Improved security of the pages by adding nonces everywhere there is an update button within the Dashboard related to the plugin
* Added confirmation message on top of the list in front-end view after an update is made (to avoid confusion whether the settings were updated or not)
* The height of an asset row (CSS or JavaScript) is now smaller as "Unload on this page" and bulk unloads (site-wide, by post type etc.) are placed on the same line if the screen width is large enough, convenient when going through a big list of assets

= 1.2.8.4 =
* Added "Input Fields Style" option in plugin's "Settings" which would turn the fancy CSS3 iPhone-like checkboxes to standard HTML checkboxes (good for people with disabilities who use a screen reader software or personal preference)
* Added notification in the front-end view in case WP Rocket is enabled with "User Cache" enabled
* Option to have the "Inline code associated with the handle" contracted on request as it will reduce the length of the assets management page in case there are large blocks of text making it easier to scan through the assets list
* Tested the plugin for full compatibility with PHP 7.2 (5.3+ minimum required to use it)

= 1.2.8.3 =
* Added the logo on top of each admin page belonging to the plugin
* Changed plugin's icon from the Dashboard left menu

= 1.2.8.2 =
* Added option to expand / contract "Styles" and "Scripts" management list and ability to choose the initial state on page load via plugin's "Settings" page

= 1.2.8.1 =
* Added "Test Mode" option which will unload assets only if the user is logged in as administrator and has capability of activating plugins.
* This is good for debugging in case one might worry that a CSS/JavaScript file could be unloaded by mistake and break the website for the regular (non-logged in) users.
* Once the page loads fine and all looks good, the "Test Mode" can be disabled so the visitors will load the lighter version of the page.

= 1.2.8 =
* Bug Fix: PHP code change to properly detect the singular pages had the wrong condition set

= 1.2.7.9 =
* Improved CSS styling for the assets list to avoid conflicts between other CSS rules from other themes and plugins

= 1.2.7.8 =
* PHP Code Improvement

= 1.2.7.7 =
* In case the assets can't be retrieved via AJAX calls within the Dashboard, the user will be notified about it and any response errors (e.g. 500 Internal Errors) would be printed for debugging purposes
* Make the user aware that there could be also CSS files loaded from the WordPress core that should be unloaded only if the user is comfortable with that
* Improved "Help" page by adding more explanations about how to upgrade to the Pro version and how to seek professional help in case you're stuck

= 1.2.7.6 =
* Bug Fix: "Everywhere" bulk unloads could not be removed from "Bulk Unloaded" page

= 1.2.7.5 =
* Bug Fix: When inline CSS code was attached to a handle, it would trigger an error and prevent the assets from printing in the back-end view

= 1.2.7.4 =
* Added "Feature Request" link
* Bug Fix: Sometimes scripts are loading on Dashboard view, but not showing on Front-end view
* Better detection for the home page especially if custom layouts are added like the one from "Extra" theme

= 1.2.7.3 =
* Made it more clear what bulk unloads are within the description of the options
* Added more extra options to the plugin's settings that become available if a premium upgrade is made
* Updated banner preview from the WordPress plugin's page

= 1.2.7.2 =
* Bug Fix: Sometimes, specific scripts were showing up on Dashboard view, but not showing on Front-end view
* Extra confirmation required when unloading site-wide "jQuery Migrate" and "Comment Reply" from the plugin's settings (to avoid accidental unload)

= 1.2.7.1 =
* Removed "@" from printing in the output when using AJAX call to fetch the assets, to avoid conflict with Cloudflare's email protection
* Replaced deprecated jQuery's live() with on() to avoid JavaScript error on the front-end in case jQuery Migrate is disabled

= 1.2.7 =
* Removed iCheck and replaced with pure CSS to make the plugin lighter
* Added top menu for easier navigation between plugin's pages;
* Added "Pages Info" with explanations regarding the type of pages that can be unloaded
* Removed "Lite" from the plugin's title

= 1.2.6.9 =
* Made sure that default.php (new file) is not missing within /templates/meta-box-loaded-assets/ directory

= 1.2.6.8 =
* Important: If you use the premium extension, please upgrade to 1.0.3
* Removed "WP" from the plugin's title
* Prevent the LITE plugin from loading if the PRO version is enabled as loading both plugins is not relevant anymore
* Avoided loading Asset CleanUp's own CSS and JS within the Dashboard view as they are irrelevant since the're only loaded for the admins that manage the plugin

= 1.2.6.7 =
* Bug Fix: "Unload on All Pages of [post type here] post type" was not showing within the Dashboard view

= 1.2.6.6 =
* Bug Fix: Assets were not retrieved within in the Dashboard for the home page
* Compatible with WP Asset CleanUp Pro

= 1.2.6.5 =
* Bug Fix: Fatal error "Can't use method return value in write context" (for PHP versions < 5.5)

= 1.2.6.4 =
* Bug Fix: When editing a post/page within the Dashboard and the "Update" button was pressed before the "WP Asset CleanUp" meta box was loading, it sent an empty unloaded list to the plugin and it deleted the current settings for that particular post/page

= 1.2.6.3 =
* Bug Fix: On some environments, a fatal error shows when activating the plugin (the issue was posted on the support and the ticket solved)

= 1.2.6.2 =
* Added "Disable jQuery Migrate Site-Wide?" and "Disable Comment Reply Site-Wide?" (which belong to WordPress core files and often are not used in a WordPress website) to "Settings" page for the convenience of the user
* Bug Fix: jQuery Migrate can be properly unloaded now without affecting the load of jQuery

= 1.2.6.1 =
* Bug Fix for PHP versions lower than 5.6 - Menu.php triggered a PHP warning as PHP constants were not allowed in class constants

= 1.2.6 =
* New Feature: Disable Emojis Site-Wide
* Hide "WP Asset Clean Up" menu if the logged in user doesn't have 'manage_options' capabilities (technically, it's just for administrators)

= 1.2.5.3 =
* Bug Fix: PHP Warning when array was passed to json_decode(), instead of string

= 1.2.5.2 =
* Bug Fix: Unload on All Pages of [post_type_here] post type wasn't keeping previous records when choosing new values to unload

= 1.2.5.1 =
* Bug Fix: Better accuracy for determining the current post ID and whether the page is the home page

= 1.2.5 =
* Bug Fix: Remove JavaScript error from window.btoa() in case the page contains non-latin characters
* Added "Get Help" page within the plugin's menu to anyone interested in hiring me or any of my colleagues for professional help related to the plugin or any other WordPress task

= 1.2.4.4 =
* Updated AJAX calls to work fine within the Dashboard even if mod_security Apache module is enabled as there were some problems on specific servers
* Added "Unload on this page" text next to the first checkbox to explain its purpose better
* Added "WP Remote Post" method to retrieve assets (in case the default "Direct" method doesn't work)
* Enable/disable asset list loading within the Dashboard (in case one prefers to only have it within the front-end)

= 1.2.4.3 =
* Bug Fix: PHP versions < 5.4 triggered errors

= 1.2.4.2 =
* Now styles that are loaded in the BODY section of the page are unloaded (if selected); Sometimes, in special cases, within "wp_footer" action (or other similar one such as "get_footer"), wp_enqueue_style is called

= 1.2.4.1 =
* Bug Fix: When the handle's key on update was equal with 0 (for remove global unload), the rule would not be remove *

= 1.2.4 =
* Bug Fix: Remove "Unload everywhere" rule had to be updated to work no matter what key is assigned to the handle in the array resulting from the JSON

= 1.2.3 =
* Assets can now be disabled for all the pages belonging to a specific post type
* The list of assets disabled globally (everywhere, for a specific post type etc.) can be managed in a single page too

= 1.2.2 =
* Bug Fix: Sometimes scripts in the footer were not detected for unloading

= 1.2.1 =
* Bug Fix: Sometimes the assets exceptions list (when disabled globally) for the homepage is not loaded from the right source

= 1.2 =
* Disable assets site-wide
* Add exceptions on pages where assets should load (if they are disabled everywhere)
* Bug Fix: Sometimes, due to website caching services/plugins, the HTML comments are removed needed from getting the assets

= 1.1.4.6 =
* Now the asset list can be updated on the front-end (below the loaded page, post, front page) if feature is enabled in the "Settings"
* The assets URL is now clickable and loads the CSS/JS file in a new tab

= 1.1.4.5 =
* Some assets containing specific ASCII characters in the URL were not shown. This is solved now and they will show fine in the list.
* A warning icon is shown next to each script that is part of WordPress core. Also, a message on the top of the list warns the user about the risks of unloading core files

= 1.1.4.4 =
* If the Dashboard is accessed through HTTPS, then the AJAX call to the front-end must be through HTTPS too - otherwise the call gets blocked and the assets list will not show (loading message will appear and confuse user)

= 1.1.4.3 =
* Improved code to not show any PHP errors in case WP_DEBUG constant is set to 'true'

= 1.1.4.2 =
* Prevent JavaScript errors from showing in the background and interfere with the functionality of other plugins in case script.min.js is loaded in pages where the plugin is not needed

= 1.1.4.1 =
* Prevent any calls to be made for non-published posts/pages as the list of assets is relevant only after the post is published and all assets (from plugins and the themes) are properly loaded on that post/page

= 1.1.4 =
* Bug fix that prevented the AJAX calls from triggering on specific WordPress settings

= 1.1.3 =
* Improved the code and made sure that the actual URL being fetch is shown to avoid confusion

= 1.1.2 =
* Fixed a bug that wasn't loading the iCheck jQuery plugin all the time when it was needed
* Better check if PHP version is 5.3+ (notification is shown only in the Dashboard and the plugin does not load in the front-end)

= 1.1 =
* Remove assets from loading in public custom post types too besides the basic 'post' and 'page' ones
* Remove assets from loading in home page as well if "Front page displays" is set to "Your latest posts" in "Settings" -> "Reading"
* The plugin uses is_front_page() function to determine where the visitor is on your website

= 1.0 =
* Initial Release