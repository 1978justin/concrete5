<?
defined('C5_EXECUTE') or die("Access Denied.");
?>

<div class="ccm-panel-content-inner">

<form method="post" id="ccm-check-in" action="<?=$controller->action('submit')?>">

<h5><?=t('Version Comments')?></h5>

<div class="ccm-panel-check-in-comments"><textarea name="comments" id="ccm-check-in-comments" /></textarea></div>

<? if ($cp->canApprovePageVersions()) {
	$publishTitle = t('Publish Changes');
	$pk = PermissionKey::getByHandle('approve_page_versions');
	$pk->setPermissionObject($c);
	$pa = $pk->getPermissionAccessObject();
	if (is_object($pa) && count($pa->getWorkflows()) > 0) {
		$publishTitle = t('Submit to Workflow');
	}
?>
<div class="ccm-panel-check-in-publish">

	<div class="btn-group">
		<button id="ccm-check-in-publish" type="submit" name="action" value="publish" class="btn btn-primary"><?=$publishTitle?></button>
		<button id="ccm-check-in-publish-time" type="button" class="btn btn-primary"><i class="glyphicon glyphicon-time"></i></button>
	</div>

</div>

<? } ?>

<div class="ccm-panel-check-in-preview">
	<button id="ccm-check-in-preview" type="submit" name="action" value="save" class="btn-success btn"><?=t('Save Changes')?></button>
	<button id="ccm-check-in-discard" type="submit" name="action" value="discard" class="btn-danger btn"><?=t('Discard Changes')?></button>
	<input type="hidden" name="approve" value="PREVIEW" id="ccm-approve-field" />
</div>

</form>

<script type="text/javascript">
$(function() {
    setTimeout("$('#ccm-check-in-comments').focus();",300);
    $('#ccm-check-in').concreteAjaxForm();
});
</script>

</div>