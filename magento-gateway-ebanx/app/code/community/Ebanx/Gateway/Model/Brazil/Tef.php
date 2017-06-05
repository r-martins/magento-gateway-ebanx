<?php

class Ebanx_Gateway_Model_Brazil_Tef extends Ebanx_Gateway_Model_Payment
{
	protected $gateway;

	protected $_code = 'ebanx_tef';

	protected $_formBlockType = 'ebanx/form_tef';
	protected $_infoBlockType = 'ebanx/info_tef';

	public function __construct()
	{
		parent::__construct();

		$this->gateway = $this->ebanx->tef();
	}
}
