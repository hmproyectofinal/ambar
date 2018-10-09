<?php
class Hd_Nps_Model_Soap_Client extends Hd_Base_Model_Soap_Client
{
    /**
     * Path al nodo de configuracion donde se encuentran declarada la URL del WSDL
     * @var string 
     */
    protected $_xmlConfigPathSoapWsdlUrl    = 'payment/hasar/ws_wsdl_url';
    
    /**
     * Tipo de Cliente Soap
     * 
     * Valores Posibles
     * 'standar':        SoapClient
     * 'standar-wcf':    SoapClient
     * 'zend-standard':  Zend_Soap_Client
     * 'zend-local':     Zend_Soap_Client_Local
     * 'zend-dot-net':   Zend_Soap_Client_DotNet
     *  
     * @var string
     * @see http://framework.zend.com/manual/1.12/en/zend.soap.client.html
     * 
     */
    protected $_soapClientType = 'standard';
    
    /**
     * Archivo Log de Requests
     * @var string 
     */
    protected $_requestLogFile = 'nps-soap-client.log';
    
    /**
     * Archivo Log de Responses
     * @var string 
     */
    protected $_responseLogFile = 'nps-soap-client.log';
    
    /**
     * Archivo Log Generico
     * @var string 
     */
    protected $_generalLogFile = 'nps-soap-client.log';
    
    /**
     * 
     * 
     * @param array $params
     * @return stdClass
     */
    public function doPayOnline3pRequest($params, $asArray = false)
    {
        // Soap Response
        $response = $this->call('PayOnLine_3p',$params);
        
        $result = new Varien_Object();
        $result
            ->setResponse($response)
            ->addData($this->_evalResponse($response));
        return $result;
    }
    
    /**
     * 
     * 
     * @param array $params
     * @return stdClass
     */
    public function doAuthorize3pRequest($params, $asArray = false)
    {
        // Soap Response
        $response = $this->call('Authorize_3p',$params);
        
        $result = new Varien_Object();
        $result
            ->setResponse($response)
            ->addData($this->_evalResponse($response));
        return $result;
    }
    
    /**
     * @todo Abstraer validacion para utilizar en cualquier validacion
     * 
     * @param array $params
     * @return stdClass
     */
    public function doSimpleQueryTx($params, $asArray = false)
    {
        // Soap Response
        $response = $this->call('SimpleQueryTx',$params);
        
        $result = new Varien_Object();
        
        $result->setResponse($response);
        
        // Evaluacion del Query
        $queryResult = $this->_evalResponse($response);
        if ($queryResult['status']) {
            $validateFields = array(
                'psp_AuthorizationCode',
                'psp_CardNumber_FSD',
                'psp_CardNumber_LFD',
            );
            $result->addData($this->_evalResponse($response->psp_Transaction, $validateFields));
        } else {
            $result->addData($queryResult);
        }
        return $result;
    }
    
    /**
     * Evalua el Status del response.
     * En caso de error ejecuta 
     * 
     * @param stdClass $response
     * @return boolean
     * @throws Exception
     */
    protected function _evalResponse($res, $validateFields = null)
    {
        if (is_null($res)){
            $result = array(
                'status' => false,
                'message' => Mage::helper('hd_nps')->__('Connection Error')
            );
            return $result;
        }

        $result = array(
            'status' => true,
        );
        
        $code = $res->psp_ResponseCod;
        
        if (!in_array($code, $this->_pspResponseCodesOk)) {
            
            $codeExt = isset($res->psp_ResponseExtended) 
                ? $res->psp_ResponseExtended : false; 
            
            $error = array();
            $error[] = @$this->_pspResponseCodes[$code];
            if ($codeExt) {
                $error[] = (strlen($codeExt) == 4) 
                    ? @$this->_pspResponseCodesExtended[$codeExt]
                    : $codeExt;
            }
            
            $message = implode(' - ', $error);
            
            // Return
            $result = array(
                'status' => false,
                'message' => $message,
            );
        }
        
        if($validateFields) {
            
            foreach($validateFields as $field) {
                $resArr = (array)$res;
                if(!isset($resArr[$field]) || empty($resArr[$field])) {
                    
                    $message = (strpos($resArr['psp_ResponseExtended'], 'Error Interno 1048') === false)
                        ? Mage::helper('hd_nps')->__('Transacción Incompleta o Abandonada')
                        : $resArr['psp_ResponseMsg'] . ' - ' . $resArr['psp_ResponseExtended'];
                    
                    $result = array(
                        'status' => false,
                        'message' => $message,
                    );
                    break;
                }
            }
            
        }
        
        return $result;
        
    }
    
    /**
     * Codigo Existosos
     * @var type 
     */
    protected $_pspResponseCodesOk = array(
        '0','1','2',
    );
    
    /**
     * Todos los Codigos
     * @var type 
     */
    protected $_pspResponseCodes = array(
        0 => 'Transacción exitosa - Aprobada',
        1 => 'Transacción de registro 3 partes exitosa',
        2 => 'Transacción de consulta exitosa.',
        3 => 'Excede el límite de la tarjeta',
        4 => 'Tarjeta vencida',
        5 => 'Código de seguridad inválido',
        6 => 'Tarjeta inválida',
        7 => 'Transacción rechazada por el procesador',
        8 => 'Error en 3D Secure',
        9 => 'Error detectado en el cliente',
        10 => 'Error de procesamiento detectado en PSP',
        11 => 'Error en el procesador',
        12 => 'Error en el MOP',
        13 => 'Error en la definición WSDL',
        14 => 'Error ejecutando proceso Batch',
        15 => 'Transacción Split reversada por API',
        16 => 'Pendiente de pago - Cupón de pago emitido',
        17 => 'Aprobada y Reversada',
        18 => 'Pendiente de pago - Alta de factura y Alta de Adhesión efectuadas',
        19 => 'Cambio de Secret Key satisfactorio',
        20 => 'Pendiente de respuesta por parte del adquirente',
        21 => 'Factura vencida',
        22 => 'Renovación de PAN o fecha de expiración',
    );

    /**
     * Codigos de Error Extendidos
     * @var type 
     */
    protected $_pspResponseCodesExtended = array(
        1000  => 'Datos incompletos en 3DSecure',
        1001  => 'Error seleccionando la cantidad de pagos',
        1002  => 'Método deshabilitado para el comercio',
        1003  => 'Secuencia de comprobación inválida',
        1004  => 'Error de base de datos',
        1005  => 'Error recuperando datos',
        1006  => 'No se encuentran los datos del comercio',
        1007  => 'Operación no soportada por el procesador',
        1008  => 'Error actualizando datos',
        1009  => 'Error insertando datos',
        1010  => 'Error no especificado',
        1011  => 'Referencia de Transacción duplicada',
        1012  => 'Comercio en mantenimiento',
        1013  => 'Comercio deshabilitado',
        1014  => 'Transacción inexistente',
        1015  => 'El número de tarjeta no es válido para el producto',
        1016  => 'Falta un campo mandatario',
        1017  => 'Tipo o longitud invá¡lido(a)',
        1018  => 'Producto no configurado',
        1019  => 'El monto a devolver es mayor al de la Transacción original',
        1020  => 'Transacción no susceptible de ser anulada o devuelta',
        1021  => 'Error buscando terminal virtual para operar',
        1022  => 'Error liberando terminal virtual',
        1023  => 'Error obteniendo el plan',
        1024  => 'Error de conexión con el procesador',
        1025  => 'Error de redirección en modo 3-partes. Falta campo mandatorio',
        1026  => 'Error de validación en modo 3-partes. psp_MerchTxRef no coincide',
        1027  => 'Error de validación en modo 3-partes. psp_Session3p no coincide',
        1028  => 'Error en modo 3-partes. Error al cargar el formulario <frm_custom> del comercio',
        1029  => 'Datos adicionales de Visa incompletos',
        1030  => 'VBV sin servicio, error de comunicación xml-rpc',
        1031  => 'VBV identificador de compra inválido',
        1032  => 'VBV error al obtener idCompra',
        1033  => 'VBV sin servicio, error al recibir respuesta de Verified by Visa. Resultado Autenticación: NO RECIBIDO.',
        1034  => 'VBV sin servicio, error al recibir respuesta de Verified by Visa. Resultado Autenticación: ERROR.',
        1035  => 'VBV comprador no autenticado.',
        1036  => 'Error WSDL - Contáctese con el administrador',
        1037  => 'Datos incompletos de Address Verification Service de Amex',
        1038  => 'URL de retorno inválida',
        1039  => 'Sesión expirada',
        1040  => 'Formulario de captura 3-partes expirado',
        1041  => 'Error de configuración',
        1042  => 'Error de ruteo: Operacion invalida para el metodo de procesamiento',
        1043  => 'Error de validación en modo 3-partes. La transaccion no es tipo 3-partes',
        1044  => 'Error de validación en metodo Split. El requerimiento no posee al menos 2 transacciones.',
        1045  => 'Método de procesamiento no habilitado para el comercio',
        1046  => 'psp_Amount total inválido',
        1047  => 'Error de validación en metodo Split. Referencia inválida a la transacción principal',
        1048  => 'La plataforma no permite el uso de html frames',
        1049  => 'Error en modo 3-partes. La transaccion ya ha sido procesada con anterioridad',
        1050  => 'Error de validación en modo 3-partes. Transacción inexistente',
        1051  => 'Error en modo 3-partes. Error al cargar el archivo de idioma para el formulario',
        1052  => 'Acceso denegado',
        1053  => 'La tarjeta crédito ingresada es inválida para la promoción',
        1054  => 'Promoción no definida para el comercio',
        1055  => 'El campo psp_FirstPaymentDeferralDate no esta permitido para el producto/método de procesamiento',
        1056  => 'Tipo de Documento no soportado por PagoMisCuentas',
        1057  => 'El numero de tarjeta no pertenece a ningun rango de prefijos',
        1058  => 'Referencia de Transacción duplicada dentro del requerimiento Split',
        1059  => 'BIN no habilitado',
        1060  => 'Error eliminando datos',
        1061  => 'MerchantId duplicado dentro del requerimiento Split',
        1062  => 'WebPay Transbank no permite operaciones con importes en centavos',
        1063  => 'Moneda invalida para el método de procesamiento',
        1064  => 'El campo psp_PaymentAmount es obligatorio en el modo psp_Plan=CC',
        1065  => 'No se ha recibido respuesta de WebPay Transbank',
        1066  => 'Cantidad de cuotas no permitida',
        1067  => 'Idioma de formulario no soportado por el medio de pagos',
        1068  => 'psp_ExpDate1 invalido',
        1069  => 'psp_ExpDate2 invalido',
        1070  => 'psp_ExpDate3 invalido',
        1071  => 'psp_FirstExpDate invalido',
        1072  => 'Imposible anular una Autorizacion por un monto mayor o menor',
        1073  => 'El monto a capturar supera al maximo permitido',
        1074  => 'Plan invalido para el producto',
        1075  => 'Transacción no susceptible de ser capturada',
        1076  => 'Imposible anular una Devolucion por un monto mayor o menor',
        1077  => 'Error de sistema',
        1078  => 'Imposible anular por un monto mayor o menor',
        1079  => 'Producto no soportado para el método del procesamiento',
        1080  => 'Plan invalido para el método de procesamiento',
        1081  => 'Plan invalido para la cantidad de cuotas informadas',
        1082  => 'El campo psp_PaymentAmount no esta permitido para el método de procesamiento',
        1083  => 'psp_CardExpDate invalido',
        1084  => 'Se requiere envio de campo psp_CardExpDate',
        1085  => 'Se requiere envio de campo psp_CardNumber y psp_CardExpDate',
        1086  => 'Campos e-commerce de Visa incompletos',
        1087  => 'Pais invalido para el método de procesamiento',
        1088  => 'Error en conexión al servicio de pago',
        1089  => 'Autorizacion capturada con anterioridad',
        1090  => 'Transaccion recurrente no soportada por el servicio de pago',
        1091  => 'Transacción inexistente en el servicio de pago',
        1092  => 'Requerimiento SOAP inválido, verifique estructura de datos de envío',
        1093  => 'Método de procesamiento inválido',
        1094  => 'QueryCriteria inválido',
        1095  => 'Consulta inválida',
        1096  => 'El servicio de pago ha devuelto un código de respuesta inesperado',
        1097  => 'Relacion Pais-Moneda-Producto invalida para el servicio de pago',
        1098  => 'psp_3dSecureAction invalido para el servicio de pago',
        1099  => 'Timeout aguardando respuesta del servicio de pago',
        1100  => 'Servicio en mantenimiento',
        1101  => 'El browser del usuario no tiene habilitado el uso de cookies',
        1102  => 'Transaccion recurrente no acepta cuotas',
        1103  => 'Metodo no disponible para transacciones recurrentes',
        1104  => '3D-Secure no soportado por el servicio de pago',
    );

    public function getConfigData($field, $storeId = null)
    {
        return $this->_method->getConfigData($field, $storeId);
    }
    
    public function isDebugMode()
    {
        return $this->_method->isDebug();
    }
    
    /**
     * @var Hd_Nps_Model_Psp 
     */
    protected $_method;
    
    /**
     * @return Hd_Nps_Model_Psp 
     */
    protected function _getMethod()
    {
        return $this->_method;
    }

    /**
     * Implementacion para recibir el PaymentMethod
     * @param type $params
     */
    public function __construct($params)
    {
        if (@$method = $params['method']) {
            if ($method instanceof Hd_Nps_Model_Psp) {
                $this->_method = $method;
                $this->_xmlConfigPathSoapWsdlUrl = $method->getXmlConfigPathSoapWsdlUrl();
            }
        }
        parent::__construct();
    }
}