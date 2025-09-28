<?php
declare(strict_types=1);

namespace Shubo\TBC\Gateway\Response;

use InvalidArgumentException;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class VoidHandler implements HandlerInterface
{
    /**
     * If the reverse response passed the validation close the magento transaction and save the response.
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

        $paymentDO = $handlingSubject['payment'];

        $payment = $paymentDO->getPayment();

        /** @var $payment Payment */
        $payment->setTransactionAdditionalInfo('response', json_encode($response));
        $payment->setIsTransactionClosed(true);
    }
}
