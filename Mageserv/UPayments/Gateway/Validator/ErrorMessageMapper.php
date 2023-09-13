<?php
/**
 * ErrorMessageMapper
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Gateway\Validator;


use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;

class ErrorMessageMapper implements ErrorMessageMapperInterface
{
    /**
     * Returns customized error message by provided code.
     * If message not found `null` will be returned.
     *
     * @param string $code
     * @return Phrase|null
     */
    public function getMessage(string $code)
    {
        return $code;
    }
}
