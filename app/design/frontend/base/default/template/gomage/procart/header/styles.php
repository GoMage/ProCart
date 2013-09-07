<?php
 /**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.2
 * @since        Class available since Release 1.0
 */
 ?>
<style type="text/css">	
	.gpc-loadinfo{		
		<?php if($_color = Mage::getStoreConfig('gomage_procart/ajaxloader/bordercolor')):?>
		border-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
		<?php endif;?>
		
		<?php if($_color = Mage::getStoreConfig('gomage_procart/ajaxloader/bgcolor')):?>
		background-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
		<?php endif;?>
		
		<?php if($_width = intval(Mage::getStoreConfig('gomage_procart/ajaxloader/width'))):?>
		width:<?php echo $_width;?>px !important;
		margin-left: -<?php echo intval($_width/2 + 9);?>px;
		<?php endif;?>
		
		<?php if($_height = intval(Mage::getStoreConfig('gomage_procart/ajaxloader/height'))):?>
		height:<?php echo $_height;?>px !important;
		margin-top: -<?php echo intval($_height/2 + 9);?>px;
		<?php endif;?>		
	}
	#gpc_confirmation_window, #gcp_configurable_add_to_cart{
		<?php if($_color = Mage::getStoreConfig('gomage_procart/confirm_window/bordercolor')):?>
		border-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
		<?php endif;?>
		
		<?php if($_color = Mage::getStoreConfig('gomage_procart/confirm_window/bgcolor')):?>
		background-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
		<?php endif;?>
		
		<?php if($_width = intval(Mage::getStoreConfig('gomage_procart/confirm_window/width'))):?>
		width:<?php echo $_width;?>px !important;
		<?php endif;?>
	
		<?php $_width = intval(Mage::getStoreConfig('gomage_procart/confirm_window/border_size')); ?>
		border-width:<?php echo $_width;?>px !important;
						
	}	


	<?php if ($_width>0){
	$_width_top=16+$_width;
	$_width_right=14+$_width;
	echo '#gcp_configurable_add_to_cart_close{top:-'.$_width_top.'px;right:-'.$_width_right.'px;}';
	} ?>

	<?php if($_width = intval(Mage::getStoreConfig('gomage_procart/confirm_window/width'))):?>
	#gpc_confirmation_window{
		margin-left:-<?php $margin=$_width+20;$_width = intval(Mage::getStoreConfig('gomage_procart/confirm_window/border_size'));$margin+=$_width*2;echo $margin/2;?>px;
	}	
	<?php endif;?>	
</style>