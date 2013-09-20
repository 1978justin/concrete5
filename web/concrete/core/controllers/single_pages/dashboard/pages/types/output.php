<?
defined('C5_EXECUTE') or die("Access Denied.");

class Concrete5_Controller_Dashboard_Pages_Types_Output extends DashboardBaseController {

	public function view($ptID = false) {
		$this->pagetype = PageType::getByID($ptID);
		if (!$this->pagetype) {
			$this->redirect('/dashboard/pages/types');
		}
		$this->set('pagetype', $this->pagetype);
	}


	public function edit_defaults($ptID = false, $pTemplateID = false) {
		$this->view($ptID);
		$template = PageTemplate::getByID($pTemplateID);
		if (!is_object($template)) {
			$this->redirect('/dashboard/pages/types');
		}
		$valid = false;
		foreach($this->pagetype->getPageTypePageTemplateObjects() as $pt) {
			if ($pt->getPageTemplateID() == $template->getPageTemplateID()) {
				$valid = true;
				break;
			}
		}
		if (!$valid) {
			$this->error->add(t('Invalid page template.'));
		}
		if (!$this->error->has()) {
			// we load up the master template for this composer/template combination.
			$c = $this->pagetype->getPageTypePageTemplateDefaultPageObject($template);
			$_SESSION['mcEditID'] = $c->getCollectionID();
			header('Location: ' . BASE_URL . DIR_REL . '/' . DISPATCHER_FILENAME . '?cID=' . $c->getCollectionID());
		}

	}




}