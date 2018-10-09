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

class Ecloud_Theme_Block_Homeproducts extends Mage_Catalog_Block_Product_List
{

    public function getProducts()
    {   
        $category_id = $this->getData('category');
        $title       = $this->getData('title');

        if ($category_id) {
            $layer = $this->getLayer();
            $category = Mage::getModel('catalog/category')->load($category_id);
            if($category->getData('entity_id') != ""){
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
                $this->addModelTags($category);

                $this->_productCollection = $layer->getProductCollection();
                if (sizeof($this->_productCollection) >= 1){
                    // $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());
                    if ($origCategory) {
                        $layer->setCurrentCategory($origCategory);
                    }

                    $data = array(
                        'title'             =>$title,
                        'productCollection' =>$this->_productCollection
                    );
                    return $data;
                }
            }
        }
    }
}
