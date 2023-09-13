<?php
/**
 * Product
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Gateway\Request\Builder;


use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderInterface;

class Order implements BuilderInterface
{
    protected $currencySelect;

    public function __construct(
        \Mageserv\UPayments\Model\Adminhtml\Source\CurrencySelect $currencySelect
    )
    {
        $this->currencySelect = $currencySelect;
    }

    public function build(array $buildSubject)
    {
        if (
            !isset($buildSubject['order'])
            || !$buildSubject['order'] instanceof OrderInterface
        ) {
            throw new \InvalidArgumentException('order data object should be provided');
        }
        $order = $buildSubject['order'];
        $amount = $order->getBaseGrandTotal();
        $use_order_currency = $this->currencySelect->UseOrderCurrency($order->getPayment());

        if ($use_order_currency) {
            $currency = $order->getOrderCurrencyCode();
            $amount = $order->getBaseCurrency()->convert($amount, $currency);
            $amount = $order->getPayment()->formatAmount($amount, true);
        } else {
            $currency = $order->getBaseCurrencyCode();
        }
        $items = $order->getAllVisibleItems();
        $items_arr = array_map(function ($p) {
            $q = (int)$p->getQtyOrdered();
            return "{$p->getName()} ({$q})";
        }, $items);

        $cart_desc = implode(', ', $items_arr);
        return [
            'order' => [
                'id' => $order->getIncrementId(),
                'reference' => 'Upayments_order_' . $order->getIncrementId(),
                'description' => $cart_desc,
                'currency' => $currency,
                'amount' => $amount
            ],
            'reference' => [
                'id' => 'Upayments_order_' . $order->getIncrementId()
            ]
        ];
    }
}
