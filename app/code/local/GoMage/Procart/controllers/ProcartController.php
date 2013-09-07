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
 
class GoMage_Procart_ProcartController extends Mage_Core_Controller_Front_Action{
	
	public function changeqtyAction() {
		
	    $result = array();
	    $result['error'] = false;
	    
	    if (($result['qty'] = intval($this->getRequest()->getParam('qty'))) &&
	        ($result['product_id'] = $this->getRequest()->getParam('product_id'))){
    	        $product = Mage::getModel('catalog/product')->load($result['product_id']);

                if ($product->getStockItem()->getManageStock()){
          	        $maximumQty = intval($product->getStockItem()->getMaxSaleQty());
          	        $minimumQty = intval($product->getStockItem()->getMinSaleQty());
          			if($result['qty'] > $maximumQty){
          			    $result['error'] = true;
                      	$result['message'] = $this->__('The maximum quantity allowed for purchase is %s.', $maximumQty);
                      }elseif($result['qty'] < $minimumQty){
                        $result['error'] = true;
                      	$result['message'] = $this->__('The minimum quantity allowed for purchase is %s.', $minimumQty);
                      }

                      if (!$result['error']){
                          if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE){
                                  $min_qty = $minimumQty;
                                  $max_qty = min(array($maximumQty, $product->getStockItem()->getQty()));

                                  $quote = Mage::getSingleton('checkout/session')->getQuote();
                                  $item = $quote->getItemByProduct($product);
                                  if ($item && $qty = $item->getQty()){
                                       $max_qty = $max_qty - $qty;
                                       if ($min_qty > $max_qty) $min_qty = $max_qty;
                                  }
                  			    if($result['qty'] > $max_qty || $result['qty'] < $min_qty){
                  	            	$result['error'] = true;
                              	    $result['message'] = $this->__('The requested quantity for %s is not available.', '"'.$product->getName().'"');
                  	            }
                          }
                      }
                }
	    }   
	    else{
	        $result['error'] = true;
	        $result['message'] = $this->__('The requested quantity is not available.');
	    } 
	    
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	    
	}
	
    public function changeproductqtyAction() {
		
	    $result = array();
	    $result['error'] = false;
	    
	    if (($result['qty'] = $this->getRequest()->getParam('qty')) &&
	        ($result['product_id'] = $this->getRequest()->getParam('product_id'))){
	            
	        $product = Mage::getModel('catalog/product')->load($result['product_id']);

            if ($product->getStockItem()->getManageStock()){

    	        $maximumQty = intval($product->getStockItem()->getMaxSaleQty());
    	        $minimumQty = intval($product->getStockItem()->getMinSaleQty());
    			if($result['qty'] > $maximumQty){
    			    $result['error'] = true;
                	$result['message'] = $this->__('The maximum quantity allowed for purchase is %s.', $maximumQty);
                }elseif($result['qty'] < $minimumQty){
                    $result['error'] = true;
                	$result['message'] = $this->__('The minimum quantity allowed for purchase is %s.', $minimumQty);
                }

                if (!$result['error']){
                    if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE){
                            $min_qty = $minimumQty;
                            $max_qty = min(array($maximumQty, $product->getStockItem()->getQty()));

                            $quote = Mage::getSingleton('checkout/session')->getQuote();
                            $item = $quote->getItemByProduct($product);
                            if ($item && $qty = $item->getQty()){
                                 $max_qty = $max_qty - $qty;
                                 if ($min_qty > $max_qty) $min_qty = $max_qty;
                            }
            			    if($result['qty'] > $max_qty || $result['qty'] < $min_qty){
            	            	$result['error'] = true;
                        	    $result['message'] = $this->__('The requested quantity for %s is not available.', '"'.$product->getName().'"');
            	            }
                    }
                }
            }

	    }   
	    else{
	        $result['error'] = true;
	        $result['message'] = $this->__('The requested quantity is not available.');
	    } 
	    
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	    
	}
	
    public function changeqtycartitemAction() {
		
	    $result = array();
	    $result['error'] = false;
	    
	    if (($result['qty'] = $this->getRequest()->getParam('qty')) &&
	        ($result['item_id'] = $this->getRequest()->getParam('item_id'))){

	        try {
                $cartData = array();
                $cartData[$result['item_id']] = array('qty' => $result['qty']); 
                
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter($data['qty']);
                    }
                }
                $cart = Mage::getSingleton('checkout/cart');
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

				$item = $cart->getQuote()->getItemById($result['item_id']);
				$product = Mage::getModel('catalog/product')->load($item->getProductId());

                if ($product->getStockItem()->getManageStock()){

    				$maximumQty = intval($product->getStockItem()->getMaxSaleQty());
    				$minimumQty = intval($product->getStockItem()->getMinSaleQty());
    				if($result['qty'] > $maximumQty){
    	            	$result['error'] = true;
                	    $result['message'] = $this->__('The maximum quantity allowed for purchase is %s.', $maximumQty);
    	            }elseif($result['qty'] < $minimumQty){
                        $result['error'] = true;
                    	$result['message'] = $this->__('The minimum quantity allowed for purchase is %s.', $minimumQty);
                    }else
    	            {
    		            if ($item->getHasChildren())
    		            {
    		                foreach ($item->getChildren() as $child) {
    		                    $_product_id = $child->getProductId();
    		                    $_product = Mage::getModel('catalog/product')->load($_product_id);
    		                    $maximumQty = $_product->getStockItem()->getQty();
            				    if($result['qty'] > $maximumQty){
            		            	$result['error'] = true;
                    	            $result['message'] = $this->__('The requested quantity for %s is not available.', '"'.$product->getName().'"');
            		            	break;
            		            }
    		                }
    		            }
    		            else
    		            {
        		            $maximumQty = $product->getStockItem()->getQty();
        				    if($result['qty'] > $maximumQty){
        		            	$result['error'] = true;
                    	        $result['message'] = $this->__('The requested quantity for %s is not available.', '"'.$product->getName().'"');
        		            }
    		            }
    	            }

                }
    
	            if (!$result['error']){	         
	                if (method_exists($cart, 'suggestItemsQty')){   
                        $cartData = $cart->suggestItemsQty($cartData);
	                }
                    $cart->updateItems($cartData)
                         ->save();                    
                    Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
	            }
                
            } catch (Mage_Core_Exception $e) {
                $result['error'] = true;
                $result['message'] = $e->getMessage();            
            } catch (Exception $e) {
                $result['error'] = true;
                $result['message'] = $this->__('Cannot update shopping cart.');                            
                Mage::logException($e);                
            }    
            
            if (!$result['error'] && $this->getRequest()->getParam('sidebar') == 1){                            
    	        $layout = Mage::getSingleton('core/layout');	        
    	        $result['cart'] = $layout->createBlock('checkout/cart_sidebar', 'cart_sidebar')
    	                                ->setTemplate('checkout/cart/sidebar.phtml')
                                        ->addItemRender('simple', 'checkout/cart_item_renderer', 'checkout/cart/sidebar/default.phtml')
                                        ->addItemRender('configurable', 'checkout/cart_item_renderer_configurable', 'checkout/cart/sidebar/default.phtml')
                                        ->addItemRender('grouped', 'checkout/cart_item_renderer_grouped', 'checkout/cart/sidebar/default.phtml')
                                        ->addItemRender('bundle', 'bundle/checkout_cart_item_renderer', 'checkout/cart/sidebar/default.phtml')                                    
                                        ->renderView();
            }

            if (!$result['error'] && $this->getRequest()->getParam('cart') == 1){

                if ($item_html = $this->getCartItem($result['item_id']))
                    $result['item_html'] = $item_html;
                                                                        
                if ($total = $this->getCartTolal())                        
                    $result['total'] = $total;
            }
            
            if (!$result['error']){
                $result['top_links'] = Mage::getModel('gomage_procart/observer')->getTopLinks();
            }
	        
	    }   
	    else{
	        $result['error'] = true;
	        $result['message'] = $this->__('The requested quantity is not available.');
	    } 
	    
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));	    
	}
	
	public function getCartItem($item_id)
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
            if ($_item->getId() == $item_id)
            {
                $item_html = $cart->getItemHtml($_item);
                break;
            }
        }   
        
        return $item_html;
	}
	
	public function getCartTolal()
	{
	    $layout = Mage::getSingleton('core/layout');
	    
	    return $layout->createBlock('checkout/cart_totals', 'checkout.cart.totals')
                                         ->setTemplate('checkout/cart/totals.phtml')  
                                         ->renderView();
	}

	public function changeattributecartAction() {
		
	    $result = array();
	    $result['error'] = false;
	    
	    $id = (int) $this->getRequest()->getParam('id');
	    $result['item_id'] = $id;
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }        
        $params['super_attribute'] = Zend_Json::decode($params['super_attribute']);
        
	    try {
	                   
            $cart = Mage::getSingleton('checkout/cart');            
            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }
            
            $params['qty'] = $quoteItem->getQty();
            if (method_exists($cart, 'updateItem')){
                $item = $cart->updateItem($id, new Varien_Object($params));
            }
            else{
                $request = new Varien_Object($params);                
                $productId = $quoteItem->getProduct()->getId();
                $product = Mage::getModel('catalog/product')
                            ->setStoreId(Mage::app()->getStore()->getId())
                            ->load($productId);
    
                if ($product->getStockItem()) {
                    $minimumQty = $product->getStockItem()->getMinSaleQty();
                    if ($minimumQty && ($minimumQty > 0)
                        && ($request->getQty() < $minimumQty)
                        && !$cart->getQuote()->hasProductId($productId)
                    ) {
                        $request->setQty($minimumQty);
                    }
                }
    
                $item = $cart->getQuote()->addProduct($product, $request);

                if ($item->getParentItem()) {
                    $item = $item->getParentItem();
                }                    
                if ($item->getId() != $id) {
                    $cart->getQuote()->removeItem($id);
                    $items = $cart->getQuote()->getAllItems();
                    foreach ($items as $_item) {
                        if (($_item->getProductId() == $productId) && ($_item->getId() != $item->getId())) {
                            if ($item->compare($_item)) {
                                $item->setQty($item->getQty() + $_item->getQty());
                                $this->removeItem($_item->getId());
                                break;
                            }
                        }
                    }
                } else {
                    $item->setQty($request->getQty());
                }    
                       
            }    
            if (is_string($item)) {
                Mage::throwException($item);
            }

            $cart->save();

            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            
        } catch (Mage_Core_Exception $e) {
             $result['error'] = true;
             $result['message'] = $e->getMessage();
        } catch (Exception $e) {
            $result['error'] = true;
            $result['message'] = $e->getMessage();
            Mage::logException($e);            
        }
        
	    if (!$result['error']){

            if ($item_html = $this->getCartItem($item->getId())) {
                $result['item_html'] = $item_html;
                $result['new_item_id'] = $item->getId();
            }    
                
            if ($total = $this->getCartTolal())                        
                $result['total'] = $total;
        }else {
            if ($item_html = $this->getCartItem($result['item_id'])) {
                $result['item_html'] = $item_html;              
            }        
        }
	    
	    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}    
	
		
}