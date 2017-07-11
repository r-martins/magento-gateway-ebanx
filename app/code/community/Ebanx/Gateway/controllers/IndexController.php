<?php

class Ebanx_Gateway_IndexController extends Mage_Core_Controller_Front_Action
{
	private $helper;
	private $order;
	private $hash;

	public function notificationAction()
	{
		try {
			$this->initialize();
		} catch (Ebanx_Gateway_Exception $e) {
			$this->helper->errorLog($e->getMessage());

			return $this->setResponseToJson([
				'success' => false,
				'message' => $e->getMessage()
			]);
		}

		try {
			$statusEbanx = $this->loadEbanxPaymentStatus();
			$this->updateOrder($statusEbanx);

			$this->setResponseToJson([
				'success' => true,
				'order' => $this->order->getIncrementId()
			]);
		} catch (Exception $e) {
			$this->order->addStatusHistoryComment($this->helper->__('EBANX: We could not update the order status. Error message: %s.', $e->getMessage()));
			$this->helper->errorLog($e->getMessage());
			Mage::throwException($e->getMessage());
		}
	}

	private function initialize()
	{
		$this->helper = Mage::helper('ebanx/order');
		$this->validateEbanxPaymentRequest();
		$this->hash = $this->getRequest()->getParam('hash');
		$this->loadOrder();
	}

	private function validateEbanxPaymentRequest()
	{
		$request = $this->getRequest();

		if (empty($request->getParam('hash'))) {
			throw new Ebanx_Gateway_Exception($this->helper->__('EBANX: Invalid hash parameter.'));
		}

		if (empty($request->getParam('merchant_payment_code'))) {
			throw new Ebanx_Gateway_Exception($this->helper->__('EBANX: Invalid merchant code parameter.'));
		}

		if (empty($request->getParam('payment_type_code'))) {
			throw new Ebanx_Gateway_Exception($this->helper->__('EBANX: Invalid payment code parameter.'));
		}
	}

	private function loadOrder()
	{
		$order = $this->helper->getOrderByHash($this->hash);
		$this->order = $order;
	}

	private function setResponseToJson($data)
	{
		$this->getResponse()->clearHeaders()->setHeader(
			'Content-type',
			'application/json'
		);

		$this->getResponse()->setBody(
			Mage::helper('core')->jsonEncode($data)
		);
	}

	private function loadEbanxPaymentStatus()
	{
		$api = Mage::getSingleton('ebanx/api')->ebanx();
		$isSandbox = $this->order->getPayment()->getEbanxEnvironment() === 'sandbox';
		$payment = $api->paymentInfo()->findByHash($this->hash, $isSandbox);

		$this->helper->log($payment, 'ebanx_payment_notification');

		if ($payment['status'] !== 'SUCCESS') {
			throw new Ebanx_Gateway_Exception($this->helper->__('EBANX: Payment doesn\'t exist.'));
		}

		return $payment['payment']['status'];
	}

	// Actions

	private function updateOrder($statusEbanx)
	{
		$statusMagento = $this->helper->getEbanxMagentoOrder($statusEbanx);

		$this->order->setData('status', $statusMagento);
		$this->order->addStatusHistoryComment($this->helper->__('EBANX: The payment has been updated to: %s.', $this->helper->getTranslatedOrderStatus($statusEbanx)));
		$this->order->save();
	}
}
