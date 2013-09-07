<?php

/**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.0
 * @since        Class available since Release 1.3
 */
class GoMage_Procart_Helper_Qty
{
    const QTY_TEMPLATE_CATEGORY       = 'category';
    const QTY_TEMPLATE_DEALS          = 'deals';
    const QTY_TEMPLATE_CART_PAGE      = 'cart_page';
    const QTY_TEMPLATE_CART_SIDEBAR   = 'cart_sidebar';
    const QTY_TEMPLATE_PRODUCT        = 'product';
    const QTY_TEMPLATE_CROSSSEL       = 'crosssell_prods';
    const QTY_TEMPLATE_UPSEL          = 'upsell_prods';
    const QTY_TEMPLATE_CATEGORY_POPUP = 'category_page_popup';

    /**
     * Retrieve specified ($templateType) qty template based on configuration ($configType)
     *
     * @param $configType           a config key to use configuration settings from
     * @param $templateType         a templates key to use template from
     * @return Mage_Core_Block_Template
     * @throw Exception
     */
    public function getQtyTemplate($configType, $templateType = null)
    {
        $this->checkType($configType);
        $templateType = $templateType ? $templateType : $configType;
        $this->checkType($templateType);
        if ($configType == self::QTY_TEMPLATE_DEALS) {
            return $this->getQtyTemplate(self::QTY_TEMPLATE_CATEGORY, self::QTY_TEMPLATE_DEALS);
        }
        $template = Mage::app()->getLayout()->createBlock('core/template', 'gomage.procart.qty.' . $configType . '.template');


        switch (Mage::getStoreConfig('gomage_procart/qty_settings/' . $configType)) {
            case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_LEFT_RIGHT:
                $template->setTemplate('gomage/procart/' . $templateType . '/arrows/left_right.phtml');
                break;
            case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_TOP_BOTTOM:
                $template->setTemplate('gomage/procart/' . $templateType . '/buttons/top_bottom.phtml');
                break;
            case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_LEFT_RIGHT:
                $template->setTemplate('gomage/procart/' . $templateType . '/buttons/left_right.phtml');
                break;
            case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::SMALL_BUTTONS_RIGHT_TOP_BOTTOM:
                $template->setTemplate('gomage/procart/' . $templateType . '/buttons/small_right_top_bottom.phtml');
                break;
            case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_RIGHT:
                $template->setTemplate('gomage/procart/' . $templateType . '/buttons/right.phtml');
                break;
            case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO:
                $template->setTemplate('gomage/procart/' . $templateType . '/no.phtml');
                break;
            case GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_TOP_BOTTOM:
            default:
                $template->setTemplate('gomage/procart/' . $templateType . '/arrows/top_bottom.phtml');
        }

        return $template;
    }

    private function checkType($type)
    {
        if (!in_array($type, $this->getTemplateTypes())) {
            throw new Exception("Invalid type specified for the getTemplateHtml() method! Use one of constants defined in the class.");
        }
    }

    /**
     * Retrieve full class name for qty button/arrow
     *
     * @param $type         one of node names defined under groups/qty_settings/fields node in system.xml
     * @return string       body class name for qty buttons/arrows
     * @see                 GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview
     */
    public function getQtyClassNameByType($type)
    {
        $prefixes = array(
            self::QTY_TEMPLATE_CATEGORY       => 'gpc-arrbut-cat',
            self::QTY_TEMPLATE_PRODUCT        => 'gpc-arrbut-prodp',
            self::QTY_TEMPLATE_CART_SIDEBAR   => 'gpc-arrbut-mcb',
            self::QTY_TEMPLATE_CART_PAGE      => 'gpc-arrbut-cp',
            self::QTY_TEMPLATE_CROSSSEL       => 'gpc-arrbut-cross',
            self::QTY_TEMPLATE_UPSEL          => 'gpc-arrbut-upsell',
            self::QTY_TEMPLATE_CATEGORY_POPUP => 'gpc-arrbut-popup',
        );
        $suffixes = array(
            GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_TOP_BOTTOM              => '-arr-tb', //Arrows (Top/Bottom)
            GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_LEFT_RIGHT              => '-arr-lr', //Arrows (Left/Right)
            GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_TOP_BOTTOM             => '-btn-tb', //Buttons (Top/Bottom)
            GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_LEFT_RIGHT             => '-btn-lr', //Buttons (Left/Right)
            GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::SMALL_BUTTONS_RIGHT_TOP_BOTTOM => '-btn-right-small', //Small Buttons (Right, Top/Bottom)
            GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::BUTTONS_RIGHT                  => '-btn-right', //Buttons (Right)
            GoMage_Procart_Model_Adminhtml_System_Config_Source_Qtyview::ARROWS_NO                      => '', //do not show
        );

        $suff = null;
        if (isset($suffixes[Mage::getStoreConfig('gomage_procart/qty_settings/' . $type)])) {
            $suff = $suffixes[Mage::getStoreConfig('gomage_procart/qty_settings/' . $type)];
        }

        return empty($suff) ? '' : $prefixes[$type] . $suff;
    }

    public function getTemplateTypes()
    {
        $reflect = new ReflectionClass(get_class($this));
        return $reflect->getConstants();
    }
}