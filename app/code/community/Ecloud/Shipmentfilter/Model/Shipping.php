<?php
class Ecloud_Shipmentfilter_Model_Shipping extends Mage_Shipping_Model_Shipping
{
    /**
     * Collect rates of given carrier
     *
     * @param string $carrierCode
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Shipping
     */
    public function collectCarrierRates($carrierCode, $request)
    {
        /* @var $carrier Mage_Shipping_Model_Carrier_Abstract */
        $carrier = $this->getCarrierByCode($carrierCode, $request->getStoreId());

        //llamo al método checkCarrierAvailability, que filtra y devuelve los errrores por shipping method.
        $carrierAvailable = $this->_checkCarrierAvailability($carrierCode, $request);
        if ($carrierAvailable['Status'] == 'Error') {
            $result     = Mage::getModel('shipping/rate_result');
            $error      = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($carrierCode);
            if (sizeof($carrierAvailable['Message']) > 1){
                $mensaje_error = '';
                foreach ($carrierAvailable['Message'] as $error_msg) {
                    $mensaje_error .= $error_msg . ' ';
                }
            }else{
                $mensaje_error = $carrierAvailable['Message'][0];
            }
            $error->setErrorMessage($mensaje_error);
            $result->append($error);
            $this->getResult()->append($result);

            return $this;

        }
        if (!$carrier) {
            return $this;
        }
        $carrier->setActiveFlag($this->_availabilityConfigField);
        $result = $carrier->checkAvailableShipCountries($request);
        if (false !== $result && !($result instanceof Mage_Shipping_Model_Rate_Result_Error)) {
            $result = $carrier->proccessAdditionalValidation($request);
        }
        /*
        * Result will be false if the admin set not to show the shipping module
        * if the delivery country is not within specific countries
        */
        if (false !== $result){
            if (!$result instanceof Mage_Shipping_Model_Rate_Result_Error) {
                if ($carrier->getConfigData('shipment_requesttype')) {
                    $packages = $this->composePackagesForCarrier($carrier, $request);
                    if (!empty($packages)) {
                        $sumResults = array();
                        foreach ($packages as $weight => $packageCount) {
                            //clone carrier for multi-requests
                            $carrierObj = clone $carrier;
                            $request->setPackageWeight($weight);
                            $result = $carrierObj->collectRates($request);
                            if (!$result) {
                                return $this;
                            } else {
                                $result->updateRatePrice($packageCount);
                            }
                            $sumResults[] = $result;
                        }
                        if (!empty($sumResults) && count($sumResults) > 1) {
                            $result = array();
                            foreach ($sumResults as $res) {
                                if (empty($result)) {
                                    $result = $res;
                                    continue;
                                }
                                foreach ($res->getAllRates() as $method) {
                                    foreach ($result->getAllRates() as $resultMethod) {
                                        if ($method->getMethod() == $resultMethod->getMethod()) {
                                            $resultMethod->setPrice($method->getPrice() + $resultMethod->getPrice());
                                            continue;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $result = $carrier->collectRates($request);
                    }
                } else {
                    $result = $carrier->collectRates($request);
                }
                if (!$result) {
                    return $this;
                }
            }
            //Comento para que el shipping method muestre SIEMPRE error existente.
            // if ($carrier->getConfigData('showmethod') == 0 && $result->getError()) {
            //     return $this;
            // }

            // Sort rates by price
            if (method_exists($result, 'sortRatesByPrice')) {
                $result->sortRatesByPrice();
            }
            $this->getResult()->append($result);
        }
        return $this;
    }

    /**
     * Retrieve all methods for supplied shipping data
     *
     * @todo make it ordered
     * @param Mage_Shipping_Model_Shipping_Method_Request $data
     * @return Mage_Shipping_Model_Shipping
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $storeId = $request->getStoreId();
        if (!$request->getOrig()) {
            $request
                ->setCountryId(Mage::getStoreConfig(self::XML_PATH_STORE_COUNTRY_ID, $request->getStore()))
                ->setRegionId(Mage::getStoreConfig(self::XML_PATH_STORE_REGION_ID, $request->getStore()))
                ->setCity(Mage::getStoreConfig(self::XML_PATH_STORE_CITY, $request->getStore()))
                ->setPostcode(Mage::getStoreConfig(self::XML_PATH_STORE_ZIP, $request->getStore()));
        }

        $limitCarrier = $request->getLimitCarrier();
        if (!$limitCarrier) {
            $carriers = Mage::getStoreConfig('carriers', $storeId);

            foreach ($carriers as $carrierCode => $carrierConfig) {
                $this->collectCarrierRates($carrierCode, $request);
            }
        } else {
            if (!is_array($limitCarrier)) {
                $limitCarrier = array($limitCarrier);
            }
            foreach ($limitCarrier as $carrierCode) {
                $carrierConfig = Mage::getStoreConfig('carriers/' . $carrierCode, $storeId);
                if (!$carrierConfig) {
                    continue;
                }
                $this->collectCarrierRates($carrierCode, $request);
            }
        }

        return $this;
    }




    protected function _checkCarrierAvailability($carrierCode, $request = null){
        $quote               = Mage::getSingleton('checkout/session')->getQuote();
        $productskus         = array();
        $prodattrsetnames    = array();
        $store               = Mage::app()->getStore();
        $cpIngresado         = $request->getDestPostcode();

        foreach ($quote->getAllItems() as $item)
        {
            $product           = $item->getProduct(); // the product instance
            $attributeSetModel = Mage::getModel("eav/entity_attribute_set")->load($product->getAttributeSetId());
            $prodAttrSetName   = $attributeSetModel->getAttributeSetName();

            $productskus[]      = $product->getSku();
            $prodattrsetnames[] = $prodAttrSetName;
        }
        for ($i=1; $i < 5 ; $i++) {
            //filter active.
            $filter_active  = Mage::getStoreConfig('shipment_filter/shipping'.$i.'/active',$store);
            if (!$filter_active) { continue; }
            $sm_filtered     = explode (',',Mage::getStoreConfig('shipment_filter/shipping'.$i.'/carrier',$store));
            $cpfilter_active = Mage::getStoreConfig('shipment_filter/shipping'.$i.'/cpfilter_active',$store);
            $attrsetfilter_active  = Mage::getStoreConfig('shipment_filter/shipping'.$i.'/attrset_active',$store);
            $skufilter_active = Mage::getStoreConfig('shipment_filter/shipping'.$i.'/skufilter_active',$store);

            $errors = array();
            if(in_array($carrierCode, $sm_filtered)){
               ///ir filtrando por los atributos habilitados.
                if ($cpfilter_active){
                    //zipcodes filter--> array.
                    $zips_enabled    = Mage::getStoreConfig('shipment_filter/shipping'.$i.'/zipcodes', $store);
                    $zips_enabled    = str_replace(array("\r", "\n"," "), "", $zips_enabled);
                    $zips_enabled    = explode (',',$zips_enabled);
                    $zips_enabled    = array_filter ($zips_enabled);
                    if (!in_array($cpIngresado, $zips_enabled)){
                        $error_msg  =  Mage::getStoreConfig('shipment_filter/shipping'.$i.'/cpfilter_error',$store);
                        if (empty($error_msg)){
                            $error_msg  = 'Código postal inválido para este método de envío.';
                        }
                        $result['Status']    = 'Error';
                        $result['Message'][] = $error_msg;
                        return $result;
                    }
                }
                if($skufilter_active){
                    //set skus array.
                    $skus            = Mage::getStoreConfig('shipment_filter/shipping'.$i.'/skus',$store);
                    $skus            = str_replace(array("\r", "\n"," "), "", $skus);
                    $skus            = explode (',',$skus);
                    $skus            = array_filter($skus);
                    $skus_match      = count(array_intersect($productskus, $skus));
                    $sku_intersect   = Mage::getStoreConfig('shipment_filter/shipping'.$i.'/sku_intersect',$store);

                    if ($sku_intersect) {
                        if($skus_match == 0){
                            //Si ningun SKU coincide, se inhabilita el metodo de envío
                            $error_msg = Mage::getStoreConfig('shipment_filter/shipping'.$i.'/skufilter_error', $store);
                            if (empty($error_msg)){
                                $error_msg  = 'SKU inválido para este método de envío.';
                            }
                            $result['Status']    = 'Error';
                            $result['Message'][] =  $error_msg;
                            return $result;
                        }
                    }else{
                        //Los valores de Product Skus que no esten presentes en la config. de SKUS habilitados.
                        $sku_diff = count(array_diff($productskus, $skus));
                        if($sku_diff != 0){
                            //Si ningun SKU coincide, se inhabilita el metodo de envío
                            $error_msg = Mage::getStoreConfig('shipment_filter/shipping'.$i.'/skufilter_error', $store);
                            if (empty($error_msg)){
                                $error_msg  = 'SKU inválido para este método de envío.';
                            }
                            $result['Status']    = 'Error';
                            $result['Message'][] =  $error_msg;
                            return $result;
                        }
                    }
                }
                if($attrsetfilter_active){
                    $configattrsets    = explode(',',Mage::getStoreConfig('shipment_filter/shipping'.$i.'/attributesets',$store));
                    //Check if use "INTERSECT" filter. If not, selected configattrsets MUST match ALL PRODS.
                    $attrset_intersect = Mage::getStoreConfig('shipment_filter/shipping'.$i.'/attrset_intersect',$store);
                    if ($attrset_intersect){
                        //MODO INTERSECCION.
                        //Si UN producto cumple con attribute set habilitado, habilitado el Shipping.
                        $attrsetshabilitados    = count(array_intersect($configattrsets, $prodattrsetnames));
                        if($attrsetshabilitados == 0){
                            $error_msg = Mage::getStoreConfig('shipment_filter/shipping'.$i.'/attrset_error',$store);
                            if (empty($error_msg)){
                                $error_msg  = 'Attribute Sets inválidos para este método de envío.';
                            }
                            $result['Status']    = 'Error';
                            $result['Message'][] =  $error_msg;
                            return $result;
                        }
                    }else{
                        //NO INTERSECCION.
                        //TODOS los productos deben pertenecer a los Attribute Sets habilitados.
                        $attrset_diff = count(array_diff($prodattrsetnames, $configattrsets));
                        if($attrset_diff != 0){
                            $error_msg =Mage::getStoreConfig('shipment_filter/shipping'.$i.'/attrset_error',$store);
                            if (empty($error_msg)){
                                $error_msg = 'Attribute Sets inválidos para este método de envío.';
                            }
                            $result['Status']    = 'Error';
                            $result['Message'][] =  $error_msg;
                            return $result;
                        }
                    }
                }
            }
        }
        return true;
    }
}
