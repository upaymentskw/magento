<?php
/**
 * Fail
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Controller\PayPage;


class Fail extends Checkout
{

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $message = __("Order Payment failed");
        if(!empty($params['message']))
            $message = $params['message'];
        return $this->failAndRestoreQuote($message);
    }
}
