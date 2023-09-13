<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageserv\UPayments\Gateway\Request;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Mageserv\UPayments\Gateway\Http\Client\Api;
use Mageserv\UPayments\Model\Adminhtml\Source\CurrencySelect;
use Mageserv\UPayments\Setup\UpgradeData;


class RefundRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;
    private $currencySelect;
    private $orderRepository;
    private $urlBuilder;


    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        CurrencySelect $currencySelect,
        OrderRepositoryInterface $orderRepository,
        UrlInterface $urlBuilder
    ) {
        $this->config = $config;
        $this->currencySelect = $currencySelect;
        $this->orderRepository = $orderRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (
            !isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];
        $amount = $buildSubject['amount'];
        $order = $paymentDO->getOrder();

        $order = $this->orderRepository->get($order->getId());
        $payment = $paymentDO->getPayment();

        if (!$payment instanceof OrderPaymentInterface) {
            throw new \LogicException('Order payment should be provided.');
        }
        $upay_order_id = $order->getCustomAttribute(UpgradeData::UPAY_ORDER_ID) ? $order->getCustomAttribute(UpgradeData::UPAY_ORDER_ID)->getValue() : null;

        if(!$upay_order_id)
            throw new \LogicException('Upay Order Id not found.');

        if ($this->currencySelect->UseOrderCurrency($payment)) {
            $currency = $payment->getOrder()->getOrderCurrencyCode();
            $amount = $payment->getOrder()->getBaseCurrency()->convert($amount, $currency);
            $amount = $payment->formatAmount($amount, true);
        }
        return [
            'endpoint' => Api::CREATE_REFUND,
            'orderId' => $upay_order_id,
            'totalPrice' => $amount,
            'customerFirstName' => $order->getCustomerFirstname(),
            'customerEmail' => $order->getCustomerEmail(),
            'customerMobileNumber' => $order->getBillingAddress() ? $order->getBillingAddress()->getTelephone() : "",
            'reference' => 'Upayments_order_' . $order->getIncrementId(),
            'notifyUrl' => $this->urlBuilder->getUrl('upayments/paypage/ipn')
        ];
    }
}
