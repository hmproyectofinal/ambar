Para que funcione la implementacion del "Calculador de Cuotas" es necesario copiar uno de los siguientes snippets en el template:

app/design/frontend/[package]/[theme]/catalog/product/view.phtml

Para Methodo CC
<?php echo $this->getChildHtml('payment_form_preview_nps_cc') ?>

Para Metodo BCC (Bancos Y Tarjetas)
<?php echo $this->getChildHtml('payment_form_preview_nps_bcc'); ?>
        