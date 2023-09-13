<?php

namespace Mageserv\UPayments\Model\Adminhtml\Source;


/**
 * Class CurrencySelect
 */
class CurrencySelect implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $currencyFactory;

    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    )
    {
        $this->currencyFactory = $currencyFactory;
    }

    const CURRENCY_BASE = 'base_currency';
    const CURRENCY_ORDER = 'order_currency';


    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => $this::CURRENCY_BASE,
                'label' => 'Base Currency (recommended)'
            ],
            [
                'value' => $this::CURRENCY_ORDER,
                'label' => 'Order Currency'
            ]
        ];
    }

    //

    /**
     * @param $payment
     * @return bool
     */
    public function UseOrderCurrency($payment)
    {
        $paymentMethod = $payment->getMethodInstance();
        $order = $payment->getOrder();

        if ($order->getOrderCurrencyCode() == $order->getBaseCurrencyCode()) {
            return false;
        }

        return $this->IsOrderCurrency($paymentMethod);
    }

    /**
     * @param $paymentMethod
     * @return bool
     */
    public function IsOrderCurrency($paymentMethod)
    {
        $currency_used = $paymentMethod->getConfigData('currency_select');
        return $currency_used == CurrencySelect::CURRENCY_ORDER;
    }


    public function convertOrderToBase($payment, $tranAmount)
    {

        $order = $payment->getOrder();
        $baseAmount = $order->getBaseGrandTotal();
        $rate = $this->currencyFactory->create()
            ->load($order->getOrderCurrencyCode())
            ->getAnyRate($order->getBaseCurrencyCode());
        $amount = $tranAmount * $rate;
        $amount = number_format((float)$amount, 3, '.', '');

        if (abs($baseAmount - $amount) < 0.1) {
            $amount = $baseAmount;
        }

        return $amount;
    }
}
