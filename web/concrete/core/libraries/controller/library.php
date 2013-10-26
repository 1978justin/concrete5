<?

defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Library_Controller extends AbstractController {

	protected $view;
	protected $viewPath;
	protected $theme;

	public function setViewObject(View $view) {
		$this->view = $view;
	}

	public function setTheme($mixed) {
		if ($mixed instanceof PageTheme) {
			$this->theme = $mixed->getThemeHandle();
		} else {
			$this->theme = $mixed;
		}
	}

	public function getTheme() {
		if (is_object($this->view)) {
			$rl = Router::getInstance();
			$tmpTheme = $rl->getThemeByRoute($this->view->getViewPath());
			if ($tmpTheme) {
				return $tmpTheme[0];
			}
		}
	}

	public function __construct() {
		if ($this->viewPath) {
			$this->view = new View($this->viewPath);
			$this->view->setController($this);
		}
	}

	public function getViewObject() {
		if ($this->view) {
			$this->view->setController($this);
			$this->view->setViewTheme($this->getTheme());
			return $this->view;
		}
	}

	public function action() {
		$a = func_get_args();
		array_unshift($a, $this->view->getViewPath());
		$ret = call_user_func_array(array($this->view, 'url'), $a);
		return $ret;
	}


}