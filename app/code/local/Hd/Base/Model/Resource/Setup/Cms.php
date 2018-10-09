<?php
class Hd_Base_Model_Resource_Setup_Cms extends Hd_Base_Model_Resource_Setup
{
    protected $_pageDefaultData = array(
        'is_active' => '1',
        'sort_order'=> '0',
        'root_template' => 'one_column',
        'under_version_control' => '0',
    );
    
    protected $_blockDefaultData = array(
        'is_active' => '1',
    );
    
    /**************************************************************************/
    /****************************************************************** PAGES */
    
    public function setPage($identifier, $title, $content, $stores = null, $params = null )
    {
        // Stores Ids
        $storesIds = $this->_getStoresIds($stores);
        // Cms Page
        $page = $this->loadPage($identifier,$storesIds);
        // Default Data
        if (!$page->getId()) {
            $page
                ->addData($this->_pageDefaultData)
                ->setIdentifier($identifier)
                ->setStoreId($storesIds);
        }
        
        // New Data
        $page
            ->setTitle($title)
            ->setContent($content);
        
        if (isset($params)) {
            // Change Identifier
            if (isset($params['change_identifier'])) {
                $page->setIdentifier($params['change_identifier']);
                unset($params['change_identifier']);
            }
            // Change Stores
            if (isset($params['change_stores'])) {
                $page->setStoreId($this->_getStoresIds($params['change_stores']));
                unset($params['change_stores']);
            }
            foreach($params as $key => $value) {
                $page->setData($key,$value);
            }
        }
        $page->save();
        return $this;
    }
    
    /**
     * @param string $identifier
     * @param string|array $stores
     * @return Hd_Base_Model_Resource_Setup_Cms 
     */
    public function deletePage($identifier, $stores = null)
    {
        $storesIds = $this->_getStoresIds($stores);
        $pages = $this->loadPages($identifier,$storesIds);
        if (!$count = $pages->count()) {
            $errMsg = sprintf('Can not find CMS Pages with identifier= "%s" assigned to stores: %s',$count,$identifier,implode(',',(array)$storesIds));
            Mage::logException(new Exception($errMsg));
        }
        foreach ($pages as $page)
        {
            $page->load($page->getPageId());
            if (count(array_intersect($storesIds, $page->getStoreId()))) {
                $page->delete();
            }
        }
        return $this;
    }
    
    /**
     * @return \Hd_Base_Model_Resource_Setup_Cms 
     */
    public function deleteAllPages()
    {
        $collection = Mage::getModel('cms/page')->getCollection();
        foreach($collection as $page){
            $page->delete();
        }
        return $this;
    }
    
    
    /**
     * @param string $identifier
     * @param array $stores
     * @param bool $withAdmin
     * @return Mage_Cms_Model_Page 
     */
    public function loadPage($identifier, $stores = null, $withAdmin = false)
    {
        $withAdmin = ($withAdmin || in_array('0', $stores)) ? true : false;
        $collection = $this->loadPages($identifier, $stores, $withAdmin);
        $count = $collection->count();
        if ($count > 1) {
            $errMsg = sprintf('There are %s CMS Blocks with identifier "%s" assigned to stores: %s',$count,$identifier,implode(',',(array)$stores));
            Mage::throwException($errMsg);
        }
        $page = $collection->getFirstItem();
        $page->load($page->getPageId());
        return $page;
    }
    
    /**
     * @param string $identifier
     * @param array $stores
     * @param bool $withAdmin
     * @return Mage_Cms_Model_Resource_Page_Collection 
     */
    public function loadPages($identifier, $stores = null, $withAdmin = false)
    {
        $collection = Mage::getModel('cms/page')->getCollection();
        /* @var $collection Mage_Cms_Model_Resource_Page_Collection */
        if ($stores) {
            $collection->addStoreFilter($stores,$withAdmin);
        }
        $collection
            ->addFieldToFilter('identifier',array('eq' => $identifier));
        return $collection;
    }
    
    /**************************************************************************/
    /***************************************************************** BLOCKS */
    
    /**
     * @param type $identifier
     * @param type $title
     * @param type $content
     * @param type $stores
     * @param type $params
     * @return \Hd_Base_Model_Resource_Setup_Cms
     */
    public function setBlock($identifier, $title, $content, $stores = null, $params = null )
    {
        // Stores Ids
        $storesIds = $this->_getStoresIds($stores);
        // Cms Block
        $block = $this->loadBlock($identifier,$storesIds);
        // Default Data
        if (!$block->getId()) {
            $block
                ->addData($this->_blockDefaultData)
                ->setIdentifier($identifier)
                ->setStoreId($storesIds)
                ->setStores($storesIds);
        }
        // New Data
        $block
            ->setTitle($title)
            ->setContent($content);
        
        if (isset($params)) {
            if (isset($params['change_identifier'])) {
                $block->setIdentifier($params['change_identifier']);
                unset($params['change_identifier']);
            }
            if (isset($params['change_stores'])) {
                $storesIds = $this->_getStoresIds($params['change_stores']);
                $block->setStoreId($storesIds);
                $block->setStores($storesIds);
                unset($params['change_stores']);
            }
            foreach($params as $key => $value) {
                $block->setData($key,$value);
            }
        }
        $block->save();
        return $this;
    }
    
    /**
     * @param type $identifier
     * @param type $stores
     * @return \Hd_Base_Model_Resource_Setup_Cms
     */
    public function deleteBlock($identifier, $stores = null )
    {
        $storesIds = $this->_getStoresIds($stores);
        $blocks = $this->loadBlocks($identifier,$storesIds);
        if (!$count = $blocks->count()) {
            $errMsg = sprintf('Can not find CMS Blocks with identifier= "%s" assigned to stores: %s',$count,$identifier,implode(',',(array)$storesIds));
            Mage::logException(new Exception($errMsg));
        }        
        foreach ($blocks as $block) {
            $block->load($block->getBlockId());
            if (count(array_intersect($storesIds, $block->getStoreId()))) {
                $block->delete();
            }
        }
        return $this;
    }
    
    /**
     * @return \Hd_Base_Model_Resource_Setup_Cms 
     */
    public function deleteAllBlocks()
    {
        $collection = Mage::getModel('cms/block')->getCollection();
        foreach($collection as $block){
            $block->delete();
        }
        return $this;
    }
    
    
    /**
     * @param string $identifier
     * @param array $stores
     * @param bool $withAdmin
     * @return Mage_Cms_Model_Block 
     */
    public function loadBlock($identifier, $stores = null, $withAdmin = false)
    {
        $withAdmin = isset($stores) && ($withAdmin || in_array('0', $stores)) ? true : false;
        $collection = $this->loadBlocks($identifier, $stores, $withAdmin);
        $count = $collection->count();
        if ($count > 1) {
            $errMsg = sprintf('There are %s CMS Blocks with identifier "%s" assigned to stores: %s',$count,$identifier,implode(',',(array)$stores));
            Mage::throwException($errMsg);
        }
        $block = $collection->getFirstItem();
        $block->load($block->getBlockId());
        return $block;
    }
    
    /**
     * @param string $identifier
     * @param array $stores | Array de IDs de los stores
     * @param bool $withAdmin | Determina si carga o no paginas con StoreId = 0
     * 
     * @return Mage_Cms_Model_Resource_Block_Collection
     */
    public function loadBlocks($identifier, $stores = null, $withAdmin = false)
    {
        $collection = Mage::getModel('cms/block')->getCollection();
        /* @var $collection Mage_Cms_Model_Resource_Block_Collection */
        if ($stores) {
            $collection->addStoreFilter($stores,$withAdmin);
        }
        $collection
            ->addFieldToFilter('identifier',array('eq' => $identifier));
        return $collection;
    }
    
    /**
     * Si es null (default): devuelve un array con el valor "0" (aplica a todos los stores)
     * Si es array: Devuelve un array con los id de los Stores con 'code' = $store[n]
     * Si es string: Devuele un array con el id del Store con 'code' = $stores
     * 
     * @param mixed $stores
     * @return array
     */
    protected function _getStoresIds($stores = null)
    {
        // Stores
        if (is_null($stores)) {
            $storesIds = array('0');
        } else if (!is_array($stores)) {
            $store = $this->loadStore($stores);
            if (!$storeId = $store->getId()) {
                Mage::throwException('Invalid Store Id: ' . $stores);
            }
            $storesIds = array($storeId);
        } else {
            $storesIds = array();
            foreach ($stores as $storeCode) {
                $store = $this->loadStore($storeCode);
                if (!$storeId = $store->getId()) {
                    Mage::throwException('Invalid Store Code: ' . $storeCode);
                }
                $storesIds[] = $storeId;
            }
        }
        return $storesIds;
    }
    
}