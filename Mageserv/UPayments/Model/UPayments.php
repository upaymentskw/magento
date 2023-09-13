<?php
/**
 * UPayments
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Model;


class UPayments extends \Magento\Payment\Model\Method\Adapter
{
    //

    public function getConfigPaymentAction()
    {
        return parent::getConfigPaymentAction();
    }

    public function isInitializeNeeded()
    {
        return parent::isInitializeNeeded();
    }

    public function initialize($paymentAction, $stateObject)
    {
        return parent::initialize($paymentAction, $stateObject);
    }
}
