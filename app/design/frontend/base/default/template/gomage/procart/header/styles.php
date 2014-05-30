<?php
 /**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2014 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.1
 * @since        Class available since Release 1.0
 */
 ?>
<style type="text/css">

    /* Continue Button Design */
    
    <?php
			$_color1 = Mage::getStoreConfig('gomage_procart/continue_btn_design/shopping_button_color'); //Continue Shopping Button Color
			$_color2 = Mage::getStoreConfig('gomage_procart/continue_btn_design/shopping_button_color2'); //Continue Button Color 2
			$_mouse_over1 = Mage::getStoreConfig('gomage_procart/continue_btn_design/mouse_over_bg_color'); //Mouse Over Backgroung Color
			$_mouse_over2 = Mage::getStoreConfig('gomage_procart/continue_btn_design/mouse_over_bg_color2'); //Mouse Over Backgroung Color 2
			$_button_radius = Mage::getStoreConfig('gomage_procart/continue_btn_design/button_radius'); //Button Radius
    ?>    
    
    
    <?php if (Mage::getStoreConfig('gomage_procart/continue_btn_design/gradient') && $_color1 && $_color2): ?>
    button.gpc_msg_bnt_cs span span{
      background-color: <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>;
      background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>), to(<?php echo Mage::helper('gomage_procart')->formatColor($_color2);?>));
      background-image: -webkit-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_color2);?>);
      background-image:    -moz-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_color2);?>);
      background-image:     -ms-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_color2);?>);
      background-image:      -o-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_color2);?>);
      background-image:         linear-gradient(to bottom, <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_color2);?>);
      border-radius: <?php echo $_button_radius;?>px;
      }
    button.gpc_msg_bnt_cs:hover span span{
      background-color: <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>;
      background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>), to(<?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over2);?>));
      background-image: -webkit-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over2);?>);
      background-image:    -moz-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over2);?>);
      background-image:     -ms-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over2);?>);
      background-image:      -o-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over2);?>);
      background-image:         linear-gradient(to bottom, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over2);?>);
      }    
    <?php else:?>
    button.gpc_msg_bnt_cs span span{
      background-color: <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>;
      border-radius: <?php echo $_button_radius;?>px;
      }
    button.gpc_msg_bnt_cs:hover span span{
      background-color: <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>;
      }
    <?php endif;?>    
    /* END Continue Button Design */


    /* Cart/Checkout Button Design */
   
    <?php
			$_color1 = Mage::getStoreConfig('gomage_procart/cart_button_design/cart_btn_color'); //Cart/Checkout Button Color
			$_color2 = Mage::getStoreConfig('gomage_procart/cart_button_design/cart_btn_color2'); //Cart/Checkout Button Color 2
			$_mouse_over1 = Mage::getStoreConfig('gomage_procart/cart_button_design/mouse_over_bg_color'); //Mouse Over Backgroung Color
			$_mouse_over2 = Mage::getStoreConfig('gomage_procart/cart_button_design/mouse_over_bg_color2'); //Mouse Over Backgroung Color 2
			$_button_radius = Mage::getStoreConfig('gomage_procart/cart_button_design/button_radius'); //Button Radius

	 ?>
    <?php if (Mage::getStoreConfig('gomage_procart/cart_button_design/gradient') && $_color1 && $_color2): ?>
    button.gpc_msg_bnt_ptc span span,
    #gcp_configurable_add_to_cart button.btn-cart span span{
      background-color: <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>;
      background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>), to(<?php echo Mage::helper('gomage_procart')->formatColor($_color2);?>));
      background-image: -webkit-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_color2);?>);
      background-image:    -moz-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_color2);?>);
      background-image:     -ms-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_color2);?>);
      background-image:      -o-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_color2);?>);
      background-image:         linear-gradient(to bottom, <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_color2);?>);
      border-radius: <?php echo $_button_radius;?>px;
      }
    button.gpc_msg_bnt_ptc:hover span span,
    #gcp_configurable_add_to_cart button.btn-cart:hover span span{
      background-color: <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>;
      background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>), to(<?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over2);?>));
      background-image: -webkit-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over2);?>);
      background-image:    -moz-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over2);?>);
      background-image:     -ms-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over2);?>);
      background-image:      -o-linear-gradient(top, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over2);?>);
      background-image:         linear-gradient(to bottom, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>, <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over2);?>);
      }  
    <?php else:?>
    button.gpc_msg_bnt_ptc span span,
    #gcp_configurable_add_to_cart button.btn-cart span span{
      background-color: <?php echo Mage::helper('gomage_procart')->formatColor($_color1);?>;
      border-radius: <?php echo $_button_radius;?>px;
      }
    button.gpc_msg_bnt_ptc:hover span span,
    #gcp_configurable_add_to_cart button.btn-cart:hover span span{
      background-color: <?php echo Mage::helper('gomage_procart')->formatColor($_mouse_over1);?>;
      }
    <?php endif;?>
    /* END Cart/Checkout Button Design */


    #gcp_configurable_add_to_cart #gcp_configurable_add_to_cart_content{
      max-height: <?php echo Mage::getStoreConfig('gomage_procart/confirm_wind/max_win_height'); ?>px !important;
    }



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

        <?php if($_color = Mage::getStoreConfig('gomage_procart/ajaxloader/textcolor')):?>
        color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
        <?php endif;?>
	}
	#gpc_confirmation_window, #gcp_configurable_add_to_cart{
		<?php if($_color = Mage::getStoreConfig('gomage_procart/confirm_wind/bordercolor')):?>
		border-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
		<?php endif;?>

		<?php if($_color = Mage::getStoreConfig('gomage_procart/confirm_wind/bgcolor')):?>
		background-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
		<?php endif;?>

		<?php if($_width = intval(Mage::getStoreConfig('gomage_procart/confirm_wind/width'))):?>
		width:<?php echo $_width;?>px !important;
		<?php endif;?>

		<?php $_width = intval(Mage::getStoreConfig('gomage_procart/confirm_wind/border_size')); ?>
		border-width:<?php echo $_width;?>px !important;

	}

	<?php if ($_width>0){
	$_width_top=16+$_width;
	$_width_right=14+$_width;
	echo '#gcp_configurable_add_to_cart_close{top:-'.$_width_top.'px;right:-'.$_width_right.'px;}';
	} ?>

	<?php if($_width = intval(Mage::getStoreConfig('gomage_procart/confirm_wind/width'))):?>
	#gpc_confirmation_window{
		margin-left:-<?php $margin=$_width+20;$_width = intval(Mage::getStoreConfig('gomage_procart/confirm_wind/border_size'));$margin+=$_width*2;echo $margin/2;?>px;
	}
	<?php endif;?>

    .procart-qbv.procart-qbht,
    .procart-qbh.procart-qbhr,
    .procart-qb-right-plus{
        /* Plus Button Background Color */
        <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_qty_button_design/arrows_color_bg_plus')):?>
            background-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
        <?php endif;?>
        /* Plus Button Text Color */
        <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_qty_button_design/arrows_color_text_plus')):?>
            color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
        <?php endif;?>
    }

    .procart-qbv.procart-qbhb,
    .procart-qbh.procart-qbhl,
    .procart-qb-right-minus{
        /* Minus Button Background Color */
        <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_detract_qty_button_design/arrows_color_bg_minus')):?>
          background-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
        <?php endif;?>
        /* Minus Button Text Color */
        <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_detract_qty_button_design/arrows_color_text_minus')):?>
          color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
        <?php endif;?>
    }
    .procart-tarr{
        <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_qty_button_design/arrows_color_bg_plus')):?>
            border-bottom-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
        <?php endif;?>      
      }
    .procart-barr{
        <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_detract_qty_button_design/arrows_color_bg_minus')):?>
          border-top-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
        <?php endif;?>        
      }
    .procart-larr{
        <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_detract_qty_button_design/arrows_color_bg_minus')):?>
          border-right-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
        <?php endif;?>      
      } 
    .procart-rarr{
        <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_qty_button_design/arrows_color_bg_plus')):?>
            border-left-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
        <?php endif;?>      
      }  

    /* Add Quantity Button Design - Mouse Over Background Color / Mouse Over Text Color */
    .procart-tarr:hover{
    <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_qty_button_design/mouse_over_bg_color')):?>
        border-bottom-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
    <?php endif;?>
      }
    .procart-qbv.procart-qbht:hover, 
    .procart-qbh.procart-qbhr:hover,
    .procart-qb-right-plus:hover{
      <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_qty_button_design/mouse_over_bg_color')):?>
        background:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
      <?php endif;?> 
      <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_qty_button_design/mouse_over_txt_color')):?>
        color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
      <?php endif;?>      
      }

    /* Detract Quantity Button Design - Mouse Over Background Color / Mouse Over Text Color */
    .procart-barr:hover{
      <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_detract_qty_button_design/mouse_over_bg_color')):?>
        border-top-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
      <?php endif;?>    
      }
    .procart-qbv.procart-qbhb:hover,
    .procart-qbh.procart-qbhl:hover,
    .procart-qb-right-minus:hover{
      <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_detract_qty_button_design/mouse_over_bg_color')):?>
        background-color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
      <?php endif;?>
      <?php if($_color = Mage::getStoreConfig('gomage_procart/skin_detract_qty_button_design/mouse_over_txt_color')):?>
        color:<?php echo Mage::helper('gomage_procart')->formatColor($_color);?> !important;
      <?php endif;?>      
      }
      
</style>