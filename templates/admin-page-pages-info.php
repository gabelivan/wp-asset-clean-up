<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}

include_once '_top-area.php';

// [wpacu_lite]
$availableForPro = '<a href="'.WPACU_PLUGIN_GO_PRO_URL.'?utm_source=plugin_pages_info" class="go-pro-link-no-style"><span class="tooltip">Available for Pro users<br />Click to upgrade!</span> <img width="20" height="20" src="'.WPACU_PLUGIN_URL.'/assets/icons/icon-lock.svg" valign="top" alt="" /></a>';
// [/wpacu_lite]
?>
<div class="wpacu-wrap">
    <h1><?php echo WPACU_PLUGIN_TITLE; ?></h1>

    <!-- [wpacu_lite] -->
    <p>* <em>Unloading assets (CSS &amp; JavaScript) for page types that have a locker next to their name requires an <a href="<?php echo WPACU_PLUGIN_GO_PRO_URL; ?>?utm_source=plugin_pages_info&utm_medium=top_note">upgrade to the Pro version</a>.</em></p>
    <!-- [/wpacu_lite] -->

    <div class="wpacu_table_wrap">
        <table class="table table-striped">
            <thead class="thead-default">
                <tr>
                    <th align="left">PAGE TYPE</th>
                    <th align="left">DESCRIPTION</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="17%"><strong>Homepage</strong></td>
                    <td>This could be: your latest posts or a static page ("Pages" type), depending on your configuration from <a target="_blank" href="https://codex.wordpress.org/Settings_Reading_Screen">Dashboard's "Settings" &#187; "Reading"</a> page. If the home page is not a static page, but it's showing the latest posts (default view), then you can manage its assets from Asset CleanUp's "Homepage" tab above. &#10230; <a href="https://codex.wordpress.org/Settings_Reading_Screen#Reading_Settings">Read more about "Reading Settings" for "Front page displays"</a></td>
                </tr>
                <tr>
                    <td><strong>Posts</strong></td>
                    <td>Post Type: 'post' (e.g. blog entries) &#10230; <a href="https://codex.wordpress.org/Posts_Screen">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Pages</strong></td>
                    <td>Post Type: 'page' (e.g. about us, contact) &#10230; <a href="https://codex.wordpress.org/Pages_Screen">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Attachment</strong></td>
                    <td>Post Type: 'attachment' (e.g. files from <a target="_blank" href="https://codex.wordpress.org/Media_Library_Screen">"Media" &#187; "Library"</a>, the page loaded usually prints the image or other media type) &#10230; <a href="https://codex.wordpress.org/Edit_Media">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Custom Post Type</strong></td>
                    <td>Popular examples: 'product' created by WooCommerce, 'download' created by Easy Digital Downloads etc. &#10230; <a href="https://codex.wordpress.org/Post_Types#Custom_Post_Types">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Categories</strong><?php echo $availableForPro; ?></td>
                    <td>Default Taxonomy (they are found in "Posts" &#187; "Categories", accessing a category link reveals all the posts from that category) &#10230; <a href="https://codex.wordpress.org/Posts_Categories_Screen">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Tags</strong><?php echo $availableForPro; ?></td>
                    <td>Default Taxonomy (they are found in "Posts" &#187; "Tags", accessing a tag link reveals all the posts associated with the tag) &#10230; <a href="https://codex.wordpress.org/Posts_Tags_Screen">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Custom Taxonomy</strong><?php echo $availableForPro; ?></td>
                    <td>Popular examples: 'product_cat' created by WooCommerce, 'download_category' created by Easy Digital Downloads etc. &#10230; <a href="https://codex.wordpress.org/Taxonomies#Custom_Taxonomies">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Search</strong><?php echo $availableForPro; ?></td>
                    <td>Default Search Template (search.php &#187; this is the template that displays the search results; the query parameter "s" is within the URL). If you create a <a href="https://codex.wordpress.org/Creating_a_Search_Page">Search Page</a>, it will belong to the "Pages" page type. The assets can be unloaded <strong>only in the front-end view</strong> (<em>"Manage in the Front-end?" from "Settings" tab has to be enabled</em>).</td>
                </tr>
                <tr>
                    <td><strong>Author</strong><?php echo $availableForPro; ?></td>
                    <td>Shows all posts belonging to a specific author (e.g. https://yourwebsite.com/author/yourname/). The assets can be unloaded <strong>only in the front-end view</strong> (<em>"Manage in the Front-end?" from "Settings" tab has to be enabled</em>).</td>
                </tr>
                <tr>
                    <td><strong>Date</strong><?php echo $availableForPro; ?></td>
                    <td>Shows all posts based on the chosen date (e.g. https://yourwebsite.com/2018/08/). The assets can be unloaded <strong>only in the front-end view</strong> (<em>"Manage in the Front-end?" from "Settings" tab has to be enabled</em>).</td>
                </tr>
                <tr>
                    <td><strong>404 Not Found</strong><?php echo $availableForPro; ?></td>
                    <td>This page (404.php within the theme) is reached when a request is not valid. It could be an old link that is not used anymore or the visitor typed the wrong URL to an article etc. (e.g. https://yourwebsite.com/this-is-a-non-existent-page.html). The assets can be unloaded <strong>only in the front-end view</strong> (<em>"Manage in the Front-end?" from "Settings" tab has to be enabled</em>). &#10230; <a href="https://codex.wordpress.org/Creating_an_Error_404_Page">read more</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>