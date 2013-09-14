<?
defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Model_PageTemplate extends Object {

	public static function exportList($xml) {
		$nxml = $xml->addChild('pagetemplates');
		$list = PageTemplate::getList();
		foreach($list as $pt) {
			$type = $nxml->addChild('pagetemplate');
			$type->addAttribute('icon', $pt->getPageTemplateIcon());
			$type->addAttribute('name', $pt->getPageTemplateName());
			$type->addAttribute('handle', $pt->getPageTemplateHandle());
			$type->addAttribute('package', $pt->getPackageHandle());
			$type->addAttribute('internal', $pt->isPageTemplateInternal());
		}
	}

	public function getPageTemplateID() { return $this->pTemplateID; }
	public function getPageTemplateName() { return $this->pTemplateName; }
	public function getPageTemplateHandle() { return $this->pTemplateHandle; }
	public function isPageTemplateInternal() {return $this->pTemplateIsInternal;}
	public function getPageTemplateIcon() {return $this->pTemplateIcon;}
	public function getPackageID() {return $this->pkgID;}
	public function getPackageHandle() {
		return PackageList::getHandle($this->pkgID);
	}

	public function refreshPageTemplateAreas($theme = false) {
		if (!is_object($theme)) {
			$theme = PageTheme::getSiteTheme();
		}
		$v = new PageTemplateRequestView();
		$v->setPageTheme($theme);
		$v->render($this);

		print_r($v->getPageTemplateRequestViewAreaHandles());
	}

	public static function getByHandle($pTemplateHandle) {
		$db = Loader::db();
		$q = "select pTemplateID, pTemplateHandle, pTemplateIsInternal, pTemplateName, pTemplateIcon, pkgID from PageTemplates where pTemplateHandle = ?";
		$r = $db->Execute($q, array($pTemplateHandle));
		if ($r) {
			$row = $r->fetchRow();
			$r->free();
			if (is_array($row)) {
				$pt = new PageTemplate; 
				$pt->setPropertiesFromArray($row);
			}					
		}
		return $pt;
	}

	public function setPropertiesFromArray($row) {
		if (!$row['ctIcon']) {
			$row['ctIcon'] = FILENAME_PAGE_TEMPLATE_DEFAULT_ICON;
		}
		parent::setPropertiesFromArray($row);
	}
	
	public static function getByID($pTemplateID) {
		$pt = CacheLocal::getEntry('page_template_by_id', $pTemplateID);
		if (is_object($pt)) {
			return $pt;
		} else if ($pt === -1) {
			return false;
		}

		$db = Loader::db();
		$q = "select pTemplateID, pTemplateHandle, pTemplateIsInternal, pTemplateName, pTemplateIcon, pkgID from PageTemplates where pTemplateID = ?";
		$r = $db->query($q, array($pTemplateID));
		if ($r) {
			$row = $r->fetchRow();
			$r->free();
			if (is_array($row)) {
				$pt = new PageTemplate; 
				$pt->setPropertiesFromArray($row);
			}
		}
		CacheLocal::set('page_template_by_id', $pTemplateID, $pt);
		return $pt;
	}
	
	public function delete() {
		$db = Loader::db();
		$db->query("delete from PageTemplates where pTemplateID = ?", array($this->pTemplateID));
	}
	
	public static function getListByPackage($pkg) {
		$db = Loader::db();
		$list = array();
		$r = $db->Execute('select pTemplateID from PageTemplates where pkgID = ? order by pTemplateName asc', array($pkg->getPackageID()));
		while ($row = $r->FetchRow()) {
			$list[] = PageTemplate::getByID($row['pTemplateID']);
		}
		$r->Close();
		return $list;
	}	

	public static function getList($includeInternal = false) {
		$db = Loader::db();
		$list = array();
		if ($includeInternal) {
			$r = $db->Execute('select pTemplateID from PageTemplates order by pTemplateName asc');
		} else {
			$r = $db->Execute('select pTemplateID from PageTemplates where pTemplateIsInternal = 0 order by pTemplateName asc');
		}
		while ($row = $r->FetchRow()) {
			$list[] = PageTemplate::getByID($row['pTemplateID']);
		}
		$r->Close();
		return $list;
	}
	
	public static function add($pTemplateHandle, $pTemplateName, $pTemplateIcon = FILENAME_PAGE_TEMPLATE_DEFAULT_ICON, $pkg = null, $pTemplateIsInternal = false) {
		$pkgID = (!is_object($pkg)) ? 0 : $pkg->getPackageID();
		$_pTemplateIsInternal = 0;
		if ($pTemplateIsInternal) {
			$_pTemplateIsInternal = 1;
		}
		$db = Loader::db();
		$v = array($pTemplateHandle, $pTemplateName, $pTemplateIcon, $_pTemplateIsInternal, $pkgID);
		$q = "insert into PageTemplates (pTemplateHandle, pTemplateName, pTemplateIcon, pTemplateIsInternal, pkgID) values (?, ?, ?, ?, ?)";
		$r = $db->prepare($q);
		$res = $db->execute($r, $v);
		if ($res) {
			$pTemplateID = $db->Insert_ID();
			return PageTemplate::getByID($pTemplateID);
		}
	}
	
	public function update($pTemplateHandle, $pTemplateName, $pTemplateIcon = FILENAME_PAGE_TEMPLATE_DEFAULT_ICON) {
		$db = Loader::db();

		$v = array($pTemplateHandle, $pTemplateName, $pTemplateIcon, $this->pTemplateID);
		$r = $db->prepare("update PageTemplates set pTemplateHandle = ?, pTemplateName = ?, pTemplateIcon = ? where pTemplateID = ?");
		$res = $db->execute($r, $v);
	}
	
	public function getIcons() {
		$f = Loader::helper('file');
		return $f->getDirectoryContents(DIR_FILES_PAGE_TEMPLATE_ICONS);				
	}

	public function getPageTemplateIconImage() {
		$src = REL_DIR_FILES_PAGE_TEMPLATE_ICONS.'/'.$this->pTemplateIcon;
		$iconImg = '<img src="'.$src.'" height="' . PAGE_TEMPLATE_ICON_HEIGHT . '" width="' . PAGE_TEMPLATE_ICON_WIDTH . '" alt="'.$this->getPageTemplateName().'" title="'.$this->getPageTemplateName().'" />';
		return $iconImg;
	}
}