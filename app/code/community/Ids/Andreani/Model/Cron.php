<?php
/**
 * Created by Jhonattan Campo
 * Date: 19/07/16
 * Class Ids_Andreani_Model_Cron
 * @description Clase que contiene la lógica del cron
 * de sucursales.
 */
require_once Mage::getBaseDir('lib') . '/Andreani/wsseAuth.php';
class Ids_Andreani_Model_Cron extends Mage_Core_Model_Abstract
{
    public function __construct()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 1800);
    }

    /**
     * @descripction  arma el array para consultar al WS
     * @return array
     */
    public function getProvinciasCollection()
    {
        $localCollection = Mage::getModel('andreani/provincias')->getCollection()
            ->addFieldToSelect('provincia')
            ->addFieldToSelect('localidad');
        $localCollection->getSelect()->order('provincia ASC');

        $arrayProv = array();
        $provinciasLocalidades = $localCollection->getData();

        for($i = 0; $i < count($provinciasLocalidades); $i++ )
        {
            if($i==0)
            {
                $arrayProv[$provinciasLocalidades[0]['provincia']] = [];
                $arrayProv[$provinciasLocalidades[0]['provincia']][$provinciasLocalidades[0]['localidad']] = $provinciasLocalidades[0]['localidad'];
            }
            else
            {
                if($provinciasLocalidades[$i]['provincia']==$provinciasLocalidades[$i-1]['provincia'])
                {
                    $arrayProv[$provinciasLocalidades[$i]['provincia']][$provinciasLocalidades[$i]['localidad']] = $provinciasLocalidades[$i]['localidad'] ;
                }
                else
                {
                    $arrayProv[$provinciasLocalidades[$i]['provincia']]   = [];
                    $arrayProv[$provinciasLocalidades[$i]['provincia']][$provinciasLocalidades[$i]['localidad']] = $provinciasLocalidades[$i]['localidad'] ;
                }
            }
        }

        return $arrayProv;
    }

    /**
     * @description se conecta al WS y arma el JSON con la respuesta.
     * @return Exception|SoapFault
     */
    protected function _setJsonSucursales()
    {
        if (Mage::getStoreConfig('carriers/andreaniconfig/testmode',Mage::app()->getStore()) == 1) {
            $sucursalesWSUrl    = Mage::helper('andreani')->getWSMethodUrl(Ids_Andreani_Helper_Data::SUCURSALES,Ids_Andreani_Helper_Data::ENVMODTEST);
            $soapVersion  		= Mage::helper('andreani')->getSoapVersion(Ids_Andreani_Helper_Data::SUCURSALES,Ids_Andreani_Helper_Data::ENVMODTEST);
        } else {
            $sucursalesWSUrl    = Mage::helper('andreani')->getWSMethodUrl(Ids_Andreani_Helper_Data::SUCURSALES,Ids_Andreani_Helper_Data::ENVMODPROD);
            $soapVersion	  	= Mage::helper('andreani')->getSoapVersion(Ids_Andreani_Helper_Data::SUCURSALES,Ids_Andreani_Helper_Data::ENVMODPROD);
        }
        $options = array(
            'soap_version' => $soapVersion,
            'exceptions' => true,
            'trace' => 1
        );


        $userCredentials = Mage::helper('andreani')->getUserCredentials();
        $wsse_header    = new WsseAuthHeader($userCredentials['username'], $userCredentials['password']);
        $client         = new SoapClient($sucursalesWSUrl, $options);
        $client->__setSoapHeaders(array($wsse_header));

        $arrayProv = $this->getProvinciasCollection();

        try {

            foreach($arrayProv AS $provincia => $localidades)
            {
                $phpResponse = $client->ConsultarSucursales(array(
                    'consulta' => array(
                    )
                ));

                $sucursales = $phpResponse->ConsultarSucursalesResult->ResultadoConsultarSucursales;

                foreach($localidades AS $localidad)
                {
                    $arrayProv[$provincia][$localidad] = array();
                    foreach ($sucursales AS $sucursal)
                    {
                        $direccion  = explode(',',$sucursal->Direccion);
                        $local      = trim($direccion[2]);
                        $prov       = trim($direccion[3]);
                        if($local == $localidad && $provincia == $prov)
                        {
                            $arrayProv[$provincia][$localidad][] = $sucursal;
                        }

                    }
                }
            }

            $arrayProv  = array_map('array_filter', $arrayProv);
            $serialJson = serialize(json_encode($arrayProv));

            return $this->_saveJsonToDb($serialJson);

        } catch (SoapFault $e) {
            return $e;
        }
    }

    /**
     * @description guarda el JSON en la DB
     * @param $serialJson
     * @return Exception
     */
    protected function _saveJsonToDb($serialJson)
    {
        if($serialJson!='')
        {
            try {
                $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
                $currentDate = date('Y-m-d H:i:s', $currentTimestamp);
                //Insert sucursales data
                $sucursalesCollection = Mage::getModel('andreani/sucursales')->getCollection()
                    ->addFieldToSelect('id')
                    ->addFieldToSelect('json');

                if (count($sucursalesCollection->getData()) > 0) {
                    foreach ($sucursalesCollection->getData() AS $sucursalesData) {
                        $sucursalesModel = Mage::getModel('andreani/sucursales')->load($sucursalesData['id']);
                        $sucursalesModel->setData('json', $serialJson);
                        $sucursalesModel->setData('updated_at', $currentDate)->save();
                    }
                } else {
                    $sucursalesCollection = Mage::getModel('andreani/sucursales');
                    $sucursalesCollection->setData('json', $serialJson);
                    $sucursalesCollection->setData('created_at', $currentDate)->save();
                }
                return $serialJson;
            }catch (Exception $e) {
                return $e;
            }
        }
    }

    /**
     * @description trigger de la generación del JSON
     * @return Exception|SoapFault
     */
    public function run()
    {
        return $this->_setJsonSucursales();
    }
}