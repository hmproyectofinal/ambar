<?php
class Ids_Andreani_Block_Adminhtml_Pedidos_Edit_Renderer_Button extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

  public function render(Varien_Object $row) {
      $columnaID = $row->getId();
      //You can write html for your button here
      $model = Mage::getModel('andreani/order')->load($columnaID);
      $constanciaURL = $model->getData('constancia');
      $estadoenvio = $model->getData('estado');
      $shipments = Mage::getResourceModel('sales/order_shipment_collection')
          ->setOrderFilter($model->getIdOrden())
          ->load();
      foreach ($shipments AS $shipment) {
          if (!empty($shipment)) {
              //Si el envío está hecho y hay datos de andreani en la DB para generar
              if ($shipment->getAndreaniDatosGuia() != '') {
                  $htmlBoton = '<a  href="'.$this->getUrl('andreani/generarguia/index', array('shipment' => $shipment->getId())).'" target="_blank"><button >Imprimir Constancia</button></a>';
              }
          }
      }
//      $htmlBoton = get_class($shipments);

      if ($constanciaURL != '') {
          //URL de andreani servicio de impresion viejo
          $htmlViejo = '<a  href="'.$constanciaURL.'" target="_blank"><button >Imprimir Constancia</button></a>';
          $html = ($htmlBoton == '')? $htmlViejo : $htmlBoton;
      }else{
          $html = '<span>No hay ninguna constancia para ser impresa.</span>';
          if ($estadoenvio != 'Enviado') {
              $html = $html . "El Pedido no ha sido Enviado.";
          }else{
              $html = $htmlBoton;
          }
      }
      return $html;
  }
}