<? defined('C5_EXECUTE') or die("Access Denied."); ?>

</div>

	<? if (is_array($extraParams)) { // defined within the area/content classes 
		foreach($extraParams as $key => $value) { ?>
			<input type="hidden" name="<?=$key?>" value="<?=$value?>">
		<? } ?>
	<? } ?>
	
	<? if (!$disableSubmit) { ?>
		<input type="hidden" name="_add" value="1">
	<? } ?>

<?
if ($bt->supportsInlineEditing()) { ?>

<script type="text/javascript">
$(document).on('inlineEditCancel', function() {
	$('#a<?=$a->getAreaID()?>-bt<?=$bt->getBlockTypeID()?>').remove();
});
</script>

<? } ?>

<? if (!$bt->supportsInlineEditing()) { ?>	

	<div class="ccm-buttons dialog-buttons">
	<a href="javascript:void(0)" <? if ($replaceOnUnload) { ?> onclick="location.href='<?=DIR_REL?>/<?=DISPATCHER_FILENAME?>?cID=<?=$c->getCollectionID()?>'; return true" class="btn ccm-button-left cancel" <? } else { ?> onclick="$(document).trigger('blockWindowClose');jQuery.fn.dialog.closeTop()" class="btn ccm-button-left cancel"<? } ?>><?=t('Cancel')?></a>
	<a href="javascript:void(0)" onclick="$('#ccm-form-submit-button').get(0).click()" class="pull-right btn btn-primary"><?=t('Add')?> <i class="icon-plus-sign icon-white"></i></a>
	</div>

<? } ?>

	<!-- we do it this way so we still trip javascript validation. stupid javascript. //-->
	
	<input type="submit" name="ccm-add-block-submit" value="submit" style="display: none" id="ccm-form-submit-button" />

	<input type="hidden" name="processBlock" value="1">
</form>

</div>
