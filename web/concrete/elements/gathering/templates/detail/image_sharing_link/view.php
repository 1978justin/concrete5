<? defined('C5_EXECUTE') or die("Access Denied."); 
$nh = Loader::helper('navigation'); 
if (is_array($image)) {
$image = $image[0];
}?>

<div class="ccm-gathering-overlay image-sharing-link" id="image-sharing-link-<?php echo $this->gaiID; ?>">
	<div class="image-sharing-link-controls">
		<img class="overlay-header-image" src="<?=$image->getPath()?>" style="max-width: 600px" />
		<div class="ccm-gathering-overlay-title ccm-gathering-thumbnail-caption">
			<a href="<?php echo $nh->getCollectionURL($this->page); ?>"><?php echo t('Full Article') ?></a>
			<a href="#" class="gathering-full-screen"><i class="icon-fullscreen icon-white"></i></a>
			<a href="#" class="gathering-detail-share"><i class="icon-share-alt icon-white"></i></a>
		</div>
	</div>
	<? if ($description) { ?>
	<div class="description">
		<p><?=$description?></p>
	</div>
	<? } ?>
</div>
