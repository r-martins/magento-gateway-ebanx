<?php

use Ebanx\Benjamin\Models\Currency;

/**
 * Ebanx_Gateway_Block_Health_Check class used to display (or not) a health check info on payment method configuration
 */
final class Ebanx_Gateway_Block_Health_Check implements Varien_Data_Form_Element_Renderer_Interface
{
    public $supportedCurrencies;

    /**
     * Method setForm is required by interface, but we don't need it
     *
     * @return void
     */
    public function setForm()
    {
    }

    /**
     * Method setConfigData is required by interface, but we don't need it
     *
     * @return void
     */
    public function setConfigData()
    {
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element Abstract element
     *
     * @return void|string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $strToAppendOnNotification = '';

        array_map(function ($value) use (&$strToAppendOnNotification) {
            if (is_string($value)) {
                $strToAppendOnNotification .= '<li>' . $value . '</li>';
            }
        }, $this->checkHealth());

        if (!empty($strToAppendOnNotification)) {
            return '<div class="notification-global"><strong class="label">Health Check: </strong><ul>' . $strToAppendOnNotification . '</ul></div><br>';
        }
    }

    /**
     * @return array
     */
    public function checkHealth()
    {
        return array(
            $this->hasMininumPhpVersion(),
            $this->hasUrlFopenAllowed(),
            $this->hasCurlMinimumVersion(),
            $this->hasAnySupportedCurrency(),
        );
    }

    /**
     * @return boolean|string
     */
    public function hasMininumPhpVersion()
    {
        if (5.4 > floatval(phpversion())) {
            return '<strong>PHP Version: </strong> The minimum version required is 5.4, you are currently using ' . phpversion() . '.';
        }
        return true;
    }

    /**
     * @return boolean|string
     */
    public function hasUrlFopenAllowed()
    {
        if (!ini_get('allow_url_fopen')) {
            return '<strong>PHP Settings: </strong> PHP setting "allow_url_fopen" must be enabled.';
        }
        return true;
    }

    /**
     * @return boolean|string
     */
    public function hasCurlMinimumVersion()
    {
        // phpcs:disable
        $curlInfo = curl_version();
        // phpcs:enable
        if (7.16 > floatval($curlInfo['version'])) {
            return '<strong>CURL Version: </strong> The minimum version required is 7.16, you are currently using ' . $curlInfo['version'] . '.';
        }
        return true;
    }

    /**
     * @return boolean|string
     */
    public function hasAnySupportedCurrency()
    {
        $this->supportedCurrencies = Currency::all();

        try {
            $stores = Mage::app()->getStores();
            $currentSupportedCurrencies = [];
            foreach ($stores as $store) {
                $currentCurrencyCode = $store->getCurrentCurrencyCode();
                if (in_array($currentCurrencyCode, $this->supportedCurrencies)) {
                    $currentSupportedCurrencies[] = $currentCurrencyCode;
                }
            }
            if (count($currentSupportedCurrencies) == 0) {
                return '<strong>Currency not supported: </strong> Your website must support one of these currencies '
                    . implode(', ', $this->supportedCurrencies) . '.';;
            }
        } catch (Exception $e) {
            return true;
        }
        return true;
    }
}
