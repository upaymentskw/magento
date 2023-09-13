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

class Plugin implements BuilderInterface
{

    public function build(array $buildSubject)
    {
        return [
            'plugin' => [
                'src' => 'magento'
            ]
        ];
    }
}
