<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class Ecloud_Theme_CartController extends Mage_Checkout_CartController{

	public function addAction()
	{
	    $cart   = $this->_getCart();
	    $params = $this->getRequest()->getParams();

	    if($params['isAjax'] == 1){
	        $response = array();
	        try {
	        	/**************** FIX BUG WITH LOCALE ES_AR ********************/
	        	/*
	            if (isset($params['qty'])) {
	                $filter = new Zend_Filter_LocalizedToNormalized(
	                array('locale' => Mage::app()->getLocale()->getLocaleCode())
	                );
	                $params['qty'] = $filter->filter($params['qty']);
	            }*/
	            /***************************************************************/

	            $product = $this->_initProduct();
	            $related = $this->getRequest()->getParam('related_product');
	            /**
	             * Check product availability
	             */
	            if (!$product) {
	                $response['status'] = 'ERROR';
	                $response['message'] = $this->__('Unable to find Product ID');
	            }

	            $cart->addProduct($product, $params);
	            if (!empty($related)) {
	                $cart->addProductsByIds(explode(',', $related));
	            }

	            $cart->save();

	            $this->_getSession()->setCartWasUpdated(true);

	            /**
	             * @todo remove wishlist observer processAddToCart
	             */
	            Mage::dispatchEvent('checkout_cart_add_product_complete',
	            array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
	            );

	            if (!$cart->getQuote()->getHasError()){
	                $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
	                $response['status'] = 'SUCCESS';
	                $response['message'] = $message;
	                //New Code Here
	                $this->loadLayout();
	                $toplink = $this->getLayout()->getBlock('top.links')->toHtml();
	                $sidebar = $this->getLayout()->getBlock('minicart_head')->toHtml();

	                Mage::register('referrer_url', $this->_getRefererUrl());

	                $response['toplink'] = $toplink;
	                $response['sidebar'] = $sidebar;
	            }
	        } catch (Mage_Core_Exception $e) {
	            $msg = "";
	            if ($this->_getSession()->getUseNotice(true)) {
	                $msg = $e->getMessage();
	            } else {
	                $messages = array_unique(explode("\n", $e->getMessage()));
	                foreach ($messages as $message) {
	                    $msg .= $message.'<br/>';
	                }
	            }
	            $response['status'] = 'ERROR';
	            $response['message'] = $msg;
	        } catch (Exception $e) {
	            $response['status'] = 'ERROR';
	            $response['message'] = $this->__('Cannot add the item to shopping cart.');
	            Mage::logException($e);
	        }
	        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));

	        return;
	    }else{
	        return parent::addAction();
	    }
	}

	/**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
        	/**************** FIX BUG WITH LOCALE ES_AR ********************/
	       	/*
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }*/
            /****************************************************************/

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->escapeHtml($item->getProduct()->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update the item.'));
            Mage::logException($e);
            $this->_goBack();
        }
        $this->_redirect('*/*');
    }

	/**
     * Update shopping cart data action
     */
    public function updatePostAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }

        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');

        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            default:
                $this->_updateShoppingCart();
        }

        $this->_goBack();
    }

	/**
     * Update customer's shopping cart
     */
    protected function _updateShoppingCart()
    {

    	Mage::log("entra a ecloudtheme",null,"cart.log");

        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                /*$filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }*/
                $cart = $this->_getCart();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();
            }
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
    }

    /**
     * Minicart ajax update qty action
     */
    public function ajaxUpdateAction()
    {
        if (!$this->_validateFormKey()) {
            Mage::throwException('Invalid form key');
        }
        $id = (int)$this->getRequest()->getParam('id');
        $qty = $this->getRequest()->getParam('qty');
        $result = array();
        if ($id) {
            try {
                $cart = $this->_getCart();
                /**************** FIX BUG WITH LOCALE ES_AR ********************/
	        	/*
                if (isset($qty)) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $qty = $filter->filter($qty);
                }*/
                /****************************************************************/

                $quoteItem = $cart->getQuote()->getItemById($id);
                if (!$quoteItem) {
                    Mage::throwException($this->__('Quote item is not found.'));
                }
                if ($qty == 0) {
                    $cart->removeItem($id);
                } else {
                    $quoteItem->setQty($qty)->save();
                }
                $this->_getCart()->save();

                $this->loadLayout();
                $result['content'] = $this->getLayout()->getBlock('minicart_content')->toHtml();

                $result['qty'] = $this->_getCart()->getSummaryQty();

                if (!$quoteItem->getHasError()) {
                    $result['message'] = $this->__('Item was updated successfully.');
                } else {
                    $result['notice'] = $quoteItem->getMessage();
                }
                $result['success'] = 1;
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['error'] = $this->__('Can not save item.');
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}