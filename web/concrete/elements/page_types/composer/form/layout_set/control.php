<?
defined('C5_EXECUTE') or die("Access Denied.");
?>
<div class="ccm-page-type-composer-form-layout-control-set-control" data-page-type-composer-form-layout-control-set-control-id="<?=$control->getPageTypeComposerFormLayoutSetControlID()?>">
<div class="ccm-page-type-composer-form-item-control-bar">
	<ul class="ccm-page-type-composer-item-controls">
		<li><a href="#" data-command="move-set-control" style="cursor: move"><i class="glyphicon glyphicon-move"></i></a></li>
		<li><a data-command="edit-form-set-control" href="<?=REL_DIR_FILES_TOOLS_REQUIRED?>/page_types/composer/form/edit_control?ptComposerFormLayoutSetControlID=<?=$control->getPageTypeComposerFormLayoutSetControlID()?>" class="dialog-launch" dialog-width="400" dialog-height="200" dialog-modal="true" dialog-title="<?=t('Edit Form Control')?>"><i class="glyphicon glyphicon-pencil"></i></a></li>
		<li><a href="#" data-delete-set-control="<?=$control->getPageTypeComposerFormLayoutSetControlID()?>"><i class="glyphicon glyphicon-trash"></i></a></li>
	</ul>

	<div style="display: none">
		<div data-delete-set-control-dialog="<?=$control->getPageTypeComposerFormLayoutSetControlID()?>">
			<?=t("Delete this control? This cannot be undone.")?>
			<?=Loader::helper('validation/token')->output('delete_set_control')?>
		</div>
	</div>

<div class="ccm-page-type-composer-form-layout-control-set-control-inner">
	<?
	print $control->getPageTypeComposerControlLabel();
	?>
</div>
</div>
</div>
