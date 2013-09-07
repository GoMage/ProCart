<?php
 /**
 * GoMage Procart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.1
 * @since        Class available since Release 1.0
 */
	
class GoMage_Procart_Model_Observer{
		    
    static public function checkK($event)
    {			
		$key = Mage::getStoreConfig('gomage_activation/procart/key');			
		Mage::helper('gomage_procart')->a($key);			
	}  

    public function addToCart($event)
    {        
        $request = $event->getRequest();
        if ($request->getParam('gpc_add') == 1)
		{
		    $result = array();
	        $result['success'] = true;
	        
	        $result['cart'] = $this->getCartSidebar();                                                                                    
            $result['top_links'] = $this->getTopLinks();
            
            $result['prod_name'] = $event->getProduct()->getName();                        	        
            $result['qty'] = $event->getRequest()->getParam('qty');
            $result['product_id'] = $event->getProduct()->getId();
            
            $result['base_cart'] = $this->getBaseCartItems();
                        
            $layout = Mage::getSingleton('core/layout');            
            $result['total'] = $layout->createBlock('checkout/cart_totals', 'checkout.cart.totals')
                                     ->setTemplate('checkout/cart/totals.phtml')  
                                     ->renderView();
                                     
            $result['shipping'] = $layout->createBlock('checkout/cart_shipping', 'checkout.cart.shipping')
                                     ->setTemplate('checkout/cart/shipping.phtml')  
                                     ->renderView();
                        
            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);  
            Mage::app()->getFrontController()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));           
		}
	}
	
    public function getBaseCartItems()
	{
	    $item_html = '';
	    $layout = Mage::getSingleton('core/layout');
        $cart = $layout->createBlock('checkout/cart', 'checkout.cart')    	                                    	                                
                                ->addItemRender('simple', 'checkout/cart_item_renderer', 'checkout/cart/item/default.phtml')
                                ->addItemRender('configurable', 'checkout/cart_item_renderer_configurable', 'checkout/cart/item/default.phtml')
                                ->addItemRender('grouped', 'checkout/cart_item_renderer_grouped', 'checkout/cart/item/default.phtml')
                                ->addItemRender('bundle', 'bundle/checkout_cart_item_renderer', 'checkout/cart/item/default.phtml');                                        
        foreach ($cart->getItems() as $_item)
        {                                
            $item_html .= $cart->getItemHtml($_item);                        
        }   
        
        return $item_html;
	}
	
    public function getWishlistSidebar()
	{	    
	    $layout = Mage::getSingleton('core/layout');
        return  $layout->createBlock('wishlist/customer_sidebar', 'wishlist_sidebar')    	                                    	                                
                                ->setTemplate('wishlist/sidebar.phtml')
                                ->renderView();                                        
	}
	
    public function updateItemOptions($event)
    {        
        $request = $event->getRequest();
        if ($request->getParam('gpc_add') == 1)
		{
		    $result = array();
	        $result['success'] = true;
	        	        
	        $result['cart'] = $this->getCartSidebar();                                                                                     
            $result['top_links'] = $this->getTopLinks();
            
            $result['prod_name'] = $event->getItem()->getProduct()->getName();                        	        
            $result['qty'] = $event->getRequest()->getParam('qty');
            $result['product_id'] = $event->getItem()->getProduct()->getId();
            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);  
            Mage::app()->getFrontController()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            Mage::getSingleton('checkout/session')->setData('gomage_procart_result', Mage::helper('core')->jsonEncode($result));
            Mage::getSingleton('checkout/session')->setData('gomage_procart_updateitem', true);
		}
	}
	
	public function getCartSidebar(){
	    $layout = Mage::getSingleton('core/layout');
	    
	    return $layout->createBlock('checkout/cart_sidebar', 'cart_sidebar')
	                                ->setTemplate('checkout/cart/sidebar.phtml')
                                    ->addItemRender('simple', 'checkout/cart_item_renderer', 'checkout/cart/sidebar/default.phtml')
                                    ->addItemRender('configurable', 'checkout/cart_item_renderer_configurable', 'checkout/cart/sidebar/default.phtml')
                                    ->addItemRender('grouped', 'checkout/cart_item_renderer_grouped', 'checkout/cart/sidebar/default.phtml')
                                    ->addItemRender('bundle', 'bundle/checkout_cart_item_renderer', 'checkout/cart/sidebar/default.phtml')                                    
                                    ->renderView();
	}
	
	public function getTopLinks()
	{
	    $layout = Mage::getSingleton('core/layout');
	    
	    $top_links = $layout->createBlock('page/template_links', 'gcp.top.links');
        
	    $checkout_cart_link = $layout->createBlock('checkout/links', 'checkout_cart_link');
        $wishlist_link = $layout->createBlock('wishlist/links', 'wishlist_link');
                            
        $top_links->setChild('checkout_cart_link', $checkout_cart_link);
        $top_links->setChild('wishlist_link', $wishlist_link);        
        
        if (method_exists($top_links, 'addLinkBlock')){
            $top_links->addLinkBlock('checkout_cart_link');
            $top_links->addLinkBlock('wishlist_link');
        }
        
        $checkout_cart_link->addCartLink();
                     
        return $top_links->renderView();
	}
	
	public function showConfigurableParams($event)
	{
	    $request = $event->getControllerAction()->getRequest();
	    if ($request->getParam('gpc_show_configurable') == 1)
	    {
	        $form = Mage::getBlockSingleton('gomage_procart/product_configurable_form');
	        $product = Mage::registry('current_product');
	        $form->setProduct($product);
	         
	        $layout = Mage::getSingleton('core/layout');	        
	        $product_options = $layout->getBlock('product.info.options.wrapper');
	        $product_options_bottom = $layout->getBlock('product.info.options.wrapper.bottom');
	        	        
	        $form->setChild('gcp_configurable_options', $product_options);
	        $form->setChild('gcp_configurable_options_bottom', $product_options_bottom);
	        
	        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
	            $product_info_bundle = $layout->getBlock('product.info.bundle');
	            $form->setChild('gcp_product_info_bundle', $product_info_bundle);
	        }
	        
	        $result = array();
	        $result['success'] = true;
	        $result['form'] = $form->renderView();
	        $result['qty'] = $request->getParam('qty');
	        
	        $event->getControllerAction()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	    }	    
	}
	
	public function addGroupProductWithError($event){
	    
	    $request = $event->getControllerAction()->getRequest();
	    $product = Mage::getModel('catalog/product')->load($request->getParam('id'));

	    if ($product && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED){
	    
    	    if(Mage::helper('gomage_procart')->isProCartEnable() && Mage::getStoreConfig('gomage_procart/qty_settings/product_page')){	        	                 
    	                 
        	    $messages = Mage::getSingleton('checkout/session')->getMessages(false)->getItems(Mage_Core_Model_Message::ERROR);
        	    
        	    $message_text = '';
        	    
        	    foreach ( $messages as $message) {
        	        $message_text .= $message->getText();
        	    }
      	    
        	    if ($message_text){
        	        Mage::getSingleton('checkout/session')->getMessages(true);
        	        $result = array();
        	        $result['success'] = false;
        	        $result['message'] = $message_text;
        	        $event->getControllerAction()->getResponse()
        	                ->setBody(Mage::helper('core')->jsonEncode($result));
        	        $event->getControllerAction()->setFlag('', 'no-renderLayout', true);                	        
        	    }        	    	    
    	    }
	    }
	}
	
	
	public function showGroupedParams($event){
	    $request = $event->getEvent()->getControllerAction()->getRequest();
	    if (($request->getParam('gpc_add') == 1) && ($gpc_prod_id = $request->getParam('gpc_prod_id'))){
	        $product = Mage::getModel('catalog/product')->load($gpc_prod_id);
	        if ($product->isGrouped()){
	            $result = array();
	            $result['success'] = true;
	            $result['is_grouped'] = true;
	            $result['deals_id'] = $request->getParam('deals_id');
	            
	            $form = Mage::getBlockSingleton('gomage_procart/product_grouped_form');
	            $form->setProduct($product);
	            $form->setProductId($product->getId());
	            
	            $layout = Mage::getSingleton('core/layout');
	            
	            $product_info_grouped = $layout->createBlock('catalog/product_view_type_grouped', 'product.info.grouped')
	                                           ->setTemplate('catalog/product/view/type/grouped.phtml');
	            $product_info_grouped_extra = $layout->createBlock('core/text_list', 'product.info.grouped.extra');
	            $product_info_grouped->setChild('product_type_data_extra', $product_info_grouped_extra);                               	            
	            $form->setChild('product_type_data', $product_info_grouped);
	            
    	        $product_options_bottom = $layout->createBlock('catalog/product_view', 'product.info.options.wrapper.bottom')
	                                             ->setTemplate('catalog/product/view/options/wrapper/bottom.phtml');

	            $product_tierprice = $layout->createBlock('catalog/product_view', 'product.tierprices')
	                                        ->setTemplate('catalog/product/view/tierprices.phtml');                                 
	            $product_options_bottom->insert('product.tierprices');
	            $clone_prices = $layout->createBlock('catalog/product_view', 'product.clone_prices')
	                                   ->setTemplate('catalog/product/view/price_clone.phtml');
	            $product_options_bottom->append('prices', $clone_prices);

	            $info_addto = $layout->createBlock('catalog/product_view', 'product.info.addto')
	                                 ->setTemplate('catalog/product/view/addto.phtml');
	            $product_options_bottom->append('product.info.addto');
	            $info_addtocart = $layout->createBlock('catalog/product_view', 'product.info.addtocart')
	                                     ->setTemplate('catalog/product/view/addtocart.phtml');
	            $product_options_bottom->append('product.info.addtocart');                     

    	        $form->setChild('product_options_wrapper_bottom', $product_options_bottom);
	            
	            $result['form'] = $form->renderView();
	            $result['qty'] = $request->getParam('qty');
	            
	            $event->getEvent()->getControllerAction()->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
	            $event->getEvent()->getControllerAction()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	        }
	        elseif ($product->isConfigurable()){
	            $result = array();
	            $result['success'] = true;
	            $result['is_configurable'] = true;
	            $result['deals_id'] = $request->getParam('deals_id');
	            
	            $additional = array();
	            $additional['_query']['options'] = 'cart';
	            $additional['_query']['gpc_prod_id'] = $product->getId();	            
	             
	            $result['url'] = $product->getUrlModel()->getUrl($product, $additional);
	            $result['product_id'] = $product->getId();
	            $result['qty'] = $request->getParam('qty');
	            
	            $event->getEvent()->getControllerAction()->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
	            $event->getEvent()->getControllerAction()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	        }
	    }
	}
	
	public function disableShoppingCart($event){	

	    if (Mage::getSingleton('checkout/session')->getData('gomage_procart_updateitem')){
	        Mage::getSingleton('checkout/session')->setData('gomage_procart_updateitem', false);
	        $event->getEvent()->getControllerAction()->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
	        $event->getEvent()->getControllerAction()->getResponse()->setBody(Mage::getSingleton('checkout/session')->getData('gomage_procart_result'));
	        Mage::getSingleton('checkout/session')->setData('gomage_procart_result', null);
	        return $this;
	    }
	    
	    if (Mage::helper('gomage_procart')->isShoppingCartDisable()){
	        $event->getEvent()->getControllerAction()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage'));
	    }
	}
	
	public function deleteCartItem($event){
	    $request = $event->getEvent()->getControllerAction()->getRequest();
	    if (($request->getParam('gpc_sedebar_delete') == 1 || $request->getParam('gpc_cart_delete') == 1) && ($id = $request->getParam('id'))){
	        
	        $helper = Mage::helper('gomage_procart');
	        $result = array();
	        $result['error'] = false;
	        
	        $cart = Mage::getSingleton('checkout/cart');
	        $item = $cart->getQuote()->getItemById($id);
	        $product = Mage::getModel('catalog/product')->load($item->getProductId());
	        
	        try {
                Mage::getSingleton('checkout/cart')->removeItem($id)->save();
            } catch (Exception $e) {                
                $result['error'] = true;
                $result['message'] = $helper->__('Cannot remove the item.');
            }
            
            $layout = Mage::getSingleton('core/layout');
            
	        if (!$result['error'] && $request->getParam('gpc_sedebar_delete') == 1){                                	        	        
    	        $result['cart'] = $layout->createBlock('checkout/cart_sidebar', 'cart_sidebar')
    	                                ->setTemplate('checkout/cart/sidebar.phtml')
                                        ->addItemRender('simple', 'checkout/cart_item_renderer', 'checkout/cart/sidebar/default.phtml')
                                        ->addItemRender('configurable', 'checkout/cart_item_renderer_configurable', 'checkout/cart/sidebar/default.phtml')
                                        ->addItemRender('grouped', 'checkout/cart_item_renderer_grouped', 'checkout/cart/sidebar/default.phtml')
                                        ->addItemRender('bundle', 'bundle/checkout_cart_item_renderer', 'checkout/cart/sidebar/default.phtml')                                    
                                        ->renderView();
            }
            
            if (!$result['error'] && $request->getParam('gpc_cart_delete') == 1){
                if (!Mage::helper('checkout/cart')->getCart()->getItemsCount()){
                    $result['redirect'] = Mage::getUrl('checkout/cart');
                }
                $result['item_id'] = $id;                	            
	            $result['total'] = $layout->createBlock('checkout/cart_totals', 'checkout.cart.totals')
                                         ->setTemplate('checkout/cart/totals.phtml')  
                                         ->renderView();
                                         
                $result['shipping'] = $layout->createBlock('checkout/cart_shipping', 'checkout.cart.shipping')
                                         ->setTemplate('checkout/cart/shipping.phtml')  
                                         ->renderView();                         
            }
            
	        if (!$result['error']){
                $result['top_links'] = $this->getTopLinks();
                
                if ($product->getStockItem()->getManageStock()){
    				$result['product_id'] = $product->getId();
    	            $result['max_qty'] = intval($product->getStockItem()->getQty());
                }	
            }
            
            $event->getEvent()->getControllerAction()->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
	        $event->getEvent()->getControllerAction()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            
	    }
	}
	
	public function noCookies($event){
	    $helper = Mage::helper('gomage_procart');	    
	    if ($helper->isProCartEnable() && $event->getEvent()->getAction()->getRequest()->getParam('gpc_add')){	        	        
	        $result = array();
	        $result['success'] = false;
	        $result['error'] = true;
	        $result['message'] = $helper->__('Please enable cookies in your web browser to continue.');            
	        $result['redirect'] = Mage::getUrl('core/index/noCookies');
	        echo Mage::helper('core')->jsonEncode($result); exit(); 
	    }
	}
	
	public function RedirectToWishlist($event){
	    $request = $event->getEvent()->getControllerAction()->getRequest();
	    if ($request->getParam('gpc_wishlist_add') == 1){
    	    if(!Mage::getSingleton('customer/session')->isLoggedIn()){
    	        $result = array();    	        
    	        $result['redirect'] = Mage::getUrl('wishlist');    	        
	            echo Mage::helper('core')->jsonEncode($result); exit();
    	    }    	    
	    } 
	}
	
	public function WishlistAdd($event){	    
	    $request = Mage::app()->getFrontController()->getRequest();
	    if ($request->getParam('gpc_wishlist_add') == 1){
	        Mage::helper('wishlist')->calculate();
	        Mage::unregister('wishlist');
    	    $result = array();    	        
        	$result['prod_name'] = $event->getProduct()->getName(); 
        	$result['top_links'] = $this->getTopLinks();  
        	$result['wishlist'] = $this->getWishlistSidebar();     	
            echo Mage::helper('core')->jsonEncode($result); exit();
	    }                
	}
	
    public function CompareAdd($event){	    
	    $request = Mage::app()->getFrontController()->getRequest();
	    if ($request->getParam('gpc_compare_add') == 1){
    	    $result = array();    	        
        	$result['prod_name'] = $event->getProduct()->getName();

        	Mage::helper('catalog/product_compare')->calculate();
        	$layout = Mage::getSingleton('core/layout');
	    
    	    $result['compare_products'] = $layout->createBlock('catalog/product_compare_sidebar', 'catalog.compare.sidebar')
	                                ->setTemplate('catalog/product/compare/sidebar.phtml')                                                                        
                                    ->renderView();
        	
            echo Mage::helper('core')->jsonEncode($result); exit();
	    }                
	}
	
	public function CompareRemove($event){
	    $request = Mage::app()->getFrontController()->getRequest();
	    if (($request->getParam('gpc_remove_compare') == 1) &&
	        ($request->getParam('isAjax') == 1)){
    	    $result = array();
    	    
    	    Mage::helper('catalog/product_compare')->calculate();
        	$layout = Mage::getSingleton('core/layout');
	    
    	    $result['compare_products'] = $layout->createBlock('catalog/product_compare_sidebar', 'catalog.compare.sidebar')
	                                ->setTemplate('catalog/product/compare/sidebar.phtml')                                                                        
                                    ->renderView();
        	
            echo Mage::helper('core')->jsonEncode($result); exit();
	    }        	        
	}
			
}