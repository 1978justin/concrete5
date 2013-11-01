<?
defined('C5_EXECUTE') or die("Access Denied.");

class Concrete5_Controller_Page_Account_Profile_Edit extends AccountPageController {
	
	public function view() {
		$u = new User();
		$profile = UserInfo::getByID($u->getUserID());
		if (is_object($profile)) {
			$this->set('profile', $profile);
		} else {
			throw new Exception(t('You must be logged in to access this page.'));
		}
	}

	public function callback($type,$method='callback') {
		$at = AuthenticationType::getByHandle($type);
		$this->view();
		if (!method_exists($at->controller, $method)) {
			throw new exception('Invalid method.');
		}
		if ($method != 'callback') {
			if (!is_array($at->controller->apiMethods) || !in_array($method,$at->controller->apiMethods)) {
				throw new Exception("Invalid method.");
			}
		}
		try {
			$message = call_user_method($method, $at->controller);
			if (trim($message)) {
				$this->set('message',$message);
			}
		} catch (exception $e) {
			if ($e instanceof AuthenticationTypeFailureException) {
				// Throw again if this is a big`n
				throw $e;
			}
			$this->error->add($e->getMessage());
		}
	}
		
	public function save() { 
		$this->view();
		$ui = $this->get('profile');

		$uh = Loader::helper('concrete/user');
		$th = Loader::helper('text');
		$vsh = Loader::helper('validation/strings');
		$cvh = Loader::helper('concrete/validation');
		$e = Loader::helper('validation/error');
	
		$data = $this->post();
		
		
		// validate the user's email
		$email = $this->post('uEmail');
		if (!$vsh->email($email)) {
			$e->add(t('Invalid email address provided.'));
		} else if (!$cvh->isUniqueEmail($email) && $ui->getUserEmail() != $email) {
			$e->add(t("The email address '%s' is already in use. Please choose another.",$email));
		}

		// password
		if(strlen($data['uPasswordNew'])) {
			$passwordNew = $data['uPasswordNew'];
			$passwordNewConfirm = $data['uPasswordNewConfirm'];
			
			if ((strlen($passwordNew) < USER_PASSWORD_MINIMUM) || (strlen($passwordNew) > USER_PASSWORD_MAXIMUM)) {
				$e->add(t('A password must be between %s and %s characters', USER_PASSWORD_MINIMUM, USER_PASSWORD_MAXIMUM));
			}		
			
			if (strlen($passwordNew) >= USER_PASSWORD_MINIMUM && !$cvh->password($passwordNew)) {
				$e->add(t('A password may not contain ", \', >, <, or any spaces.'));
			}
			
			if ($passwordNew) {
				if ($passwordNew != $passwordNewConfirm) {
					$e->add(t('The two passwords provided do not match.'));
				}
			}
			$data['uPasswordConfirm'] = $passwordNew;
			$data['uPassword'] = $passwordNew;
		}		
		
		$aks = UserAttributeKey::getEditableInProfileList();

		foreach($aks as $uak) {
			if ($uak->isAttributeKeyRequiredOnProfile()) {
				$e1 = $uak->validateAttributeForm();
				if ($e1 == false) {
					$e->add(t('The field "%s" is required', tc('AttributeKeyName', $uak->getAttributeKeyName())));
				} else if ($e1 instanceof ValidationErrorHelper) {
					$e->add($e1);
				}
			}
		}

		if (!$e->has()) {		
			$data['uEmail'] = $email;		
			if(ENABLE_USER_TIMEZONES) {
				$data['uTimezone'] = $this->post('uTimezone');
			}
			
			$ui->update($data);
			
			foreach($aks as $uak) {
				$uak->saveAttributeForm($ui);				
			}
			$this->redirect("/account/profile/public", "save_complete");
		} else {
			$this->set('error', $e);
		}
	}
}
