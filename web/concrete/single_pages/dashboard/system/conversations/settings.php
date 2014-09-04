<?php  defined('C5_EXECUTE') or die("Access Denied.");
$form = Loader::helper('form');
$file = Loader::helper('file');
echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Conversations Settings'), false, 'span8 offset2', false);
?>
<form action="<?=$view->action('save')?>" method='post'>
		<fieldset>
			<legend><?php echo t('Attachment Settings'); ?></legend>
			<p style="margin-bottom: 25px; color: #aaa; display: block;" class="small"><?php echo t('Note: These settings can be overridden in the block edit form for individual conversations.') ?></p>
			<div class="form-group">
				<label class="control-label"><?=t('Max Attachment Size for Guest Users. (MB)')?></label>
				<?=$form->text('maxFileSizeGuest', $maxFileSizeGuest > 0 ? $maxFileSizeGuest : '')?>
			</div>
            <div class="form-group">
				<label class="control-label"><?=t('Max Attachment Size for Registered Users. (MB)')?></label>
				<?=$form->text('maxFileSizeRegistered', $maxFileSizeRegistered > 0 ? $maxFileSizeRegistered : '')?>
			</div>
            <div class="form-group">
				<label class="control-label"><?=t('Max Attachments Per Message for Guest Users.')?></label>
				<?=$form->text('maxFilesGuest', $maxFilesGuest > 0 ? $maxFilesGuest : '')?>
			</div>
            <div class="form-group">
				<label class="control-label"><?=t('Max Attachments Per Message for Registered Users')?></label>
				<?=$form->text('maxFilesRegistered', $maxFilesRegistered > 0 ?  $maxFilesRegistered : '')?>
			</div>
            <div class="form-group">
				<label class="control-label"><?=t('Allowed File Extensions (Comma separated, no periods).')?></label>
    			<?=$form->textarea('fileExtensions', $fileExtensions)?>
			</div>
		</fieldset>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
		    <button class='btn btn-primary pull-right'><?php echo t('Save'); ?></button>
	    </div>
    </div>
</form>