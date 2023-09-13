<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageserv\UPayments\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use Mageserv\UPayments\Helper\Data;
use Mageserv\UPayments\Model\Ui\ConfigProvider;
use Mageserv\UPayments\Model\Ui\StatusEnum;
use Mageserv\UPayments\Setup\UpgradeData;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\OrderFactory;
class TxnIdHandler implements HandlerInterface
{
    protected $helper;
    protected $logger;
    protected $paymentTokenRepository;
    public function __construct(
        Data $helper,
        LoggerInterface $logger,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    )
    {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (
            !isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        $payment = $paymentDO->getPayment();
        $transaction = $response['data']['transactionData'];
        $isClosed = $transaction['result'] == StatusEnum::CAPTURED;
        /** @var $payment \Magento\Sales\Model\Order\Payment */

        $payment
            ->setAmountPaid($transaction['total_price'])
            ->setTransactionId($transaction['payment_id'])
            ->setIsTransactionClosed($isClosed);

        try{
            $payment->setAdditionalInformation(UpgradeData::UPAY_ORDER_ID , $transaction['refund_order_id']);
            $payment->setAdditionalInformation(UpgradeData::UPAY_TRACK_ID , $transaction['track_id']);
            $payment->setAdditionalInformation("transaction_data" , json_encode($transaction));
        }catch (\Exception $e){
            $this->logger->critical(__("Could not save additional information") . $e->getMessage());
        }

        // remove card if not requested
        try{
            $isSavedCardRequest = $payment->getAdditionalInformation(VaultConfigProvider::IS_ACTIVE_CODE);
            $extensionAttributes = $payment->getExtensionAttributes();
            $paymentToken = $extensionAttributes->getVaultPaymentToken();
            if($paymentToken && !$isSavedCardRequest && ($payment->getMethodInstance()->getCode() == ConfigProvider::CODE_UPAYMENTS_WHITELIST))
                $this->paymentTokenRepository->delete($paymentToken);
        }catch (\Exception $exception){
            \Mageserv\UPayments\Logger\UPaymentsLogger::ulog($exception->getMessage());

            $this->logger->critical("Could not revoke the card toke due to:" . $exception->getMessage());
        }
    }
}
