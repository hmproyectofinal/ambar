<?php
class Treggo_Customshippingmethod_Model_Shipping
extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
  protected $_code = 'treggo_customshippingmethod';
  
  public function postToTreggo($endpoint, $curlPostData, $shippingObj = null) 
  {
    $version = Mage::helper('treggo_customshippingmethod')->getExtensionVersion();
    $curlPostData['via'] = 'Magento_' . $version;
    $curlPostData['tipo'] = 'ecommerce';
    $curlPostData['api'] = $this->getConfigData('apikey');

    if(!$this->validateRegion($shippingObj['address'], $shippingObj['region'])) {
      return $this->errorResponse('El campo dirección no debe contener la provincia');
    }

    $serviceUrl = 'http://empresas.treggocity.com'.$endpoint;
    $curl = curl_init($serviceUrl);

    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curlPostData));

    $curlResponse = curl_exec($curl);
    if ($curlResponse === false) {
      $info = curl_getinfo($curl);
      curl_close($curl);

      return $this->errorResponse('No se puede comunicar con el servidor de TREGGO por favor controle la conectividad entre servidores');
    }

    curl_close($curl);
    $decoded = json_decode($curlResponse);

    if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
      return $this->errorResponse('No se puede comunicar con el servidor de TREGGO por favor controle la conectividad entre servidores');
    }
    
    return $decoded;
  }

  public function errorResponse($error) 
  {
    $errorObj = new stdClass();
    $errorObj->errores = array($error);
    $curlResponse = new stdClass();
    $curlResponse->error = $error;
    $curlResponse->correcto = false;
    $curlResponse->pedido = $errorObj;

    return $curlResponse;
  }

  public function validateRegion($address, $region) 
  {
    if (strpos($address, $region) !== false) {
        return false;
    }

    return true;
  }

  public function collectRates(Mage_Shipping_Model_Rate_Request $request) 
  {
    $quote = Mage::getSingleton('checkout/session')->getQuote();
    $result = Mage::getModel('shipping/rate_result');
    
    $destCountry = $request->getDestCountryId();
    $destRegion = $request->getDestRegionId();
    $destRegionCode = $request->getDestRegionCode();
    $destStreet = $request->getDestStreet(0);
    $destCity = $request->getDestCity();
    $destPostcode = $request->getDestPostcode();
    $countryId = $request->getCountryId();
    $regionId = $request->getRegionId();
    $city = $request->getCity();
    $postcode = $request->getPostcode();
    $weight = $quote->getShippingAddress()->getWeight();
    $street = $quote->getShippingAddress()->getStreet1();

    if($destCountry == 'AR') {
      $destCountry = 'Argentina';
    }

    //$address = $package["destination"]["address"]." ".$package["destination"]["address_2"].", ".$package["destination"]["city"];
    $address = "$destStreet, $destPostcode $destCity $destRegionCode $destCountry";

    if(!isset($destStreet)) {
      $address = "$destCity $destRegionCode, $destCountry";
    }

    $data = array(
      "peso" => $weight,
      "destinos" => array (
        array (
          "direccion" => $this->getConfigData('warehouse')
        ),
        array (
          "direccion" => $address
        )
      )
    );

    $shippingObj = array();
    $shippingObj['address'] = $destStreet; 
    $shippingObj['region'] = $destRegionCode; 
    $response = $this->postToTreggo("/api/1/cotizacion", $data, $shippingObj);
    $errors = '';

    if(isset($response->pedido->errores)) {
      foreach ($response->pedido->errores as $error) {
        $errors .= ' '.$error;
      }
    } else {
      if($response->pedido->importe != '') {
        $price = $response->pedido->importe;
      }
    }    

    if (strpos(Mage::helper('core/http')->getHttpReferer(), 'checkout/cart') !== false) {
      $price = $response->pedido->importe;
    }      
    
    if(!isset($price)) {
      $error = Mage::getModel('shipping/rate_result_error');
      $error->setCarrier($this->_code);
      $error->setCarrierTitle($this->getConfigData('title'));
      $error->setErrorMessage("Envio por Treggo:" . $errors);
      $result->append($error);
    } else {
      $method = Mage::getModel('shipping/rate_result_method');
      $method->setCarrier($this->_code);
      $method->setMethod($this->_code);
      $method->setCarrierTitle($this->getConfigData('title'));
      $method->setMethodTitle($this->getConfigData('name'));
      $method->setPrice($price);
      $method->setCost($price);
      $result->append($method);
    }

    return $result;
  }
 
  public function getAllowedMethods() 
  {
    return array(
      'treggo_customshippingmethod' => $this->getConfigData('name'),
    );
  }

  protected function _getDefaultRate() 
  {
    $rate = Mage::getModel('shipping/rate_result_method');
     
    $rate->setCarrier($this->_code);
    $rate->setCarrierTitle($this->getConfigData('title'));
    $rate->setMethod($this->_code);
    $rate->setMethodTitle($this->getConfigData('shippingtext'));
    $rate->setPrice($this->getConfigData('price'));
    $rate->setCost(0);
     
    return $rate;
  }
}