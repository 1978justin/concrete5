<? defined('C5_EXECUTE') or die("Access Denied."); ?>

<div style="position: relative">

<div style="z-index: 4; opacity: 0.3; position: absolute; top: 0px; left: 0px; margin-left: -5px; margin-top: -15px; width: 1040px; height: 500px; background-image: url(<?=$image->getPath()?>); background-repeat: no-repeat;"></div>

<div style="position: relative; color: #000; z-index: 5">
<h2><?=$title?></h2>
<h5><?=date(DATE_APP_GENERIC_MDYT_FULL, strtotime($date_time))?></h5>
<p><?=$description?></p>
<a href="<?=$link?>" class="btn"><?=t("Read More")?></a>
</div>
</div>