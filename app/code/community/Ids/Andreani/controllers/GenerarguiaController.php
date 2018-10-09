<?php
/**
 * Created by Jhonattan Campo.
 * Date: 08/08/16
 * Class Ids_Andreani_GenerarguiaController
 */
require_once(Mage::getBaseDir('lib') . '/html2fpdf/vendor/autoload.php');
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
class Ids_Andreani_GenerarguiaController extends Mage_Core_Controller_Front_Action
{

    /**
     * @description recibe los parámetros y le manda la información necesaria a la librería que
     * se encarga de generar el pdf.
     */
    public function indexAction()
    {
        $shipment   = $this->getRequest()->getParam('shipment');

        if($shipment!='')
        {
            $shipmentObj        = Mage::getModel('sales/order_shipment')->load($shipment);
            $andreaniDatosGuia  = $shipmentObj->getAndreaniDatosGuia();
            $andreaniDatosGuia  = json_decode(unserialize($andreaniDatosGuia));
            $object             = $andreaniDatosGuia->datosguia->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult;

            $html = $this->generarhtml($shipment);
            Mage::helper('andreani')->crearCodigoDeBarras($object->NumeroAndreani);
            try{
                $pdf = new Html2Pdf('P', 'A4', 'en', true,'UTF-8', array(0, 0, 0, 0));
                $pdf->setDefaultFont('Helvetica');
                // Volcamos el HTML contenido en la variable $html para crear el contenido del PDF
                $pdf -> WriteHTML($html);
                // Volcamos el pdf generado con nombre 'doc.pdf'. En este caso con el parametro 'D' forzamos la descarga del mismo.
                $pdf -> Output(date_timestamp_get(date_create()).'_orden_'.$shipmentObj->getOrder()->getIncrementId().'.pdf', 'D');
                unlink(Mage::getBaseDir('media').'/uploads/default/'.$object->NumeroAndreani.'.png');
            } catch (Html2PdfException $e) {
                $formatter = new ExceptionFormatter($e);
                echo $formatter->getHtmlMessage();
            }
        }
    }

    /**
     * @description genera el html que será parseado a PDF por la librería "html2pdf"
     */
    public function generarhtml($shipmentId)
    {
        //$shipmentId         = $this->getRequest()->getParam('shipment');
        $shipment           = Mage::getModel('sales/order_shipment')->load($shipmentId);
        
        $andreaniDatosGuia  = $shipment->getAndreaniDatosGuia();
        $andreaniDatosGuia  = json_decode(unserialize($andreaniDatosGuia));

        $block              = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'generarguia',
            array('template' => 'andreani/guia.phtml')
        )->setData('andreani_datos_guia',$andreaniDatosGuia);

        return $block->toHtml();

    }

    public function generarhtmlAction($shipmentId)
    {
        $shipmentId         = $this->getRequest()->getParam('shipment');
        $shipment           = Mage::getModel('sales/order_shipment')->load($shipmentId);

        $andreaniDatosGuia  = $shipment->getAndreaniDatosGuia();
        $andreaniDatosGuia  = json_decode(unserialize($andreaniDatosGuia));

        $block              = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'generarguia',
            array('template' => 'andreani/guia.phtml')
        )->setData('andreani_datos_guia',$andreaniDatosGuia);

        echo $block->toHtml();

    }
   
}