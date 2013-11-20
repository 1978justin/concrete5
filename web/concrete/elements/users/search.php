<? defined('C5_EXECUTE') or die("Access Denied."); ?> 
<?
$form = Loader::helper('form');

$searchFields = array(
	'date_added' => t('Registered Between'),
	'is_active' => t('Activated Users')
);

if (PERMISSIONS_MODEL == 'advanced') { 
	$searchFields['group_set'] = t('Group Set');
}

Loader::model('attribute/categories/collection');
$searchFieldAttributes = UserAttributeKey::getSearchableList();
foreach($searchFieldAttributes as $ak) {
	$searchFields[$ak->getAttributeKeyID()] = tc('AttributeKeyName', $ak->getAttributeKeyName());
}

$ek = PermissionKey::getByHandle('edit_user_properties');
$ik = PermissionKey::getByHandle('activate_user');
$dk = PermissionKey::getByHandle('delete_user');

$searchRequest = $controller->getSearchRequest();

?>

<script type="text/template" data-template="search-form">
<form role="form" data-search-form="users" action="<?=URL::to('/system/search/users/submit')?>" class="form-inline ccm-search-fields">
	<div class="ccm-search-fields-row">
	<div class="form-group">
		<select data-bulk-action="users" disabled class="ccm-search-bulk-action form-control">
			<option value=""><?=t('Items Selected')?></option>
			<? if ($ek->validate()) { ?>
				<option value="properties"><?=t('Edit Properties')?></option>
			<? } ?>
			<? if ($ik->validate()) { ?>
				<option value="activate"><?=t('Activate')?></option>
				<option value="deactivate"><?=t('Deactivate')?></option>
			<? } ?>
			<option value="group_add"><?=t('Add to Group')?></option>
			<option value="group_remove"><?=t('Remove from Group')?></option>
			<? if ($dk->validate()) { ?>
			<option value="delete"><?=t('Delete')?></option>
			<? } ?>
			<? if ($mode == 'choose_multiple') { ?>
				<option value="choose"><?=t('Choose')?></option>
			<? } ?>
		</select>	
	</div>
	<div class="form-group">
		<div class="ccm-search-main-lookup-field">
			<i class="glyphicon glyphicon-search"></i>
			<?=$form->search('keywords', $searchRequest['keywords'], array('placeholder' => t('Username or Email')))?>
			<button type="submit" class="ccm-search-field-hidden-submit" tabindex="-1"><?=t('Search')?></button>
		</div>
	</div>
	<ul class="ccm-search-form-advanced list-inline">
		<li><a href="#" data-search-toggle="advanced"><?=t('Advanced Search')?></a>
		<li><a href="#" data-search-toggle="customize" data-search-column-customize-url="<?=URL::to('/system/dialogs/user/search/customize')?>"><?=t('Customize Results')?></a>
	</ul>
	</div>
	<div class="ccm-search-fields-row">
		<div class="form-group">
			<?=$form->label('gID', t('In Group'))?>
			<? 
			Loader::model('search/group');
			$gl = new GroupSearch();
			$gl->setItemsPerPage(-1);
			$g1 = $gl->getPage();
			?>		
			<div class="ccm-search-field-content">			
			<select multiple name="gID[]" class="chosen-select form-control" style="width: 200px">
				<? foreach($g1 as $gRow) {
					$g = Group::getByID($gRow['gID']);
					$gp = new Permissions($g);
					if ($gp->canSearchUsersInGroup($g)) {
						?>
					<option value="<?=$gRow['gID']?>"  <? if (is_array($_REQUEST['gID']) && in_array($gRow['gID'], $_REQUEST['gID'])) { ?> selected="selected" <? } ?>><?=$g->getGroupDisplayName()?></option>
				<? 
					}
				} ?>
			</select>
			</div>
		</div>
	</div>
	<div class="ccm-search-fields-advanced"></div>
</form>
</script>

<script type="text/template" data-template="search-field-row">
<div class="ccm-search-fields-row">
	<select name="field[]" class="ccm-search-choose-field" data-search-field="users">
		<option value=""><?=t('Choose Field')?></option>
		<? foreach($searchFields as $key => $value) { ?>
			<option value="<?=$key?>" <% if (typeof(field) != 'undefined' && field.field == '<?=$key?>') { %>selected<% } %> data-search-field-url="<?=URL::to('/system/search/users/field', $key)?>"><?=$value?></option>
		<? } ?>
	</select>
	<div class="ccm-search-field-content"><% if (typeof(field) != 'undefined') { %><%=field.html%><% } %></div>
	<a data-search-remove="search-field" class="ccm-search-remove-field" href="#"><i class="glyphicon glyphicon-minus-sign"></i></a>
</div>
</script>

<script type="text/template" data-template="search-results-table-body">
<% _.each(items, function(user) {%>
<tr>
	<td><span class="ccm-search-results-checkbox"><input type="checkbox" data-search-checkbox="individual" value="<%=user.uID%>" /></span></td>
	<% for(i = 0; i < user.columns.length; i++) {
		var column = user.columns[i]; 
		%>
		<td><%=column.value%></td>
	<% } %>
</tr>
<% }); %>
</script>

<? Loader::element('search/template')?>



