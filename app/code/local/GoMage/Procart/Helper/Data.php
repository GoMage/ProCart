<?php

/**
 * GoMage Procart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2014 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.1
 * @since        Class available since Release 1.0
 */
class GoMage_Procart_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getConfigData($node)
    {
        return Mage::getStoreConfig('gomage_procart/' . $node);
    }

    public function getAllStoreDomains()
    {
        $domains = array();
        foreach (Mage::app()->getWebsites() as $website) {

            $url = $website->getConfig('web/unsecure/base_url');
            if ($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))) {
                $domains[] = $domain;
            }

            $url = $website->getConfig('web/secure/base_url');

            if ($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))) {
                $domains[] = $domain;
            }
        }

        return array_unique($domains);
    }

    public function getAvailabelWebsites()
    {
        return $this->_w();
    }

    public function getAvailavelWebsites()
    {
        return $this->_w();
    }

    protected function _w()
    {

        if (!Mage::getStoreConfig('gomage_activation/procart/installed') ||
            (intval(Mage::getStoreConfig('gomage_activation/procart/count')) > 10)
        ) {
            return array();
        }

        $time_to_update = 60 * 60 * 24 * 15;

        $r = Mage::getStoreConfig('gomage_activation/procart/ar');
        $t = Mage::getStoreConfig('gomage_activation/procart/time');
        $s = Mage::getStoreConfig('gomage_activation/procart/websites');

        $last_check = str_replace($r, '', Mage::helper('core')->decrypt($t));

        $allsites = explode(',', str_replace($r, '', Mage::helper('core')->decrypt($s)));
        $allsites = array_diff($allsites, array(""));

        if (($last_check + $time_to_update) < time()) {
            $this->a(Mage::getStoreConfig('gomage_activation/procart/key'),
                intval(Mage::getStoreConfig('gomage_activation/procart/count')),
                implode(',', $allsites)
            );
        }

        return $allsites;

    }

    public function a($k, $c = 0, $s = '')
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('https://www.gomage.com/index.php/gomage_downloadable/key/check'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'key=' . urlencode($k) . '&sku=procart&domains=' . urlencode(implode(',', $this->getAllStoreDomains())) . '&ver=' . urlencode('2.0'));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $content = curl_exec($ch);

        $r = Zend_Json::decode($content);
        $e = Mage::helper('core');
        if (empty($r)) {

            $value1 = Mage::getStoreConfig('gomage_activation/procart/ar');

            $groups = array(
                'procart' => array(
                    'fields' => array(
                        'ar' => array(
                            'value' => $value1
                        ),
                        'websites' => array(
                            'value' => (string)Mage::getStoreConfig('gomage_activation/procart/websites')
                        ),
                        'time' => array(
                            'value' => (string)$e->encrypt($value1 . (time() - (60 * 60 * 24 * 15 - 1800)) . $value1)
                        ),
                        'count' => array(
                            'value' => $c + 1)
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


        if (isset($r['d']) && isset($r['c'])) {
            $value1 = $e->encrypt(base64_encode(Zend_Json::encode($r)));


            if (!$s) {
                $s = Mage::getStoreConfig('gomage_activation/procart/websites');
            }

            $s = array_slice(explode(',', $s), 0, $r['c']);

            $value2 = $e->encrypt($value1 . implode(',', $s) . $value1);

        }
        $groups = array(
            'procart' => array(
                'fields' => array(
                    'ar' => array(
                        'value' => $value1
                    ),
                    'websites' => array(
                        'value' => (string)$value2
                    ),
                    'time' => array(
                        'value' => (string)$e->encrypt($value1 . time() . $value1)
                    ),
                    'installed' => array(
                        'value' => 1
                    ),
                    'count' => array(
                        'value' => 0)

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

    public function ga()
    {
        return Zend_Json::decode(base64_decode(Mage::helper('core')->decrypt(Mage::getStoreConfig('gomage_activation/procart/ar'))));
    }

    public function getIsCartPage()
    {
        return ((Mage::app()->getFrontController()->getRequest()->getRequestedRouteName() == 'checkout') &&
            (Mage::app()->getFrontController()->getRequest()->getRequestedControllerName() == 'cart') &&
            (Mage::app()->getFrontController()->getRequest()->getRequestedActionName() == 'index'));

    }

    public function isWhishlistMove()
    {
        return (Mage::app()->getFrontController()->getRequest()->getParam('gpc_wishlist_add') == 1);
    }

    public function getChangeAttributeCart()
    {
        return ((Mage::app()->getFrontController()->getRequest()->getRequestedRouteName() == 'gomage_procart') &&
            (Mage::app()->getFrontController()->getRequest()->getRequestedControllerName() == 'procart') &&
            (Mage::app()->getFrontController()->getRequest()->getRequestedActionName() == 'changeattributecart'));

    }

    public function getChangeQtyCart()
    {
        return ((Mage::app()->getFrontController()->getRequest()->getRequestedRouteName() == 'gomage_procart') &&
            (Mage::app()->getFrontController()->getRequest()->getRequestedControllerName() == 'procart') &&
            (Mage::app()->getFrontController()->getRequest()->getRequestedActionName() == 'changeqtycartitem'));

    }

    public function isProCartEnable()
    {
        return (Mage::getStoreConfig('gomage_procart/general/enable') &&
            in_array(Mage::app()->getStore()->getWebsiteId(), $this->getAvailavelWebsites()));
    }

    public function isCrosssellAdd()
    {
        return ((Mage::app()->getFrontController()->getRequest()->getParam('gpc_crosssell') == 1) ||
            (Mage::app()->getFrontController()->getRequest()->getParam('gpc_add') == 1));
    }

    public function isProductReviewPage()
    {
        return Mage::app()->getFrontController()->getRequest()->getRequestedRouteName() == 'review';
    }

    public function isShoppingCartDisable()
    {
        if (count(Mage::getSingleton('checkout/session')->getMessages(false)->getItems(Mage_Core_Model_Message::ERROR)) && $this->isProCartEnable()) {
            return false;
        }
        $procart = (Mage::getStoreConfig('gomage_procart/general/disable_cart') &&
            $this->isProCartEnable());
        if ($procart) {
            return true;
        }

        $_modules = Mage::getConfig()->getNode('modules')->children();
        $_modulesArray = (array)$_modules;

        $lightcheckout = (isset($_modulesArray['GoMage_Checkout']) &&
            $_modulesArray['GoMage_Checkout']->is('active'));

        if (!$lightcheckout) {
            return false;
        }

        $lightcheckout = (Mage::helper('gomage_checkout')->getConfigData('general/disable_cart') &&
            Mage::helper('gomage_checkout')->getConfigData('general/enabled') &&
            in_array(Mage::app()->getStore()->getWebsiteId(), Mage::helper('gomage_checkout')->getAvailavelWebsites()));

        return $lightcheckout;
    }

    public function getProcartProductData($product, $cart = false, $parent_id = false)
    {
        if ($product->getStockItem()->getManageStock() && !$product->getStockItem()->getBackorders()) {
            $min_qty = $product->getStockItem()->getMinSaleQty();
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                $max_qty = min(array($product->getStockItem()->getMaxSaleQty(), $product->getStockItem()->getQty()));
            } else {
                $max_qty = $product->getStockItem()->getMaxSaleQty();
            }

            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $item = $quote->getItemByProduct($product);
            if ($item && $qty = $item->getQty()) {
                $max_qty = $max_qty - $qty;
                if ($min_qty > $max_qty) {
                    $min_qty = $max_qty;
                }
            }
        } else {
            $min_qty = $product->getStockItem()->getMinSaleQty();
            $max_qty = $product->getStockItem()->getMaxSaleQty();
        }

        if ($parent_id || $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            $min_qty = 0;
        }

        $product_data = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $qty_increments = $product_data->getQtyIncrements();

        if ($qty_increments && !$parent_id) {
            $min_qty = $qty_increments;
        }

        if ($parent_id) {
            $parent_product = Mage::getModel('catalog/product')->load($parent_id);

            if ($parent_product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $product_data = Mage::getModel('cataloginventory/stock_item')->loadByProduct($parent_product);
                $qty_increments = $product_data->getQtyIncrements();
                $min_qty = $qty_increments;
            }
        }

        $block_product_list = Mage::getBlockSingleton('catalog/product_list');

        return array('min_qty' => intval($min_qty),
            'max_qty' => intval($max_qty),
            'name' => $product->getName(),
            'is_simple' => ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE ? 1 : 0),
            'is_grouped' => ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED ? 1 : 0),
            'is_bundled' => ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ? 1 : 0),
            'is_giftcard' => ($product->getTypeId() == 'giftcard' ? 1 : 0),
            'parent_id' => $parent_id,
            'product_url' => $product->getProductUrl(),
            'addtocart_url' => $block_product_list->getAddToCartUrl($product),
            'increments' => ($qty_increments ? $qty_increments : 1)
        );
    }

    public function isConfigureCart()
    {
        return strpos(Mage::helper('core/url')->getCurrentUrl(), 'configure/id') ? 1 : 0;
    }

    public function getBundleProductSelections($parentProduct)
    {
        $typeInstance = $parentProduct->getTypeInstance(true);
        return $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($parentProduct),
            $parentProduct
        );
    }

    public function getIsAnymoreVersion($major, $minor, $revision = 0)
    {
        $version_info = Mage::getVersion();
        $version_info = explode('.', $version_info);

        if ($version_info[0] > $major) {
            return true;
        } elseif ($version_info[0] == $major) {
            if ($version_info[1] > $minor) {
                return true;
            } elseif ($version_info[1] == $minor) {
                if ($version_info[2] >= $revision) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function formatColor($value)
    {
        if ($value = preg_replace('/[^a-zA-Z0-9\s]/', '', $value)) {
            $value = '#' . $value;
        }
        return $value;
    }

    public function isEnterprise()
    {
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        return in_array('Enterprise_Enterprise', $modules);
    }

    public function getIsUltimentoTheme()
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        if (!isset($modules['Ultimento_Theme'])) {
            return false;
        }
        return $modules['Ultimento_Theme']->is('active');
    }

    public function notify()
    {
        $frequency = intval(Mage::app()->loadCache('gomage_notifications_frequency'));
        if (!$frequency) {
            $frequency = 24;
        }
        $last_update = intval(Mage::app()->loadCache('gomage_notifications_last_update'));

        if (($frequency * 60 * 60 + $last_update) > time()) {
            return false;
        }

        $timestamp = $last_update;
        if (!$timestamp) {
            $timestamp = time();
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, sprintf('https://www.gomage.com/index.php/gomage_notification/index/data'));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'sku=procart&timestamp=' . $timestamp . '&ver=' . urlencode('2.0'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            $content = curl_exec($ch);

            $result = Zend_Json::decode($content);

            if ($result && isset($result['frequency']) && ($result['frequency'] != $frequency)) {
                Mage::app()->saveCache($result['frequency'], 'gomage_notifications_frequency');
            }

            if ($result && isset($result['data'])) {
                if (!empty($result['data'])) {
                    Mage::getModel('adminnotification/inbox')->parse($result['data']);
                }
            }
        } catch (Exception $e) {
        }

        Mage::app()->saveCache(time(), 'gomage_notifications_last_update');

    }


}