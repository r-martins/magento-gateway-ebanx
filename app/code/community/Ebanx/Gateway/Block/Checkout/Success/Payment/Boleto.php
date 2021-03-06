<?php

class Ebanx_Gateway_Block_Checkout_Success_Payment_Boleto extends Ebanx_Gateway_Block_Checkout_Success_Cashpayment
{
    /**
     * @return array
     */
    public function getEbanxBarCodeFormated()
    {
        $code = $this->getEbanxBarCode();

        return array(
            'boleto1' => '<span>' . substr($code, 0, 5) . '</span>',
            'boleto2' => '<span>' . substr($code, 5, 5) . '</span>',
            'boleto3' => '<span>' . substr($code, 10, 5) . '</span>',
            'boleto4' => '<span>' . substr($code, 15, 6) . '</span>',
            'boleto5' => '<span>' . substr($code, 21, 5) . '</span>',
            'boleto6' => '<span>' . substr($code, 26, 6) . '</span>',
            'boleto7' => '<span>' . substr($code, 32, 1) . '</span>',
            'boleto8' => '<span>' . substr($code, 33, 14) . '</span>',
        );
    }

    /**
     * @return string
     */
    public function getEbanxBarCode()
    {
        return $this->getOrder()->getPayment()->getEbanxBarCode();
    }

    /**
     * @return Ebanx_Gateway_Block_Checkout_Success_Cashpayment|void
     */
    protected function _construct()
    {
        parent::_construct();
    }
}
