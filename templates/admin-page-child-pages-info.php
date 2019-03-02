<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}

// [wpacu_lite]
$availableForPro = '<a href="'.WPACU_PLUGIN_GO_PRO_URL.'?utm_source=plugin_pages_info_lock_page_type" class="go-pro-link-no-style"><span class="wpacu-tooltip">Available for Pro users<br />Click to upgrade!</span> <img width="20" height="20" src="'.WPACU_PLUGIN_URL.'/assets/icons/icon-lock.svg" valign="top" alt="" /></a>';
$availableForProBtn = '<a class="button button-disabled go-pro-link-no-style" style="font-style: normal;" href="'.WPACU_PLUGIN_GO_PRO_URL.'?utm_source=plugin_pages_info_lock_action_btn"><span class="wpacu-tooltip wpacu-on-pages-btn">Available for Pro users<br />Click to upgrade!</span>Manage Assets</a>';
// [/wpacu_lite]
?>
<div id="wpacu-pages-info-area" class="wpacu-wrap">
    <div style="margin: 20px 0 0;" class="wpacu-notice-info">
        <p>This is an overview of all the WordPress pages where Asset CleanUp can be used to unload unused CSS &amp; JavaScript files. Unloading assets (CSS &amp; JavaScript) for page types that have a locker next to their name requires an <a href="<?php echo WPACU_PLUGIN_GO_PRO_URL; ?>?utm_source=plugin_pages_info&utm_medium=top_note">upgrade to the Pro version</a>.</p>
    </div>

    <div class="wpacu_table_wrap">
        <table class="table table-striped">
            <thead class="thead-default">
                <tr>
                    <th align="left">PAGE TYPE</th>
                    <th align="left">ACTION</th>
                    <th align="left">DESCRIPTION</th>
                </tr>
            </thead>
            <tbody>
            <!--
                //removeIf(development)
                <tr>
                    <td width="15%"><strong>Homepage</strong></td>
                    <td><a class="button" href="<?php echo admin_url('admin.php?page=wpassetcleanup_assets_manager&wpacu_for=homepage'); ?>">Manage Assets</a></td>
                    <td>This could be: your latest posts or a static page ("Pages" type), depending on your configuration from <a target="_blank" href="https://codex.wordpress.org/Settings_Reading_Screen">Dashboard's "Settings" &#187; "Reading"</a> page. If the home page is not a static page, but it's showing the latest posts (default view), then you can manage its assets from Asset CleanUp's "Homepage" tab above. &#10230; <a href="https://codex.wordpress.org/Settings_Reading_Screen#Reading_Settings">Read more about "Reading Settings" for "Front page displays"</a></td>
                </tr>
                //endRemoveIf(development)
            -->
                <tr>
                    <td width="16%"><strong>Posts</strong></td>
                    <td><a class="button" id="wpacu-manage-assets-posts-info-btn" href="#wpacu-manage-assets-posts-info">Manage Assets</a></td>
                    <td>Post Type: 'post' (e.g. blog entries) &#10230; <a href="https://codex.wordpress.org/Posts_Screen">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Custom Post Type</strong></td>
                    <td><a class="button" id="wpacu-manage-assets-custom-post-type-info-btn" href="#wpacu-manage-assets-custom-post-type-info">Manage Assets</a></td>
                    <td>Popular examples: 'product' created by WooCommerce, 'download' created by Easy Digital Downloads etc. &#10230; <a href="https://codex.wordpress.org/Post_Types#Custom_Post_Types">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Pages</strong></td>
                    <td><a class="button" id="wpacu-manage-assets-pages-info-btn" href="#wpacu-manage-assets-pages-info">Manage Assets</a></td>
                    <td>Post Type: 'page' (e.g. About us, Contact) &#10230; <a href="https://codex.wordpress.org/Pages_Screen">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Media Attachment</strong></td>
                    <td><a class="button" id="wpacu-manage-assets-attachments-info-btn" href="#wpacu-manage-assets-attachments-info">Manage Assets</a></td>
                    <td>Post Type: 'attachment' (e.g. files from <a target="_blank" href="https://codex.wordpress.org/Media_Library_Screen">"Media" &#187; "Library"</a>, the page loaded usually prints the image or other media type) &#10230; <a href="https://codex.wordpress.org/Edit_Media">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Categories</strong><?php echo $availableForPro; ?></td>
                    <td><?php echo $availableForProBtn; ?></td>
                    <td>Default Taxonomy (they are found in "Posts" &#187; "Categories", accessing a category link reveals all the posts from that category) &#10230; <a href="https://codex.wordpress.org/Posts_Categories_Screen">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Tags</strong><?php echo $availableForPro; ?></td>
                    <td><?php echo $availableForProBtn; ?></td>
                    <td>Default Taxonomy (they are found in "Posts" &#187; "Tags", accessing a tag link reveals all the posts associated with the tag) &#10230; <a href="https://codex.wordpress.org/Posts_Tags_Screen">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Custom Taxonomy</strong><?php echo $availableForPro; ?></td>
                    <td><?php echo $availableForProBtn; ?></td>
                    <td>Popular examples: 'product_cat' created by WooCommerce, 'download_category' created by Easy Digital Downloads etc. &#10230; <a href="https://codex.wordpress.org/Taxonomies#Custom_Taxonomies">read more</a></td>
                </tr>
                <tr>
                    <td><strong>Search</strong><?php echo $availableForPro; ?></td>
                    <td><?php echo $availableForProBtn; ?></td>
                    <td>Default Search Template (search.php &#187; this is the template that displays the search results; the query parameter "s" is within the URL). If you create a <a href="https://codex.wordpress.org/Creating_a_Search_Page">Search Page</a>, it will belong to the "Pages" page type. The assets can be unloaded <strong>only in the front-end view</strong> (<em>"Manage in the Front-end?" from "Settings" tab has to be enabled</em>).</td>
                </tr>
                <tr>
                    <td><strong>Author</strong><?php echo $availableForPro; ?></td>
                    <td><?php echo $availableForProBtn; ?></td>
                    <td>Shows all posts belonging to a specific author (e.g. https://yourwebsite.com/author/yourname/). The assets can be unloaded <strong>only in the front-end view</strong> (<em>"Manage in the Front-end?" from "Settings" tab has to be enabled</em>).</td>
                </tr>
                <tr>
                    <td><strong>Date</strong><?php echo $availableForPro; ?></td>
                    <td><?php echo $availableForProBtn; ?></td>
                    <td>Shows all posts based on the chosen date (e.g. https://yourwebsite.com/2018/08/). The assets can be unloaded <strong>only in the front-end view</strong> (<em>"Manage in the Front-end?" from "Settings" tab has to be enabled</em>).</td>
                </tr>
                <tr>
                    <td><strong>404 Not Found</strong><?php echo $availableForPro; ?></td>
                    <td><?php echo $availableForProBtn; ?></td>
                    <td>This page (404.php within the theme) is reached when a request is not valid. It could be an old link that is not used anymore or the visitor typed the wrong URL to an article etc. (e.g. https://yourwebsite.com/this-is-a-non-existent-page.html). The assets can be unloaded <strong>only in the front-end view</strong> (<em>"Manage in the Front-end?" from "Settings" tab has to be enabled</em>). &#10230; <a href="https://codex.wordpress.org/Creating_an_Error_404_Page">read more</a></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Start "Posts" Modal -->
    <div id="wpacu-manage-assets-posts-info" class="wpacu-modal">
        <div class="wpacu-modal-content">
            <span class="wpacu-close">&times;</span>
            <h2>Posts</h2>
            <p style="margin-bottom: 0;">&#10230; If "Manage in the Dashboard?" is enabled:</p>
            <p style="margin-top: 0;">Go to "Posts" -&gt; "All Posts" -&gt; [Choose the page you want to manage the assets for] -&gt; Scroll to "Asset CleanUp" meta box where you will see the loaded CSS &amp; JavaScript files</p>
            <hr />
            <p style="margin-bottom: 0;">&#10230; If "Manage in the Front-end?" is enabled and you're logged in:</p>
            <p style="margin-top: 0;">Go to the page where you want to manage the files and scroll to the bottom of the page where you will see the list.</p>
        </div>
    </div>
    <!-- End "Posts" Modal -->

    <!-- Start "Custom Post Type" Modal -->
    <div id="wpacu-manage-assets-custom-post-type-info" class="wpacu-modal">
        <div class="wpacu-modal-content">
            <span class="wpacu-close">&times;</span>
            <h2>Custom Post Type</h2>
            <p><strong>Example:</strong> WooCommerce product</p>
            <p style="margin-bottom: 0;">&#10230; If "Manage in the Dashboard?" is enabled:</p>
            <p style="margin-top: 0;">Go to "Products" -&gt; "All Products" -&gt; [Choose the page you want to manage the assets for] -&gt; Scroll to "Asset CleanUp" meta box where you will see the loaded CSS &amp; JavaScript files</p>
            <hr />
            <p style="margin-bottom: 0;">&#10230; If "Manage in the Front-end?" is enabled and you're logged in:</p>
            <p style="margin-top: 0;">Go to the product page where you want to manage the files and scroll to the bottom of the page where you will see the list.</p>
        </div>
    </div>
    <!-- End "Custom Post Type" Modal -->

    <!-- Start "Pages" Modal -->
    <div id="wpacu-manage-assets-pages-info" class="wpacu-modal">
        <div class="wpacu-modal-content">
            <span class="wpacu-close">&times;</span>
            <h2>Pages</h2>
            <p style="margin-bottom: 0;">&#10230; If "Manage in the Dashboard?" is enabled:</p>
            <p style="margin-top: 0;">Go to "Pages" -&gt; "All Pages" -&gt; [Choose the page you want to manage the assets for] -&gt; Scroll to "Asset CleanUp" meta box where you will see the loaded CSS &amp; JavaScript files</p>
            <hr />
            <p style="margin-bottom: 0;">&#10230; If "Manage in the Front-end?" is enabled and you're logged in:</p>
            <p style="margin-top: 0;">Go to the product page where you want to manage the files and scroll to the bottom of the page where you will see the list.</p>
        </div>
    </div>
    <!-- End "Pages" Modal -->

    <!-- Start "Media Attachment Pages" Modal -->
    <div id="wpacu-manage-assets-attachments-info" class="wpacu-modal">
        <div class="wpacu-modal-content">
            <span class="wpacu-close">&times;</span>
            <h2>Media Attachment Pages</h2>
            <p>Note: This is rarely used/needed and in some WordPress setups, the attachment's permalink redirects to the media file itself.</p>
            <p style="margin-bottom: 0;">&#10230; If "Manage in the Dashboard?" is enabled:</p>
            <p style="margin-top: 0;">Go to "Media" -&gt; "Library" -&gt; [Choose the media you want to manage the assets for] -&gt; Scroll to "Asset CleanUp" meta box where you will see the loaded CSS &amp; JavaScript files</p>
            <hr />
            <p style="margin-bottom: 0;">&#10230; If "Manage in the Front-end?" is enabled and you're logged in:</p>
            <p style="margin-top: 0;">Go to the media's permalink ("View" links in the media list) page where you want to manage the files and scroll to the bottom of the page where you will see the list.</p>
        </div>
    </div>
    <!-- End "Media Attachment Pages" Modal -->
</div>

<style type="text/css">
    /* The Modal (background) */
    .wpacu-modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1000000; /* Sit on top */
        padding-top: 15%; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    /* Modal Content */
    .wpacu-modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 600px;
        border-radius: 10px;
    }

    /* The Close Button */
    .wpacu-close {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .wpacu-close:hover,
    .wpacu-close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        var currentModal;
        $('.wpacu-modal').each(function (wpacuIndex) {
            var wpacuModalId = $(this).attr('id');

            // Get the modal
            var wpacuModal = document.getElementById(wpacuModalId);

            // Get the button that opens the modal
            var wpacuBtn = document.getElementById(wpacuModalId + '-btn');

            //removeIf(development)
            console.log(wpacuModalId + '-btn');
            //endRemoveIf(development)

            // When the user clicks the button, open the modal
            wpacuBtn.onclick = function() {
                wpacuModal.style.display = 'block';
                currentModal = wpacuModal;
            };

            // Get the <span> element that closes the modal
            var wpacuSpan = document.getElementsByClassName('wpacu-close')[wpacuIndex];

            // When the user clicks on <span> (x), close the modal
            wpacuSpan.onclick = function() {
                wpacuModal.style.display = 'none';
            };
        });

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
            if (event.target === currentModal) {
                currentModal.style.display = 'none';
            }
        };
    });
</script>