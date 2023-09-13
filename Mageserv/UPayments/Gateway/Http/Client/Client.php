<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageserv\UPayments\Gateway\Http\Client;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class Client implements ClientInterface
{
    const SUCCESS = 1;
    const FAILURE = 0;

    /**
     * @var Logger
     */
    private $logger;
    private $apiClient;
    private $serializer;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger,
        Api $apiClient,
        Json $serializer
    )
    {
        $this->logger = $logger;
        $this->apiClient = $apiClient;
        $this->serializer = $serializer;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();
        if(empty($request['endpoint']))
            return [
                'success' => true,
                'message' => 'No need for this request'
            ];

        $endpoint = "";
        $type = "POST";
        if(!empty($request['endpoint'])){
            $endpoint = $request['endpoint'];
            unset($request['endpoint']);
        }
        if(!empty($request['request_type'])){
            $type = $request['request_type'];
            unset($request['request_type']);
        }

        return  $this->serializer->unserialize($this->apiClient->sendRequest(
            $endpoint,
            $type,
            $request
        ));
    }
}
