<?php
namespace WpAssetCleanUp;

/**
 * Class MetaBoxes
 * @package WpAssetCleanUp
 */
class MetaBoxes
{
	/**
	 *
	 */
	public function initManagerMetaBox()
	{
		add_action( 'add_meta_boxes', array( $this, 'addAssetManagerMetaBox' ) );
	}

	/**
	 *
	 */
	public function initCustomOptionsMetaBox()
	{
		add_action( 'add_meta_boxes', array( $this, 'addPageOptionsMetaBox' ) );
	}

	/**
	 * @param $postType
	 */
	public function addAssetManagerMetaBox($postType)
	{
		$obj = get_post_type_object($postType);

		if (isset($obj->public) && $obj->public > 0) {
			add_meta_box(
				WPACU_PLUGIN_ID . '_asset_list',
				__('Asset CleanUp: CSS &amp; JavaScript Manager', WPACU_PLUGIN_TEXT_DOMAIN),
				array($this, 'renderAssetManagerMetaBoxContent'),
				$postType,
				'advanced',
				'high'
			);
		}
	}

	/**
	 * This is triggered only in the Edit Mode Dashboard View
	 */
	public function renderAssetManagerMetaBoxContent()
	{
		global $post;

		if ($post->ID === null) {
			return;
		}

		$data = array('status' => 1);

		$postId = (isset($post->ID) && $post->ID > 0) ? $post->ID : 0;

		$getAssets = true;

		if (! Main::instance()->settings['dashboard_show']) {
			$getAssets = false;
			$data['status'] = 2; // "Manage within Dashboard" is disabled in plugin's settings
		} elseif ($postId < 1 || get_post_status($postId) !== 'publish') {
			$data['status'] = 3; // "draft", "auto-draft" post (it has to be published)
			$getAssets = false;
		}

		if (class_exists('WPSEO_Options') && 'attachment' === get_post_type($post->ID)) {
			try {
				if (\WPSEO_Options::get( 'disable-attachment' ) === true) {
					$getAssets = false;
					$data['status'] = 4; // "Redirect attachment URLs to the attachment itself?" is enabled in "Yoast SEO" -> "Media"
				}
			} catch (\Exception $e) {}
		}

		//removeIf(development)
		/*
		if ($getAssets) {
			// Add an nonce field so we can check for it later.
			wp_nonce_field( WPACU_PLUGIN_ID . '_meta_box', WPACU_PLUGIN_ID . '_nonce');
		}
		*/
		//endRemoveIf(development)

		$data['get_assets'] = $getAssets;

		if ($getAssets) {
			$data['fetch_url'] = Misc::getPageUrl( $postId );
		}

		Main::instance()->parseTemplate('meta-box', $data, true);
	}

	/**
	 * @param $postType
	 */
	public function addPageOptionsMetaBox($postType)
	{
		$obj = get_post_type_object($postType);

		if (isset($obj->public) && $obj->public > 0) {
			add_meta_box(
				WPACU_PLUGIN_ID . '_page_options',
				__('Asset CleanUp: Options', WPACU_PLUGIN_TEXT_DOMAIN),
				array($this, 'renderPageOptionsMetaBoxContent'),
				$postType,
				'side',
				'high'
			);
		}
	}

	/**
	 *
	 */
	public function renderPageOptionsMetaBoxContent()
	{
		$data = array('page_options' => self::getPageOptions());

		Main::instance()->parseTemplate('meta-box-side-page-options', $data, true);
	}

	/**
	 * @param int $postId
	 *
	 * @return array|mixed|object
	 */
	public static function getPageOptions($postId = 0)
	{
		if ($postId < 1) {
			global $post;
			$postId = (int)$post->ID;
		}

		if ($postId > 1) {
			$metaPageOptionsJson = get_post_meta($postId, '_'.WPACU_PLUGIN_ID.'_page_options', true);

			//removeIf(development)
			/*
			if ($checkRights) {
				// Maybe update $pageOptions based on the user rights
				if ($pageOptions['apply_options_for'] !== 'everyone' && ! Menu::userCanManageAssets()) { // only 'admin' or 'everyone'
					return array(); // guest user and the changes should apply only to the admin
				}
			}
			*/
			//endRemoveIf(development)

			return @json_decode( $metaPageOptionsJson, ARRAY_A );
		}

		return array();
	}
}
