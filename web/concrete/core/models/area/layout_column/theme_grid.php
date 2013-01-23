<?

defined('C5_EXECUTE') or die("Access Denied.");

class Concrete5_Model_ThemeGridAreaLayoutColumn extends AreaLayoutColumn {

	public static function getByID($arLayoutColumnID) {
		$db = Loader::db();
		$row = $db->GetRow('select * from AreaLayoutThemeGridColumns where arLayoutColumnID = ?', array($arLayoutColumnID));
		if (is_array($row) && $row['arLayoutColumnID']) {
			$al = new ThemeGridAreaLayoutColumn();
			$al->loadBasicInformation($arLayoutColumnID);
			$al->setPropertiesFromArray($row);
			return $al;
		}
	}	

	public function duplicate($newAreaLayout) {
		$areaLayoutColumnID = parent::duplicate($newAreaLayout);
		$db = Loader::db();
		$v = array($areaLayoutColumnID, $this->arLayoutColumnSpan, $this->arLayoutColumnOffset);
		$db->Execute('insert into AreaLayoutThemeGridColumns (arLayoutColumnID, arLayoutColumnSpan, arLayoutColumnOffset) values (?, ?, ?)', $v);
		$newAreaLayoutColumn = ThemeGridAreaLayoutColumn::getByID($areaLayoutColumnID);
		return $newAreaLayoutColumn;
	}


	public function getAreaLayoutColumnSpan() {
		return $this->arLayoutColumnSpan;
	}

	public function getAreaLayoutColumnOffset() {
		return $this->arLayoutColumnOffset;
	}
		
	public function getAreaLayoutColumnClass() {
		$gf = $this->arLayout->getThemeGridFrameworkObject();
		if (is_object($gf)) {
			// the width parameter of the column becomes the span
			return $gf->getPageThemeGridFrameworkColumnClassForSpan($this->arLayoutColumnSpan);
		}
	}

	// this returns offsets in the form of spans
	public function getAreaLayoutColumnOffsetEditClass() {
		$gf = $this->arLayout->getThemeGridFrameworkObject();
		if (is_object($gf)) {
			return $gf->getPageThemeGridFrameworkColumnClassForSpan($this->arLayoutColumnOffset);
		}
	}

	public function getAreaLayoutColumnOffsetClass() {
		$gf = $this->arLayout->getThemeGridFrameworkObject();
		if (is_object($gf)) {
			// the width parameter of the column becomes the span
			if ($gf->hasPageThemeGridFrameworkOffsetClasses()) { 
				return $gf->getPageThemeGridFrameworkColumnClassForOffset($this->arLayoutColumnOffset);
			} else {
				return $gf->getPageThemeGridFrameworkColumnClassForSpan($this->arLayoutColumnOffset);
			}
		}
	}

	public function delete() {
		$db = Loader::db();
		$db->Execute("delete from AreaLayoutThemeGridColumns where arLayoutColumnID = ?", array($this->arLayoutColumnID));
		parent::delete();
	}

	public function setAreaLayoutColumnSpan($span) {
		if (!$span) {
			$span = 0;
		}
		$db = Loader::db();
		$db->Execute('update AreaLayoutThemeGridColumns set arLayoutColumnSpan = ? where arLayoutColumnID = ?', array($span, $this->arLayoutColumnID));
		$this->arLayoutColumnSpan = $span;
	}

	public function setAreaLayoutColumnOffset($offset) {
		if (!$offset) {
			$offset = 0;
		}
		$db = Loader::db();
		$db->Execute('update AreaLayoutThemeGridColumns set arLayoutColumnOffset = ? where arLayoutColumnID = ?', array($offset, $this->arLayoutColumnID));
		$this->arLayoutColumnOffset = $offset;
	}



}