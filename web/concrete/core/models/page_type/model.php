<?
defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Model_PageType extends Object {

	protected $ptDraftVersionsToSave = 10;

	public function getPageTypeID() {return $this->ptID;}
	public function getPageTypeName() {return $this->ptName;}
	public function getPageTypeHandle() {return $this->ptHandle;}
	public function getPageTypePublishTargetTypeID() {return $this->ptPublishTargetTypeID;}
	public function getPageTypePublishTargetObject() {return $this->ptPublishTargetObject;}
	public function getPageTypeAllowedPageTemplates() {
		return $this->ptAllowedPageTemplates;
	}
	public function getPageTypeDefaultPageTemplateID() {return $this->ptDefaultPageTemplateID;}
	public function getPageTypeDefaultPageTemplateObject() {return PageTemplate::getByID($this->ptDefaultPageTemplateID);}
	public function getPermissionObjectIdentifier() {
		return $this->getPageTypeID();
	}
	public function isPageTypeInternal() {
		return $this->ptIsInternal;
	}
	public function doesPageTypeLaunchInComposer() {
		return $this->ptLaunchInComposer;
	}
	public function getPackageID() {return $this->pkgID;}
	public function getPackageHandle() {
		return PackageList::getHandle($this->pkgID);
	}

	protected function stripEmptyPageTypeComposerControls(Page $c) {
		$controls = PageTypeComposerControl::getList($this);
		foreach($controls as $cn) {			
			$cn->setPageObject($c);
			if ($cn->shouldPageTypeComposerControlStripEmptyValuesFromPage() && $cn->isPageTypeComposerControlValueEmpty()) {
				$cn->removePageTypeComposerControlFromPage();
			}
		}
	}

	public function publish(Page $c) {
		$this->stripEmptyPageTypeComposerControls($c);
		$parent = Page::getByID($c->getPageDraftTargetParentPageID());
		$c->move($parent);
		$u = new User();
		$v = CollectionVersion::get($c, 'RECENT');
		$pkr = new ApprovePagePageWorkflowRequest();
		$pkr->setRequestedPage($c);
		$pkr->setRequestedVersionID($v->getVersionID());
		$pkr->setRequesterUserID($u->getUserID());
		$pkr->trigger();
		$c->activate();
		$u->unloadCollectionEdit($c);
		CacheLocal::flush();
	}
	
	public function savePageTypeComposerForm(Page $c) {
		$controls = PageTypeComposerControl::getList($this);
		$outputControls = array();
		foreach($controls as $cn) {
			$data = $cn->getRequestValue();
			$cn->publishToPage($c, $data, $controls);
			$outputControls[] = $cn;
		}

		// set page name from controls
		// now we see if there's a page name field in there
		$containsPageNameControl = false;
		foreach($outputControls as $cn) {
			if ($cn instanceof NameCorePagePropertyPageTypeComposerControl) {
				$containsPageNameControl = true;
				break;
			}
		}
		if (!$containsPageNameControl) {
			foreach($outputControls as $cn) {
				if ($cn->canPageTypeComposerControlSetPageName()) {
					$pageName = $cn->getPageTypeComposerControlPageNameValue($c);
					$c->updateCollectionName($pageName);
				}
			}
		}

		// remove all but the most recent X drafts.
		$vl = new VersionList($c);
		$vl->setItemsPerPage(-1);
		// this will ensure that we only ever keep X versions.
		$vArray = $vl->getPage();
		if (count($vArray) > $this->ptDraftVersionsToSave) {
			for ($i = $this->ptDraftVersionsToSave; $i < count($vArray); $i++) {
				$v = $vArray[$i];
				@$v->delete();
			} 
		}


		$c = Page::getByID($c->getCollectionID(), 'RECENT');
		$controls = array();
		foreach($outputControls as $oc) {
			$oc->setPageObject($c);
			$controls[] = $oc;
		}
		return $controls;
	}


	public function getPageTypeSelectedPageTemplateObjects() {
		$templates = array();
		$db = Loader::db();
		$r = $db->Execute('select pTemplateID from PageTypePageTemplates where ptID = ? order by pTemplateID asc', array($this->ptID));
		while ($row = $r->FetchRow()) {
			$pt = PageTemplate::getByID($row['pTemplateID']);
			if (is_object($pt)) {
				$templates[] = $pt;
			}
		}
		return $templates;
	}

	public static function getByDefaultsPage(Page $c) {
		if ($c->isMasterCollection()) {
			$db = Loader::db();
			$ptID = $db->GetOne('select ptID from PageTypePageTemplateDefaultPages where cID = ?', array($c->getCollectionID()));
			if ($ptID) {
				return PageType::getByID($ptID);
			}
		}
	}

	public function getPageTypePageTemplateDefaultPageObject(PageTemplate $template) {
		$db = Loader::db();
		$cID = $db->GetOne('select cID from PageTypePageTemplateDefaultPages where ptID = ? and pTemplateID = ?', array(
			$this->ptID, $template->getPageTemplateID()
		));
		if (!$cID) {
			// we create one.
			$dh = Loader::helper('date');
			$cDate = $dh->getSystemDateTime();
			$data['pTemplateID'] = $template->getPageTemplateID();
			$cobj = Collection::add($data);
			$cID = $cobj->getCollectionID();
			
			$v2 = array($cID, 1);
			$q2 = "insert into Pages (cID, cIsTemplate) values (?, ?)";
			$r2 = $db->prepare($q2);
			$res2 = $db->execute($r2, $v2);

			$cID = $db->Insert_ID();
			$db->Execute('insert into PageTypePageTemplateDefaultPages (ptID, pTemplateID, cID) values (?, ?, ?)', array(
				$this->ptID, $template->getPageTemplateID(), $cID
			));
		}

		return Page::getByID($cID, 'RECENT');
	}

	public function getPageTypePageTemplateObjects() {
		$_templates = array();
		if ($this->ptAllowedPageTemplates == 'C') {
			$_templates = $this->getPageTypeSelectedPageTemplateObjects();
		} else {
			$templates = PageTemplate::getList();
			$db = Loader::db();
			if ($this->ptAllowedPageTemplates == 'X') {
				$_templates = array();
				$pageTemplateIDs = $db->GetCol('select pTemplateID from PageTypePageTemplates where ptID = ? order by pTemplateID asc', array($this->ptID));
				foreach($templates as $pt) {
					if (!in_array($pt->getPageTemplateID(), $pageTemplateIDs)) {
						$_templates[] = $pt;
					}
				}
			} else {
				$_templates = $templates;
			}
		}
		$defaultTemplate = PageTemplate::getByID($this->getPageTypeDefaultPageTemplateID());
		if (is_object($defaultPageTemplate) && (!in_array($defaultTemplate, $_templates))) {
			$_templates[] = $defaultTemplate;
		}
		
		return $_templates;
	}

	public static function importTargets($node) {
		$ptHandle = (string) $node['handle'];
		$db = Loader::db();
		$ptID = $db->GetOne('select ptID from PageTypes where ptHandle = ?', array($ptHandle));
		$cm = PageType::getByID($ptID);
		if (is_object($cm) && isset($node->target)) {
			$target = PageTypePublishTargetType::importConfiguredPageTypePublishTarget($node->target);
			$cm->setConfiguredPageTypePublishTargetObject($target);
		}
	}

	public static function import($node) {
		$types = array();
		if ((string) $node->pagetemplates['type'] == 'custom' || (string) $node->pagetemplates['type'] == 'except') {
			if ((string) $node->pagetemplates['type'] == 'custom') {
				$ptAllowedPageTemplates = 'C';
			} else {
				$ptAllowedPageTemplates = 'X';
			}
			
			foreach($node->pagetemplates->pagetemplate as $pagetemplate) {
				$types[] = PageTemplate::getByHandle((string) $pagetemplate['handle']);
			}
		} else {
			$ptAllowedPageTemplates = 'A';
		}
		$ptName = (string) $node['name'];
		$ptHandle = (string) $node['handle'];
		$db = Loader::db();
		$defaultPageTemplate = PageTemplate::getByHandle((string) $node->pagetemplates['default']);

		$ptID = $db->GetOne('select ptID from PageTypes where ptHandle = ?', array($ptHandle));
		$data = array(
			'handle' => $ptHandle,
			'name' => $ptName
		);
		if ($defaultPageTemplate) {
			$data['defaultTemplate'] = $defaultPageTemplate;
		}
		if ($ptAllowedPageTemplates) {
			$data['allowedTemplates'] = $ptAllowedPageTemplates;
		}
		if ($node['internal']) {
			$data['internal'] = true;
		}

		$data['ptLaunchInComposer'] = 0;
		if ($node['launch-in-composer'] == '1') {
			$data['ptLaunchInComposer'] = 1;
		}

		$data['templates'] = $types;
		if ($ptID) {
			$cm = PageType::getByID($ptID);
			$cm->update($data);
		} else {
			$cm = PageType::add($data);
		}
		$node = $node->composer;
		if (isset($node->formlayout->set)) {
			foreach($node->formlayout->set as $setnode) {
				$set = $cm->addPageTypeComposerFormLayoutSet((string) $setnode['name']);
				if (isset($setnode->control)) {
					foreach($setnode->control as $controlnode) {
						$controltype = PageTypeComposerControlType::getByHandle((string) $controlnode['type']);
						$control = $controltype->configureFromImport($controlnode);
						$setcontrol = $control->addToPageTypeComposerFormLayoutSet($set);
						$required = (string) $controlnode['required'];
						$customTemplate = (string) $controlnode['custom-template'];
						$label = (string) $controlnode['custom-label'];
						$outputControlID = (string) $controlnode['output-control-id'];
						if ($required == '1') {
							$setcontrol->updateFormLayoutSetControlRequired(true);
						} else {
							$setcontrol->updateFormLayoutSetControlRequired(false);
						}
						if ($customTemplate) {
							$setcontrol->updateFormLayoutSetControlCustomTemplate($customTemplate);
						}
						if ($label) {
							$setcontrol->updateFormLayoutSetControlCustomLabel($label);
						}
						if ($outputControlID) {
							ContentImporter::addPageTypeComposerOutputControlID($setcontrol, $outputControlID);
						}
					}
				}
			}
		}
	}

	public static function importContent($node) {
		$db = Loader::db();
		$ptHandle = (string) $node['handle'];
		$ptID = $db->GetOne('select ptID from PageTypes where ptHandle = ?', array($ptHandle));
		if ($ptID) {
			$pt = PageType::getByID($ptID);
			if (isset($node->composer->output->pagetemplate)) {
				$ci = new ContentImporter();
				foreach($node->composer->output->pagetemplate as $pagetemplate) {
					$ptt = PageTemplate::getByHandle((string) $pagetemplate['handle']);
					if (is_object($ptt)) {
						// let's get the defaults page for this
						$xc = $pt->getPageTypePageTemplateDefaultPageObject($ptt);
						// now that we have the defaults page, let's import this content into it.
						if (isset($pagetemplate->page)) {
							$ci->importPageAreas($xc, $pagetemplate->page);
						}
					}
				}
			}
		}
	}


	public static function exportList($xml) {
		$list = self::getList();
		$nxml = $xml->addChild('pagetypes');
		
		foreach($list as $sc) {
			$activated = 0;
			$templates = $sc->getPageTypePageTemplateObjects();
			$pagetype = $nxml->addChild('pagetype');
			$pagetype->addAttribute('name', $sc->getPageTypeName());
			$pagetype->addAttribute('handle', $sc->getPageTypeHandle());
			$pagetype->addAttribute('package', $sc->getPackageHandle());
			if ($sc->isPageTypeInternal()) {
				$pagetype->addAttribute('internal', 'true');
			}
			if ($sc->doesPageTypeLaunchInComposer()) {
				$pagetype->addAttribute('launch-in-composer', '1');
			} else {
				$pagetype->addAttribute('launch-in-composer', '0');
			}
			$pagetemplates = $pagetype->addChild('pagetemplates');
			if ($sc->getPageTypeAllowedPageTemplates() == 'A') {
				$pagetemplates->addAttribute('type', 'all');
			} else {
				if ($sc->getPageTypeAllowedPageTemplates() == 'X') {
					$pagetemplates->addAttribute('type', 'except');
				} else {
					$pagetemplates->addAttribute('type', 'custom');
				}
				foreach($templates as $tt) {
					$pagetemplates->addChild('pagetemplate')->addAttribute('handle', $tt->getPageTemplateHandle());
				}	
			}

			$defaultPageTemplate = PageTemplate::getByID($sc->getPageTypeDefaultPageTemplateID());
			if (is_object($defaultPageTemplate)) {
				$pagetemplates->addAttribute('default', $defaultPageTemplate->getPageTemplateHandle());
			}
			$target = $sc->getPageTypePublishTargetObject();
			$target->export($pagetype);

			$fsn = $pagetype->addChild('formlayout');
			$fieldsets = PageTypeComposerFormLayoutSet::getList($sc);
			foreach($fieldsets as $fs) {
				$fs->export($fsn);
			}

			$osn = $pagetype->addChild('output');
			foreach($templates as $tt) {
				$pagetemplate = $osn->addChild('pagetemplate');
				$pagetemplate->addAttribute('handle', $tt->getPageTemplateHandle());
				$xc = $sc->getPageTypePageTemplateDefaultPageObject($tt);
				$xc->export($pagetemplate);
			}
		}
	}

	public function rescanPageTypeComposerOutputControlObjects() {
		$sets = PageTypeComposerFormLayoutSet::getList($this);
		foreach($sets as $s) {
			$controls = PageTypeComposerFormLayoutSetControl::getList($s);
			foreach($controls as $cs) {
				$type = $cs->getPageTypeComposerControlTypeObject();
				if ($type->controlTypeSupportsOutputControl()) {
					$cs->ensureOutputControlExists();
				}
			}
		}
	}

	public static function add($data, $pkg = false) {
		$ptHandle = $data['handle'];
		$ptName = $data['name'];
		$ptDefaultPageTemplateID = 0;
		$ptLaunchInComposer = 0;
		$pkgID = 0;
		if (is_object($pkg)) {
			$pkgID = $pkg->getPackageID();
		}

		if (is_object($data['defaultTemplate'])) {
			$ptDefaultPageTemplateID = $data['defaultTemplate']->getPageTemplateID();
		}
		$ptAllowedPageTemplates = 'A';
		if ($data['allowedTemplates']) {
			$ptAllowedPageTemplates = $data['allowedTemplates'];
		}
		$templates = array();
		if (is_array($data['templates'])) {
			$templates = $data['templates'];
		}
		$ptIsInternal = 0;
		if ($data['internal']) {
			$ptIsInternal = 1;
		}

		if ($data['ptLaunchInComposer']) {
			$ptLaunchInComposer = 1;
		}

		$db = Loader::db();
		$db->Execute('insert into PageTypes (ptName, ptHandle, ptDefaultPageTemplateID, ptAllowedPageTemplates, ptIsInternal, ptLaunchInComposer, pkgID) values (?, ?, ?, ?, ?, ?, ?)', array(
			$ptName, $ptHandle, $ptDefaultPageTemplateID, $ptAllowedPageTemplates, $ptIsInternal, $ptLaunchInComposer, $pkgID
		));
		$ptID = $db->Insert_ID();
		if ($ptAllowedPageTemplates != 'A') {
			foreach($templates as $pt) {
				$db->Execute('insert into PageTypePageTemplates (ptID, pTemplateID) values (?, ?)', array(
					$ptID, $pt->getPageTemplateID()
				));
			}
		}

		$ptt = PageType::getByID($db->Insert_ID());

		// copy permissions from the defaults to the page type
		$cpk = PermissionKey::getByHandle('access_page_type_permissions');
		$permissions = PermissionKey::getList('pagetype');
		foreach($permissions as $pk) { 
			$pk->setPermissionObject($ptt);
			$pk->copyFromDefaultsToPageType($cpk);
		}

		// now we clear the default from edit page drafts
		$pk = PermissionKey::getByHandle('edit_page_type_drafts');
		$pk->setPermissionObject($ptt);
		$pt = $pk->getPermissionAssignmentObject();
		$pt->clearPermissionAssignment();

		// now we assign the page draft owner access entity
		$pa = PermissionAccess::create($pk);
		$pe = PageOwnerPermissionAccessEntity::getOrCreate();
		$pa->addListItem($pe);
		$pt->assignPermissionAccess($pa);

		return $ptt;
	}

	public function update($data) {

		$ptHandle = $this->getPageTypeHandle();
		$ptName = $this->getPageTypeName();
		$ptDefaultPageTemplateID = $this->getPageTypeDefaultPageTemplateID();
		$ptAllowedPageTemplates = $this->getPageTypeAllowedPageTemplates();
		$ptLaunchInComposer = $this->doesPageTypeLaunchInComposer();

		if ($data['name']) {
			$ptName = $data['name'];
		}
		if ($data['handle']) {
			$ptHandle = $data['handle'];
		}
		if (is_object($data['defaultTemplate'])) {
			$ptDefaultPageTemplateID = $data['defaultTemplate']->getPageTemplateID();
		}
		if ($data['allowedTemplates']) {
			$ptAllowedPageTemplates = $data['allowedTemplates'];
		}
		if (isset($data['ptLaunchInComposer'])) {
			$ptLaunchInComposer = $data['ptLaunchInComposer'];
		}

		$templates = $this->getPageTypePageTemplateObjects();
		if (is_array($data['templates'])) {
			$templates = $data['templates'];
		}
		$ptIsInternal = $this->isPageTypeInternal();
		if ($data['internal']) {
			$ptIsInternal = 1;
		}		
		$db = Loader::db();
		$db->Execute('update PageTypes set ptName = ?, ptHandle = ?, ptDefaultPageTemplateID = ?, ptAllowedPageTemplates = ?, ptIsInternal = ?, ptLaunchInComposer = ? where ptID = ?', array(
			$ptName,
			$ptHandle,
			$ptDefaultPageTemplateID,
			$ptAllowedPageTemplates,
			$ptIsInternal,
			$ptLaunchInComposer,
			$this->ptID
		));
		$db->Execute('delete from PageTypePageTemplates where ptID = ?', array($this->ptID));
		if ($ptAllowedPageTemplates != 'A') {
			foreach($templates as $pt) {
				$db->Execute('insert into PageTypePageTemplates (ptID, pTemplateID) values (?, ?)', array(
					$this->ptID, $pt->getPageTemplateID()
				));
			}
		}
		$this->rescanPageTypeComposerOutputControlObjects();
	}

	public static function getList($includeInternal = false) {
		$db = Loader::db();
		if (!$includeInternal) {
			$ptIDs = $db->GetCol('select ptID from PageTypes where ptIsInternal = false order by ptID asc');
		} else {
			$ptIDs = $db->GetCol('select ptID from PageTypes order by ptID asc');
		}
		$list = array();
		foreach($ptIDs as $ptID) {
			$cm = PageType::getByID($ptID);
			if (is_object($cm)) {
				$list[] = $cm;
			}
		}
		return $list;
	}

	public static function getByID($ptID) {
		$db = Loader::db();
		$r = $db->GetRow('select * from PageTypes where ptID = ?', array($ptID));
		if (is_array($r) && $r['ptID']) {
			$cm = new PageType;
			$cm->setPropertiesFromArray($r);
			$cm->ptPublishTargetObject = unserialize($r['ptPublishTargetObject']);
			return $cm;
		}
	}

	public static function getByHandle($ptHandle) {
		$db = Loader::db();
		$ptID = $db->GetOne('select ptID from PageTypes where ptHandle = ?', array($ptHandle));
		if ($ptID) {
			return PageType::getByID($ptID);
		}
	}

	public function delete() {
		$sets = PageTypeComposerFormLayoutSet::getList($this);
		foreach($sets as $set) {
			$set->delete();
		}
		$db = Loader::db();
		$db->Execute('delete from PageTypes where ptID = ?', array($this->ptID));
		$db->Execute('delete from PageTypePageTemplates where ptID = ?', array($this->ptID));
		$db->Execute('delete from PageTypeComposerOutputControls where ptID = ?', array($this->ptID));
	}

	public function setConfiguredPageTypePublishTargetObject(PageTypePublishTargetConfiguration $configuredTarget) {
		$db = Loader::db();
		if (is_object($configuredTarget)) {
			$db->Execute('update PageTypes set ptPublishTargetTypeID = ?, ptPublishTargetObject = ? where ptID = ?', array(
				$configuredTarget->getPageTypePublishTargetTypeID(),
				@serialize($configuredTarget),
				$this->getPageTypeID()
			));
		}
	}

	public function rescanFormLayoutSetDisplayOrder() {
		$sets = PageTypeComposerFormLayoutSet::getList($this);
		$displayOrder = 0;
		foreach($sets as $s) {
			$s->updateFormLayoutSetDisplayOrder($displayOrder);
			$displayOrder++;
		}
	}

	public function addPageTypeComposerFormLayoutSet($ptComposerFormLayoutSetName) {
		$db = Loader::db();
		$displayOrder = $db->GetOne('select count(ptComposerFormLayoutSetID) from PageTypeComposerFormLayoutSets where ptID = ?', array($this->ptID));
		if (!$displayOrder) {
			$displayOrder = 0;
		}
		$db->Execute('insert into PageTypeComposerFormLayoutSets (ptComposerFormLayoutSetName, ptID, ptComposerFormLayoutSetDisplayOrder) values (?, ?, ?)', array(
			$ptComposerFormLayoutSetName, $this->ptID, $displayOrder
		));	
		return PageTypeComposerFormLayoutSet::getByID($db->Insert_ID());
	}

	public function validateCreateDraftRequest($pt) {
		$e = Loader::helper('validation/error');
		$availablePageTemplates = $this->getPageTypePageTemplateObjects();
		$availablePageTemplateIDs = array();
		foreach($availablePageTemplates as $ppt) {
			$availablePageTemplateIDs[] = $ppt->getPageTemplateID();
		}
		if (!is_object($pt)) {
			$e->add(t('You must choose a page template.'));
		} else if (!in_array($pt->getPageTemplateID(), $availablePageTemplateIDs)) {
			$e->add(t('This page template is not a valid template for this page type.'));
		}
		return $e;
	}	

	public function createDraft(PageTemplate $pt, $u = false) {
		// now we setup in the initial configurated page target
		
		if (!is_object($u)) {
			$u = new User();
		}
		$db = Loader::db();
		$ptID = $this->getPageTypeID();
		$parent = Page::getByPath(PAGE_DRAFTS_PAGE_PATH);
		$data = array('cvIsApproved' => 0);
		$p = $parent->add($this, $data, $pt);
		$p->deactivate();

		// we have to publish the controls to the page. i'm not sure why
		$controls = PageTypeComposerControl::getList($this);
		$outputControls = array();
		foreach($controls as $cn) {
			$cn->publishToPage($p, array(), $controls);
		}

		// now we need to clear out the processed controls in case we 
		// save again in the same request
		CorePagePropertyPageTypeComposerControl::clearComposerRequestProcessControls();

		return $p;
	}

}