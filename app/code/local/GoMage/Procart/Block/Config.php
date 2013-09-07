<?php
 /**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.3
 * @since        Class available since Release 1.0
 */
	
class GoMage_Procart_Block_Config extends Mage_Core_Block_Template{

    protected $_config = null; 
    protected $_qty_template = null;
    protected $_qty_deals_template = null;
    protected $_qty_cart_template = null;
    protected $_qty_product_template = null;
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('gomage/procart/config.phtml');                               
    }
    
    public function _prepareLayout()
    {
        /* @var $helper GoMage_Procart_Helper_Data */
        $helper = Mage::helper('gomage_procart');
        /* @var $helper GoMage_Procart_Helper_Qty */
        $helperQty = Mage::helper('gomage_procart/qty');
        $root = $this->getLayout()->getBlock('root');
        if ($helper->isProCartEnable() && $root){
            foreach($helperQty->getTemplateTypes() as $type){
                $root->addBodyClass($helperQty->getQtyClassNameByType($type));
            }
            if((Mage::getStoreConfig('gomage_procart/qty_settings/'.GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CATEGORY) == GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO) &&
            Mage::getConfig('gomage_procart/qty_settings/category_page_qty')){
                $root->addBodyClass('gpc-arrbut-cat-en');
            }
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
		     $text = nl2br(str_replace("\r", '', $text));
		     $this->_config['gpc_loadinfo_text'] = $text; 
		     $this->_config['gpc_availability_min'] = $this->__('has minimal qty to order');
		     $this->_config['gpc_availability_max'] = $this->__('not available in requested quantity');

		     $this->_config['changeqty_url'] = $this->getUrl('gomageprocart/procart/changeqty');		     
		     $this->_config['changeqtycartitem_url'] = $this->getUrl('gomageprocart/procart/changeqtycartitem');
             $isSecure = Mage::app()->getStore()->isCurrentlySecure();
		     $this->_config['changeattributecart_url'] = $this->getUrl('gomageprocart/procart/changeattributecart',array('_secure' => $isSecure));
		     $this->_config['changeproductqty_url'] = $this->getUrl('gomageprocart/procart/changeproductqty');

		     $this->_config['qty_editor_category_page'] = Mage::getStoreConfig('gomage_procart/qty_settings/category_page_qty');

		     $this->_config['show_window'] = Mage::getStoreConfig('gomage_procart/confirm_window/show_window');
		     $this->_config['auto_hide_window'] = Mage::getStoreConfig('gomage_procart/confirm_window/auto_hide_window');
		     $this->_config['redirect_to'] = Mage::getStoreConfig('gomage_procart/confirm_window/redirect_to');
		     $this->_config['add_effect'] = Mage::getStoreConfig('gomage_procart/general/add_effect');
		     $this->_config['cart_button_color'] = Mage::getStoreConfig('gomage_procart/confirm_window/cart_button_color');
		     $this->_config['background_view'] = Mage::getStoreConfig('gomage_procart/confirm_window/background_view');		     
		     $this->_config['window_width'] = Mage::getStoreConfig('gomage_procart/confirm_window/width');
		     
		     $this->_config['addition_product_list_url'] = $this->getUrl('gomageprocart/procart/getproductlist');
		     $this->_config['name_url_encoded'] = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
             $this->_config['disable_cart'] = Mage::getStoreConfig('gomage_procart/general/disable_cart');
         }
         return Mage::helper('core')->jsonEncode($this->_config);          
    }
    
    public function getQtyTemplate()
    {
         $template = Mage::helper('gomage_procart/qty')
             ->getQtyTemplate(GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CATEGORY);
         return Mage::helper('core')->jsonEncode($template->toHtml());
    }
    
    public function getQtyDealsTemplate()
    {
        $template = Mage::helper('gomage_procart/qty')
            ->getQtyTemplate(GoMage_Procart_Helper_Qty::QTY_TEMPLATE_DEALS);
        return Mage::helper('core')->jsonEncode($template->toHtml());
    }

    public function getQtyCartTemplate()
    {
        $template = Mage::helper('gomage_procart/qty')
            ->getQtyTemplate(GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CART_PAGE);
        return Mage::helper('core')->jsonEncode($template->toHtml());
    }
    
    public function getQtyProductTemplate()
    {
        $template = Mage::helper('gomage_procart/qty')
            ->getQtyTemplate(GoMage_Procart_Helper_Qty::QTY_TEMPLATE_PRODUCT);
        return Mage::helper('core')->jsonEncode($template->toHtml());
    }

    public function getQtyCategoryPopupTemplate()
    {
        $template = Mage::helper('gomage_procart/qty')
            ->getQtyTemplate(GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CATEGORY_POPUP);
        return Mage::helper('core')->jsonEncode($template->toHtml());
    }

    public function getQtyCrosssellTemplate()
    {
        $template = Mage::helper('gomage_procart/qty')
            ->getQtyTemplate(GoMage_Procart_Helper_Qty::QTY_TEMPLATE_CROSSSEL);
        return Mage::helper('core')->jsonEncode($template->toHtml());
    }
}