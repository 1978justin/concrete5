<?
defined('C5_EXECUTE') or die("Access Denied.");
?>

<?=Loader::helper('concrete/interface')->tabs(array(
	array('sources', t('Data Sources'), true),
	array('output', t('Output'))
));?>


<div class="ccm-tab-content" id="ccm-tab-content-sources">
<?
$this->inc('form/sources.php');
?>
</div>

<div class="ccm-tab-content" id="ccm-tab-content-output">
<?
$this->inc('form/output.php');
?>
</div>