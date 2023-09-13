<?php
/**
 * PaymentValidator
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Mageserv\UPayments\Gateway\Http\Client\Api;

class PaymentValidator extends AbstractValidator
{
    protected $api;
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        Api $api
    )
    {
        $this->api = $api;
        parent::__construct($resultFactory);
    }

    /**
     * Performs validation of result code
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }
        $response = $validationSubject['response'];
        \Mageserv\UPayments\Logger\UPaymentsLogger::ulog("CLIENT RESP::" . print_r($response, true));
        $message = $response['message'] ?? "";
        $status = (bool) $response['status'] ?? false;
        if($status && !empty($response['data']) && !empty($response['data']['transactionData'])){
            $track_id = $response['data']['transactionData']['track_id'];
            $status = $this->api->checkPayment($track_id);
            if(!$status)
                $message = "UPayments Couldn't validate the payment";
        }else{
            $status = false;
            $message = __("Couldn't validate gateway response");
        }

        return $this->createResult(
            $status,
            [$message]
        );
    }
}
