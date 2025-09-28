<?php
declare(strict_types=1);

namespace Shubo\TBC\Gateway\Response;

use Magento\Framework\Phrase;
use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;

class ErrorMessageMapper implements ErrorMessageMapperInterface
{
    /**
     * We get error message directly from the response.
     *
     * @param string $code
     * @return Phrase|null
     */
    public function getMessage(string $code)
    {
        return __('Flitt response: ' . $code);
    }
}
