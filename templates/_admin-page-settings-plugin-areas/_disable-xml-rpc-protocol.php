<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
	exit;
}

$tabIdArea = 'wpacu-setting-disable-xml-rpc';
$styleTabContent = ($selectedTabArea === $tabIdArea) ? 'style="display: table-cell;"' : '';
?>
<div id="<?php echo $tabIdArea; ?>" class="wpacu-settings-tab-content" <?php echo $styleTabContent; ?>>
    <h2><?php _e('Disable XML-RPC Protocol Support partially or completely', WPACU_PLUGIN_TEXT_DOMAIN); ?></h2>
    <table class="wpacu-form-table">
        <!-- Disable "XML-RPC" protocol support? -->
        <tr valign="top">
            <td>
                <select id="wpacu_disable_xmlrpc"
                        name="<?php echo WPACU_PLUGIN_ID . '_settings'; ?>[disable_xmlrpc]">
                    <option <?php if (! in_array($data['disable_xmlrpc'], array('disable_pingback', 'disable_all'))) { echo 'selected="selected"'; } ?>
                            value="keep_it_on">Keep it enabled (default)</option>

                    <option <?php if ($data['disable_xmlrpc'] === 'disable_pingback') { echo 'selected="selected"'; } ?>
                            value="disable_pingback">Disable XML-RPC Pingback Only

                    <option <?php if ($data['disable_xmlrpc'] === 'disable_all') { echo 'selected="selected"'; } ?>
                            value="disable_all">Disable XML-RPC Completely</option>
                </select>
                <code>&lt;link rel=&quot;pingback&quot; href=&quot;https://www.yourwebsite.com/xmlrpc.php&quot; /&gt;</code>
                <p style="margin-bottom: 10px;">This will disable XML-RPC protocol support and cleans up the "pingback" tag from the HEAD section of your website.</p>
                <p style="margin-bottom: 10px;">This is an API service used by WordPress for 3rd party applications, such as mobile apps, communication between blogs, plugins such as Jetpack. If you use, or are planning to use a remote system to post content to your website, you can keep this feature enabled (which it is by default). Many users do not use this function at all and if you're one of them, you can disable it.</p>

                <p style="margin-bottom: 10px;"><strong>Disable XML-RPC Pingback Only</strong>: If you need the XML-RPC protocol support, but you do not use the pingbacks which are used by your website to notify another website that you have linked to it from your page(s), you can just disable the pinbacks and keep the other XML-RPC functionality. This is also a security measure to prevent DDoS attacks.</p>

                <p style="margin-bottom: 0;"><strong>Disable XML-RPC Completely</strong>: If you do not use Jetpack plugin for off-site server communication or you only use the Dashboard to post content (without any remote software connection to the WordPress website such as Windows Live Writer or mobile apps), then you can disable the XML-RPC functionality. You can always re-enable it whenever you believe you'll need it.</p>
            </td>
        </tr>
    </table>
</div>
