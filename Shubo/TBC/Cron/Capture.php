<?php
declare(strict_types=1);

namespace Shubo\TBC\Cron;

use Exception;
use Psr\Log\LoggerInterface;
use Shubo\TBC\Helpers\Config;
use Magento\Sales\Model\Order;
use Shubo\TBC\Helpers\Constants;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Capture
{
    /**
     * @param Config $config
     * @param CollectionFactory $orderCollectionFactory
     * @param LoggerInterface $logger
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     */
    public function __construct(
        protected Config $config,
        protected CollectionFactory $orderCollectionFactory,
        protected LoggerInterface $logger,
        protected InvoiceService $invoiceService,
        protected Transaction $transaction
    ) {
    }

    /**
     * If auto capture is enabled, capture authorized orders.
     *
     * @return void
     */
    public function execute()
    {
        if ($this->config->isAutoCapture()) {
            $orders = $this->getAuthorizedOrders();

            /** @var Order $order */
            foreach ($orders as $order) {
                if (!$order->canInvoice()) {
                    $this->logger->error(
                        sprintf('Invoice can not be created for the order: %s', $order->getId())
                    );
                }

                try {
                    $invoice = $this->invoiceService->prepareInvoice($order);
                    $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
                    $invoice->register();

                    $this->transaction->addObject($invoice)->addObject($invoice->getOrder())->save();
                } catch (LocalizedException|Exception $e) {
                    $this->logger->error(
                        sprintf(
                            'Error while creating the invoice for the order: %s, ERROR MESSAGE: %s',
                            $order->getId(),
                            $e->getMessage()
                        )
                    );
                }
            }
        }
    }

    /**
     * Get the orders that are paid by the tbc payment and are not captured yet.
     *
     * @return array
     */
    protected function getAuthorizedOrders(): array
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->getSelect()
            ->join(
                ['payment' => $collection->getTable('sales_order_payment')],
                'main_table.entity_id = payment.parent_id',
                ['method', 'base_amount_authorized', 'base_amount_paid']
            )
            ->where('payment.method = ?', Constants::PAYMENT_CODE)
            ->where('main_table.state = ?', Order::STATE_PROCESSING)
            ->where('payment.base_amount_authorized > 0')
            ->where('payment.base_amount_paid = 0 OR payment.base_amount_paid IS NULL');

        return $collection->getItems();
    }
}
