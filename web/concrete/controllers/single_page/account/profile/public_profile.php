<?
namespace Concrete\Controller\SinglePage\Account\Profile;
use \Concrete\Core\Page\Controller\AccountPageController;
use Config;
use Loader;
use User;
use UserInfo;
use Exception;

class PublicProfile extends AccountPageController {

	public function save_complete() {
		$this->set('success', t('Profile updated successfully.'));
		$this->view();
	}

	public function view($userID = 0) {
		if(!Config::get('concrete.user.profiles_enabled')) {
			header("HTTP/1.0 404 Not Found");
			$this->render("/page_not_found");
		}

		$html = Loader::helper('html');
		$canEdit = false;
		$u = new User();

		if ($userID > 0) {
			$profile = UserInfo::getByID($userID);
			if (!is_object($profile)) {
				throw new Exception('Invalid User ID.');
			}
		} else if ($u->isRegistered()) {
			$profile = UserInfo::getByID($u->getUserID());
		} else {
			$this->set('intro_msg', t('You must sign in order to access this page!'));
			$this->render('/login');
		}
		if (is_object($profile) && $profile->getUserID() == $u->getUserID()) {
			$canEdit = true;
		}

		$this->set('profile', $profile);
		$this->set('badges', $profile->getUserBadges());
		$this->set('av', Loader::helper('concrete/avatar'));
		$this->set('t', Loader::helper('text'));
		$this->set('canEdit',$canEdit);
	}

}
