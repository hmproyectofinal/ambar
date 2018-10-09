<?php

class Ecloud_TransactionalEmail_Adminhtml_TransactionalEmail_EstadotemplateController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction() {
		$this->_title($this->__("Administrador de Templates de email de Estados"));
		$this->loadLayout();
		$this->_addContent($this->getLayout()->createBlock('transactionalemail/adminhtml_estadotemplate'));
		$this->renderLayout();
	}

	public function editAction()
	{
		$this->_title($this->__("Editar Item"));

		$id = $this->getRequest()->getParam("id");
		$model = Mage::getModel("transactionalemail/estadotemplate")->load($id);
		if ($model->getId()) {
			Mage::register("estadotemplate_data", $model);
			$this->loadLayout();
			$this->_setActiveMenu("transactionalemail/estadotemplate");
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Administrador de Templates"), Mage::helper("adminhtml")->__("Administrador de Templates de estado"));
			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock("transactionalemail/adminhtml_estadotemplate_edit"))->_addLeft($this->getLayout()->createBlock("transactionalemail/adminhtml_estadotemplate_edit_tabs"));
			$this->renderLayout();
		} 
		else {
			Mage::getSingleton("adminhtml/session")->addError(Mage::helper("transactionalemail")->__("Item no existe."));
			$this->_redirect("*/*/");
		}
	}

	public function newAction()
	{
		$this->_title($this->__("Nuevo item"));

		$id = $this->getRequest()->getParam("id");
		$model = Mage::getModel("transactionalemail/estadotemplate")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("estadotemplate_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("transactionalemail/estadotemplate");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Administrador de Templates"), Mage::helper("adminhtml")->__("Administrador de Templates de estado"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Descripcion"), Mage::helper("adminhtml")->__("Descripcion"));


		$this->_addContent($this->getLayout()->createBlock("transactionalemail/adminhtml_estadotemplate_edit"))->_addLeft($this->getLayout()->createBlock("transactionalemail/adminhtml_estadotemplate_edit_tabs"));

		$this->renderLayout();

	}
	public function saveAction()
	{
		$post_data=$this->getRequest()->getPost();
		if ($post_data) {
			try {
				
				if(isset($post_data['stores'])) {
				    if(in_array('0',$post_data['stores'])){
				        $post_data['store_id'] = '0';
				    }
				    else{
				        $post_data['store_id'] = implode(",", $post_data['stores']);
				    }
				}

				$model = Mage::getModel("transactionalemail/estadotemplate")
				->addData($post_data)
				->setId($this->getRequest()->getParam("id"))
				->save();
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("estadotemplate was successfully saved"));
				Mage::getSingleton("adminhtml/session")->setEstadotemplateData(false);
				if ($this->getRequest()->getParam("back")) {
					$this->_redirect("*/*/edit", array("id" => $model->getId()));
					return;
				}
				$this->_redirect("*/*/");
				return;
			} 
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
				Mage::getSingleton("adminhtml/session")->setEstadotemplateData($this->getRequest()->getPost());
				$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
				return;
			}

		}
		$this->_redirect("*/*/");
	}



	public function deleteAction()
	{
		if( $this->getRequest()->getParam("id") > 0 ) {
			try {
				$model = Mage::getModel("transactionalemail/estadotemplate");
				$model->setId($this->getRequest()->getParam("id"))->delete();
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item fue borrado."));
				$this->_redirect("*/*/");
			} 
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
				$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
			}
		}
		$this->_redirect("*/*/");
	}	
}