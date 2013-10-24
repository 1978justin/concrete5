<?
defined('C5_EXECUTE') or die("Access Denied.");

class Concrete5_Controller_Page_Dashboard_Users_AddGroup extends DashboardController {

	public function checkExpirationOptions($g) {
		if ($_POST['gUserExpirationIsEnabled']) {
			$date = Loader::helper('form/date_time');
			switch($_POST['gUserExpirationMethod']) {
				case 'SET_TIME':
					$g->setGroupExpirationByDateTime($date->translate('gUserExpirationSetDateTime'), $_POST['gUserExpirationAction']);
					break;
				case 'INTERVAL':
					$g->setGroupExpirationByInterval($_POST['gUserExpirationIntervalDays'], $_POST['gUserExpirationIntervalHours'], $_POST['gUserExpirationIntervalMinutes'], $_POST['gUserExpirationAction']);
					break;
			}
		} else {
			$g->removeGroupExpiration();
		}
	}

	public function checkBadgeOptions($g) {
		if ($_POST['gIsBadge']) {
			$g->setBadgeOptions($this->post('gBadgeFID'), $this->post('gBadgeDescription'), $this->post('gBadgeCommunityPointValue'));
		} else {
			$g->clearBadgeOptions();
		}
	}

	public function checkAutomationOptions($g) {
		if ($_POST['gIsAutomated']) {
			$g->setAutomationOptions($this->post('gCheckAutomationOnRegister'), $this->post('gCheckAutomationOnLogin'), $this->post('gCheckAutomationOnJobRun'));
		} else {
			$g->clearAutomationOptions();
		}
	}


	public function do_add() {
		$txt = Loader::helper('text');
		$valt = Loader::helper('validation/token');
		$gName = $txt->sanitize($_POST['gName']);
		$gDescription = $_POST['gDescription'];
		
		if (!$gName) {
			$this->error->add(t("Name required."));
		}
		
		if (!$valt->validate('add_or_update_group')) {
			$this->error->add($valt->getErrorMessage());
		}

		if ($_POST['gIsBadge']) {
			if (!$this->post('gBadgeDescription')) {
				$this->error->add(t('You must specify a description for this badge. It will be displayed publicly.'));
			}
		}
		
		$g1 = Group::getByName($gName);
		if ($g1 instanceof Group) {
			if ((!is_object($g)) || $g->getGroupID() != $g1->getGroupID()) {
				$this->error->add(t('A group named "%s" already exists', $g1->getGroupName()));
			}
		}

		if (!$this->error->has()) { 	
			$g = Group::add($gName, $_POST['gDescription']);
			$this->checkExpirationOptions($g);
			$this->checkBadgeOptions($g);
			$this->checkAutomationOptions($g);
			$this->redirect('/dashboard/users/groups', 'group_added');
		}	
	}

}