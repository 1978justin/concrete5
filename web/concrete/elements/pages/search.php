<?
defined('C5_EXECUTE') or die("Access Denied.");
$form = Loader::helper('form');

$searchFields = array(
	'keywords' => t('Full Page Index'),
	'date_added' => t('Date Added'),
	'theme' => t('Theme'),
	'last_modified' => t('Last Modified'),
	'date_public' => t('Public Date'),
	'owner' => t('Page Owner'),
	'num_children' => t('# Children'),
	'version_status' => t('Approved Version')
);

if (PERMISSIONS_MODEL != 'simple') {
	$searchFields['permissions_inheritance'] = t('Permissions Inheritance');
}

$searchFieldAttributes = CollectionAttributeKey::getSearchableList();
foreach($searchFieldAttributes as $ak) {
	$searchFields[$ak->getAttributeKeyID()] = tc('AttributeKeyName', $ak->getAttributeKeyName());
}

?>

<script type="text/template" data-template="search-form">
<form role="form" data-search-form="pages" action="<?=URL::to('/system/search/pages/submit')?>" class="form-inline ccm-search-fields">
	<div class="form-group">
		<select data-bulk-action="pages" disabled class="ccm-search-bulk-action form-control">
			<option value=""><?=t('Items Selected')?></option>
			<option data-bulk-action-type="dialog" data-bulk-action-title="<?=t('Page Properties')?>" data-bulk-action-url="<?=URL::to('/system/dialogs/page/bulk/properties')?>" data-bulk-action-dialog-width="640" data-bulk-action-dialog-height="480"><?=t('Edit Properties')?></option>
			<option value="move_copy"><?=t('Move/Copy')?></option>
			<option value="speed_settings"><?=t('Speed Settings')?></option>
			<? if (PERMISSIONS_MODEL == 'advanced') { ?>
				<option value="permissions"><?=t('Change Permissions')?></option>
				<option value="permissions_add_access"><?=t('Change Permissions - Add Access')?></option>
				<option value="permissions_remove_access"><?=t('Change Permissions - Remove Access')?></option>
			<? } ?>
			<option value="design"><?=t('Design')?></option>
			<option value="delete"><?=t('Delete')?></option>
		</select>	
	</div>
	<div class="form-group">
		<div class="ccm-search-main-lookup-field">
			<i class="glyphicon glyphicon-search"></i>
			<?=$form->search('cvName', $searchRequest['cvName'], array('placeholder' => t('Page Name')))?>
			<button type="submit" class="ccm-search-field-hidden-submit" tabindex="-1"><?=t('Search')?></button>
		</div>
	</div>
	<ul class="ccm-search-form-advanced list-inline">
		<li><a href="#" data-search-toggle="advanced"><?=t('Advanced Search')?></a>
		<li><a href="#" data-search-toggle="customize" data-search-column-customize-url="<?=URL::to('/system/dialogs/search/pages/customize')?>"><?=t('Customize Results')?></a>
	</ul>
	<div class="ccm-search-fields-advanced"></div>
</form>
</script>

<script type="text/template" data-template="search-field-row">
<div class="ccm-search-fields-row">
	<select name="field[]" class="ccm-search-choose-field" data-search-field="pages">
		<option value=""><?=t('Choose Field')?></option>
		<? foreach($searchFields as $key => $value) { ?>
			<option value="<?=$key?>" <% if (typeof(field) != 'undefined' && field.field == '<?=$key?>') { %>selected<% } %> data-search-field-url="<?=URL::to('/system/search/pages/field', $key)?>"><?=$value?></option>
		<? } ?>
	</select>
	<div class="ccm-search-field-content"><% if (typeof(field) != 'undefined') { %><%=field.html%><% } %></div>
	<a data-search-remove="search-field" class="ccm-search-remove-field" href="#"><i class="glyphicon glyphicon-minus-sign"></i></a>
</div>
</script>

<script type="text/template" data-template="search-results-table-body">
<% _.each(items, function(page) {%>
<tr data-launch-menu="<%=page.cID%>">
	<td><span class="ccm-search-results-checkbox"><input type="checkbox" data-search-checkbox="individual" value="<%=page.cID%>" /></span></td>
	<% for(i = 0; i < page.columns.length; i++) {
		var column = page.columns[i];
		if (column.key == 'cvName') { %>
			<td class="ccm-search-results-name"><%=column.value%></td>
		<% } else { %>
			<td><%=column.value%></td>
		<% } %>
	<% } %>
</tr>
<% }); %>
</script>

<script type="text/template" data-template="search-results-menu">
<div class="popover fade" data-menu="<%=item.cID%>">
	<div class="arrow"></div>
	<div class="popover-inner">
	<ul class="dropdown-menu">
		<li><a href="<?=DIR_REL?>/<?=DISPATCHER_FILENAME?>?cID=<%=item.cID%>"><?=t('Visit Page')?></a></li>
	</ul>
</div>
</script>

<? Loader::element('search/template')?>