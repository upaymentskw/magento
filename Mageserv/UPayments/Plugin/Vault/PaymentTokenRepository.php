<?php
/**
 * PaymentTokenRepository
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Plugin\Vault;


use Mageserv\UPayments\Gateway\Http\Client\Api;
use Mageserv\UPayments\Model\Ui\ConfigProvider;

class PaymentTokenRepository
{
    protected $api;
    public function __construct(
        Api $api
    )
    {
        $this->api = $api;
    }

    public function beforeDelete(
        \Magento\Vault\Model\PaymentTokenRepository $subject,
        \Magento\Vault\Api\Data\PaymentTokenInterface $paymentToken
    ){
        if($paymentToken->getPaymentMethodCode() == ConfigProvider::CODE_UPAYMENTS_WHITELIST)
            $this->api->revokeCardToken($paymentToken->getGatewayToken());
        return [$paymentToken];
    }
}
