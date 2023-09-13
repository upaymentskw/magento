<?php
/**
 * Success
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Controller\PayPage;



use Magento\Sales\Model\Order;
use Mageserv\UPayments\Model\Ui\StatusEnum;

class Success extends Checkout
{
    /**
     * payment_id=04cc4e9d476e4dea&
     * result=CAPTURED&post_date&tran_id&ref&track_id=202314474519060652001976313072&auth&
     * order_id=202210101255255144669&payment_type=card&
     * upay_order_id=ME3OdxVO6m202210101255255144669168718606510447602564906a911ef69&
     * receipt_id=ME3OdxVO6m202210101255255144669168718606510447602564906a911ef69&
     * trn_udf=test%20data
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $message = null;
        try{
            if(!empty($params['order_id']) && !empty($params['track_id'])){
                /** @var Order $order */
                $order = $this->orderFactory->create()->loadByIncrementId($params['order_id']);
                $success = $this->api->checkPayment($params['track_id']);
                if($success){
                    $this->helper->successOrder($order, $params);
                    try{
                        $this->helper->fetchSavedCardsByOrder($order);
                    }catch (\Exception $ex){}

                    return $this->_redirect('checkout/onepage/success');
                }
            }
        }catch (\Exception $exception){
            $message = $exception->getMessage();
        }
        return $this->failAndRestoreQuote($message);
    }
}
