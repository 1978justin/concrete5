<?

defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Contains the blocktype object, the block type list (which is just a wrapper for querying the system for block types, and the block type
 * DB wrapper for ADODB.
 * @package Blocks
 * @author Andrew Embler <andrew@concrete5.org>
 * @category Concrete
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 *
 */
 /**
 *
 * @access private
 */	
	class Concrete5_Model_BlockTypeDB extends ADOdb_Active_Record {
		public $_table = 'BlockTypes';
	}

/**
*
* Any type of content that can be added to pages is represented as a type of block, and thereby a block type object.
* @package Blocks
* @author Andrew Embler <andrew@concrete5.org>
* @license    http://www.concrete5.org/license/     MIT License
* @package Blocks
* @category Concrete
*/		
	class Concrete5_Model_BlockType extends Object {

		/**
		 * @var array $addBTUArray
		 */
		public $addBTUArray = array();
		
		/**
		 * @var array $addBTGArray
		 */
		public $addBTGArray = array();
		
		/**
		 * @var BlockTypeController
		 */
		public $controller;
		
		/**
		 * Gets the BlockType object for the given Block Type Handle
		 * ex: 
		 * <code><?php
		 * $bt = BlockType::getByHandle('content'); // returns the BlockType object for the core Content block
		 * ?></code>
		 * @param string $handle
		 * @return BlockType|false
		 */
		public static function getByHandle($handle) {
			$bt = CacheLocal::getEntry('blocktype', $handle);
			if ($bt === -1) {
				return false;
			}

			if (is_object($bt)) {
				$bt->controller = Loader::controller($bt);
				return $bt;
			}

			$bt = BlockType::get('btHandle = ?', array($handle));
			if (is_object($bt)) {
				CacheLocal::set('blocktype', $handle, $bt);
				$bt->controller = Loader::controller($bt);
				return $bt;
			}

			CacheLocal::set('blocktype', $handle, -1);
			return false;
		}

		/**
		 * Gets the BlockType for a given Block Type ID
		 * @param int $btID
		 * @return BlockType
		 */
		public static function getByID($btID) {
			$bt = CacheLocal::getEntry('blocktype', $btID);
			if ($bt === -1) {
				return false;
			} else if (!is_object($bt)) {
				$where = 'btID = ?';
				$bt = BlockType::get($where, array($btID));			
				if (is_object($bt)) {
					CacheLocal::set('blocktype', $btID, $bt);
				} else {
					CacheLocal::set('blocktype', $btID, -1);
				}
			}
			$bt->controller = Loader::controller($bt);
			return $bt;
		}
		
		
		/**
		 * internal method to query the BlockTypes table and get a BlockType object
		 * @param string
		 * @param array
		 */
		protected static function get($where, $properties) {
			$db = Loader::db();
			
			$q = "select btID, btName, btDescription, btHandle, pkgID, btActiveWhenAdded, btIsInternal, btSupportsInlineEditing, btCopyWhenPropagate, btIncludeAll, btDisplayOrder, btInterfaceWidth, btInterfaceHeight from BlockTypes where {$where}";
			
			$r = $db->query($q, $properties);
			
			if ($r->numRows() > 0) {
				$row = $r->fetchRow();
				$bt = new BlockType;
				$bt->setPropertiesFromArray($row);
				return $bt;
			}
			
		}
		
		/** 
		 * if a the current BlockType is Internal or not - meaning one of the core built-in concrete5 blocks
		 * @access private
		 * @return boolean
		 */
		function isBlockTypeInternal() {return $this->btIsInternal;}

		/** 
		 * if a the current BlockType supports inline editing or not
		 * @return boolean
		 */
		function supportsInlineEditing() {return $this->btSupportsInlineEditing;}
		
		/** 
		 * Returns true if the block type is internal (and therefore cannot be removed) a core block
		 * @return boolean
		 */
		public function isInternalBlockType() {
			return $this->btIsInternal;
		}
		
		/** 
		 * Returns true if the block type ships with concrete5 by checking to see if it's in the concrete/blocks/ directory
		 * @deprecated
		 */
		public function isCoreBlockType() {
			return is_dir(DIR_FILES_BLOCK_TYPES_CORE . '/' . $this->getBlockTypeHandle());
		}

		/**
		 * Determines if the block type has templates available
		 * @return boolean
		 */
		public function hasAddTemplate() {
			$bv = new BlockView();
			$bv->setBlockObject($this);
			$path = $bv->getBlockPath(FILENAME_BLOCK_ADD);
			if (file_exists($path . '/' . FILENAME_BLOCK_ADD)) {
				return true;
			}
			return false;
		}
		
		public function getBlockTypeSets() {
			$db = Loader::db();
			$list = array();
			$r = $db->Execute('select btsID from BlockTypeSetBlockTypes where btID = ? order by displayOrder asc', array($this->getBlockTypeID()));
			while ($row = $r->FetchRow()) {
				$list[] = BlockTypeSet::getByID($row['btsID']);
			}
			$r->Close();
			return $list;
		}

		/**
		 * returns the width in pixels that the block type's editing dialog will open in
		 * @return int
		 */
		public function getBlockTypeInterfaceWidth() {return $this->btInterfaceWidth;}
		
		/**
		 * returns the height in pixels that the block type's editing dialog will open in
		 * @return int
		 */
		public function getBlockTypeInterfaceHeight() {return $this->btInterfaceHeight;}
		
		/**
		 * returns the id of the BlockType's package if it's in a package
		 * @return int
		 */
		public function getPackageID() {return $this->pkgID;}
		
		/**
		 * returns the handle of the BlockType's package if it's in a package
		 * @return string
		 */
		public function getPackageHandle() {
			return PackageList::getHandle($this->pkgID);
		}
		
		
		/**
		 * determines if a user or group can add a block of the current BlockType
		 * @param UserInfo|Group $obj
		 * @return boolean
		 */
		public function canAddBlock($obj) {
			switch(strtolower(get_class($obj))) {
				case 'group':
					return in_array($obj->getGroupID(), $this->addBTGArray);
					break;
				case 'userinfo':
					return in_array($obj->getUserID(), $this->addBTUArray);
					break;
			}
		}
		
		/** 
		 * Returns the number of unique instances of this block throughout the entire site
		 * note - this count could include blocks in areas that are no longer rendered by the theme
		 * @return int
		 */
		public function getCount() {
			$db = Loader::db();
			$count = $db->GetOne("select count(btID) from Blocks where btID = ?", array($this->btID));
			return $count;
		}
		
		/**
		 * Not a permissions call. Actually checks to see whether this block is not an internal one.
		 * @return boolean
		 */
		public function canUnInstall() {
			/*$cnt = $this->getCount();
			if ($cnt > 0 || $this->isBlockTypeInternal()) {
				return false;
			}*/
			
			return (!$this->isBlockTypeInternal());
		}
		
		/**
		 * gets the BlockTypes description text
		 * @return string
		 */
		function getBlockTypeDescription() {
			return $this->btDescription;
		}
		
		/**
		 * Gets the custom templates available for the current BlockType
		 * @return array an array of strings
		 */
		function getBlockTypeCustomTemplates() {
			$btHandle = $this->getBlockTypeHandle();
			$pkgHandle = $this->getPackageHandle();

			$templates = array();
			$fh = Loader::helper('file');
			
			if (file_exists(DIR_FILES_BLOCK_TYPES . "/{$btHandle}/" . DIRNAME_BLOCK_TEMPLATES)) {
				$templates = array_merge($templates, $fh->getDirectoryContents(DIR_FILES_BLOCK_TYPES . "/{$btHandle}/" . DIRNAME_BLOCK_TEMPLATES));
			}
			
			/*
			if ($pkgHandle != null) {
				if (is_dir(DIR_PACKAGES . '/' . $pkgHandle)) {
					$templates = array_merge($templates, $fh->getDirectoryContents(DIR_PACKAGES . "/{$pkgHandle}/" . DIRNAME_BLOCKS . "/{$btHandle}/" . DIRNAME_BLOCK_TEMPLATES));
				} else {
					$templates = array_merge($templates, $fh->getDirectoryContents(DIR_PACKAGES_CORE . "/{$pkgHandle}/" . DIRNAME_BLOCKS . "/{$btHandle}/" . DIRNAME_BLOCK_TEMPLATES));
				}
			}
			*/ 
			
			// NOW, we check to see if this btHandle has any custom templates that have been installed as separate packages
			$pl = PackageList::get();
			$packages = $pl->getPackages();
			foreach($packages as $pkg) {
				$d = (is_dir(DIR_PACKAGES . '/' . $pkg->getPackageHandle())) ? DIR_PACKAGES . '/'. $pkg->getPackageHandle() : DIR_PACKAGES_CORE . '/'. $pkg->getPackageHandle();
				if (is_dir($d . '/' . DIRNAME_BLOCKS . '/' . $btHandle . '/' . DIRNAME_BLOCK_TEMPLATES)) {
					$templates = array_merge($templates, $fh->getDirectoryContents($d . '/' . DIRNAME_BLOCKS . '/' . $btHandle . '/' . DIRNAME_BLOCK_TEMPLATES));
				}
			}
			
			if (file_exists(DIR_FILES_BLOCK_TYPES_CORE . "/{$btHandle}/" . DIRNAME_BLOCK_TEMPLATES)) {
				$templates = array_merge($templates, $fh->getDirectoryContents(DIR_FILES_BLOCK_TYPES_CORE . "/{$btHandle}/" . DIRNAME_BLOCK_TEMPLATES));
			}

			$templates = array_unique($templates);
	
			return $templates;
		}

		
		/** 
		 * gets the available composer templates 
		 * used for editing instances of the BlockType while in the composer ui in the dashboard
		 * @return array array of strings
		 */
		function getBlockTypeComposerTemplates() {
			$btHandle = $this->getBlockTypeHandle();
			$pkgHandle = $this->getPackageHandle();

			$templates = array();
			$fh = Loader::helper('file');
			
			if (file_exists(DIR_FILES_BLOCK_TYPES . "/{$btHandle}/" . DIRNAME_BLOCK_TEMPLATES_COMPOSER)) {
				$templates = array_merge($templates, $fh->getDirectoryContents(DIR_FILES_BLOCK_TYPES . "/{$btHandle}/" . DIRNAME_BLOCK_TEMPLATES_COMPOSER));
			}

			if (file_exists(DIR_FILES_BLOCK_TYPES_CORE . "/{$btHandle}/" . DIRNAME_BLOCK_TEMPLATES_COMPOSER)) {
				$templates = array_merge($templates, $fh->getDirectoryContents(DIR_FILES_BLOCK_TYPES_CORE . "/{$btHandle}/" . DIRNAME_BLOCK_TEMPLATES_COMPOSER));
			}

			$templates = array_unique($templates);
	
			return $templates;
		}
		
		public function getBlockTypeComposerFilename() {
			return $this->cmpFilename;
		}

		public function setBlockTypeComposerFilename($cmpFilename) {
			$this->cmpFilename = $cmpFilename;
		}
		
		function setBlockTypeDisplayOrder($displayOrder) {
			$db = Loader::db();
			
			$displayOrder = intval($displayOrder); //in case displayOrder came from a string (so ADODB escapes it properly)
			
			$sql = "UPDATE BlockTypes SET btDisplayOrder = btDisplayOrder - 1 WHERE btDisplayOrder > ?";
			$vals = array($this->btDisplayOrder);
			$db->Execute($sql, $vals);
			
			$sql = "UPDATE BlockTypes SET btDisplayOrder = btDisplayOrder + 1 WHERE btDisplayOrder >= ?";
			$vals = array($displayOrder);
			$db->Execute($sql, $vals);
			
			$sql = "UPDATE BlockTypes SET btDisplayOrder = ? WHERE btID = ?";
			$vals = array($displayOrder, $this->btID);
			$db->Execute($sql, $vals);
			
			// now we remove the block type from cache
			$ca = new Cache();
			$ca->delete('blockTypeByID', $this->btID);
			$ca->delete('blockTypeByHandle', $this->btHandle);
			$ca->delete('blockTypeList', false);
		}

		/**
		 * installs a new BlockType from a package, 
		 * typicaly called from a package controller's install() method durring package installation 
		 * @todo Documentation how is the btID used, if you want to reserve/specify a btID??
		 * @param string $btHandle the block Type's handle
		 * @param Package $pkg
		 * @param int $btID if it's an existing block type
		 * @return void|string error message
		 */
		public function installBlockTypeFromPackage($btHandle, $pkg, $btID = 0) {
			$dir1 = DIR_PACKAGES . '/' . $pkg->getPackageHandle() . '/' . DIRNAME_BLOCKS;
			$dir2 = DIR_PACKAGES_CORE . '/' . $pkg->getPackageHandle() . '/' . DIRNAME_BLOCKS;
			
			if (file_exists($dir1)) {
				$dir = $dir1;
			} else {
				$dir = $dir2;
			}

			// now we check to see if it's been overridden in the site root and if so we do it there
			if ($btID > 0) { 
				// this is only necessary when it's an existing refresh
				if (file_exists(DIR_FILES_BLOCK_TYPES . '/' . $btHandle . '/' . FILENAME_BLOCK_CONTROLLER)) {
					$dir = DIR_FILES_BLOCK_TYPES;
				}
			}
			
			$bt = new BlockType;
			$bt->btHandle = $btHandle;
			$bt->pkgHandle = $pkg->getPackageHandle();
			$bt->pkgID = $pkg->getPackageID();
			return BlockType::doInstallBlockType($btHandle, $bt, $dir, $btID);
		}
		
		/**
		 * refreshes the BlockType's database schema throws an Exception if error
		 * @return void
		 */
		public function refresh() {
			if ($this->getPackageID() > 0) {
				$pkg = Package::getByID($this->getPackageID());
				$resp = BlockType::installBlockTypeFromPackage($this->getBlockTypeHandle(), $pkg, $this->getBlockTypeID());			
				if ($resp != '') {
					throw new Exception($resp);
				}
			} else {
				$resp = BlockType::installBlockType($this->getBlockTypeHandle(), $this->getBlockTypeID());			
				if ($resp != '') {
					throw new Exception($resp);
				}
			}
		}
		
		/**
		 * installs a core or root level BlockType (from /blocks or /concrete/blocks, not a package)
		 * should likely be a static method
		 * @param string $btHandle
		 * @param int $btID btID if it's an existing block type
		 */
		public function installBlockType($btHandle, $btID = 0) {
		
			if ($btID == 0) {
				// then we don't allow one to already exist
				$db = Loader::db();
				$cnt = $db->GetOne("select btID from BlockTypes where btHandle = ?", array($btHandle));
				if ($cnt > 0) {
					return false;
				}
			}
			
			if (file_exists(DIR_FILES_BLOCK_TYPES . '/' . $btHandle . '/' . FILENAME_BLOCK_CONTROLLER)) {
				$dir = DIR_FILES_BLOCK_TYPES;
			} else {
				$dir = DIR_FILES_BLOCK_TYPES_CORE;
			}
			
			$bt = new BlockType;
			$bt->btHandle = $btHandle;
			$bt->pkgHandle = null;
			$bt->pkgID = 0;
			return BlockType::doInstallBlockType($btHandle, $bt, $dir, $btID);
		}
		
		/** 
		 * Renders a particular view of a block type, using the public $controller variable as the block type's controller
		 * @param string template 'view' for the default
		 * @return void
		 */
		public function render($view = 'view') {
			$bv = new BlockView();
			$bv->setController($this->controller);
			$bv->render($this, $view);
		}			
		
		/**
		 * get's the block type controller
		 * @return BlockTypeController
		 */
		public function getController() {
			return $this->controller;
		}
		
		/**
		 * installs a block type
		 * @param string $btHandle
		 * @param BlockType $bt
		 * @param string $dir
		 * @param int $btID
		 */
		protected function doInstallBlockType($btHandle, $bt, $dir, $btID = 0) {
			$db = Loader::db();
			$env = Environment::get();
			$env->clearOverrideCache();
			
			if (file_exists($dir . '/' . $btHandle . '/' . FILENAME_BLOCK_CONTROLLER)) {
				$class = $bt->getBlockTypeClass();
				
				$path = $dir . '/' . $btHandle;
				if (!class_exists($class)) {
					require_once($dir . '/' . $btHandle . '/' . FILENAME_BLOCK_CONTROLLER);
				}
				
				if (!class_exists($class)) {
					throw new Exception(t("%s not found. Please check that the block controller file contains the correct class name.", $class));
				}
				$bta = new $class;
				
				//Attempt to run the subclass methods (install schema from db.xml, etc.)
				$r = $bta->install($path);
				
				//Validate
				if ($r === false) {
					return t('Error: Block Type cannot be installed because no db.xml file can be found. Either create a db.xml file for this block type, or remove the $btTable variable from its controller.');
				} else if (!$r->result && $r->message) {
					return $r->message;
				} else if (!$r->result && !$r->message) {
					return t('Error: Block Type cannot be installed due to an unknown database error. Please check that the db.xml file for this block type is formatted correctly.');
				} else if ($message = BlockType::validateInstalledDatabaseTable($bta->getBlockTypeDatabaseTable())) {
					$db->Execute('DROP TABLE IF EXISTS ' . $bta->getBlockTypeDatabaseTable());
					return $message;
				}
				
				$currentLocale = Localization::activeLocale();
				if ($currentLocale != 'en_US') {
					// Prevent the database records being stored in wrong language
					Localization::changeLocale('en_US');
				}

				//Install the block
				$btd = new BlockTypeDB();
				$btd->btHandle = $btHandle;
				$btd->btName = $bta->getBlockTypeName();
				$btd->btDescription = $bta->getBlockTypeDescription();
				$btd->btActiveWhenAdded = $bta->isActiveWhenAdded();
				$btd->btCopyWhenPropagate = $bta->isCopiedWhenPropagated();
				$btd->btIncludeAll = $bta->includeAll();
				$btd->btIsInternal = $bta->isBlockTypeInternal();
				$btd->btSupportsInlineEditing = $bta->supportsInlineEditing();
				$btd->btInterfaceHeight = $bta->getInterfaceHeight();
				$btd->btInterfaceWidth = $bta->getInterfaceWidth();
				$btd->pkgID = $bt->getPackageID();
				if ($currentLocale != 'en_US') {
					Localization::changeLocale($currentLocale);
				}
				
				if ($btID > 0) {
					$btd->btID = $btID;
					$btDisplayOrder = $db->GetOne('select btDisplayOrder from BlockTypes where btID = ?', array($btID));
					if (!$btDisplayOrder) {
						$btDisplayOrder = 0;
					}
					$btd->btDisplayOrder = $btDisplayOrder;
					$r = $btd->Replace();
				} else {
					if ($bta->isBlockTypeInternal()) {
						$btd->btDisplayOrder = 0;
					} else {
						$btMax = $db->GetOne('select max(btDisplayOrder) from BlockTypes');
						if ($btMax < 1 && $btMax !== '0') {
							$btd->btDisplayOrder = 0;
						} else {
							$btd->btDisplayOrder = $btMax + 1;
						}
					}

					$r = $btd->save();
				}
				
				// now we remove the block type from cache
				$ca = new Cache();
				$ca->delete('blockTypeByID', $btID);
				$ca->delete('blockTypeByHandle', $btHandle);
				$ca->delete('blockTypeList', false);		 	

				if (!$r) {
					return $db->ErrorMsg();
				}
			} else {
				return t("No block found with the handle %s.", $btHandle);
			}
		}
		
		/**
		 * Internal helper function for doInstallBlockType().
		 * 
		 * Checks if the database table with the given name
		 * has at least 2 fields and one of those is called "btID".
		 *
		 * If valid, we return an empty string.
		 * If not valid, we return an error message.
		 * If passed an empty string, we return an empty string.
		 */
		private function validateInstalledDatabaseTable($btTable) {
			$message = '';
			if (!empty($btTable)) {
				$fields = Loader::db()->MetaColumnNames($btTable);
				if (count($fields) < 2) {
					$message = t('Error: Block Type table must contain at least two fields.');
				} else if (!in_array('bID', $fields)) {
					$message = t('Error: Block Type table must contain a "bID" primary key field.');
				}
			}
			return $message;
		}
		
		/*
		 * Returns a path to where the block type's files are located.
		 * @access public
		 * @return string $path
		 */
		public function getBlockTypePath() {
			if ($this->getPackageID() > 0) {
				$pkgHandle = $this->getPackageHandle();
				$dirp = (is_dir(DIR_PACKAGES . '/' . $pkgHandle)) ? DIR_PACKAGES : DIR_PACKAGES_CORE;
				$dir = $dirp . '/' . $pkgHandle . '/' . DIRNAME_BLOCKS . '/' . $this->getBlockTypeHandle();
			} else {
				if (is_dir(DIR_FILES_BLOCK_TYPES . '/' . $this->getBlockTypeHandle())) {
					$dir = DIR_FILES_BLOCK_TYPES . '/' . $this->getBlockTypeHandle();
				} else {
					$dir = DIR_FILES_BLOCK_TYPES_CORE . '/' . $this->getBlockTypeHandle();
				}
			}
			return $dir;	
		}
		
/** @todo Continue documenting from here down **/
		
		 
		/*
		 * @access private
		 *
		 */
		protected function _getClass() {

			$btHandle = $this->btHandle;
			$pkgHandle = $this->getPackageHandle();
			$env = Environment::get();
			require_once($env->getPath(DIRNAME_BLOCKS . '/' . $this->btHandle . '/' . FILENAME_BLOCK_CONTROLLER, $pkgHandle));
			
			// takes the handle and performs some magic to get the class;
			$btHandle = $this->getBlockTypeHandle();
			// split by underscores or dashes
			$words = preg_split('/\_|\-/', $btHandle);
			for ($i = 0; $i < count($words); $i++) {
				$words[$i] = ucfirst($words[$i]);
			}
			
			$class = implode('', $words);
			$class = $class . 'BlockController';
			return $class;
		}
		
		public function inc($file, $args = array()) {
			extract($args);
			$bt = $this;
			global $c;
			global $a;
			$env = Environment::get();
			include($env->getPath(DIRNAME_BLOCKS . '/' . $this->getBlockTypeHandle() . '/' . $file, $this->getPackageHandle()));
		}
		
		public function getBlockTypeClass() {
			return $this->_getClass();
		}
		
		/**
		 * Deprecated -- use getBlockTypeClass() instead.
		 */
		public function getBlockTypeClassFromHandle() {
			return $this->getBlockTypeClass();
		}
		
		/** 
		 * Removes the block type. Also removes instances of content.
		 */
		public function delete() {
			$db = Loader::db();
			$r = $db->Execute('select cID, cvID, b.bID, arHandle from CollectionVersionBlocks cvb inner join Blocks b on b.bID = cvb.bID where btID = ?', array($this->getBlockTypeID()));
			while ($row = $r->FetchRow()) {
				$nc = Page::getByID($row['cID'], $row['cvID']);
				$b = Block::getByID($row['bID'], $nc, $row['arHandle']);
				if (is_object($b)) {
					$b->deleteBlock();
				}
			}
			
			$ca = new Cache();
			$ca->delete('blockTypeByID', $this->btID);
			$ca->delete('blockTypeByHandle', $this->btHandle);		 	
			$ca->delete('blockTypeList', false);		 	
			$db->Execute("delete from BlockTypes where btID = ?", array($this->btID));
			
			//Remove gaps in display order numbering (to avoid future sorting errors)
			BlockTypeList::resetBlockTypeDisplayOrder('btDisplayOrder');
		}
		
		/** 
		 * Allows block types to be updated
		 * @param array $data
		 */
		 
		 public function update($data) {
		 	$db = Loader::db();
		 	$btHandle = $this->btHandle;
		 	$btName = $this->btName;
		 	$btDescription = $this->btDescription;
		 	if (isset($data['btHandle'])) {
		 		$btHandle = $data['btHandle'];
		 	}
		 	if (isset($data['btName'])) {
		 		$btName = $data['btName'];
		 	}
		 	if (isset($data['btDescription'])) {
		 		$btDescription = $data['btDescription'];
		 	}
		 	$db->Execute('update BlockTypes set btHandle = ?, btName = ?, btDescription = ? where btID = ?', array($btHandle, $btName, $btDescription, $this->btID));

			// now we remove the block type from cache
			$ca = new Cache();
			$ca->delete('blockTypeByID', $this->btID);
			$ca->delete('blockTypeByHandle', $btHandle);
			$ca->delete('blockTypeList', false);		 	
		 }
		 
		 
		/* 
		 * Adds a block to the system without adding it to a collection. 
		 * Passes page and area data along if it is available, however.
		 */
		public function add($data, $c = false, $a = false) {
			$db = Loader::db();
			
			$u = new User();
			if (isset($data['uID'])) {
				$uID = $data['uID'];
			} else { 
				$uID = $u->getUserID();
			}
			$btID = $this->btID;
			$dh = Loader::helper('date');
			$bDate = $dh->getSystemDateTime();
			$bIsActive = ($this->btActiveWhenAdded == 1) ? 1 : 0;
			
			$v = array($_POST['bName'], $bDate, $bDate, $bIsActive, $btID, $uID);
			$q = "insert into Blocks (bName, bDateAdded, bDateModified, bIsActive, btID, uID) values (?, ?, ?, ?, ?, ?)";
			
			$r = $db->prepare($q);
			$res = $db->execute($r, $v);

			$bIDnew = $db->Insert_ID();

			// we get the block object for the block we just added

			if ($res) {
				$nb = Block::getByID($bIDnew);
	
				$btHandle = $this->getBlockTypeHandle();
				
				$class = $this->getBlockTypeClass();
				$bc = new $class($nb);
				if (is_object($c)) {
					$bc->setCollectionObject($c);
				}
				$bc->save($data);
				
				// the previous version of the block above is cached without the values				
				$nb->refreshCache();
				
				return Block::getByID($bIDnew);
				
			}
			
		}
		
		function getBlockTypeID() {
			return $this->btID;
		}
		
		function getBlockTypeHandle() {
			return $this->btHandle;
		}
		
		// getBlockAddAction vs. getBlockTypeAddAction() - The difference is very simple. We call getBlockTypeAddAction() to grab the
		// action properties for the form that presents the drop-down select menu for selecting which type of block to add. We call the other
		// function when we've already chosen a type to add, and we're interested in actually adding the block - content completed - to the database
				
		function getBlockAddAction(&$a, $alternateHandler = null) {
			// Note: This is fugly, since we're just grabbing query string variables, but oh well. Not _everything_ can be object oriented
			$btID = $this->btID;
			$step = ($_REQUEST['step']) ? '&step=' . $_REQUEST['step'] : '';			
			$c = $a->getAreaCollectionObject();
			$cID = $c->getCollectionID();
			$arHandle = urlencode($a->getAreaHandle());
			$valt = Loader::helper('validation/token');
			
			
			if ($alternateHandler) {
				$str = $alternateHandler . "?cID={$cID}&arHandle={$arHandle}&btID={$btID}&mode=edit" . $step . '&' . $valt->getParameter();
			} else {
				$str = DIR_REL . "/" . DISPATCHER_FILENAME . "?cID={$cID}&arHandle={$arHandle}&btID={$btID}&mode=edit" . $step . '&' . $valt->getParameter();
			}
			return $str;			
		}
		
		
		function getBlockTypeName() {
			return $this->btName;
		}
		
		function isInstalled() {
			return $this->installed;
		}
		
		function getBlockTypeActiveWhenAdded() {
			return $this->btActiveWhenAdded;
		}
		
		function isCopiedWhenPropagated() {
			return $this->btCopyWhenPropagate;
		}

		function includeAll() {
			return $this->btIncludeAll;
		}
		
		function hasCustomEditTemplate() {
			return $this->hasCustomEditTemplate;
		}
		
		function hasCustomViewTemplate() {
			return $this->hasCustomViewTemplate;
		}
		
		function hasCustomAddTemplate() {
			return $this->hasCustomAddTemplate;
		}

	}
	