<?
defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Controller_Dialogs_Page_Delete extends FrontendEditPageController {

	protected $viewPath = '/system/dialogs/page/delete';

	protected function canAccess() {
		return $this->permissions->canDeletePage();
	}

	public function view() {
		$this->set('numChildren', $this->page->getNumChildren());
	}

	public function submit() {
		if ($this->validateAction()) {
			$c = $this->page;
			$cp = $this->permissions;
			$u = new User();
			if ($cp->canDeletePage() && $c->getCollectionID() != HOME_CID && (!$c->isMasterCollection())) {
				$children = $c->getNumChildren();
				if ($children == 0 || $u->isSuperUser()) {
					if ($c->isExternalLink()) {
						$c->delete();
					} else { 
						$pkr = new DeletePagePageWorkflowRequest();
						$pkr->setRequestedPage($c);
						$pkr->setRequesterUserID($u->getUserID());
						$u->unloadCollectionEdit($c);
						$response = $pkr->trigger();
						$pr = new PageEditResponse();
						$pr->setPage($c);
						$parent = Page::getByID($c->getCollectionParentID(), 'ACTIVE');
						if ($response instanceof WorkflowProgressResponse) {
							// we only get this response if we have skipped workflows and jumped straight in to an approve() step.
							$pr->setMessage(t('Page deleted successfully.'));
							$pr->setRedirectURL(BASE_URL . '/' . DISPATCHER_FILENAME . '?cID=' . $parent->getCollectionID());
						} else {
							$pr->setMessage(t('Page request saved. This action will have to be approved before the page is deleted.'));
						}
						Loader::helper('ajax')->sendResult($pr);
					}
				}
			}
		}
	}

}

