<?php
/**
 * Checkout
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Controller\PayPage;


use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Mageserv\UPayments\Gateway\Http\Client\Api;
use Mageserv\UPayments\Helper\Data;
use Magento\Sales\Model\OrderFactory;
abstract class Checkout extends Action
{
    protected $orderRepository;
    protected $checkoutSession;
    protected $messageManager;
    protected $helper;
    protected $orderFactory;
    protected $api;
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        OrderFactory $orderFactory,
        Session $checkoutSession,
        ManagerInterface $messageManager,
        Data $helper,
        Api $api
    )
    {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->api = $api;
    }

    public function failAndRestoreQuote($message = null)
    {
        $params = $this->getRequest()->getParams();
        try{
            if($this->checkoutSession->getLastRealOrderId()){
                $order = $this->orderFactory->create()->loadByIncrementId($this->checkoutSession->getLastRealOrderId());
                $this->helper->cancelOrder($order, $message);
            }
        }catch(\Exception $exception){
            if(!empty($params['order_id'])){
                $order = $this->orderFactory->create()->loadByIncrementId($params['order_id']);
                $this->helper->cancelOrder($order, $message);
            }
        } finally {

        }
        if($this->checkoutSession->restoreQuote()){
            if(!$message)
                $message = __('Error: Payment Failed, Invalid response from gateway');
            $this->messageManager->addErrorMessage(
                $message
            );
        }
        return $this->_redirect('checkout/cart');
    }
}
