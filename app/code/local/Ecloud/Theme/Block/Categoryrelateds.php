<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Ecloud_Theme
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Ecloud_Theme_Block_Categoryrelateds extends Mage_Catalog_Block_Product_List
{

    public function getRelatedProducts()
    {   
        $layer = $this->getLayer();
        $related_category = $layer->getCurrentCategory()->getCategoryRelated();

        if ($related_category!='') {
            $category = Mage::getModel('catalog/category')->load($related_category);
            if (!empty($category->getData())) {
                $layer->setCurrentCategory($category);
                $this->_productCollection = $layer->getProductCollection()->setPageSize(4);
                if (sizeof($this->_productCollection) > 1) {
                    return $this->_productCollection;
                } 
            }
            return false;
        }
        return false;
    }
}

