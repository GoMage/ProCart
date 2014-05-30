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
class GoMage_Procart_Model_Observer
{

    static public function checkK($event)
    {
        $key = Mage::getStoreConfig('gomage_activation/procart/key');
        Mage::helper('gomage_procart')->a($key);
    }

    public function addToCart($event)
    {
        $request = $event->getRequest();
        if ($request->getParam('gpc_add') == 1) {
            $result = array();
            $result['success'] = true;

            $result['cart'] = $this->getCartSidebar();
            $result['top_links'] = $this->getTopLinks();

            $result['prod_name'] = $event->getProduct()->getName();
            $result['qty'] = $event->getRequest()->getParam('qty');
            $result['product_id'] = $event->getProduct()->getId();

            $result['minicart_content'] = $this->getMinicart();
            $result['total_qty'] = Mage::getSingleton('checkout/cart')->getSummaryQty();

            $result['base_cart'] = $this->getBaseCartItems();
            /* @var $block_helper GoMage_Procart_Helper_Blocks */
            $block_helper = Mage::helper('gomage_procart/blocks');
            $layout = Mage::getSingleton('core/layout');
            $result['total'] = $layout->createBlock('checkout/cart_totals', 'checkout.cart.totals')
                ->setTemplate('checkout/cart/totals.phtml')
                ->renderView();

            $result['shipping'] = $layout->createBlock('checkout/cart_shipping', 'checkout.cart.shipping')
                ->setTemplate('checkout/cart/shipping.phtml')
                ->renderView();

            if (($request->getParam('gpc_crosssell') == 1)) {
                $result['crosssell'] = $block_helper->getCrosssell();
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

            //redirect to PayPal
            if ($request->getParam('return_url')) {
                $result['return_url'] = $request->getParam('return_url');
            }

            $helper = Mage::helper('gomage_procart');
            if ($helper->getIsUltimentoTheme()) {
                $layout->createBlock('page/html_head', 'head');
                $result['ultcustomernav'] = $layout->createBlock('catalog/navigation_ultcustomernav', 'root.ult.customernav')
                    ->setTemplate('catalog/navigation/ultcustomernav.phtml')
                    ->renderView();
            }

            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
            Mage::app()->getFrontController()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function getBaseCartItems()
    {
        $item_html = '';
        /* @var $block_helper GoMage_Procart_Helper_Blocks */
        $block_helper = Mage::helper('gomage_procart/blocks');
        $cart = $block_helper->getShoppingCartBlock();
        foreach ($cart->getItems() as $_item) {
            $item_html .= $cart->getItemHtml($_item);
        }

        return $item_html;
    }

    public function getWishlistSidebar()
    {
        $layout = Mage::getSingleton('core/layout');
        $block = $layout->createBlock('wishlist/customer_sidebar', 'wishlist_sidebar')
            ->setTemplate('wishlist/sidebar.phtml');

        $block->addPriceBlockTypeBundle("bundle", "bundle/catalog_product_price", "bundle/catalog/product/price.phtml");

        return $block->renderView();
    }

    public function updateItemOptions($event)
    {
        $request = $event->getRequest();
        if ($request->getParam('gpc_add') == 1) {
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

    public function getCartSidebar()
    {
        $layout = Mage::getSingleton('core/layout');
        $enterprise = Mage::helper('gomage_procart')->isEnterprise();
        $template = $enterprise ? 'checkout/cart/cartheader.phtml' : 'checkout/cart/sidebar.phtml';
        $cart_sidebar = $layout->createBlock('checkout/cart_sidebar', 'cart_sidebar')
            ->setTemplate($template)
            ->addItemRender('simple', 'checkout/cart_item_renderer', 'checkout/cart/sidebar/default.phtml')
            ->addItemRender('configurable', 'checkout/cart_item_renderer_configurable', 'checkout/cart/sidebar/default.phtml')
            ->addItemRender('grouped', 'checkout/cart_item_renderer_grouped', 'checkout/cart/sidebar/default.phtml')
            ->addItemRender('bundle', 'bundle/checkout_cart_item_renderer', 'checkout/cart/sidebar/default.phtml');

        $addtocart_paypal = $layout->createBlock('paypal/express_shortcut', 'extra_actions')
            ->setTemplate('paypal/express/shortcut.phtml');
        $cart_sidebar->append('extra_actions');

        return $cart_sidebar->renderView();
    }

    public function getMinicart()
    {
        $layout = Mage::getSingleton('core/layout');
        $layout->getUpdate()->load('default');
        $layout->generateXml()->generateBlocks();

        $minicart = $layout->getBlock('minicart_content');
        if ($minicart) {
            return $minicart->toHtml();
        }
        return '';
    }

    public function getTopLinks()
    {
        $layout = Mage::getSingleton('core/layout');

        $top_links = $layout->createBlock('page/template_links', 'gcp.top.links');

        $checkout_cart_link = $layout->createBlock('checkout/links', 'checkout_cart_link');
        $wishlist_link = $layout->createBlock('wishlist/links', 'wishlist_link');

        $top_links->setChild('checkout_cart_link', $checkout_cart_link);
        $top_links->setChild('wishlist_link', $wishlist_link);

        if (method_exists($top_links, 'addLinkBlock')) {
            $top_links->addLinkBlock('checkout_cart_link');
            $top_links->addLinkBlock('wishlist_link');
        }

        $checkout_cart_link->addCartLink();

        return $top_links->renderView();
    }

    public function showConfigurableParams($event)
    {
        $request = $event->getControllerAction()->getRequest();
        if ($request->getParam('gpc_show_configurable') == 1) {
            /* @var $form GoMage_Procart_Block_Product_Configurable_Form */
            $form = Mage::getBlockSingleton('gomage_procart/product_configurable_form');
            /* @var $product Mage_Catalog_Model_Product */
            $product = Mage::registry('current_product');
            $form->setProduct($product);
            /* @var $layout Mage_Core_Model_Layout */
            $layout = Mage::getSingleton('core/layout');
            /* @var $tierPrices Mage_Catalog_Block_Product_View */
            $tierPrices = Mage::getBlockSingleton('catalog/product_view');
            $tierPrices->setTemplate('catalog/product/view/tierprices.phtml');
            $product_bottom_options_wrapper = $layout->getBlock('product.info.options.wrapper.bottom');
            $form->setChild('product_tierprices', $tierPrices);

            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $form->setChild('gcp_product_bundle_prices', 'bundle.prices');
                $form->setChild('gcp_product_info_bundle', 'product.info.bundle');
                $form->setChild('gcp_product_bundle_addtocart', 'product.info.addtocart');
            }
            $ti = $product->getTypeInstance();
            if (method_exists($ti, 'isGiftCard') && $ti->isGiftCard()) {
                $form->setChild('gcp_product_giftcard', 'product.info.giftcard');
            }

            $form->setChild('gcp_configurable_options', 'product.info.options.wrapper');
            $form->setChild('gcp_configurable_options_bottom', $product_bottom_options_wrapper);

            $result = array();
            $result['success'] = true;
            $result['form'] = $form->renderView();
            $result['qty'] = $request->getParam('qty');
            $result['product_id'] = $product->getId();

            $event->getControllerAction()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function addProductWithError($event)
    {

        $request = $event->getControllerAction()->getRequest();
        $product = Mage::getModel('catalog/product')->load($request->getParam('id'));

        if ($product && in_array($product->getTypeId(), array(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE))) {

            if (Mage::helper('gomage_procart')->isProCartEnable() && Mage::getStoreConfig('gomage_procart/qty_settings/product_page') != GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO) {

                if (Mage::getSingleton('core/session')->getData('gpc_from_compare')) {
                    Mage::getSingleton('core/session')->setData('gpc_from_compare', false);
                    return $this;
                }

                $messages = array_merge(Mage::getSingleton('checkout/session')->getMessages(false)->getItems(Mage_Core_Model_Message::ERROR),
                    Mage::getSingleton('checkout/session')->getMessages(false)->getItems(Mage_Core_Model_Message::NOTICE)
                );

                $message_text = '';

                foreach ($messages as $message) {
                    $message_text .= str_replace('""', '"' . $product->getName() . '"', $message->getText());
                }

                if ($message_text) {
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


    public function showGroupedParams($event)
    {
        $request = $event->getEvent()->getControllerAction()->getRequest();
        if (($request->getParam('gpc_add') == 1) && ($gpc_prod_id = $request->getParam('gpc_prod_id'))) {
            $product = Mage::getModel('catalog/product')->load($gpc_prod_id);
            if ($product->isGrouped()) {
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

                $addtocart_paypal = $layout->createBlock('paypal/express_shortcut', 'product.info.addtocart.paypal')
                    ->setTemplate('paypal/express/shortcut.phtml')
                    ->setIsInCatalogProduct(1);
                $info_addtocart->append('product.info.addtocart.paypal');

                $product_options_bottom->append('product.info.addtocart');

                $form->setChild('product_options_wrapper_bottom', $product_options_bottom);

                $result['form'] = $form->renderView();
                $result['qty'] = $request->getParam('qty');

                $event->getEvent()->getControllerAction()->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                $event->getEvent()->getControllerAction()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            } elseif ($product->isConfigurable()) {
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

    public function disableShoppingCart($event)
    {
        if (Mage::getSingleton('checkout/session')->getData('gomage_procart_updateitem')) {
            Mage::getSingleton('checkout/session')->setData('gomage_procart_updateitem', false);
            $event->getEvent()->getControllerAction()->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $event->getEvent()->getControllerAction()->getResponse()->setBody(Mage::getSingleton('checkout/session')->getData('gomage_procart_result'));
            Mage::getSingleton('checkout/session')->setData('gomage_procart_result', null);
            return $this;
        }

        if (Mage::helper('gomage_procart')->isShoppingCartDisable()) {
            $event->getEvent()->getControllerAction()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage'));
        }
    }

    public function deleteCartItem($event)
    {
        $request = $event->getEvent()->getControllerAction()->getRequest();
        if (($request->getParam('gpc_sedebar_delete') == 1 || $request->getParam('gpc_cart_delete') == 1) && ($id = $request->getParam('id'))) {

            $helper = Mage::helper('gomage_procart');
            /* @var $block_helper GoMage_Procart_Helper_Blocks */
            $block_helper = Mage::helper('gomage_procart/blocks');
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

            if (!$result['error'] && $request->getParam('gpc_sedebar_delete') == 1) {
                $result['cart'] = $this->getCartSidebar();
            }

            if (!$result['error'] && $request->getParam('gpc_cart_delete') == 1) {
                if (!Mage::helper('checkout/cart')->getCart()->getItemsCount()) {
                    $result['redirect'] = Mage::getUrl('checkout/cart');
                }
                $result['item_id'] = $id;
                $result['total'] = $layout->createBlock('checkout/cart_totals', 'checkout.cart.totals')
                    ->setTemplate('checkout/cart/totals.phtml')
                    ->renderView();

                $result['shipping'] = $layout->createBlock('checkout/cart_shipping', 'checkout.cart.shipping')
                    ->setTemplate('checkout/cart/shipping.phtml')
                    ->renderView();

                $result['crosssell'] = $block_helper->getCrosssell();
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
                $result['top_links'] = $this->getTopLinks();

                if ($product->getStockItem()->getManageStock()) {
                    $result['product_id'] = $product->getId();
                    $result['max_qty'] = intval($product->getStockItem()->getQty());
                }
            }

            $event->getEvent()->getControllerAction()->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $event->getEvent()->getControllerAction()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        }
    }

    public function noCookies($event)
    {
        $helper = Mage::helper('gomage_procart');
        if ($helper->isProCartEnable() && $event->getEvent()->getAction()->getRequest()->getParam('gpc_add')) {
            $result = array();
            $result['success'] = false;
            $result['error'] = true;
            $result['message'] = $helper->__('Please enable cookies in your web browser to continue.');
            $result['redirect'] = Mage::getUrl('core/index/noCookies');
            echo Mage::helper('core')->jsonEncode($result);
            exit();
        }
    }

    public function RedirectToWishlist($event)
    {
        $request = $event->getEvent()->getControllerAction()->getRequest();
        if ($request->getParam('gpc_wishlist_add') == 1) {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                $result = array();
                Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current' => true)));
                $result['redirect'] = Mage::helper('customer')->getLoginUrl();
                echo Mage::helper('core')->jsonEncode($result);
                exit();
            }
        }
    }

    public function WishlistChange($event)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if ($request->getParam('gpc_wishlist_add') == 1) {
            Mage::helper('wishlist')->calculate();
            Mage::unregister('wishlist');
            Mage::unregister('shared_wishlist');
            Mage::unregister('_helper/wishlist');
            $result = array();
            $result['prod_name'] = '';
            if ($event->getProduct()) {
                $result['prod_name'] = $event->getProduct()->getName();
            } elseif ($product_id = $request->getParam('product')) {
                $result['prod_name'] = Mage::getModel('catalog/product')->load($product_id)->getName();
            }
            if (Mage::helper('gomage_procart')->isEnterprise()) {
                $result['top_links'] = Mage::helper('gomage_procart/blocks')->getWishlistTopLink();
            } else {
                $result['top_links'] = $this->getTopLinks();
            }
            $result['wishlist'] = $this->getWishlistSidebar();
            echo Mage::helper('core')->jsonEncode($result);
            exit();
        }
    }

    public function WishlistAddFromCart($event)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if ($request->getParam('gpc_wishlist_add') == 1) {
            $result = array();
            $messages = Mage::getSingleton('checkout/session')->getMessages(true)->getItems(Mage_Core_Model_Message::SUCCESS);
            $message_text = '';
            foreach ($messages as $message) {
                $message_text .= html_entity_decode($message->getText()) . PHP_EOL;
            }
            if ($message_text) {
                if (Mage::helper('gomage_procart')->isEnterprise()) {
                    $result['top_links'] = Mage::helper('gomage_procart/blocks')->getWishlistTopLink();
                } else {
                    $result['top_links'] = $this->getTopLinks();
                }
                $result['wishlist'] = $this->getWishlistSidebar();
                $result['items_html'] = $this->getBaseCartItems();

                $layout = Mage::getSingleton('core/layout');
                $result['total'] = $layout->createBlock('checkout/cart_totals', 'checkout.cart.totals')
                    ->setTemplate('checkout/cart/totals.phtml')
                    ->renderView();

                if (!Mage::helper('checkout/cart')->getCart()->getItemsCount()) {
                    $result['redirect'] = Mage::getUrl('checkout/cart');
                }
                $result['message'] = $message_text;

                /* @var $block_helper GoMage_Procart_Helper_Blocks */
                $block_helper = Mage::helper('gomage_procart/blocks');
                $result['crosssell'] = $block_helper->getCrosssell();

            } else {
                Mage::getSingleton('checkout/session')->getMessages(true)->getItems(Mage_Core_Model_Message::ERROR);
            }
            echo Mage::helper('core')->jsonEncode($result);
            exit();
        }
    }

    public function CompareAdd($event)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if ($request->getParam('gpc_compare_add') == 1) {
            $result = array();
            $result['prod_name'] = $event->getProduct()->getName();
            Mage::getSingleton('catalog/session')->getMessages(true);
            Mage::helper('catalog/product_compare')->calculate();
            $layout = Mage::getSingleton('core/layout');

            $result['compare_products'] = $layout->createBlock('catalog/product_compare_sidebar', 'catalog.compare.sidebar')
                ->setTemplate('catalog/product/compare/sidebar.phtml')
                ->renderView();

            echo Mage::helper('core')->jsonEncode($result);
            exit();
        }
    }

    public function CompareRemove($event)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if (($request->getParam('gpc_remove_compare') == 1) &&
            ($request->getParam('isAjax') == 1)
        ) {
            $result = array();

            Mage::helper('catalog/product_compare')->calculate();
            $layout = Mage::getSingleton('core/layout');

            $result['compare_products'] = $layout->createBlock('catalog/product_compare_sidebar', 'catalog.compare.sidebar')
                ->setTemplate('catalog/product/compare/sidebar.phtml')
                ->renderView();

            echo Mage::helper('core')->jsonEncode($result);
            exit();
        }
    }

    public function AddProductFromWishlist($event)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if ($request->getParam('gpc_add') == 1) {
            $itemId = $request->getParam('item');
            if ($itemId) {
                $item = Mage::getModel('wishlist/item')->load($itemId);
                if (!$item->getId()) {
                    $result = array();
                    $result['success'] = false;
                    $result['redirect'] = Mage::helper('checkout/cart')->getCartUrl();
                    echo Mage::helper('core')->jsonEncode($result);
                    exit();

                } else {
                    $messages = Mage::getSingleton('catalog/session')->getMessages(false)->getItems(Mage_Core_Model_Message::NOTICE);
                    $message_text = '';
                    foreach ($messages as $message) {
                        $message_text .= str_replace('""', '"' . $item->getProduct()->getName() . '"', $message->getText());
                    }

                    $messages = Mage::getSingleton('wishlist/session')->getMessages(false)->getItems(Mage_Core_Model_Message::ERROR);
                    foreach ($messages as $message) {
                        $message_text .= str_replace('""', '"' . $item->getProduct()->getName() . '"', $message->getText());
                    }

                    if ($message_text) {
                        Mage::getSingleton('catalog/session')->getMessages(true);
                        Mage::getSingleton('wishlist/session')->getMessages(true);
                        $result['success'] = false;
                        $result['message'] = $message_text;
                        echo Mage::helper('core')->jsonEncode($result);
                        exit();
                    }
                }
            }
        }
    }

    public function updateItemOptionsWithError($event)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if ($request->getParam('gpc_add') == 1) {

            $product = Mage::getModel('catalog/product')->load($request->getParam('product', 0));
            $product_name = ($product->getId() ? $product->getName() : '');


            $messages = array_merge(Mage::getSingleton('checkout/session')->getMessages(false)->getItems(Mage_Core_Model_Message::ERROR), Mage::getSingleton('checkout/session')->getMessages(false)->getItems(Mage_Core_Model_Message::NOTICE));
            $message_text = '';
            foreach ($messages as $message) {
                $message_text .= str_replace('""', '"' . $product_name . '"', $message->getText());
            }
            if ($message_text) {
                Mage::getSingleton('checkout/session')->getMessages(true);
                $result['success'] = false;
                $result['message'] = $message_text;
                echo Mage::helper('core')->jsonEncode($result);
                exit();
            }
        }
    }

    public function beforeAddToCart($event)
    {
        $result = array();
        $params = Mage::app()->getFrontController()->getRequest()->getParams();
        if (isset($params['gpc_add']) && ($params['gpc_add'] == 1)) {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $result['qty'] = $filter->filter($params['qty']);
                /* @var $product Mage_Catalog_Model_Product */
                $product = $this->_initProduct()->getStockItem();
                $this->checkProductQty($result['qty'], $product);
            } else {
                if (isset($params['super_group'])) {
                    foreach ($params['super_group'] as $productId => $qty) {
                        $product = Mage::getModel('catalog/product')->load($productId)->getStockItem();
                        $product->setIsChildItem(true);
                        $this->checkProductQty($qty, $product);
                    }
                }
            }
        }
    }

    private function checkProductQty($qty, $productStockItem)
    {
        $res = $productStockItem->checkQtyIncrements($qty);
        if ($res->getHasError()) {
            $result['qty'] = $qty;
            $result['success'] = false;
            $result['message'] = $res->getMessage();
            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
            echo Mage::helper('core')->jsonEncode($result);
            exit;
        }
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    private function _initProduct()
    {
        $productId = (int)Mage::app()->getFrontController()->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Handle differently (ajax-like) every error that occurs when adding to cart
     *
     * @param $event
     */
    public function handleAddToCartErrors($event)
    {
        $params = Mage::app()->getFrontController()->getRequest()->getParams();

        if (Mage::helper('gomage_procart')->isProCartEnable()) {

            if (isset($params['gpc_from_compare']) && ($params['gpc_from_compare'] == 1)) {
                Mage::getSingleton('core/session')->setData('gpc_from_compare', true);
            }

            if (isset($params['gpc_add']) && ($params['gpc_add'] == 1)) {
                $messages = Mage::getSingleton('checkout/session')->getMessages(false)->getItems(Mage_Core_Model_Message::ERROR);
                $message_text = '';

                foreach ($messages as $message) {
                    $message_text .= html_entity_decode($message->getText()) . PHP_EOL;
                }

                if ($message_text) {
                    Mage::getSingleton('checkout/session')->getMessages(true);
                    $result = array();
                    $result['success'] = false;
                    $result['con'] = $event->getControllerAction()->getFullActionName();
                    $result['message'] = $message_text;
                    echo Mage::helper('core')->jsonEncode($result);
                    exit();
                }
            }

        }
    }
}