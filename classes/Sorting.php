<?php
namespace WpAssetCleanUp;

/**
 * Class Sorting
 * @package WpAssetCleanUp
 */
class Sorting
{
	/**
	 * Sorts styles or scripts list in alphabetical ascending order (from A to Z) by the handle name
	 *
	 * @param $list
	 *
	 * @return mixed
	 */
	public static function sortListByAlpha($list)
	{
		if (! empty($list['styles'])) {
			$newStyles = array();

			foreach ($list['styles'] as $indexNo => $styleObj) {
				if (! isset($styleObj->handle)) {
					continue;
				}

				$newStyles[$styleObj->handle] = $styleObj;
			}

			$list['styles'] = $newStyles;

			sort($list['styles']);
		}

		if (! empty($list['scripts'])) {
			$newScripts = array();

			foreach ($list['scripts'] as $indexNo => $scriptObj) {
				if (! isset($scriptObj->handle)) {
					continue;
				}

				$newScripts[$scriptObj->handle] = $scriptObj;
			}

			$list['scripts'] = $newScripts;

			sort($list['scripts']);
		}

		return $list;
	}

	/**
	 * The appended location values will be used to sort the list of assets
	 *
	 * @param $list
	 *
	 * @return mixed
	 */
	public static function appendLocation($list)
	{
		$pluginsUrl = plugins_url();

		//removeIf(development)
			//$currentTheme = wp_get_theme();
		//endRemoveIf(development)

		$urlsToThemeDirs = array();

		foreach (search_theme_directories() as $themeDir => $themeDirArray) {
			$themeUrl = '/'.
	            str_replace(
	                '//',
		            '/',
		            str_replace(ABSPATH, '', $themeDirArray['theme_root']) . '/'. $themeDir . '/'
	            );

			$urlsToThemeDirs[] = $themeUrl;
		}

		$urlsToThemeDirs = array_unique($urlsToThemeDirs);

		//removeIf(development)
			//echo '<pre>'; print_r($urlsToThemeDirs);

			/*
			$relPluginsPath = dirname(str_replace(ABSPATH, '', WPACU_PLUGIN_DIR)).'/';

			if ($relPluginsPath{0} !== '/') {
				$relPluginsPath = '/'.$relPluginsPath;
			}
			*/
		//endRemoveIf(development)

		//removeIf(development)
			//$locations = array();
		//endRemoveIf(development)

		foreach (array('styles', 'scripts') as $assetType) {
			foreach ( $list[$assetType] as $indexNo => $asset ) {

				//removeIf(development)
					/*
					if (! (isset($asset->src) && $asset->src)) {
						continue;
					}
					*/
				//endRemoveIf(development)

				$src = isset($asset->src) ? $asset->src : '';

				if (strpos($src,'/wp-includes/') === 0) {
					// Core Files
					$asset->locationMain = 'wp_core';
					$asset->locationChild = 'none';
				} elseif ( strpos( $src, $pluginsUrl ) !== false ) {
					// From plugins directory (usually /wp-content/plugins/)
					$relSrc = str_replace( $pluginsUrl, '', $src );

					if ( $relSrc{0} === '/' ) {
						$relSrc = substr( $relSrc, 1 );
					}

					list( $pluginDir ) = explode( '/', $relSrc );

					$asset->locationMain  = 'plugins';
					$asset->locationChild = $pluginDir;

					//removeIf(development)
						//$locations[ $assetsKey ][] = $asset;
					//endRemoveIf(development)
				} else {
					$isWithinThemes = false;

					foreach ( $urlsToThemeDirs as $urlToThemeDir ) {
						$srcRel = str_replace(site_url(),'', $src);

						//removeIf(development)
							//echo $src . ' - '. $urlToThemeDir. ' = '.stripos( $src, $urlToThemeDir ).'<br />';
						//endRemoveIf(development)

						if ( strpos( $srcRel, $urlToThemeDir ) !== false ) {
							$isWithinThemes = true;

							//removeIf(development)
							//echo $urlToThemeDir.'<br />';
							//endRemoveIf(development)

							$themeDir = substr(strrchr(trim($urlToThemeDir, '/'), '/'), 1);

							$asset->locationMain  = 'themes';
							$asset->locationChild = $themeDir;
							break;
							//removeIf(development)
								//$locations['themes'] [$themeDir] [ $assetsKey ][] = $asset;
							//endRemoveIf(development)
						}
					}

					// Default: "External"
					if ( ! $isWithinThemes ) {
						// Outside "themes", "plugins" and "wp-includes"
						$asset->locationMain  = 'external';
						$asset->locationChild = 'none';
						//removeIf(development)
							//$locations['external'][$assetsKey][] = $asset;
						//endRemoveIf(development)
					}
				}

				$list[$assetType][$indexNo] = $asset;
			}
		}

		return $list;
	}
}
