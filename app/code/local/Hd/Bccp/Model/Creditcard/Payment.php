<?php
class Hd_Bccp_Model_Creditcard_Payment 
    extends Hd_Bccp_Model_Abstract
{
    protected $_eventPrefix = 'hd_bccp_creditcard_payment';
    
    protected $_eventObject = 'payment';
    
    protected function _construct()
    {
        $this->_init("hd_bccp/creditcard_payment", "payment_id");
    }
    
    /**
     * @todo Agregar Load en caso de Promocion.
     * - Debe devolver un falso objeto payment en funcion de la promo 
     * 
     * El "id" debe ser "promo-{id_promocion}-{payments}"
     * 
     * @param type $id
     * @param type $field
     */
    public function load($id, $field = null)
    {
        // Load Promo Payment
        if(is_null(null) && !is_numeric($id) && strpos($id, 'PROMO') > -1) {
            
            $explodeId  = explode('-',$id);
            $promoId    = $explodeId[1];
            $promoIssue = $explodeId[2];
            
            $model = Mage::getModel('hd_bccp/promo');
            /* @var $model Hd_Bccp_Model_Promo */
            $model
                ->setMethodCode($this->_methodCode)
                ->load($promoId);
            
            if (!$model->getId()) {
                Mage::throwException(Mage::helper('hd_bccp')->__('Invalid Promo Payment Id'));
            }
            
            $payments = $model->getResultPayments();
            
            if(!isset($payments[$promoIssue]) || !($payments[$promoIssue] instanceof Hd_Bccp_Model_Creditcard_Payment)) {
                Mage::throwException(Mage::helper('hd_bccp')->__('Invalid Promo Payment Issue.'));
            }
            
            $this->addData($payments[$promoIssue]->getData());
            unset($explodeId, $promoId, $promoIssue, $model, $payments);
            
            return $this;
        }
        return parent::load($id, $field);
    }
    
}