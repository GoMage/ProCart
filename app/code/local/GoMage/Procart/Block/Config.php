<?php
 /**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.0
 * @since        Class available since Release 1.0
 */
	
class GoMage_Procart_Block_Config extends Mage_Core_Block_Template{

    protected $_config = null; 
    protected $_qty_template = null;
    protected $_qty_cart_template = null;
    protected $_qty_product_template = null;
        
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('gomage/procart/config.phtml');                               
    }
    
    public function _prepareLayout()
    {
        $helper = Mage::helper('gomage_procart');
        $root = $this->getLayout()->getBlock('root');
        if ($helper->isProCartEnable() && $root){
            switch (Mage::getStoreConfig('gomage_procart/qty_settings/qty_view'))
            {
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_LEFT_RIGHT:
                      $root->addBodyClass('gpc-ar-lr'); 
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_TOP_BOTTOM:
                      $root->addBodyClass('gpc-but-tb'); 
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_LEFT_RIGHT:
                      $root->addBodyClass('gpc-but-lr'); 
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_TOP_BOTTOM:
                 default:    
                      $root->addBodyClass('gpc-ar-tb'); 
                         
            }
            $root->addBodyClass('gpc-arbc-' . Mage::getStoreConfig('gomage_procart/qty_settings/arrows_color'));

            if (Mage::getStoreConfig('gomage_procart/qty_settings/category_page'))
                $root->addBodyClass('gpc-arrbut-cat-en');
            if (Mage::getStoreConfig('gomage_procart/qty_settings/product_page'))
                $root->addBodyClass('gpc-arrbut-prodp-en');
            if (Mage::getStoreConfig('gomage_procart/qty_settings/cart_block'))        
                $root->addBodyClass('gpc-arrbut-mcb-en');
            if (Mage::getStoreConfig('gomage_procart/qty_settings/cart_page'))        
                $root->addBodyClass('gpc-arrbut-cp-en');

            $styles_block = $this->getLayout()->createBlock('core/template', 'gomage_procart_styles')->setTemplate('gomage/procart/header/styles.php');
	        $this->getLayout()->getBlock('head')->setChild('gomage_procart_styles', $styles_block);     
        }    
        parent::_prepareLayout();
    }
    
    public function getConfig()
    {
         if (!$this->_config)
         {             
             $this->_config = array();
             $helper = Mage::helper('gomage_procart');
             $this->_config['enable'] = ($helper->isProCartEnable() ? 1 : 0);
             
             if($loadimage = Mage::getStoreConfig('gomage_procart/ajaxloader/loadimage')) 
	            $this->_config['loadimage'] = Mage::getBaseUrl('media') . 'gomage/config/' . $loadimage;	
	         else
	            $this->_config['loadimage'] = $this->getSkinUrl('images/gomage/gpc_loadinfo.gif');

	         $this->_config['loadimagealign'] = Mage::getStoreConfig('gomage_procart/ajaxloader/imagealign');

	         $text = trim(Mage::getStoreConfig('gomage_procart/ajaxloader/text')) ? trim(Mage::getStoreConfig('gomage_procart/ajaxloader/text')) : $this->__('Loading, please wait...');
		     $text = addslashes(str_replace("\n", "<br/>", str_replace("\r", '', $text)));		     
		     $this->_config['gpc_loadinfo_text'] = $text; 
		     
		     $this->_config['changeqty_url'] = $this->getUrl('gomageprocart/procart/changeqty');		     
		     $this->_config['changeqtycartitem_url'] = $this->getUrl('gomageprocart/procart/changeqtycartitem');
		     $this->_config['changeattributecart_url'] = $this->getUrl('gomageprocart/procart/changeattributecart');
		     $this->_config['changeproductqty_url'] = $this->getUrl('gomageprocart/procart/changeproductqty');
		     
		     $this->_config['change_qty_cart_page'] = Mage::getStoreConfig('gomage_procart/qty_settings/cart_page');		     		     		     
		     $this->_config['change_qty_category_page'] = Mage::getStoreConfig('gomage_procart/qty_settings/category_page');
		     $this->_config['change_qty_product_page'] = (Mage::registry('current_product') && Mage::getStoreConfig('gomage_procart/qty_settings/product_page') ? 1 : 0);
		     		     		     
		     $this->_config['show_window'] = Mage::getStoreConfig('gomage_procart/confirm_window/show_window');
		     $this->_config['auto_hide_window'] = Mage::getStoreConfig('gomage_procart/confirm_window/auto_hide_window');
		     $this->_config['redirect_to'] = Mage::getStoreConfig('gomage_procart/confirm_window/redirect_to');
		     $this->_config['add_effect'] = Mage::getStoreConfig('gomage_procart/general/add_effect');
		     $this->_config['cart_button_color'] = Mage::getStoreConfig('gomage_procart/confirm_window/cart_button_color');
		     $this->_config['background_view'] = Mage::getStoreConfig('gomage_procart/confirm_window/background_view');
         }
         return Mage::helper('core')->jsonEncode($this->_config);          
    }
    
    public function getQtyTemplate()
    {
         if (!$this->_qty_template)
         {
             $template = $this->getLayout()->createBlock('core/template', 'gomage.procart.qty.template');
                                       
             switch (Mage::getStoreConfig('gomage_procart/qty_settings/qty_view'))
             {
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_LEFT_RIGHT:
                     $template->setTemplate('gomage/procart/config/arrows/left_right.phtml');
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_TOP_BOTTOM:
                     $template->setTemplate('gomage/procart/config/buttons/top_bottom.phtml');
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_LEFT_RIGHT:
                     $template->setTemplate('gomage/procart/config/buttons/left_right.phtml');
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_TOP_BOTTOM:
                 default:    
                     $template->setTemplate('gomage/procart/config/arrows/top_bottom.phtml');
                         
             } 
             
             $this->_qty_template = $template->toHtml();
         }
         return Mage::helper('core')->jsonEncode($this->_qty_template);                  
    }

    public function getQtyCartTemplate()
    {
         if (!$this->_qty_cart_template)
         {
             $template = $this->getLayout()->createBlock('core/template', 'gomage.procart.qty.cart.template');
             
             switch (Mage::getStoreConfig('gomage_procart/qty_settings/qty_view'))
             {
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_LEFT_RIGHT:
                     $template->setTemplate('gomage/procart/cart/arrows/left_right.phtml');
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_TOP_BOTTOM:
                     $template->setTemplate('gomage/procart/cart/buttons/top_bottom.phtml');
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_LEFT_RIGHT:
                     $template->setTemplate('gomage/procart/cart/buttons/left_right.phtml');
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_TOP_BOTTOM:
                 default:    
                     $template->setTemplate('gomage/procart/cart/arrows/top_bottom.phtml');
                         
             } 
             
             $this->_qty_cart_template = $template->toHtml();
         }
         return Mage::helper('core')->jsonEncode($this->_qty_cart_template);                  
    }
    
    public function getQtyProductTemplate()
    {
         if (!$this->_qty_product_template)
         {
             $template = $this->getLayout()->createBlock('core/template', 'gomage.procart.qty.product.template');
             
             switch (Mage::getStoreConfig('gomage_procart/qty_settings/qty_view'))
             {
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_LEFT_RIGHT:
                     $template->setTemplate('gomage/procart/product/arrows/left_right.phtml');
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_TOP_BOTTOM:
                     $template->setTemplate('gomage/procart/product/buttons/top_bottom.phtml');
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_LEFT_RIGHT:
                     $template->setTemplate('gomage/procart/product/buttons/left_right.phtml');
                 break;
                 case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_TOP_BOTTOM:
                 default:    
                     $template->setTemplate('gomage/procart/product/arrows/top_bottom.phtml');
                         
             } 
             
             $this->_qty_product_template = $template->toHtml();
         }
         return Mage::helper('core')->jsonEncode($this->_qty_product_template);                  
    }
}