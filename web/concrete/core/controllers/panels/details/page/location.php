<?
defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Controller_Panel_Details_Page_Location extends FrontendEditPageController {

	protected $viewPath = '/system/panels/details/page/location';

	protected function canAccess() {
		return (!$this->page->isPageDraft() && $this->page->getCollectionID() != HOME_CID && is_object($this->asl) && $this->asl->allowEditPaths());
	}

	public function __construct() {
		parent::__construct();
		$pk = PermissionKey::getByHandle('edit_page_properties');
		$pk->setPermissionObject($this->page);
		$this->asl = $pk->getMyAssignment();
	}

	public function view() {
		$this->requireAsset('core/sitemap');
		$this->set('parent', Page::getByID($this->page->getCollectionParentID(), 'ACTIVE'));
	}

	public function submit() {
		if ($this->validateAction()) {
			$oc = $this->page;
			$successMessage = false;
			$ocp = new Permissions($oc);
			if ($oc->getCollectionParentID() != $_POST['cParentID']) {
				$dc = Page::getByID($_POST['cParentID'], 'RECENT');
				if (!is_object($dc) || $dc->isError()) {
					throw new Exception('Invalid parent page.');
				}
				$dcp = new Permissions($dc);
				$ct = PageType::getByID($this->page->getPageTypeID());
				if (!$dcp->canAddSubpage($ct)) {
					throw new Exception('You do not have permission to add this subpage here.');
				}
				if (!$oc->canMoveCopyTo($dc)) {
					throw new Exception('You cannot add a page beneath itself.');
				}

				$pkr = new MovePagePageWorkflowRequest();
				$pkr->setRequestedPage($oc);
				$pkr->setRequestedTargetPage($dc);
				$pkr->setSaveOldPagePath(false);
				$u = new User();
				$pkr->setRequesterUserID($u->getUserID());
				$u->unloadCollectionEdit($oc);
				$r = $pkr->trigger();
				if ($r instanceof WorkflowProgressResponse) { 
					$successMessage .= '"' . $oc->getCollectionName() . '" '.t('was moved beneath').' "' . $dc->getCollectionName() . '." ';
				} else { 
					$successMessage .= t("Your request to move \"%s\" beneath \"%s\" has been stored. Someone with approval rights will have to activate the change.\n", $oc->getCollectionName() , $dc->getCollectionName() );
				}
			}

			if (!$successMessage) {
				$successMessage = t('Page location saved successfully.');
			}

			$r = new PageEditVersionResponse();
			$r->setPage($this->page);
			$r->setMessage($successMessage);
			$nc = Page::getByID($this->page->getCollectionID(), 'ACTIVE');
			$r->setRedirectURL(Loader::helper('navigation')->getLinkToCollection($nc));
			Loader::helper('ajax')->sendResult($r);
		}
	}

}