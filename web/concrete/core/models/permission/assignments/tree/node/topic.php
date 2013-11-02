<?
defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Model_TopicTreeNodePermissionAssignment extends TreeNodePermissionAssignment {

	protected $inheritedPermissions = array(
		'view_topic_tree_node' => 'view_topic_category_tree_node'
	);

	public function setPermissionObject(TopicTreeNode $node) {
		$this->permissionObject = $node;
		
		if ($node->overrideParentTreeNodePermissions()) {
			$this->permissionObjectToCheck = $node;
		} else {
			$parent = TreeNode::getByID($node->getTreeNodePermissionsNodeID());
			$this->permissionObjectToCheck = $parent;
		}
	}

	public function getPermissionAccessObject() {
		$db = Loader::db();
		if ($this->permissionObjectToCheck instanceof TopicTreeNode) {
			$pa = parent::getPermissionAccessObject();
 		} else if ($this->permissionObjectToCheck instanceof TopicCategoryTreeNode && isset($this->inheritedPermissions[$this->pk->getPermissionKeyHandle()])) { 
			$inheritedPKID = $db->GetOne('select pkID from PermissionKeys where pkHandle = ?', array($this->inheritedPermissions[$this->pk->getPermissionKeyHandle()]));
	 		$r = $db->GetOne('select paID from TreeNodePermissionAssignments where treeNodeID = ? and pkID = ?', array(
	 			$this->permissionObjectToCheck->getTreeNodePermissionsNodeID(), $inheritedPKID
	 		));
	 		$pa = PermissionAccess::getByID($r, $this->pk);
		} else {
			return false;
		}
		
		return $pa;

	}


}