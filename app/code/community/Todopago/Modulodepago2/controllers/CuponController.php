<?php

class Todopago_Modulodepago2_CuponController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {


        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle($this->__("Cupón de Pago"));
        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
        $breadcrumbs->addCrumb("home", array(
            "label" => $this->__("Home Page"),
            "title" => $this->__("Home Page"),
            "link" => Mage::getBaseUrl()
        ));

        $breadcrumbs->addCrumb("cupón de pago", array(
            "label" => $this->__("Cupón de Pago"),
            "title" => $this->__("Cupón de Pago")
        ));

        $this->renderLayout();
    }

    public function codebarAction() {
        require_once('Codebar/BCGFontFile.php');
        require_once('Codebar/BCGColor.php');
        require_once('Codebar/BCGDrawing.php');
        $this->getResponse()->setHeader('Content-type', 'image/png');
// The arguments are R, G, and B for color.
        $colorFont = new BCGColor(0, 0, 0);
        $colorBack = new BCGColor(255, 255, 255);



        $code = $this->codeBarFactory($this->getRequest()->get('CODE')); // Or another class name from the manual
        $code->setScale(2); // Resolution
        $code->setThickness(30); // Thickness
        $code->setForegroundColor($colorFont); // Color of bars
        $code->setBackgroundColor($colorBack); // Color of spaces

        $code->parse($this->getRequest()->get('BAR')); // Text

        $drawing = new BCGDrawing('', $colorBack);
        $drawing->setBarcode($code);

        $drawing->draw();

        $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
    }

    private function codeBarFactory($code) {
        $var = null;
        switch ($code) {
            case 'CODE_128':
                require_once('Codebar/BCGcode128.php');
                $var = new BCGcode128();
                break;
            case'INTERLEAVED_2_OF_5':
                require_once('Codebar/BCGi25.php');
                $var = new BCGi25();
                break;
        }
        return $var;
    }

}
