<?php defined('C5_EXECUTE') or die("Access Denied.");

abstract class Concrete5_Model_PageTypePublishTargetType extends Object {

	abstract public function configurePageTypePublishTarget(PageType $pt, $post);
	abstract public function configurePageTypePublishTargetFromImport($txml);

	public function getPageTypePublishTargetTypeName() {return $this->ptPublishTargetTypeName;}
	public function getPageTypePublishTargetTypeHandle() {return $this->ptPublishTargetTypeHandle;}
	public function getPageTypePublishTargetTypeID() { return $this->ptPublishTargetTypeID;}
	public function getPackageID() { return $this->pkgID;}
	public function getPackageHandle() {
		return PackageList::getHandle($this->pkgID);
	}
	public function getPackageObject() {return Package::getByID($this->pkgID);}

	public static function getByID($ptPublishTargetTypeID) {
		$db = Loader::db();
		$r = $db->GetRow('select ptPublishTargetTypeID, ptPublishTargetTypeHandle, ptPublishTargetTypeName, pkgID from PageTypePublishTargetTypes where ptPublishTargetTypeID = ?', array($ptPublishTargetTypeID));
		if (is_array($r) && $r['ptPublishTargetTypeHandle']) {
			$class = Loader::helper('text')->camelcase($r['ptPublishTargetTypeHandle']) . 'PageTypePublishTargetType';
			$sc = new $class();
			$sc->setPropertiesFromArray($r);
			return $sc;
		}
	}

	public static function getByHandle($ptPublishTargetTypeHandle) {
		$db = Loader::db();
		$r = $db->GetRow('select ptPublishTargetTypeID, ptPublishTargetTypeHandle, ptPublishTargetTypeName, pkgID from PageTypePublishTargetTypes where ptPublishTargetTypeHandle = ?', array($ptPublishTargetTypeHandle));
		if (is_array($r) && $r['ptPublishTargetTypeHandle']) {
			$class = Loader::helper('text')->camelcase($r['ptPublishTargetTypeHandle']) . 'PageTypePublishTargetType';
			$sc = new $class();
			$sc->setPropertiesFromArray($r);
			return $sc;
		}
	}

	public static function importConfiguredPageTypePublishTarget($txml) {
		$type = PageTypePublishTargetType::getByHandle((string) $txml['handle']);
		$target = $type->configurePageTypePublishTargetFromImport($txml);
		return $target;
	}
	
	public static function add($ptPublishTargetTypeHandle, $ptPublishTargetTypeName, $pkg = false) {
		$pkgID = 0;
		if (is_object($pkg)) {
			$pkgID = $pkg->getPackageID();
		}
		$db = Loader::db();
		$db->Execute('insert into PageTypePublishTargetTypes (ptPublishTargetTypeHandle, ptPublishTargetTypeName, pkgID) values (?, ?, ?)', array($ptPublishTargetTypeHandle, $ptPublishTargetTypeName, $pkgID));
		return PageTypePublishTargetType::getByHandle($ptPublishTargetTypeHandle);
	}
	
	public function delete() {
		$db = Loader::db();
		$db->Execute('delete from PageTypePublishTargetTypes where ptPublishTargetTypeID = ?', array($this->ptPublishTargetTypeID));
	}
	
	public static function getList() {
		$db = Loader::db();
		$ids = $db->GetCol('select ptPublishTargetTypeID from PageTypePublishTargetTypes order by ptPublishTargetTypeName asc');
		$types = array();
		foreach($ids as $id) {
			$type = PageTypePublishTargetType::getByID($id);
			if (is_object($type)) {
				$types[] = $type;
			}
		}
		return $types;
	}

	public static function getListByPackage($pkg) {
		$db = Loader::db();
		$ids = $db->GetCol('select ptPublishTargetTypeID from PageTypePublishTargetTypes where pkgID = ? order by ptPublishTargetTypeName asc', array($pkg->getPackageID()));
		$types = array();
		foreach($ids as $id) {
			$type = PageTypePublishTargetType::getByID($id);
			if (is_object($type)) {
				$types[] = $type;
			}
		}
		return $types;
	}
	
	public static function exportList($xml) {
		$list = self::getList();
		$nxml = $xml->addChild('pagetypepublishtargettypes');
		
		foreach($list as $sc) {
			$activated = 0;
			$type = $nxml->addChild('type');
			$type->addAttribute('handle', $sc->getPageTypePublishTargetTypeHandle());
			$type->addAttribute('name', $sc->getPageTypePublishTargetTypeName());
			$type->addAttribute('package', $sc->getPackageHandle());
		}
	}
			
	public function hasOptionsForm() {
		$env = Environment::get();
		$rec = $env->getRecord(DIRNAME_ELEMENTS . '/' . DIRNAME_PAGE_TYPES . '/' . DIRNAME_ELEMENTS_PAGE_TYPES_PUBLISH_TARGET_TYPES . '/' . $this->getPageTypePublishTargetTypeHandle() . '.php', $this->getPackageHandle());
		return $rec->exists();
	}	

	public function includeOptionsForm($pagetype = false) {
		Loader::element(DIRNAME_PAGE_TYPES . '/' . DIRNAME_ELEMENTS_PAGE_TYPES_PUBLISH_TARGET_TYPES . '/' . $this->getPageTypePublishTargetTypeHandle(), array('pagetype' => $pagetype), $this->getPackageHandle());
	}


}