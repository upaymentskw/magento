<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageserv\UPayments\Gateway\Request;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Mageserv\UPayments\Gateway\Http\Client\Api;
use Mageserv\UPayments\Helper\Data;
use Mageserv\UPayments\Model\Adminhtml\Source\CurrencySelect;
use Mageserv\UPayments\Observer\DataAssignObserver;

class CaptureRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;
    private $currencySelect;
    private $customerRepository;
    private $uidHelper;
    private $cartRepository;
    private $chargeRequestBuilder;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        CurrencySelect $currencySelect,
        CustomerRepositoryInterface $customerRepository,
        Data $uidHelper,
        CartRepositoryInterface $cartRepository,
        BuilderInterface $chargeRequestBuilder
    ) {
        $this->config = $config;
        $this->currencySelect = $currencySelect;
        $this->customerRepository = $customerRepository;
        $this->uidHelper = $uidHelper;
        $this->cartRepository = $cartRepository;
        $this->chargeRequestBuilder = $chargeRequestBuilder;
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
        $payment = $paymentDO->getPayment();
        if (!$payment instanceof OrderPaymentInterface) {
            throw new \LogicException('Order payment should be provided.');
        }
        $chargeRequest = $this->chargeRequestBuilder->build([
            'order' => $payment->getOrder(),
            'payment' => $buildSubject['payment']
        ]);
        $additional_data = $payment->getAdditionalInformation();
        if(!empty($additional_data[DataAssignObserver::PAYMENT_TOKEN])){
            $token = $additional_data[DataAssignObserver::PAYMENT_TOKEN];
        }else{
            $extensionAttributes = $payment->getExtensionAttributes();
            $paymentToken = $extensionAttributes->getVaultPaymentToken();
            $token = $paymentToken->getGatewayToken();
        }
        if($token){
            $chargeRequest['tokens']['creditCard'] = $token;
        }
        $chargeRequest['endpoint'] = Api::CHARGE_ENDPOINT;
        return $chargeRequest;
        /*$use_order_currency = $this->currencySelect->UseOrderCurrency($payment);
        if ($use_order_currency) {
            $currency = $payment->getOrder()->getOrderCurrencyCode();
            $amount = $payment->getOrder()->getBaseCurrency()->convert($amount, $currency);
            $amount = $payment->formatAmount($amount, true);
        } else {
            $currency = $payment->getOrder()->getBaseCurrencyCode();
        }
        $order = $payment->getOrder();

        $quote_id = $order->getQuoteId();
        $quote = $this->cartRepository->get($quote_id);
        $items = $order->getAllVisibleItems();
        $items_arr = array_map(function ($p) {
            $q = (int)$p->getQtyOrdered();
            return "{$p->getName()} ({$q})";
        }, $items);

        $cart_desc = implode(', ', $items_arr);
        $orderReqs = [
            'order' => [
                'id' => $quote->getReservedOrderId(),
                'reference' => 'Upayments_order_' . $order->getIncrementId(),
                'description' => $cart_desc,
                'currency' => $currency,
                'amount' => $amount
            ],
            'reference' => [
                'id' => 'Upayments_order_' . $order->getIncrementId()
            ]
        ];
        $customerId = $order->getCustomerId();
        if($customerId){
            $customer = $this->customerRepository->getById($customerId);
            $uid = $customer->getCustomAttribute(\Mageserv\UPayments\Setup\InstallData::UPAYMENTS_TOKEN_ATTRIBUTE) ? $customer->getCustomAttribute(\Mageserv\UPayments\Setup\InstallData::UPAYMENTS_TOKEN_ATTRIBUTE)->getValue() : null;
            if(!$uid){
                $uid = $this->uidHelper->generateCustomerUid($customer->getEmail());
                $customer->setCustomAttribute(\Mageserv\UPayments\Setup\InstallData::UPAYMENTS_TOKEN_ATTRIBUTE, $uid);
                $this->customerRepository->save($customer);
            }
        }else{
            $uid = $this->uidHelper->generateCustomerUid($order->getCustomerEmail());
        }
        $customerReqs = [
            'customer' => [
                'uniqueToken' => $uid,
                'name' => $order->getBillingAddress()->getFirstname() . " " . $order->getBillingAddress()->getLastname(),
                'email' => $order->getCustomerEmail(),
                'mobile' => substr($order->getBillingAddress()->getTelephone(), -8)
            ]
        ];
        $additional_data = $payment->getAdditionalInformation();
        if(!empty($additional_data[DataAssignObserver::PAYMENT_TOKEN])){
            $token = $additional_data[DataAssignObserver::PAYMENT_TOKEN];
        }else{
            $extensionAttributes = $payment->getExtensionAttributes();
            $paymentToken = $extensionAttributes->getVaultPaymentToken();
            $token = $paymentToken->getGatewayToken();
        }
        return [
            'endpoint' => Api::AUTO_DEDUCT_ENDPOINT,
            ...$orderReqs,
            'language' => $this->uidHelper->getConfigData(Data::PAYPAGE_LANG_XML_PATH),
            ...$customerReqs,
            'card' => [
                'token' => $token
            ]
        ];*/
    }
}
