<?php
 /**
 * GoMage Procart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.0
 * @since        Class available since Release 1.0
 */

class GoMage_Procart_Helper_Data extends Mage_Core_Helper_Abstract{
    
    public function getConfigData($node){
		return Mage::getStoreConfig('gomage_procart/'.$node);
	}
	
	public function getAllStoreDomains(){
		
		$domains = array();
    	
    	foreach (Mage::app()->getWebsites() as $website) {
    		
    		$url = $website->getConfig('web/unsecure/base_url');
    		
    		if($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))){
    		
    		$domains[] = $domain;
    		
    		}
    		
    		$url = $website->getConfig('web/secure/base_url');
    		
    		if($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))){
    		
    		$domains[] = $domain;
    		
    		}
    		
    	}
    	
    	return array_unique($domains);
    	
		
	}
	
	public function getAvailabelWebsites(){
		return $this->_w();
	}
	
	public function getAvailavelWebsites(){
		return $this->_w();
	}
	
	protected function _w(){
    
        if(!Mage::getStoreConfig('gomage_activation/procart/installed') || 
           (intval(Mage::getStoreConfig('gomage_activation/procart/count')) > 10))
		{
			return array();
		}
		            		
		$time_to_update = 60*60*24*15;
		
		$r = Mage::getStoreConfig('gomage_activation/procart/ar');
		$t = Mage::getStoreConfig('gomage_activation/procart/time');
		$s = Mage::getStoreConfig('gomage_activation/procart/websites');
		
		$last_check = str_replace($r, '', Mage::helper('core')->decrypt($t));
		
		$allsites = explode(',', str_replace($r, '', Mage::helper('core')->decrypt($s)));
		$allsites = array_diff($allsites, array(""));
			
		if(($last_check+$time_to_update) < time()){
			$this->a(Mage::getStoreConfig('gomage_activation/procart/key'), 
			         intval(Mage::getStoreConfig('gomage_activation/procart/count')),
			         implode(',', $allsites));
		}
		
		return $allsites;
		
	}
	
	public function a($k, $c = 0, $s = ''){
		
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('https://www.gomage.com/index.php/gomage_downloadable/key/check'));		
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'key='.urlencode($k).'&sku=procart&domains='.urlencode(implode(',', $this->getAllStoreDomains())));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        $content = curl_exec($ch);
        
        $r	= Zend_Json::decode($content);
        $e = Mage::helper('core');
        if(empty($r)){
        	
        	$value1 = Mage::getStoreConfig('gomage_activation/procart/ar');
        	
	        $groups = array(
	        	'procart'=>array(
	        		'fields'=>array(
	        			'ar'=>array(
	        				'value'=>$value1
	        			),
	        			'websites'=>array(
	        				'value'=>(string)Mage::getStoreConfig('gomage_activation/procart/websites')
	        			),
	        			'time'=>array(
	        				'value'=>(string)$e->encrypt($value1.(time()-(60*60*24*15-1800)).$value1)
	        			),
	        			'count'=>array(
	        				'value'=>$c+1)
	        		)
	        	)
        	);
        	
	        Mage::getModel('adminhtml/config_data')
	                ->setSection('gomage_activation')
	                ->setGroups($groups)
	                ->save();
        	
	        Mage::getConfig()->reinit();
            Mage::app()->reinitStores();        
	                
        	return;
        }
        
        $value1 = '';
        $value2 = '';
        
        
        
        if(isset($r['d']) && isset($r['c'])){
    		$value1 = $e->encrypt(base64_encode(Zend_Json::encode($r)));
        
        
        if (!$s) $s = Mage::getStoreConfig('gomage_activation/procart/websites');
        
        $s = array_slice(explode(',', $s), 0, $r['c']);
        
        $value2 = $e->encrypt($value1.implode(',', $s).$value1);
        
        }
        $groups = array(
        	'procart'=>array(
        		'fields'=>array(
        			'ar'=>array(
        				'value'=>$value1
        			),
        			'websites'=>array(
        				'value'=>(string)$value2
        			),
        			'time'=>array(
        				'value'=>(string)$e->encrypt($value1.time().$value1)
        			),
        			'installed'=>array(
        				'value'=>1
        			),
        			'count'=>array(
        				'value'=>0)
        			
        		)
        	)
        );
        
        Mage::getModel('adminhtml/config_data')
                ->setSection('gomage_activation')
                ->setGroups($groups)
                ->save();
                
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();        
        
	}
	
	public function ga(){
		return Zend_Json::decode(base64_decode(Mage::helper('core')->decrypt(Mage::getStoreConfig('gomage_activation/procart/ar'))));
	}
	
	public function getIsCartPage(){
	      return ((Mage::app()->getFrontController()->getRequest()->getRequestedRouteName() == 'checkout') &&
	             (Mage::app()->getFrontController()->getRequest()->getRequestedControllerName() == 'cart') &&
	             (Mage::app()->getFrontController()->getRequest()->getRequestedActionName() == 'index'));
                   
	}
	
    public function getChangeAttributeCart(){	        
	      return ((Mage::app()->getFrontController()->getRequest()->getRequestedRouteName() == 'gomage_procart') &&
	             (Mage::app()->getFrontController()->getRequest()->getRequestedControllerName() == 'procart') &&
	             (Mage::app()->getFrontController()->getRequest()->getRequestedActionName() == 'changeattributecart'));
                   
	}
	
    public function getChangeQtyCart(){	        
	      return ((Mage::app()->getFrontController()->getRequest()->getRequestedRouteName() == 'gomage_procart') &&
	             (Mage::app()->getFrontController()->getRequest()->getRequestedControllerName() == 'procart') &&
	             (Mage::app()->getFrontController()->getRequest()->getRequestedActionName() == 'changeqtycartitem'));
                   
	}
	
	public function isProCartEnable(){
	    return (Mage::getStoreConfig('gomage_procart/general/enable') &&
	            in_array(Mage::app()->getStore()->getWebsiteId(), $this->getAvailavelWebsites()));
	}	
	
    public function isShoppingCartDisable(){
	    $procart = (Mage::getStoreConfig('gomage_procart/general/disable_cart') &&
	                $this->isProCartEnable());
	    if ($procart) return true;

	    $_modules = Mage::getConfig()->getNode('modules')->children();       	   	   
	    $_modulesArray = (array)$_modules;
	    
	    $lightcheckout = (isset($_modulesArray['GoMage_Checkout']) && 
	                      $_modulesArray['GoMage_Checkout']->is('active'));
	                      
	    if (!$lightcheckout) return false;

	    $lightcheckout = (Mage::helper('gomage_checkout')->getConfigData('general/disable_cart') &&
	                      Mage::helper('gomage_checkout')->getConfigData('general/enabled'));

	    return $lightcheckout;                  
	}
	
    public function getProcartProductData($product){
        
         $min_qty = $product->getStockItem()->getMinSaleQty();
         if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE){                 
             $max_qty = min(array($product->getStockItem()->getMaxSaleQty(), $product->getStockItem()->getQty()));
         }else{
             $max_qty = $product->getStockItem()->getMaxSaleQty();
         }    

         $quote = Mage::getSingleton('checkout/session')->getQuote();
         $item = $quote->getItemByProduct($product);
         if ($item && $qty = $item->getQty()){
             $max_qty = $max_qty - $qty;  
             if ($min_qty > $max_qty) $min_qty = $max_qty;
         }
         
         return array('min_qty' => intval($min_qty),
                      'max_qty' => intval($max_qty),
                      'name' => $product->getName(),
                      'is_simple' => ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE ? 1 : 0),
                      'is_grouped' => ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED ? 1 : 0)    
                 );
    }
    
    public function getIsAnymoreVersion($major, $minor, $revision = 0)
    {
        $version_info = Mage::getVersion();
        $version_info = explode('.', $version_info);
        
        if ($version_info[0] > $major)
        {
           return true;
        }   
        elseif ($version_info[0] == $major)
        {
            if ($version_info[1] > $minor)
            {
                return true; 
            }
            elseif ($version_info[1] == $minor)
            {
                if ($version_info[2] >= $revision)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }        
        }
        else
        { 
           return false;
        }                                
    }
    
	
}
