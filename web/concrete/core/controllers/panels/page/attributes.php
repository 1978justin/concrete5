<?
defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Controller_Panel_Page_Attributes extends FrontendEditPageController {

	protected $viewPath = '/system/panels/page/attributes';

	public function canAccess() {
		return $this->permissions->canEditPageProperties();
	}

	public function view() {
		$pk = PermissionKey::getByHandle('edit_page_properties');
		$pk->setPermissionObject($c);
		$assignment = $pk->getMyAssignment();
		$allowed = $assignment->getAttributesAllowedArray();

		$category = AttributeKeyCategory::getByHandle('collection');
		$sets = $category->getAttributeSets();
		$leftovers = $category->getUnassignedAttributeKeys();

		$selectedAttributes = $this->page->getSetCollectionAttributes();
		$selectedAttributeIDs = array();
		foreach($selectedAttributes as $ak) {
			$selectedAttributeIDs[] = $ak->getAttributeKeyID();
		}

		$data = array();
		foreach($sets as $set) {
			$obj = new stdClass;
			$obj->title = $set->getAttributeSetName();
			$obj->attributes = array();
			foreach($set->getAttributeKeys() as $ak) {
				if (in_array($ak->getAttributeKeyID(), $allowed)) {
					$obj->attributes[] = $ak;
				}
			}
			if (count($obj->attributes)) {
				$data[] = $obj;
			}
		}
		if (count($leftovers)) {
			$obj = new stdClass;
			$obj->title = t('Other');
			$obj->attributes = array();
			foreach($leftovers as $ak) {
				if (in_array($ak->getAttributeKeyID(), $allowed)) {
					$obj->attributes[] = $ak;
				}
			}
			if (count($obj->attributes)) {
				$data[] = $obj;
			}
		}

		$this->set('selectedAttributeIDs', $selectedAttributeIDs);
		$this->set('assignment', $asl);
		$this->set('attributes', $data);
	}
		

}

