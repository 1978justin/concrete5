<?
defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Library_DatabaseItemListAttributeKeyColumn extends DatabaseItemListColumn {

	protected $attributeKey = false;
	
	public function getAttributeKey() {
		return $this->attributeKey;
	}

	public function __construct($attributeKey, $isSortable = true, $defaultSort = 'asc') {
		$this->attributeKey = $attributeKey;
		parent::__construct('ak_' . $attributeKey->getAttributeKeyHandle(), tc('AttributeKeyName', $attributeKey->getAttributeKeyName()), false, $isSortable, $defaultSort);
	}
	
	public function getColumnValue($obj) {
		if (is_object($this->attributeKey)) {
			$vo = $obj->getAttributeValueObject($this->attributeKey);
			if (is_object($vo)) {
				return $vo->getValue('display');
			}
		}
	}
}