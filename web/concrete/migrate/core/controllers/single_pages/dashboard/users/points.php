<?php

class Concrete5_Controller_Page_Dashboard_Users_Points extends DashboardController {

	public $helpers = array('form','concrete/interface', 'concrete/urls', 'image', 'concrete/asset_library','form/user_selector');
	
	public function view() {
		$upEntryList = $this->getEntries();
		$this->set('pagination',$upEntryList->getPagination());
		$this->set('upEntryList',$upEntryList);
		$this->set('entries',$upEntryList->getPage());
	}
	
	public function getEntries() {
		$entries = new UserPointEntryList();
		$entries->setItemsPerPage(100);
		
		if($_REQUEST['uName']) {
			$entries->filterByUserName($_REQUEST['uName']);
		}
		
		if($_REQUEST['upaName'] && strlen($_REQUEST['upaName'])) {
			$entries->filterByUserPointActionName($_REQUEST['upaName']);
		}
		
		switch($_REQUEST['ccm_order_by']) {
			case 'uName':
				$entries->sortBy('Users.uName', $_REQUEST['ccm_order_dir']);
			break;
			case 'upaName':
				$entries->sortBy('UserPointActions.upaName', $_REQUEST['ccm_order_dir']);
			break;
			case 'upPoints':
				$entries->sortBy('UserPointHistory.upPoints', $_REQUEST['ccm_order_dir']);
			break;
			case 'timestamp':
				$entries->sortBy('UserPointHistory.timestamp', $_REQUEST['ccm_order_dir']);
			break;
			default:
				$entries->sortBy('timestamp','desc');
			break;
		}
		
		return $entries;
	}
	
	public function deleteEntry($upID) {
		$up = new UserPointEntry();
		$up->load($upID);
		$up->Delete();
		$this->redirect('/dashboard/users/points/', 'entry_deleted');
	}
	
	public function entry_deleted() {
		$this->set('message',t('User Point Entry Deleted'));
		$this->view();
	}
}