<?php
declare(strict_types=1);

/**
 * @category
 * @package _
 * @author Nikoloz Shubitidze <info@scandiweb.com>
 */

namespace Shubo\TBC\Gateway\Http\Client;

use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class Transaction implements ClientInterface
{
    /**
     * @param Curl $curl
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected Curl $curl,
        protected LoggerInterface $logger,
    ) {
    }

    /**
     * Send the request to Flitt and return the response.
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $this->curl->setHeaders($transferObject->getHeaders());
        $this->curl->post($transferObject->getUri(), json_encode($transferObject->getBody()));

        $response = json_decode($this->curl->getBody(), true);
        $response = $response['response'];

        $this->logger->info(
            json_encode([
                'request' => [
                    $transferObject->getUri(),
                    $transferObject->getHeaders(),
                    $transferObject->getMethod(),
                    $transferObject->getBody()
                ],
                'response' => $response
            ])
        );

        return $response;
    }
}
