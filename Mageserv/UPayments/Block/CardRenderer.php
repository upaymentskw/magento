<?php

namespace Mageserv\UPayments\Block;

use Magento\Vault\Block\AbstractCardRenderer;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Mageserv\UPayments\Model\Ui\ConfigProvider;


class CardRenderer extends AbstractCardRenderer
{
    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === ConfigProvider::CODE_UPAYMENTS_WHITELIST;
    }

    /**
     * @return string
     */
    public function getNumberLast4Digits()
    {
        $tokenDetails = $this->getTokenDetails();

        return substr($tokenDetails['number'], -4);
    }

    /**
     * @return string
     */
    public function getExpDate()
    {
        return substr($this->getToken()->getExpiresAt(), 0, 10);
    }

    /**
     * @return string
     */
    public function getIconUrl()
    {
        $card_type = $this->getTokenDetails()['scheme'];
        $m_card_type = $this->convertCardType($card_type);

        return $this->getIconForType($m_card_type)['url'];
    }

    /**
     * @return int
     */
    public function getIconHeight()
    {
        return null; // $this->getIconForType($this->getTokenDetails()['type'])['height'];
    }

    /**
     * @return int
     */
    public function getIconWidth()
    {
        return null; // $this->getIconForType($this->getTokenDetails()['type'])['width'];
    }

    //

    private function convertCardType($card_type)
    {
        switch ($card_type) {
            case 'VISA':
                return 'VI';

            case 'MASTERCARD':
                return 'MC';

            case 'AMERICANEXPRESS':
                return 'AE';

            case 'JCB':
                return 'JCB';

            case 'Discover':
                return 'DI';

            default:
                return 'OT';
        }
    }
}
