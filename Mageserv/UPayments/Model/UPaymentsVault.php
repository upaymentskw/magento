<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageserv\UPayments\Model;

use Magento\Vault\Model\Method\Vault;


class UPaymentsVault extends Vault
{
    public function isInitializeNeeded()
    {
        return false;
    }
}
