<?php

/**
 * GoMage Procart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2016 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use
 * @version      Release: 2.2.0
 * @since        Class available since Release 2.2
 */
 
class GoMage_Procart_Block_Checkout_Links extends Mage_Checkout_Block_Links
{
	public function addCartLink()
	{
		$helper = Mage::helper('gomage_procart');
		
		if (!$helper || !$helper->isProCartEnable()) {
			return parent::addCartLink();
		}
		
		$parentBlock = $this->getParentBlock();
		
		if ($parentBlock && Mage::helper('core')->isModuleOutputEnabled('Mage_Checkout')) {
			$count = $this->getSummaryQty() 
				? $this->getSummaryQty()
					: $this->helper('checkout/cart')->getSummaryCount();
			
			if ($count == 1) {
				$text = $this->__('My Cart (%s item)', $count);
			} elseif ($count > 0) {
				$text = $this->__('My Cart (%s items)', $count);
			} else {
				$text = $this->__('My Cart');
			}
			
			$parentBlock->removeLinkByUrl($this->getUrl('checkout/cart'));
			
			$before		= '';
			$sidebar	= $parentBlock->getLayout()
				->createBlock('checkout/cart_sidebar')
				->setName('cart_sidebar')
				->setTemplate('checkout/cart/sidebar.phtml')
				->toHtml();
			$after		= '<div id="gpc-minicart" style="display: none;">' . $sidebar . '</div>';
			
			$parentBlock->addLink($text, '', $text, true, array(), 50, null, 'class="top-link-cart skip-link skip-cart" onclick="$(\'gpc-minicart\').toggle(); return false;"', $before, $after);
		}
		
		return $this;
	}
}