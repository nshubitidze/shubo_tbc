<?php
declare(strict_types=1);

namespace Shubo\TBC\Gateway\Request;

use Laminas\Http\Request;
use Flitt\Helper\ApiHelper;
use InvalidArgumentException;
use Shubo\TBC\Helpers\Config;
use Shubo\TBC\Helpers\Constants;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class AuthorizationRequest implements BuilderInterface
{
    /**
     * @param Config $config
     */
    public function __construct(
        protected Config $config
    ) {
    }

    /**
     * Setup request data to be used for capture.
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment']) || !($buildSubject['payment'] instanceof PaymentDataObjectInterface)) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $buildSubject['payment'];

        $headers = [
            'Content-Type' => 'application/json'
        ];

        $params = [
            'order_id' => $payment->getOrder()->getOrderIncrementId(),
            'merchant_id' => $this->config->getMerchantId()
        ];

        $params['signature'] = ApiHelper::generateSignature($params, $this->config->getSecretKeyForPurchase());

        $body = [
            'request' => $params
        ];

        return [
            'uri' => Constants::FLIT_API_STATUS_ENDPOINT,
            'method' => Request::METHOD_POST,
            'body' => $body,
            'headers' => $headers,
        ];
    }
}
