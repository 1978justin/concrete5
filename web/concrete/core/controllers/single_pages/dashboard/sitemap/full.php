<?
defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Controller_Page_Dashboard_Sitemap_Full extends DashboardController {

	public function view() {
		$this->requireAsset('core/sitemap');
	}
}