<?
defined('C5_EXECUTE') or die("Access Denied.");

class Concrete5_Library_PageSearchResultItem extends SearchResultItem {

	public $cID;

	public function __construct(SearchResult $result, DatabaseItemListColumnSet $columns, $item) {
		$list = $result->getItemListObject();
		if ($list->isIndexedSearch()) {
			$this->columns[] = new SearchResultItemColumn(t('Score'), $item->getPageIndexScore());
		}
		parent::__construct($result, $columns, $item);
		$this->populateDetails($item);
	}

	protected function populateDetails($item) {
		$this->cID = $item->getCollectionID();
	}


}
