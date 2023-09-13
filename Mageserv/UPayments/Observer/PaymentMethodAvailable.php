<?php
/**
 * PaymentMethodAvailable
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Observer;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageserv\UPayments\Gateway\Http\Client\Api;
use Mageserv\UPayments\Gateway\Request\Builder\PaymentGateway;
use Mageserv\UPayments\Model\Ui\ConfigProvider;

class PaymentMethodAvailable implements ObserverInterface
{
    protected $scopeConfig;
    protected $api;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Api $api
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->api = $api;
    }

    public function execute(Observer $observer)
    {
        try{
            $paymentMethod = $observer->getEvent()->getMethodInstance();
            $code = $paymentMethod->getCode();
            $checkResult = $observer->getEvent()->getResult();
            $isWhiteLabeled = $this->api->isWhiteLabeled();
            $availableMethods = array_filter($this->api->checkAvailableMethods(), function($val, $key){
               return  $val == true;
            }, ARRAY_FILTER_USE_BOTH);
            \Mageserv\UPayments\Logger\UPaymentsLogger::ulog("Is whitelabeled::" . $isWhiteLabeled);
            \Mageserv\UPayments\Logger\UPaymentsLogger::ulog("methods::" . json_encode($availableMethods));
            if (stripos($code, "upayments_") === 0 ) {
                //enable all methods before doing the logic
                $checkResult->setData('is_available', true);
                if (!$isWhiteLabeled) {
                    if ($code != ConfigProvider::CODE_UPAYMENTS_ALL)
                        $checkResult->setData('is_available', false);
                } else {
                    $method_code =  str_replace("upayments_", "", $code);
                    if(in_array($method_code, array_keys(PaymentGateway::UPAYMENTS_METHODS_MAPPING)))
                        $method_code = PaymentGateway::UPAYMENTS_METHODS_MAPPING[$method_code];
                    \Mageserv\UPayments\Logger\UPaymentsLogger::ulog("method::" . $method_code);
                    if ($code == ConfigProvider::CODE_UPAYMENTS_ALL || !isset($availableMethods[$method_code]))
                        $checkResult->setData('is_available', false);
                    /*if ($this->scopeConfig->getValue('payment/upayments_creditcard/active')) {
                        if (($code == ConfigProvider::CODE_UPAYMENTS_WHITELIST && !$this->scopeConfig->getValue('payment/upayments_vault/active'))
                            || ($code == ConfigProvider::CODE_UPAYMENTS && $this->scopeConfig->getValue('payment/upayments_vault/active'))) {
                            $checkResult->setData('is_available', false);
                        }
                    }*/
                }
            }
            \Mageserv\UPayments\Logger\UPaymentsLogger::ulog("method::$code status::". $checkResult->getData('is_available'));
        }catch (\Exception $exception){
            \Mageserv\UPayments\Logger\UPaymentsLogger::ulog($exception->getMessage());
        }
    }
}
