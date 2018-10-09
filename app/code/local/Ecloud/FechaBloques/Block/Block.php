<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Cms
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Cms block content block
 *
 * @category   Mage
 * @package    Mage_Cms
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Ecloud_FechaBloques_Block_Block extends Mage_Core_Block_Abstract
{
    /**
     * Prepare Content HTML
     *
     * @return string
     */
    protected function _toHtml()
    {

        $blockId = $this->getBlockId();
        $html = '';
        $show_block = true;
        if ($blockId) {
            $block = Mage::getModel('cms/block')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($blockId);

            //$fecha_actual = strtotime(date('Y-m-d H:i:s'));
            $fecha_actual = Mage::getModel('core/date')->date('Y-m-d H:i:s');
            $fecha_actual = strtotime($fecha_actual);
        
            if($block->getFechaDesde() != ''){
                $fecha_desde = strtotime($block->getFechaDesde());
                if($block->getFechaHasta() != ''){
                    $fecha_hasta = strtotime($block->getFechaHasta());
                    if(!($fecha_desde <= $fecha_actual && $fecha_actual <= $fecha_hasta)){
                        $show_block = false;
                    }
                }else{
                    if(!($fecha_desde <= $fecha_actual)){
                        $show_block = false;
                    }
                }
            }else{
                if($block->getFechaHasta() != ''){
                    $fecha_hasta = strtotime($block->getFechaHasta());
                    if(!($fecha_actual <= $fecha_hasta)){
                        $show_block = false;
                    }
                }
            }

            if ($block->getIsActive() && $show_block) {
                /* @var $helper Mage_Cms_Helper_Data */
                $helper = Mage::helper('cms');
                $processor = $helper->getBlockTemplateProcessor();
                $html = $processor->filter($block->getContent());
            }
        }
        return $html;
    }
}
