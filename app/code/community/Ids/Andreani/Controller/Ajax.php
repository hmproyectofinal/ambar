<?php

/**
 * Created by Jhonattan Campo
 * Date: 20/07/16
 * Class Ids_Andreani_Controller_Ajax
 * @description Controlador que maneja la lógica del json sucursales
 * Class Ids_Andreani_Adminhtml_CronController
 */
class Ids_Andreani_Controller_Ajax extends Mage_Core_Controller_Front_Action
{

    public function sucursalesAction()
    {
        $sucursalesData = json_decode(Mage::helper('andreani')->getJsonSucursales());
        $params = $this->getRequest()->getParams();

        foreach ($sucursalesData AS $provincias => $localidades) {

            if((isset($params['provincia']) && $params['provincia'] !='')
                && (!isset($params['localidad']) || $params['localidad'] ==''))
            {
                if($params['provincia'] == $provincias)
                {
                    foreach ($localidades AS $localidad => $sucursal) {
                        $result[] = $localidad;
                    }
                }
            }
            else if((isset($params['provincia']) && $params['provincia'] !='')
                && (isset($params['localidad']) && $params['localidad'] !=''))
            {
                foreach ($localidades AS $localidad => $sucursal) {
                    if($params['localidad'] == $localidad)
                    {

                        for($i=0; $i<count($sucursal);$i++)
                        {
                            $cpDestino = explode(',',trim($sucursal[$i]->Direccion));
                            $sucursal[$i]->cpDestino = trim($cpDestino[1]);
                            $sucursal[$i]->DireccionSucursal = trim($sucursal[$i]->Direccion);
                        }


                        $result[] = $sucursal;


                    }

                }
            }
        }

        if(count($result)==0)
        {
            $result['error'] = 'Ocurrió un error, por favor intente nuevamente.';
        }
        
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function costoenvioAction()
    {
        $sucursalesData = json_decode(Mage::helper('andreani')->getJsonSucursales());
        $params = $this->getRequest()->getParams();
        $price = Mage::getModel('andreani/carrier_andreani')->cotizarEnvio($params);
        $result = $price+ (floor($price * Mage::getStoreConfig('carriers/andreanisucursal/regla')) / 100);
        //$andreaniData = Mage::getSingleton('core/session')->getAndreaniSucursal();
        Mage::getSingleton('core/session')->setAndreaniSucursal($params);
       // $andreaniData = Mage::getSingleton('core/session')->setAndreaniSucursal('cpDestino',$params['cpDestino']);
        $quote	 = Mage::getModel('checkout/cart')->getQuote();
        $address = $quote->getShippingAddress();
        $address->setShippingAmount($result)->save();
       // Mage::log($andreaniData,null,'andreani.log',true);

        /*$costoEnvio = array();
        $costoEnvio['costoEnvio'] = $result;
        $andreaniData->setAndreaniSucursal($costoEnvio);*/
        if(count($result)==0)
            $result['error'] = 'Ocurrió un error, por favor intente nuevamente.';
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }


}