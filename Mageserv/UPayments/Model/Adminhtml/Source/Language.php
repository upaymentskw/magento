<?php
/**
 * Language
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Model\Adminhtml\Source;


class Language implements \Magento\Framework\Data\OptionSourceInterface
{
    const LANG_AR = "ar";
    const LANG_EN = "en";

    public function toOptionArray()
    {
        return [
            [
                'label' => __("Arabic"),
                'value' => self::LANG_AR
            ],
            [
                'label' => __("English"),
                'value' => self::LANG_EN
            ]
        ];
    }
}
