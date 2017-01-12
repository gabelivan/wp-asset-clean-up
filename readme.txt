=== WP Asset CleanUp ===
Contributors: gabelivan
Tags: speed, pagespeed, dequeue style, dequeue script, unload style, unload script, fast
Donate link: https://www.gabelivan.com/donate/
Requires at least: 4.0
Tested up to: 4.7.1
Stable tag: 1.2.4.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Make your website load FASTER by preventing specific scripts (.JS) & styles (.CSS) from loading on pages/posts and home page

== Description ==
* Make your web pages load FASTER with "WP Asset CleanUp"
* Faster page load = Happier Visitors = More Conversions

There are often times when you are using a theme and a number of plugins which are enabled and run on the same page. However, you don't need to use all of them and to improve the speed of your website and make the HTML source code cleaner (convenient for debugging purposes), it's better to prevent those styles and scripts from loading.

For instance, you might use a plugin that generates contact forms and it loads its assets (.CSS and .JS files) in every page of your website instead of doing it only in the /contact page (if that's the only place where you need it).

WP Asset CleanUp scans your page and detects all the assets that are loaded. All you have to do when editing a page/post is just to select the ones you DO NOT wish to load.

= This plugin's benefits include =
* Decreases number of HTTP requests loaded (important for faster load)
* Reduces the HTML code of the actual page (that's even better if GZIP compression is enabled)
* Makes source code easier to scan in case you're a developer and want to search for something
* Remove possible conflicts between plugins/theme (e.g. 2 JavaScript files that are loading from different plugins and they interfere one with another)
* Better performance score if you test your URL on websites such as GTmetrix, PageSpeed Insights, Pingdom Website Speed Test
* Google will love your website more as it would be faster and fast page load is nowadays a factor in search ranking
* Your server access log files (e.g the Apache ones) will be easier to scan and would take less space on your server

Plugin works with WordPress Multisite Network enabled!

NOTE: People that have tested the plugin are so far happy with it and I want to keep a good reputation for it. In case something is not working for you or have any suggestions, please write to me on the forum and I will be happy to assist you.

**BEFORE rating this plugin**, please check the following post http://chrislema.com/theres-wrong-way-give-plugin-feedback-wordpress-org/ and then use your common sense when writing the feedback.

== Installation ==
1. Upload the "wp-asset-clean-up" folder in your plugins folder
2. Activate the plugin (if server's PHP version is below 5.3, it will show you an error and activation will not be made)
3. Edit any page / post/ custom post and you will see a meta box called "WP Asset CleanUp" which will load the list of all the loaded .css and .js files
4. To unload the assets for the "Home Page", go to "WP Asset CleanUp" menu on the left panel of the Dashboard

== Frequently Asked Questions ==
= What PHP version is required for this plugin to work? =

5.3+

= I've noticed scripts and styles that are loaded on the page, but they do not show in the "WP Asset CleanUp" list when editing the page. Why is that? =

If that's the case, then those assets weren't loaded properly into WordPress by the theme/plugin author as they were likely hardcoded and not enqueued the WordPress way. Here's a tutorial that will help you understand better the enqueuing process: http://www.wpbeginner.com/wp-tutorials/how-to-properly-add-javascripts-and-styles-in-wordpress/

= jQuery and jQuery Migrate are often loading on pages/post. Are they always needed? =

Well known jQuery library is being used by many themes and plugins so it's recommended to keep it on. jQuery Migrate was created to simplify the transition from older versions of jQuery. It restores deprecated features and behaviors so that older code will still run properly on jQuery 1.9 and later.

However, there are cases when you might not need jQuery at all in a page. If that's the case, feel free to unload it. Make sure you properly test the page afterwards, including testing it for mobile view.

= Is the plugin working with WordPress Multisite Network? =

Yes, the plugin has been tested for WordPress Multisite and all its settings are applied correctly to any of the sites that you will be updating.

= When editing a post/page, I can see the message "We're getting the loaded scripts and styles for this page. Please wait...", but nothing loads! Why? =

The plugin makes AJAX calls to retrieve the data from the front-end page with 100% accuracy. Possible reasons why nothing is shown despite the wait might be:

- Your internet connection cut off after you loaded the edit post/post (before the AJAX calls were trigerred). Make sure to check that and refresh the page if it's back on - it happened to me a few times

- There could be a conflict between plugins or your theme and something is interfering with the script that is retrieving the assets

- You are loading the WordPress Dashboard through HTTPS, but you are forcing the front-end to load via HTTP.

In this case, it's advisable to enable "Manage in the Front-end?" in "Settings" of "WP Asset CleanUp", thus making the list to show at the bottom of the posts, pages and front-page only for the logged in users with admin privileges.

Although I've written the code to ensure maximum compatibility, there are factors which are not up to the quality of the plugin that could interfere with it.
In case the assets are not loading for you, please write me on the forum and I will be happy to assist you!

= In some pages, I do not see styles and scripts in the "WP Asset CleanUp" List =

If that's the case, you might have other functions or plugins (e.g. Plugin Organizer) that are loading prior to WP Asset CleanUp.

Note that Plugin Organizer has a file that is in "mu-plugins" which will load prior to any plugin you have in "plugins", thus, if you have disabled specific plugins through "Plugin Organizer" in some pages, their assets will obviously not show in the assets list as they are not loaded at all in the first place.

= I do not know or I'm not sure which assets to unload on my pages. What should I do? =

If that's the case, then it's advisable to consult with a developer (ideally the person who helped you with your website) to give you assistance in unloading the unused assets.

== Screenshots ==
1. When editing a post/page (custom post type as well) a meta box will load with the asset list
2. Styles (.CSS) loaded for the home page when accessing the "WP Asset CleanUp" Dashboard's menu
3. Scripts (.JS) loaded for the home page having an alert message when accessing the "WP Asset CleanUp" Dashboard's menu
4. Scripts (.JS) are selected for site-wide unload

== Changelog ==
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
* Prevent JavaScript errors from showing in the background and interfere with the functionality of other plugins in case script.js is loaded in pages where the plugin is not needed

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