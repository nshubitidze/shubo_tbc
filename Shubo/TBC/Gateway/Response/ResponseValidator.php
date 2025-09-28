<?php
declare(strict_types=1);

namespace Shubo\TBC\Gateway\Response;

use Shubo\TBC\Helpers\Constants;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class ResponseValidator extends AbstractValidator
{
    /**
     * @param SubjectReader $subjectReader
     * @param ResultInterfaceFactory $resultFactory
     */
    public function __construct(
        protected SubjectReader $subjectReader,
        ResultInterfaceFactory $resultFactory,
    ) {
        parent::__construct($resultFactory);
    }

    /**
     * Validate the Flitt response, Perform basic response validation as well as request specific.
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponse($validationSubject);

        if ($response['response_status'] !== Constants::RESPONSE_SUCCESS) {
            return $this->createResult(false, errorCodes: [$response[Constants::RESPONSE_ERROR_MESSAGE]]);
        }

        if (!empty($response['capture_status']) && $response['capture_status'] === Constants::CAPTURE_STATUS_HOLD) {
            return $this->createResult(false, errorCodes: [
                'Capture has been created, but not approved yet.
                If manual capture is enabled, Please retry creating the invoice later'
            ]);
        }

        return $this->createResult(true);
    }
}
