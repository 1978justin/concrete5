<?
defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Controller_Page_Dashboard_Sitemap_Search extends DashboardController {
	
	public $helpers = array('form');
	
	public function view() {
		$cnt = new SearchPagesController();
		$cnt->search();
		$this->set('searchController', $cnt);
		$result = Loader::helper('json')->encode($cnt->getSearchResultObject()->getJSONObject());
		$this->addFooterItem("<script type=\"text/javascript\">$(function() { $('div[data-search=pages]').concreteAjaxSearch(" . $result . "); });</script>");
	}

}