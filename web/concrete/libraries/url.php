<?

defined('C5_EXECUTE') or die("Access Denied.");
class URL extends Concrete5_Library_Url {

	public static function isValidURL($path) {
		return filter_var($path, FILTER_VALIDATE_URL);
	}

	public static function to($path, $action = false) {
		if (static::isValidURL($path)) {
			return $path;
		}

		$dispatcher = '';
		if ((!URL_REWRITING_ALL) || !defined('URL_REWRITING_ALL')) {
			$dispatcher = '/' . DISPATCHER_FILENAME;
		}
		
		$path = trim($path, '/');
		if ($path == '') {
			return DIR_REL . '/';
		}
		
		// if a query string appears in this variable, then we just pass it through as is
		if (strpos($path, '?') > -1) {
			return DIR_REL . $dispatcher. '/' . $path;
		} else {
			$_path = DIR_REL . $dispatcher. '/' . $path . '/';
		}
		
		if ($action != null) {
			$_path .= $action;			
			$args = func_get_args();
			if (count($args) > 2) {
				for ($i = 2; $i < count($args); $i++){
					$_path .= '/' . $args[$i];
				}
			}
			
			if (strpos($_path, '?') === false) {
				$_path .= '/';
			}
		}
		
		if (!URL_USE_TRAILING_SLASH) {
			$_path = rtrim($_path, '/');
		}
		return $_path;

	}
}




