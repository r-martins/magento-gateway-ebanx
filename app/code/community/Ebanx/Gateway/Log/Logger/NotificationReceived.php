<?php

require_once Mage::getBaseDir('lib') . '/Ebanx/vendor/autoload.php';

final class Ebanx_Gateway_Log_Logger_NotificationReceived extends Ebanx_Gateway_Log_Logger
{
    /**
     * @param array $log_data data to log
     *
     * @return void
     */
    public static function persist(array $log_data = array())
    {
        parent::save(
            'notification_received',
            array_merge(
                Ebanx_Gateway_Log_Environment::getPlatformInfo(),
                $log_data
            )
        );
    }
}
