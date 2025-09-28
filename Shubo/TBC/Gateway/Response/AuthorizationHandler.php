<?php
declare(strict_types=1);

namespace Shubo\TBC\Gateway\Response;

use InvalidArgumentException;
use Shubo\TBC\Helpers\Constants;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class AuthorizationHandler implements HandlerInterface
{
    /**
     * Get the status of the payment, If the payment is authorized, Create magento payment object with transaction ID.
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];

        $payment = $paymentDO->getPayment();

        /** @var $payment Payment */
        $payment->setTransactionId($response[Constants::TBC_TRANSACTION_ID_FIELD]);
        $payment->setTransactionAdditionalInfo('response', json_encode($response));
        $payment->setIsTransactionClosed(false);
    }
}
