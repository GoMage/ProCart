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

class GoMage_Procart_ProcartController extends Mage_Core_Controller_Front_Action
{

    public function changeqtyAction()
    {

        $result             = array();
        $result['error']    = false;
        $result['deals_id'] = $this->getRequest()->getParam('deals_id');

        if ($result['product_id'] = $this->getRequest()->getParam('product_id')) {
            $product = Mage::getModel('catalog/product')->load($result['product_id']);
        }

        if ((($result['qty'] = intval($this->getRequest()->getParam('qty'))) &&
                ($result['product_id'] = $this->getRequest()->getParam('product_id')))
            ||
            (($result['product_id'] = $this->getRequest()->getParam('product_id'))
                &&
                ($product = Mage::getModel('catalog/product')->load($result['product_id'])
                    &&
                    ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED)))
        ) {
            $product = Mage::getModel('catalog/product')->load($result['product_id']);

            if ($product->getStockItem()->getManageStock()) {
                $maximumQty = intval($product->getStockItem()->getMaxSaleQty());

                if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
                    $minimumQty = 0;
                } else {
                    $minimumQty = intval($product->getStockItem()->getMinSaleQty());
                }

                if ($result['qty'] > $maximumQty) {
                    $result['error']   = true;
                    $result['message'] = $this->__('The maximum quantity allowed for purchase is %s.', $maximumQty);
                } elseif ($result['qty'] < $minimumQty) {
                    $result['error']   = true;
                    $result['message'] = $this->__('The minimum quantity allowed for purchase is %s.', $minimumQty);
                }

                if (!$result['error']) {
                    if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                        $min_qty = $minimumQty;
                        if ($product->getStockItem()->getBackorders()) {
                            $max_qty = $maximumQty;
                        } else {
                            $max_qty = min(array($maximumQty, $product->getStockItem()->getQty()));
                        }

                        $quote = Mage::getSingleton('checkout/session')->getQuote();
                        $item  = $quote->getItemByProduct($product);
                        if ($item && $qty = $item->getQty()) {
                            $max_qty = $max_qty - $qty;
                            if ($min_qty > $max_qty) {
                                $min_qty = $max_qty;
                            }
                        }
                        if ($result['qty'] > $max_qty || $result['qty'] < $min_qty) {
                            $result['error']   = true;
                            $result['message'] = $this->__('The requested quantity for %s is not available.', '"' . $product->getName() . '"');
                        }
                    }
                }
            }
        } else {
            $result['error']   = true;
            $result['message'] = $this->__('The requested quantity is not available.');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }

    public function changeproductqtyAction()
    {

        $result          = array();
        $result['error'] = false;

        if ((($result['qty'] = $this->getRequest()->getParam('qty')) &&
                ($result['product_id'] = $this->getRequest()->getParam('product_id')))
            ||
            (($result['product_id'] = $this->getRequest()->getParam('product_id'))
                &&
                ($result['parent_id'] = $this->getRequest()->getParam('parent_id')))
        ) {

            $product = Mage::getModel('catalog/product')->load($result['product_id']);
            if ($product->getStockItem()->getManageStock()) {

                $maximumQty = intval($product->getStockItem()->getMaxSaleQty());

                if ($this->getRequest()->getParam('parent_id')) {
                    $minimumQty = 0;
                } else {
                    $minimumQty = intval($product->getStockItem()->getMinSaleQty());
                }

                if ($result['qty'] > $maximumQty) {
                    $result['error']   = true;
                    $result['message'] = $this->__('The maximum quantity allowed for purchase is %s.', $maximumQty);
                } elseif ($result['qty'] < $minimumQty) {
                    $result['error']   = true;
                    $result['message'] = $this->__('The minimum quantity allowed for purchase is %s.', $minimumQty);
                }

                $result['cart'] = $this->getRequest()->getParam('cart');

                if (!$result['error']) {
                    if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                        $min_qty = $minimumQty;
                        if ($product->getStockItem()->getBackorders()) {
                            $max_qty = $maximumQty;
                        } else {
                            $max_qty = min(array($maximumQty, $product->getStockItem()->getQty()));
                        }

                        $quote = Mage::getSingleton('checkout/session')->getQuote();
                        $item  = $quote->getItemByProduct($product);
                        if ($item && $qty = $item->getQty()) {
                            if ($result['cart'] != '1') {
                                $max_qty = $max_qty - $qty;
                            }
                            if ($min_qty > $max_qty) {
                                $min_qty = $max_qty;
                            }
                        }
                        if ($result['qty'] > $max_qty || $result['qty'] < $min_qty) {
                            $result['error']   = true;
                            $result['message'] = $this->__('The requested quantity for %s is not available.', '"' . $product->getName() . '"');
                        }
                    }
                }
            }

        } else {
            $result['error']   = true;
            $result['message'] = $this->__('The requested quantity is not available.');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }

    private function prepareCartData($item_id, $qty)
    {
        $cartData           = array();
        $filter             = new Zend_Filter_LocalizedToNormalized(
            array('locale' => Mage::app()->getLocale()->getLocaleCode())
        );
        $cartData[$item_id] = array('qty' => $filter->filter($qty));

        return $cartData;
    }

    public function changeqtycartitemAction()
    {

        $result          = array();
        $result['error'] = false;

        if (($result['qty'] = $this->getRequest()->getParam('qty')) &&
            ($result['item_id'] = $this->getRequest()->getParam('item_id'))
        ) {
            $current_qty = $result['qty'];
            $cart        = Mage::getSingleton('checkout/cart');
            $item        = $cart->getQuote()->getItemById($result['item_id']);
            /* @var $product Mage_Catalog_Model_Product */
            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            $product_data   = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $qty_increments = intval($product_data->getQtyIncrements());

            if ($qty_increments) {
                if ($this->getRequest()->getParam('direction') == 'up') {
                    $result['qty'] = $result['qty'] + $qty_increments;
                } else {
                    $result['qty'] = $result['qty'] - $qty_increments;
                }
            } else {
                if ($this->getRequest()->getParam('direction') == 'up') {
                    $result['qty'] = $result['qty'] + 1;
                } else {
                    $result['qty'] = $result['qty'] - 1;
                }
            }


            try {
                $cartData = $this->prepareCartData($result['item_id'], $result['qty']);

                if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                if ($product->getStockItem()->getManageStock()) {
                    $maximumQty = intval($product->getStockItem()->getMaxSaleQty());
                    $minimumQty = intval($product->getStockItem()->getMinSaleQty());
                    if ($qty_increments && $qty_increments > $minimumQty) {
                        $minimumQty = $qty_increments;
                    }
                    if ($result['qty'] > $maximumQty) {
                        $result['error']   = true;
                        $result['message'] = $this->__('The maximum quantity allowed for purchase is %s.', $maximumQty);
                    } elseif ($result['qty'] < $minimumQty) {
                        $result['error']   = true;
                        $result['message'] = $this->__('The minimum quantity allowed for purchase is %s.', $minimumQty);
                    } else {
                        if ($item->getHasChildren()) {
                            foreach ($item->getChildren() as $child) {
                                $_product_id = $child->getProductId();
                                $_product    = Mage::getModel('catalog/product')->load($_product_id);

                                if ($_product->getStockItem()->getBackorders()) {
                                    $maximumQty = intval($_product->getStockItem()->getMaxSaleQty());
                                } else {
                                    $maximumQty = $_product->getStockItem()->getQty();
                                }
                                if ($result['qty'] > $maximumQty) {
                                    $result['error']   = true;
                                    $result['message'] = $this->__('The requested quantity for %s is not available.', '"' . $product->getName() . '"');
                                    break;
                                }
                            }
                        } else {
                            if ($product->getStockItem()->getBackorders()) {
                                $maximumQty = intval($product->getStockItem()->getMaxSaleQty());
                            } else {
                                $maximumQty = $product->getStockItem()->getQty();
                            }
                            if ($result['qty'] > $maximumQty) {
                                $result['error']   = true;
                                $result['message'] = $this->__('The requested quantity for %s is not available.', '"' . $product->getName() . '"');
                            }
                        }
                    }

                    $result['product_id'] = $product->getId();
                    $result['max_qty']    = $maximumQty - $result['qty'];

                }

                if (!$result['error']) {
                    if (method_exists($cart, 'suggestItemsQty')) {
                        $cartData = $cart->suggestItemsQty($cartData);
                    }
                    $cart->updateItems($cartData)
                        ->save();
                    Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                }

            } catch (Mage_Core_Exception $e) {
                $result['error']   = true;
                $result['message'] = $e->getMessage();
            } catch (Exception $e) {
                $result['error']   = true;
                $result['message'] = $this->__('Cannot update shopping cart.');
                Mage::logException($e);
            }

            if (!$result['error'] && $this->getRequest()->getParam('sidebar') == 1) {
                $result['cart'] = Mage::getModel('gomage_procart/observer')->getCartSidebar();
            }

            if ($this->getRequest()->getParam('cart') == 1) {
                $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
                $error = false;
                foreach ($items as $cartItem) {
                    $cartItem->checkData();
                    if ($cartItem->getHasError()) {
                        $error             = true;
                        $result['message'] = implode("\n", array_unique(explode("\n", $cartItem->getMessage())));
                    }
                }

                if ($error) {
                    $result['error'] = true;
                    try {
                        $cartData = $this->prepareCartData($result['item_id'], $current_qty);
                        if (method_exists($cart, 'suggestItemsQty')) {
                            $cartData = $cart->suggestItemsQty($cartData);
                        }
                        $cart->updateItems($cartData)
                            ->save();
                        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                    } catch (Exception $e) {
                        $result['message'] = $this->__('Cannot update shopping cart.');
                    }
                }

                if (!$cart->getQuote()->validateMinimumAmount()) {
                    $minimumAmount     = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                        ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));
                    $result['message'] = Mage::getStoreConfig('sales/minimum_order/description')
                        ? Mage::getStoreConfig('sales/minimum_order/description')
                        : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);
                }
            }

            if (!$result['error'] && Mage::helper('gomage_procart')->getIsUltimentoTheme()) {
                $layout = Mage::getSingleton('core/layout');
                $layout->createBlock('page/html_head', 'head');
                $result['ultcustomernav'] = $layout->createBlock('catalog/navigation_ultcustomernav', 'root.ult.customernav')
                    ->setTemplate('catalog/navigation/ultcustomernav.phtml')
                    ->renderView();
            }

            if (!$result['error'] && $this->getRequest()->getParam('cart') == 1) {

                $result['items_html'] = $this->getCartItems();

                if ($total = $this->getCartTolal()) {
                    $result['total'] = $total;
                }

                if ($shipping = $this->getCartShipping()) {
                    $result['shipping'] = $shipping;
                }
                $block_helper = Mage::helper('gomage_procart/blocks');
                if ($checkout_methods = $block_helper->getCartCheckoutMethods('top_methods')) {
                    $result['checkout_methods_top'] = $checkout_methods;
                }
                if ($checkout_methods = $block_helper->getCartCheckoutMethods('methods')) {
                    $result['checkout_methods_bottom'] = $checkout_methods;
                }
                if ($rewards_points = $block_helper->getRewardsPoints()) {
                    $result['rewards_points'] = $rewards_points;
                }
            }

            if (!$result['error']) {
                $result['top_links'] = Mage::getModel('gomage_procart/observer')->getTopLinks();
            }

        } else {
            $result['error']   = true;
            $result['message'] = $this->__('The requested quantity is not available.');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function getCartItems()
    {
        $items_html = '';
        /* @var $block_helper GoMage_Procart_Helper_Blocks */
        $block_helper = Mage::helper('gomage_procart/blocks');
        $cart         = $block_helper->getShoppingCartBlock();
        foreach ($cart->getItems() as $_item) {
            $_item->checkData();
            if (!$_item->getHasError()) {
                $items_html .= $cart->getItemHtml($_item);
            }
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

    private function prepareSuperAttribute($super_attribute)
    {
        $result          = array();
        $super_attribute = Zend_Json::decode($super_attribute);
        ksort($super_attribute, SORT_NUMERIC);
        foreach ($super_attribute as $attribute) {
            $result[$attribute['attribute_id']] = $attribute['value'];
        }
        return $result;
    }

    public function changeattributecartAction()
    {

        $result          = array();
        $result['error'] = false;

        $id                = (int)$this->getRequest()->getParam('id');
        $result['item_id'] = $id;
        $params            = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        $params['super_attribute'] = $this->prepareSuperAttribute($params['super_attribute']);

        try {

            $cart      = Mage::getSingleton('checkout/cart');
            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $params['qty'] = $quoteItem->getQty();
            if (method_exists($cart, 'updateItem')) {
                $item = $cart->updateItem($id, new Varien_Object($params));
            } else {
                $request   = new Varien_Object($params);
                $productId = $quoteItem->getProduct()->getId();
                $product   = Mage::getModel('catalog/product')
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
                if (is_string($item)) {
                    Mage::throwException($item);
                }

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
            /* if there was any exceptions - clear session but set
              a quote id to force a qoute object reinitializtion (from persistence layer) */
            $chSession = $cart->getCheckoutSession();
            $quoteId   = $chSession->getQuote()->getId();
            $chSession->clear();
            $chSession->setQuoteId($quoteId);
            $success_param = array();
            if ($quoteItem) {
                if ($quoteItem->getProduct()->getTypeInstance(true)->getSpecifyOptionMessage() == $e->getMessage()) {
                    $all_params = $params['super_attribute'];

                    $productCollection = $quoteItem->getProduct()->getTypeInstance(true)->getUsedProductCollection($quoteItem->getProduct());

                    foreach ($all_params as $attribute_id => $value) {
                        $tmp_params                = $success_param;
                        $tmp_params[$attribute_id] = $value;
                        $productObject             = $quoteItem->getProduct()->getTypeInstance(true)->getProductByAttributes($tmp_params, $quoteItem->getProduct());
                        if ($productObject && $productObject->getId()) {
                            $success_param[$attribute_id] = $value;
                            $productCollection->addAttributeToFilter($attribute_id, $value);
                        } else {

                            $result['update_attribute'] = $attribute_id;

                            $attribute_data = array();
                            $attribute      = null;
                            $product        = Mage::getModel('catalog/product')->load($quoteItem->getProduct()->getId());
                            $product->getTypeInstance(true)->getUsedProductAttributeIds($product);
                            $usedAttributes = $product->getData('_cache_instance_used_attributes');

                            foreach ($usedAttributes as $key => $_arrtibute) {
                                if ($key == $attribute_id) {
                                    $attribute = $_arrtibute;
                                    break;
                                }
                            }

                            foreach ($productCollection as $_product) {
                                $_product = Mage::getModel('catalog/product')->load($_product->getId());
                                if ($_product->isSaleable()) {
                                    $_key = $_product->getData($attribute->getProductAttribute()->getAttributeCode());

                                    foreach ($attribute->getPrices() as $_v) {
                                        if ($_v['value_index'] == $_key) {
                                            $attribute_data[count($attribute_data) + 1] = array('id' => $_key, 'label' => $_v['label']);
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
            $result['choosetext']    = Mage::helper('catalog')->__('Choose an Option...');
            $result['success_param'] = $success_param;
            $result['error']         = true;
            $result['message']       = $e->getMessage();
        } catch (Exception $e) {
            $result['error']   = true;
            $result['message'] = $e->getMessage();
            Mage::logException($e);
        }

        if (!$result['error']) {

            if ($total = $this->getCartTolal()) {
                $result['total'] = $total;
            }

            if ($shipping = $this->getCartShipping()) {
                $result['shipping'] = $shipping;
            }
            if (!$cart->getQuote()->validateMinimumAmount()) {
                $minimumAmount     = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));
                $result['message'] = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description')
                    : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);
            }

        }

        $result['items_html'] = $this->getCartItems();
        $result['cart']       = Mage::getModel('gomage_procart/observer')->getCartSidebar();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function getProductListAction()
    {
        $result       = array();
        $product_list = array();
        if ($ids = $this->getRequest()->getParam('product_ids')) {
            /* @var $helper GoMage_Procart_Helper_Data */
            $helper = Mage::helper('gomage_procart');

            $ids = array_unique(explode(',', $ids));

            foreach ($ids as $_product) {
                $product = Mage::getModel('catalog/product')->load($_product);

                if (!isset($product_list[$product->getId()])) {
                    $product_list[$product->getId()] = $helper->getProcartProductData($product);
                }
                if ($product->isComposite()) {

                    $ti = $product->getTypeInstance(true);
                    foreach ($ti->getChildrenIds($product->getId()) as $groupIds) {

                        foreach ($groupIds as $id) {
                            $childProduct = Mage::getModel('catalog/product')->load($id);

                            if (!isset($product_list[$childProduct->getId()])) {
                                $product_list[$childProduct->getId()] = $helper->getProcartProductData($childProduct, false, $product->getId());
                            } else {
                                $product_list[$childProduct->getId()]['parent_id'] = $product->getId();
                                $product_list[$childProduct->getId()]['min_qty']   = 0;
                            }
                        }
                    }
                }
                $bundle_selection_hash = array();
                if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    $selectionCollection = $helper->getBundleProductSelections($product);
                    foreach ($selectionCollection as $selection) {
                        $data                                                       = $helper->getProcartProductData($selection);
                        $data['selection_qty']                                      = $selection->getData('selection_qty');
                        $data['selection_can_change_qty']                           = $selection->getData('selection_can_change_qty');
                        $bundle_selection_hash[$selection->getData('selection_id')] = $data;
                    }
                }
            }

        }
        if (!empty($bundle_selection_hash)) {
            $result['bundle_selection_hash'] = $bundle_selection_hash;
        }
        $result['product_list'] = $product_list;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function removewishlistitemAction()
    {
        $result   = array();
        $wishlist = Mage::getModel('wishlist/wishlist')
            ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
        if ($wishlist) {
            $id   = (int)$this->getRequest()->getParam('item');
            $item = Mage::getModel('wishlist/item')->load($id);

            if ($item->getWishlistId() == $wishlist->getId()) {
                try {
                    $item->delete();
                    $wishlist->save();
                    Mage::helper('wishlist')->calculate();
                    if (Mage::helper('gomage_procart')->isEnterprise()) {
                        $result['top_links'] = Mage::helper('gomage_procart/blocks')->getWishlistTopLink();
                    } else {
                        $result['top_links'] = Mage::getModel('gomage_procart/observer')->getTopLinks();
                    }

                    $result['wishlist'] = Mage::getModel('gomage_procart/observer')->getWishlistSidebar();
                    $result['success']  = true;
                } catch (Mage_Core_Exception $e) {
                    $result['success'] = false;
                    $result['message'] = $this->__('An error occurred while deleting the item from wishlist: %s', $e->getMessage());

                }
                catch (Exception $e) {
                    $result['success'] = false;
                    $result['message'] = $this->__('An error occurred while deleting the item from wishlist.');
                }
            }
        } else {
            $result['success'] = false;
            $result['message'] = $this->__('An error occurred while deleting the item from wishlist.');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function movetowishlistAction()
    {
        $wishlist = Mage::getModel('wishlist/wishlist')
            ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);

        $session = Mage::getSingleton('checkout/session');
        $cart    = Mage::getSingleton('checkout/cart');
        $item    = null;
        $item_id = (int)$this->getRequest()->getParam('item_id');
        if ($item_id) {
            $item = $cart->getQuote()->getItemById($item_id);
        }

        try {
            if ($wishlist && $item) {
                $helper = Mage::helper('gomage_procart');
                if ($helper->getIsAnymoreVersion(1, 5)) {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    $wishlist->addNewItem($product);
                } else {
                    $wishlist->addNewItem($item->getProductId());
                }
                $wishlist->save();
                Mage::helper('wishlist')->calculate();
                $message = $this->__('%1$s has been added to your wishlist.', $item->getProduct()->getName());
                $session->addSuccess($message);

                $cart->getQuote()->removeItem($item_id);
                $cart->save();
                $session->setCartWasUpdated(true);
            } else {
                $session->addError($this->__('An error occurred while adding item to wishlist.'));
            }
        } catch
        (Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist.'));
        }

        Mage::getModel('gomage_procart/observer')->WishlistAddFromCart(false);

    }

}