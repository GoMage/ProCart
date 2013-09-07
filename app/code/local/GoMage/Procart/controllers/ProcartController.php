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
 
class GoMage_Procart_ProcartController extends Mage_Core_Controller_Front_Action{
	
	public function changeqtyAction() {
		
	    $result = array();
	    $result['error'] = false;
	    $result['deals_id'] = $this->getRequest()->getParam('deals_id');
	    
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
                                  if ($product->getStockItem()->getBackorders()){
                                      $max_qty = $maximumQty;
                                  }else{    
                                      $max_qty = min(array($maximumQty, $product->getStockItem()->getQty()));
                                  }    

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
                            if ($product->getStockItem()->getBackorders()){
                                $max_qty = $maximumQty;
                            }else{    
                                $max_qty = min(array($maximumQty, $product->getStockItem()->getQty()));
                            }    
                            
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
    		                    
    		                    if ($_product->getStockItem()->getBackorders()){
                                    $maximumQty = intval($_product->getStockItem()->getMaxSaleQty());
                                }else{    
                                    $maximumQty = $_product->getStockItem()->getQty();
                                }        
            				    if($result['qty'] > $maximumQty){
            		            	$result['error'] = true;
                    	            $result['message'] = $this->__('The requested quantity for %s is not available.', '"'.$product->getName().'"');
            		            	break;
            		            }
    		                }
    		            }
    		            else
    		            {
    		                if ($product->getStockItem()->getBackorders()){
                                $maximumQty = intval($product->getStockItem()->getMaxSaleQty());
                            }else{    
                                $maximumQty = $product->getStockItem()->getQty();
                            }        		            
        				    if($result['qty'] > $maximumQty){
        		            	$result['error'] = true;
                    	        $result['message'] = $this->__('The requested quantity for %s is not available.', '"'.$product->getName().'"');
        		            }
    		            }
    	            }
    	            
    	            $result['product_id'] = $product->getId();
    	            $result['max_qty'] = $maximumQty - $result['qty'];

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
    	        $result['cart'] = Mage::getModel('gomage_procart/observer')->getCartSidebar();
            }

            if (!$result['error'] && $this->getRequest()->getParam('cart') == 1){
                
                $result['items_html'] = $this->getCartItems();
                                                                        
                if ($total = $this->getCartTolal())                        
                    $result['total'] = $total;

                if ($shipping = $this->getCartShipping())                        
                    $result['shipping'] = $shipping;    
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
	
	public function getCartItems()
	{
	    $items_html = '';
	    $layout = Mage::getSingleton('core/layout');
        $cart = $layout->createBlock('checkout/cart', 'checkout.cart')    	                                    	                                
                                ->addItemRender('simple', 'checkout/cart_item_renderer', 'checkout/cart/item/default.phtml')
                                ->addItemRender('configurable', 'checkout/cart_item_renderer_configurable', 'checkout/cart/item/default.phtml')
                                ->addItemRender('grouped', 'checkout/cart_item_renderer_grouped', 'checkout/cart/item/default.phtml')
                                ->addItemRender('bundle', 'bundle/checkout_cart_item_renderer', 'checkout/cart/item/default.phtml');                                        
        foreach ($cart->getItems() as $_item)
        {                                           
            $items_html .= $cart->getItemHtml($_item);
        }   
        
        return $items_html;
	}
	
	public function getCartTolal()
	{
	    $layout = Mage::getSingleton('core/layout');
	    
	    return $layout->createBlock('checkout/cart_totals', 'checkout.cart.totals')
                                         ->setTemplate('checkout/cart/totals.phtml')  
                                         ->renderView();
	}
	
    public function getCartShipping()
	{
	    $layout = Mage::getSingleton('core/layout');
	    
	    return $layout->createBlock('checkout/cart_shipping', 'checkout.cart.shipping')
                                         ->setTemplate('checkout/cart/shipping.phtml')  
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
             $success_param = array(); 
             if ($quoteItem){
                 if ($quoteItem->getProduct()->getTypeInstance(true)->getSpecifyOptionMessage() == $e->getMessage()){
                     $all_params = $params['super_attribute'];
                                                                                                                              
                     $productCollection = $quoteItem->getProduct()->getTypeInstance(true)->getUsedProductCollection($quoteItem->getProduct());                      
                                           
                     foreach ($all_params as $attribute_id => $value){
                         $tmp_params = $success_param;
                         $tmp_params[$attribute_id] = $value;                          
                         $productObject = $quoteItem->getProduct()->getTypeInstance(true)->getProductByAttributes($tmp_params, $quoteItem->getProduct());
                         if ($productObject && $productObject->getId()){
                             $success_param[$attribute_id] = $value;
                             $productCollection->addAttributeToFilter($attribute_id, $value);                                                          
                         }else{
                             
                             $result['update_attribute'] = $attribute_id;
                             
                             $attribute_data = array();                                                          
                             $attribute = null;
                             $product = Mage::getModel('catalog/product')->load($quoteItem->getProduct()->getId());                             
                             $product->getTypeInstance(true)->getUsedProductAttributeIds($product);                             
                             $usedAttributes = $product->getData('_cache_instance_used_attributes');
                             
                             foreach ($usedAttributes as $key => $_arrtibute){
                                 if ($key == $attribute_id){
                                     $attribute = $_arrtibute;
                                     break;
                                 }
                             }                 
                             
                             foreach($productCollection as $_product){
                                 $_product = Mage::getModel('catalog/product')->load($_product->getId());
                                 if ($_product->isSaleable()) {
                                     $_key = $_product->getData($attribute->getProductAttribute()->getAttributeCode());
                                                             
                                     foreach ($attribute->getPrices() as $_v){
                                          if ($_v['value_index'] == $_key){
                                             $attribute_data[$_key] = $_v['label'];
                                             break; 
                                          }
                                     }
                                 }     
                             }
                             
                             $result['attribute_data'] = $attribute_data;
                             break;                               
                         } 
                     }                     
                 }
             }
             
             Mage::getSingleton('checkout/session')->unsetAll();
             
             $result['choosetext'] = Mage::helper('catalog')->__('Choose an Option...');
             $result['success_param'] = $success_param;
             $result['error'] = true;             
             $result['message'] = $e->getMessage();             
             if ($quoteItem){
             	$result['message'] = str_replace('""', '"'.$quoteItem->getProduct()->getName().'"', $result['message']);
             	$result['message'] = explode('.', $result['message']);
             	foreach ($result['message'] as $_k => $_v){
             		$result['message'][$_k] = trim($_v);
             	}
             	$result['message'] = array_unique($result['message']);             	             	
             	$result['message'] = implode('.', $result['message']);
             }
        } catch (Exception $e) {
            $result['error'] = true;
            $result['message'] = $e->getMessage();
            Mage::logException($e);            
        }
        
	    if (!$result['error']){

	        if ($total = $this->getCartTolal())                        
                $result['total'] = $total;

            if ($shipping = $this->getCartShipping())                        
                $result['shipping'] = $shipping;     
                
        }
        
        $result['items_html'] = $this->getCartItems();
        $result['cart'] = Mage::getModel('gomage_procart/observer')->getCartSidebar();
	    
	    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}

	public function getProductListAction(){
	    $result = array();
	    $product_list = array();
	    if ($ids = $this->getRequest()->getParam('product_ids')){             
             
             $helper = Mage::helper('gomage_procart');
             
             $ids = array_unique(explode(',', $ids));
             
             foreach ($ids as $_product){                 
                 $product = Mage::getModel('catalog/product')->load($_product);                 
                 $product_list[$product->getId()] = $helper->getProcartProductData($product);
             }
             
        }
        $result['product_list'] = $product_list;
	    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	
	public function removewishlistitemAction(){	    
	    $result = array();
	    $wishlist = Mage::getModel('wishlist/wishlist')
                        ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
        if ($wishlist){                
            $id = (int) $this->getRequest()->getParam('item');
            $item = Mage::getModel('wishlist/item')->load($id);
    
            if($item->getWishlistId() == $wishlist->getId()) {
                try {
                    $item->delete();
                    $wishlist->save();
                    Mage::helper('wishlist')->calculate();
                    $result['top_links'] = Mage::getModel('gomage_procart/observer')->getTopLinks();
                    $result['wishlist'] = Mage::getModel('gomage_procart/observer')->getWishlistSidebar();
                    $result['success'] = true;
                }
                catch (Mage_Core_Exception $e) {                    
                    $result['success'] = false;
                    $result['message'] = $this->__('An error occurred while deleting the item from wishlist: %s', $e->getMessage());
                    
                }
                catch(Exception $e) {          
                    $result['success'] = false;      
                    $result['message'] = $this->__('An error occurred while deleting the item from wishlist.');                    
                }
            }
        }else{
            $result['success'] = false;
            $result['message'] =  $this->__('An error occurred while deleting the item from wishlist.');
        }
        
	    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
			
}