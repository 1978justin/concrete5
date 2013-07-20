<?

defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Library_AssetList {
	
	private static $loc = null;
	public $assets = array();
	public $assetGroups = array();

	public function getRegisteredAssets() {
		return $this->assets;
	}

	public static function getInstance() {
		if (null === self::$loc) {
			self::$loc = new self;
		}
		return self::$loc;
	}

	public function register($assetType, $assetHandle, $filename, $args = array(), $pkg = false) {
		$defaults = array(
			'weight' => false,
			'position' => false,
			'local' => true,
			'version' => false,
			'postprocess' => -1 // use the asset default
		);
		// overwrite all the defaults with the arguments
		$args = array_merge($defaults, $args);

		$class = Object::camelcase($assetType) . 'Asset';
		$o = new $class($assetHandle);
		$o->setPackageObject($pkg);
		$o->setAssetIsLocal($args['local']);
		$o->mapAssetLocation($filename);
		if ($args['postprocess'] === true || $args['postprocess'] === false) {
			$o->setAssetSupportsPostProcessing($args['postprocess']);
		}
		if ($args['version']) {
			$o->setAssetVersion($args['version']);
		}
		if ($args['weight']) {
			$o->setAssetWeight($args['weight']);
		}
		if ($args['position']) {
			$o->setAssetPosition($args['position']);
		}
		$this->registerAsset($o);
		return $o;
	}

	public function registerAsset(Asset $asset) {
		// we have to check and see if the asset already exists.
		// If it exists, we only replace it if our current asset has a later version
		$doRegister = true;
		if (is_object($this->assets[$asset->getAssetType()][$asset->getAssetHandle()])) {
			$existingAsset = $this->assets[$asset->getAssetType()][$asset->getAssetHandle()];
			if (version_compare($existingAsset->getAssetVersion(), $asset->getAssetVersion(), '>')) {
				$doRegister = false;
			}
		}
		if ($doRegister) {
			$this->assets[$asset->getAssetType()][$asset->getAssetHandle()] = $asset;
		}
	}

	public function registerGroup($assetGroupHandle, $assetHandles, $customClass = false) {
		if ($customClass) {
			$class = Object::camelcase($assetGroupHandle) . 'AssetGroup';
		} else {
			$class = 'AssetGroup';
		}
		$group = new $class();
		foreach($assetHandles as $assetArray) {
			$ap = new AssetPointer($assetArray[0], $assetArray[1]);
			$group->add($ap);
		}
		$this->assetGroups[$assetGroupHandle] = $group;
	}

	public function getAsset($assetType, $assetHandle) {
		return $this->assets[$assetType][$assetHandle];
	}

	public function getAssetGroup($assetGroupHandle) {
		return $this->assetGroups[$assetGroupHandle];
	}

}