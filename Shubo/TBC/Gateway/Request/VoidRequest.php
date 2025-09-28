<?php
declare(strict_types=1);

namespace Shubo\TBC\Gateway\Request;

use Laminas\Http\Request;
use Flitt\Helper\ApiHelper;
use Shubo\TBC\Helpers\Config;
use Shubo\TBC\Helpers\Constants;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class VoidRequest implements BuilderInterface
{
    /**
     * @param Config $config
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        protected Config $config,
        protected OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * Setup request data to be used for reverse.
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $this->orderRepository->get($paymentDO->getOrder()->getId());

        $headers = [
            'Content-Type' => 'application/json'
        ];

        $params = [
            'order_id' => $order->getIncrementId(),
            'merchant_id' => $this->config->getMerchantId(),
            'amount' => (int) $order->getGrandTotal(),
            'currency' => $order->getOrderCurrencyCode(),
        ];

        $params['signature'] = ApiHelper::generateSignature($params, $this->config->getSecretKeyForPurchase());

        $body = [
            'request' => $params
        ];

        return [
            'uri' => Constants::FLIT_API_REVERSE_ENDPOINT,
            'method' => Request::METHOD_POST,
            'body' => $body,
            'headers' => $headers,
        ];
    }
}
