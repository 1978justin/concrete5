<? if (isset($key)) { ?>

<h1><span><?=t('Edit Page Attribute')?></span></h1>
<div class="ccm-dashboard-inner">

<h2><?=t('Attribute Type')?></h2>

<strong><?=$type->getAttributeTypeName()?></strong>
<br/><br/>


<form method="post" action="<?=$this->action('edit')?>" id="ccm-attribute-key-form">

<? Loader::element("attribute/type_form_required", array('category' => $category, 'type' => $type, 'key' => $key)); ?>

</form>	

</div>

<h1><span><?=t('Delete Attribute')?></span></h1>

<div class="ccm-dashboard-inner">
	<div class="ccm-spacer"></div>
	<?
	$valt = Loader::helper('validation/token');
	$ih = Loader::helper('concrete/interface');
	$delConfirmJS = t('Are you sure you want to remove this attribute?');
	?>
	<script type="text/javascript">
	deleteAttribute = function() {
		if (confirm('<?=$delConfirmJS?>')) { 
			location.href = "<?=$this->url('/dashboard/pages/types/attributes', 'delete', $key->getAttributeKeyID(), $valt->generate('delete_attribute'))?>";				
		}
	}
	</script>
	<? print $ih->button_js(t('Delete Attribute'), "deleteAttribute()", 'left');?>

	<div class="ccm-spacer"></div>
</div>

<? } else { ?>

<h1><span><?=t('Add Page Attribute')?></span></h1>
<div class="ccm-dashboard-inner">

<h2><?=t('Choose Attribute Type')?></h2>

<form method="get" action="<?=$this->action('select_type')?>" id="ccm-attribute-type-form">

<?=$form->select('atID', $types)?>
<?=$form->submit('submit', t('Go'))?>

</form>

<? if (isset($type)) { ?>
	<br/>

	<form method="post" action="<?=$this->action('add')?>" id="ccm-attribute-key-form">

	<? Loader::element("attribute/type_form_required", array('category' => $category, 'type' => $type)); ?>

	</form>	
<? } ?>

</div>

<? } ?>