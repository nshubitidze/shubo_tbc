<?php
declare(strict_types=1);

namespace Shubo\TBC\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        protected TransferBuilder $transferBuilder
    ) {
    }

    /**
     * @inheritdoc
     */
    public function create(array $request)
    {
        return $this->transferBuilder->setUri($request['uri'])
            ->setBody($request['body'])
            ->setHeaders($request['headers'])
            ->setMethod($request['method'])
            ->build();
    }
}
