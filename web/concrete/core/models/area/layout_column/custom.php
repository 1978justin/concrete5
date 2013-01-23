<?

defined('C5_EXECUTE') or die("Access Denied.");

class Concrete5_Model_CustomAreaLayoutColumn extends AreaLayoutColumn {

	public static function getByID($arLayoutColumnID) {
		$db = Loader::db();
		$row = $db->GetRow('select * from AreaLayoutCustomColumns where arLayoutColumnID = ?', array($arLayoutColumnID));
		if (is_array($row) && $row['arLayoutColumnID']) {
			$al = new CustomAreaLayoutColumn();
			$al->loadBasicInformation($arLayoutColumnID);
			$al->setPropertiesFromArray($row);
			return $al;
		}
	}	

	public function duplicate($newAreaLayout) {
		$areaLayoutColumnID = parent::duplicate($newAreaLayout);
		$db = Loader::db();
		$v = array($areaLayoutColumnID, $this->arLayoutColumnWidth);
		$db->Execute('insert into AreaLayoutCustomColumns (arLayoutColumnID, arLayoutColumnWidth) values (?, ?)', $v);
		$newAreaLayoutColumn = CustomAreaLayoutColumn::getByID($areaLayoutColumnID);
		return $newAreaLayoutColumn;
	}

	public function getAreaLayoutColumnClass() {
		return 'ccm-layout-column';
	}

	public function getAreaLayoutColumnWidth() {
		return $this->arLayoutColumnWidth;
	}

	public function setAreaLayoutColumnWidth($width) {
		$this->arLayoutColumnWidth = $width;
		$db = Loader::db();
		$db->Execute('update AreaLayoutCustomColumns set arLayoutColumnWidth = ? where arLayoutColumnID = ?', array($width, $this->arLayoutColumnID));
	}

	public function delete() {
		$db = Loader::db();
		$db->Execute("delete from AreaLayoutCustomColumns where arLayoutColumnID = ?", array($this->arLayoutColumnID));
		parent::delete();
	}




}