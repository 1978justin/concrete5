<?
/**
 * @access private
 * @package Helpers
 * @category Concrete
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */

/**
 * @access private
 * @package Helpers
 * @category Concrete
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
 
defined('C5_EXECUTE') or die("Access Denied.");
class ConcreteDashboardSitemapHelper {

	protected $autoOpenNodes = true;
	protected $displayNodePagination = false;

	function showSystemPages() {
		return $_SESSION['dsbSitemapShowSystem'] == 1;
	}

	public function setAutoOpenNodes($autoOpen) {
		$this->autoOpenNodes = $autoOpen;
	}

	public function setDisplayNodePagination($paginate) {
		$this->displayNodePagination = $paginate;
	}

	function getSubNodes($cID) {
		$db = Loader::db();
		
		$obj = new stdClass;
		$pl = new PageList();
		$pl->sortByDisplayOrder();
		if (ConcreteDashboardSitemapHelper::showSystemPages()) {
			$pl->includeSystemPages();
			$pl->includeInactivePages();
		}
		$pl->filterByParentID($cID);
		$pl->displayUnapprovedPages();
		$total = $pl->getTotal();
		if ($cID == 1) {
			$results = $pl->get();			
		} else {
			$pl->setItemsPerPage(SITEMAP_PAGES_LIMIT);
			$results = $pl->getPage();
		}
		
		$nodes = array();
		foreach($results as $c) {
			$n = ConcreteDashboardSitemapHelper::getNode($c);
			if ($n != false) {
				$nodes[] = $n;
			}
		}

		if ($total > SITEMAP_PAGES_LIMIT) {
			if ($this->displayNodePagination) {

				$n = new stdClass;
				$n->icon = false;
				$n->addClass = 'ccm-sitemap-explore-paging';
				$n->noLink = true;
				$n->unselectable = true;
				$n->title = $pl->displayPagingV2(false, true);
				$nodes[] = $n;
			} else {
				$n = new stdClass;
				$n->icon =false;
				$n->addClass = 'ccm-sitemap-explore';
				$n->noLink = true;
				$n->active = false;
				$n->focus = false;
				$n->unselectable = true;
				$n->title = t('%s more to display. <strong>View all &gt;</strong>', $total - SITEMAP_PAGES_LIMIT);
				$n->href = View::url('/dashboard/sitemap/explore/', $cID);
				$nodes[] = $n;
			}
		}
		return $nodes;
	}

	public function getNode($cItem, $includeChildren = true) {
		if (!is_object($cItem)) {
			$cID = $cItem;
			$c = Page::getByID($cID, 'RECENT');
		} else {
			$cID = $cItem->getCollectionID();
			$c = $cItem;
		}
		
		$cp = new Permissions($c);
		$canEditPageProperties = $cp->canEditPageProperties();
		$canEditPageSpeedSettings = $cp->canEditPageSpeedSettings();
		$canEditPagePermissions = $cp->canEditPagePermissions();
		$canEditPageDesign = ($cp->canEditPageTheme() || $cp->canEditPageTemplate());
		$canViewPageVersions = $cp->canViewPageVersions();
		$canDeletePage = $cp->canDeletePage();
		$canAddSubpages = $cp->canAddSubpage();
		$canAddExternalLinks = $cp->canAddExternalLink();
		
		$nodeOpen = false;
		$openNodeArray = explode(',', str_replace('_', '', $_COOKIE['ccmsitemap-expand']));
		if (is_array($openNodeArray)) {
			if (in_array($cID, $openNodeArray)) {
				$nodeOpen = true;
			}
		}
		
		$cls = ($c->getNumChildren() > 0) ? "folder" : "file";
		$leaf = ($c->getNumChildren() > 0) ? false : true;
		$numSubpages = ($c->getNumChildren()  > 0) ? $c->getNumChildren()  : '';
		
		$cvName = ($c->getCollectionName()) ? $c->getCollectionName() : '(No Title)';
		
		$ct = PageType::getByID($c->getPageTypeID());
		$isInTrash = $c->isInTrash();
		
		$isTrash = $c->getCollectionPath() == TRASH_PAGE_PATH;
		if ($isTrash || $isInTrash) { 
			$pk = PermissionKey::getByHandle('empty_trash');
			if (!$pk->validate()) {
				return false;
			}
		}
		
		$cIcon = $c->getCollectionIcon();
		if (!$cIcon) {
			if ($cID == 1) {
				$cIconClass = 'glyphicon glyphicon-home';
			} else if ($numSubpages > 0) {
				$cIcon = ASSETS_URL_IMAGES . '/dashboard/sitemap/folder.png';
			} else {
				$cIcon = ASSETS_URL_IMAGES . '/dashboard/sitemap/document.png';
			}
		}

		$cAlias = $c->isAlias();
		$cPointerID = $c->getCollectionPointerID();
		if ($cAlias) {
			/*
			if ($cPointerID > 0) {
				$cIcon = ASSETS_URL_IMAGES . '/icons/alias.png';
				$cAlias = 'POINTER';
				$cID = $c->getCollectionPointerOriginalID();
			} else {
				$cIcon = ASSETS_URL_IMAGES . '/icons/alias_external.png';
				$cAlias = 'LINK';
			}*/

		}

		/*
		$node = array(
			'cIcon' => $cIcon,
			'cAlias' => $cAlias,
			'numSubpages'=> $numSubpages,
		);

		*/

		$node = new stdClass;
		$node->title = $cvName;
		if ($numSubpages > 0) {
			$node->isLazy = true;
		}
		if ($cIcon) {
			$node->icon = $cIcon;
		} else if ($cIconClass) {
			$node->iconClass = $cIconClass;
		}
		$node->cAlias = $cAlias;
		$node->isInTrash = $isInTrash;
		$node->numSubpages = $numSubpages;
		$node->isTrash = $isTrash;
		$node->cID = $cID;
		$node->key = $cID;
		$node->canEditPageProperties = $canEditPageProperties;
		$node->canEditPageSpeedSettings = $canEditPageSpeedSettings;
		$node->canEditPagePermissions = $canEditPagePermissions;
		$node->canEditPageDesign = $canEditPageDesign;
		$node->canViewPageVersions = $canViewPageVersions;
		$node->canDeletePage = $canDeletePage;
		$node->canAddSubpages = $canAddSubpages;
		$node->canAddExternalLinks = $canAddExternalLinks;

		if ($includeChildren && ($cID == 1 || ($nodeOpen && $this->autoOpenNodes))) {
			// We open another level
			$node->children = $this->getSubNodes($cID, $level, false, $autoOpenNodes);
		}
		
		return $node;
	}

	function canRead() {
		$tp = new TaskPermission();
		return $tp->canAccessSitemap();
	}


}