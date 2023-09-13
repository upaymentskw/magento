<?php
/**
 * DataAssignObserver
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Observer;


use Magento\Framework\App\Http\Context;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class DataAssignObserver extends AbstractDataAssignObserver
{

    const CC_NUMBER = 'cc_number';
    const CC_ID = 'cc_cid';
    const CC_EXP_MONTH = 'cc_exp_month';
    const CC_EXP_YEAR = 'cc_exp_year';
    const UNIQUE_CUSTOMER_TOKEN = "customerUniqueToken";
    const PAYMENT_TOKEN = 'payment_token';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::CC_NUMBER,
        self::CC_EXP_MONTH,
        self::CC_EXP_YEAR,
        self::CC_ID,
        self::PAYMENT_TOKEN
    ];

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }
        $paymentInfo = $this->readPaymentModelArgument($observer);
        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
