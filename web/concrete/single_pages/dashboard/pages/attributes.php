<? defined('C5_EXECUTE') or die("Access Denied."); ?>

<? if (isset($key)) { ?>

<?=Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Edit Attribute'), false, false, false)?>
<form method="post" action="<?=$view->action('edit')?>" id="ccm-attribute-key-form">

<? Loader::element("attribute/type_form_required", array('category' => $category, 'type' => $type, 'key' => $key)); ?>

</form>

<?=Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false);?>




<? } else if ($this->controller->getTask() == 'select_type' || $this->controller->getTask() == 'add' || $this->controller->getTask() == 'edit') { ?>

	<?=Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Page Attributes'), false, false, false)?>

	<? if (isset($type)) { ?>
		<form method="post" action="<?=$view->action('add')?>" id="ccm-attribute-key-form">
		<? Loader::element("attribute/type_form_required", array('category' => $category, 'type' => $type)); ?>
		</form>	
	<? } ?>
	
	<?=Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false);?>



<? } else { ?>

	<?=Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Page Attributes'), false, false, false)?>

	<?
	$attribs = CollectionAttributeKey::getList();
	Loader::element('dashboard/attributes_table', array('category' => $category, 'attribs'=> $attribs, 'editURL' => '/dashboard/pages/attributes')); ?>

	<div class="ccm-pane-body ccm-pane-body-footer">
	
	<form method="get" class="form-inline" action="<?=$view->action('select_type')?>" id="ccm-attribute-type-form">
	<div class="control-group">
	<?=$form->label('atID', t('Add Attribute'))?>
	<div class="controls">
	
	<?=$form->select('atID', $types)?>
	<?=$form->submit('submit', t('Add'))?>
	
	</div>
	</div>
	
	</form>

	</div>
	
	<?=Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false);?>

<? } ?>