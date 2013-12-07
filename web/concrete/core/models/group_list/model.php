<?
defined('C5_EXECUTE') or die("Access Denied.");

/**
 * @package Users
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 * @access private
 */
 
/** 
 * @access private
 */

class Concrete5_Model_GroupSearch extends DatabaseItemList {
	
	
	protected $itemsPerPage = 10;
	protected $minimumGroupID = REGISTERED_GROUP_ID;
	
	protected $autoSortColumns = array('gName', 'gID');

	public function includeAllGroups() {
		$this->filter('gID', -1, '>');
	}
	
	public function filterByKeywords($kw) {
		$db = Loader::db();
		$this->filter(false, "(Groups.gName like " . $db->qstr('%' . $kw . '%') . " or Groups.gDescription like " . $db->qstr('%' . $kw . '%') . ")");
	}
	
	// only return groups that this user has the ability to assign.
	public function filterByAssignable() {
		if (PERMISSIONS_MODEL != 'simple') {
			// there's gotta be a more reasonable way than this but right now i'm not sure what that is.
			$excludeGroupIDs = array(GUEST_GROUP_ID, REGISTERED_GROUP_ID);
			$db = Loader::db();
			$r = $db->Execute('select gID from Groups where gID > ?', array(REGISTERED_GROUP_ID));
			while ($row = $r->FetchRow()) {
				$g = Group::getByID($row['gID']);
				$gp = new Permissions($g);
				if (!$gp->canAssignGroup()) {
					$excludeGroupIDs[] = $row['gID'];
				}
			}
			$this->filter('gID', $excludeGroupIDs, 'not in');
		}
	}

	public function updateItemsPerPage( $num ) {
		$this->itemsPerPage = $num;
	}
	
	function __construct() {
		$this->setQuery("select Groups.gID, Groups.gName, Groups.gDescription from Groups");
		$this->filter('gID', REGISTERED_GROUP_ID, '>');
	}

	public function get($itemsToGet = 100, $offset = 0) {
		$r = parent::get( $itemsToGet, intval($offset));
		$groups = array();
		foreach($r as $row) {
			$g = Group::getByID($row['gID']);			
			if (is_object($g)) {
				$groups[] = $g;
			}
		}
		return $groups;
	}
	
}