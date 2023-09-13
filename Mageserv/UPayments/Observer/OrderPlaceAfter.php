<?php
/**
 * OrderPlaceAfter
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Mageserv\UPayments\Model\Ui\ConfigProvider;
use Mageserv\UPayments\Setup\UpgradeData;
use Mageserv\UPayments\Helper\Data;
use Psr\Log\LoggerInterface;

class OrderPlaceAfter implements ObserverInterface
{
    protected $helper;
    protected $logger;
    public function __construct(
        Data $helper,
        LoggerInterface $logger
    )
    {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try{
            /** @var Order $order */
            $order = $observer->getOrder();
            $payment = $order->getPayment();
            $additional_information = $payment->getAdditionalInformation();
            $isChanged = false;
            if(!empty($additional_information[UpgradeData::UPAY_ORDER_ID])){
                $order->setData(UpgradeData::UPAY_ORDER_ID, $additional_information[UpgradeData::UPAY_ORDER_ID]);
                $isChanged = true;
            }
            if(!empty($additional_information[UpgradeData::UPAY_TRACK_ID])){
                $order->setData(UpgradeData::UPAY_TRACK_ID, $additional_information[UpgradeData::UPAY_TRACK_ID]);
                $isChanged = true;
            }
            if($isChanged)
                $order->save();

            if(!empty($additional_information['transaction_data']) && $order->getState() == "new"){
                $this->helper->successOrder($order, json_decode($additional_information['transaction_data'], true), false);
            }
        }catch (\Exception $exception){
            $this->logger->critical("Could not save order params" . $exception->getMessage());
        }
    }
}
