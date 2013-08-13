<? defined('C5_EXECUTE') or die("Access Denied."); ?>
<!DOCTYPE html>
<html>
<head>
<? 
$this->addHeaderItem(Loader::helper("html")->css('ccm.default.theme.css'));
$req = Request::get();
$req->requireAsset('css', 'bootstrap');
$req->requireAsset('javascript', 'jquery');
$req->requireAsset('javascript', 'bootstrap/alert');
$req->requireAsset('javascript', 'bootstrap/transition');

$showLogo = true;
if (is_object($c)) {
	if (is_object($cp)) {
		if ($cp->canViewToolbar()) {
			$showLogo = false;
		}
	}
		
 	Loader::element('header_required');
} else { 
	$this->outputHeaderItems();
}

?>
</head>
<body>
<div class="ccm-ui">

<? if ($showLogo) { ?>
<div id="ccm-toolbar">
	<ul>
		<li class="ccm-logo pull-left"><span><?=Loader::helper('concrete/interface')->getToolbarLogoSRC()?></span></li>
	</ul>
</div>
<? } ?>

<div class="container">
<div class="row">
<div class="col-sm-10 col-sm-offset-1">
<?php Loader::element('system_errors', array('format' => 'block', 'error' => $error, 'success' => $success, 'message' => $message)); ?>
</div>
</div>
<?php print $innerContent ?>

</div>
</div>

<? 
if (is_object($c)) {
	Loader::element('footer_required');
}
?>

</body>
</html>
