<?

defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Library_JavaScriptAsset extends Asset {
	
	protected $assetSupportsPostProcessing = true;

	public function getAssetDefaultPosition() {
		return Asset::ASSET_POSITION_FOOTER;
	}

	public function postprocess($assets) {
		if (!file_exists(DIR_FILES_CACHE . '/' . DIRNAME_JAVASCRIPT)) {
			$proceed = @mkdir(DIR_FILES_CACHE . '/' . DIRNAME_JAVASCRIPT);
		} else {
			$proceed = true;
		}
		if ($proceed) {
			$filename = '';
			for ($i = 0; $i < count($assets); $i++) {
				$asset = $assets[$i];
				$filename .= $asset->getAssetURL();
			}
			$filename = sha1($filename);
			$cacheFile = DIR_FILES_CACHE . '/' . DIRNAME_JAVASCRIPT . '/' . $filename . '.js';
			if (!file_exists($cacheFile)) {
				Loader::library('3rdparty/jsmin');
				$js = '';
				foreach($assets as $asset) {
					$js .= file_get_contents($asset->getAssetPath()) . "\n\n";
				}
				$js = JSMin::minify($js);
				@file_put_contents($cacheFile, $js);
			}
		
			$asset = new JavaScriptAsset();
			$asset->setAssetURL(REL_DIR_FILES_CACHE . '/' . DIRNAME_JAVASCRIPT . '/' . $filename . '.js');
			$asset->setAssetPath(DIR_FILES_CACHE . '/' . DIRNAME_JAVASCRIPT . '/' . $filename . '.js');
			return array($asset);
		}

		return $assets;
	}

	public function getAssetType() {return 'javascript';}

	public function populateAssetURLFromFilename($filename) {
		if ($this->local) {
			$this->assetURL = ASSETS_URL_JAVASCRIPT . '/' . $filename;
		} else {
			$this->assetURL = $filename;
		}
	}

	public function populateAssetPathFromFilename($filename) {
		if ($this->local) {
			$this->assetPath = DIR_BASE_CORE . '/' . DIRNAME_JAVASCRIPT . '/' . $filename;
		}
	}

	public function __toString() {
		return '<script type="text/javascript" src="' . $this->getAssetURL() . '"></script>';
	}

}