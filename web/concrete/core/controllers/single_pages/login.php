<?php defined('C5_EXECUTE') or die("Access Denied.");
Loader::library('authentication/open_id');

class Concrete5_Controller_Login extends Controller {

	public $helpers = array('form');
	protected $locales = array();
	protected $supportsPageCache = true;

	public function on_start() {
		$this->error = Loader::helper('validation/error');
		if (USER_REGISTRATION_WITH_EMAIL_ADDRESS) {
			$this->set('uNameLabel', t('Email Address'));
		} else {
			$this->set('uNameLabel', t('Username'));
		}

		if(!$_COOKIE[SESSION]) {
			throw new Exception(t('Your browser\'s cookie functionality is turned off. Please turn it on.'));
		}

		$txt = Loader::helper('text');
		if (strlen($_GET['uName'])) { // pre-populate the username if supplied, if its an email address with special characters the email needs to be urlencoded first,
		   $this->set("uName",trim($txt->email($_GET['uName'])));
		}


		$languages = array();
		$locales = array();
		if (Config::get('LANGUAGE_CHOOSE_ON_LOGIN')) {
			Loader::library('3rdparty/Zend/Locale');
			Loader::library('3rdparty/Zend/Locale/Data');
			$languages = Localization::getAvailableInterfaceLanguages();
			if (count($languages) > 0) {
				array_unshift($languages, 'en_US');
			}
			$locales = array();
			Zend_Locale_Data::setCache(Cache::getLibrary());
			foreach($languages as $lang) {
				$loc = new Zend_Locale($lang);
				$locales[$lang] = Zend_Locale::getTranslation($loc->getLanguage(), 'language', $lang);
				$locRegion = $loc->getRegion();
				if($locRegion !== false) {
					$locRegionName = $loc->getTranslation($loc->getRegion(), 'country', $lang);
					if($locRegionName !== false) {
						$locales[$lang] .= ' (' . $locRegionName . ')';
					}
				}
			}
			asort($locales);
			$locales = array_merge(array('' => t('** Default')), $locales);
		}
		$this->locales = $locales;
		$this->set('locales', $locales);

	}

	/* automagically run by the controller once we're done with the current method */
	/* method is passed to this method, the method that we were just finished running */
	public function on_before_render() {
		if ($this->error->has()) {
			$this->set('error', $this->error);
		}
	}

	public function view($type = NULL, $element = 'form') {
		if(strlen($type)) {
			$at = AuthenticationType::getByHandle($type);
			$this->set('authType', $at);
			$this->set('authTypeElement', $element);
		}
	}

	public function account_deactivated() {
		$this->error->add(t('This user is inactive. Please contact us regarding this account.'));
	}


	/**
	 * Concrete5_Controller_Login::callback
	 * Call an AuthenticationTypeController method throw a uri.
	 * Use: /login/TYPE/METHOD/PARAM1/.../PARAMn
	 *
	 * @param $type		AuthenticationTypeHandle
	 * @param $method	Method to be ran, defaults to "callback"
	 */
	public function callback($type,$method='callback') {
		$at = AuthenticationType::getByHandle($type);
		if (!method_exists($at->controller, $method)) {
			throw new exception('Invalid method.');
		}
		if ($method != 'callback') {
			if (!is_array($at->controller->apiMethods) || !in_array($method,$at->controller->apiMethods)) {
				throw new Exception("Invalid method.");
			}
		}
		try {
			$params = func_get_args();
			if (count($params) > 2) {
				array_shift($params);
				array_shift($params);
				$message = call_user_method_array($method, $at->controller,$params);
			} else {
				$message = call_user_method($method, $at->controller);
			}
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

	/**
	 * Concrete5_Controller_Login::authenticate
	 * Authenticate the user using a specific authentication type.
	 *
	 * @param $type	AuthenticationType handle
	 */
	public function authenticate($type) {
		try {
			$at = AuthenticationType::getByHandle($type);
			$at->controller->authenticate();
			$db = Loader::db();
			$u = new User();
			if ($u->getUserID() == 1 && $type != 'concrete') {
				$u->logout();
				throw new exception('You can only identify as the root user using the concrete login.');
			}
			$u->setLastAuthType($at);
			Events::fire('on_user_login',$this);
			$this->chooseRedirect();
		} catch (exception $e) {
			$this->error->add($e->getMessage());
		}
		$this->view();
	}

	public function chooseRedirect() {
		if (!$this->error) {
			$this->error = Loader::helper('validation/error');
		}
		$dash = Page::getByPath("/dashboard", "RECENT");
		$dbp = new Permissions($dash);

		//should administrator be redirected to dashboard?  defaults to yes if not set.
		$adminToDash=intval(Config::get('LOGIN_ADMIN_TO_DASHBOARD'));
		$u = new User(); // added for the required registration attribute change above. We recalc the user and make sure they're still logged in
		if ($u->isRegistered()) {
			if ($u->config('NEWSFLOW_LAST_VIEWED') == 'FIRSTRUN') {
				$u->saveConfig('NEWSFLOW_LAST_VIEWED', 0);
			}

			if ($loginData['redirectURL']) {
				//make double secretly sure there's no caching going on
				header("Cache-Control: no-store, no-cache, must-revalidate");
				header("Pragma: no-cache");
				header('Expires: Fri, 30 Oct 1998 14:19:41 GMT'); //in the past
				$this->externalRedirect( $loginData['redirectURL'] );
			} elseif ($dbp->canRead() && $adminToDash) {
				$this->redirect('/dashboard');
			} else {
				//options set in dashboard/users/registration
				$login_redirect_cid=intval(Config::get('LOGIN_REDIRECT_CID'));
				$login_redirect_mode=Config::get('LOGIN_REDIRECT');

				//redirect to user profile
				if ($login_redirect_mode=='PROFILE' && ENABLE_USER_PROFILES) {
					$this->redirect( '/profile/', $u->uID );

				//redirect to custom page
				} elseif ($login_redirect_mode=='CUSTOM' && $login_redirect_cid > 0) {
					$redirectTarget = Page::getByID( $login_redirect_cid );
					if (intval($redirectTarget->cID)>0) $this->redirect( $redirectTarget->getCollectionPath());
					else $this->redirect('/');

				//redirect home
				} else $this->redirect('/');
			}
		} else {
			$this->error->add('User is not registered. Check your authentication controller.');
			$u->logout();
		}
	}

	public function logout() {
		$u = new User();
		$u->logout();
		$this->redirect('/');
	}

	public function forward($cID = 0) {
		$nh = Loader::helper('validation/numbers');
		if ($nh->integer($cID)) {
			$this->set('rcID', $cID);
		}
	}
	/* @TODO this functionality needs to be ported to the concrete5 auth type
	// responsible for validating a user's email address
	public function v($hash = '') {
		$ui = UserInfo::getByValidationHash($hash);
		if (is_object($ui)) {
			$ui->markValidated();
			$this->set('uEmail', $ui->getUserEmail());
			$this->set('validated', true);
		}
	}
	
	*/

}
