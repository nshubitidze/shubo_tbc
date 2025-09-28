<?php
declare(strict_types=1);

namespace Shubo\TBC\Controller\Payment;

use Exception;
use Flitt\Helper\ApiHelper;
use Shubo\TBC\Helpers\Config;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface as LocaleResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Customer\Model\Session;

class Params implements ActionInterface, CsrfAwareActionInterface
{
    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param CheckoutSession $checkoutSession
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param Config $configHelper
     * @param LocaleResolverInterface $localeResolver
     * @param Session $customerSession
     */
    public function __construct(
        protected Context $context,
        protected JsonFactory $jsonFactory,
        protected CartRepositoryInterface $quoteRepository,
        protected CheckoutSession $checkoutSession,
        protected QuoteIdMaskFactory $quoteIdMaskFactory,
        protected Config $configHelper,
        protected LocaleResolverInterface $localeResolver,
        protected Session $customerSession,
    ) {
    }

    /**
     * Get the final params to be used in embed checkout during authorization request.
     *
     * @throws Exception
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();

        if (!$this->context->getRequest()->isPost()) {
            return $result->setData(['error' => true, 'message' => 'Invalid request method']);
        }

        try {
            $quote = $this->validateAndGetQuote();
        } catch (NoSuchEntityException|LocalizedException) {
            return $result->setData(['error' => true, 'message' => 'Invalid Quote']);
        }

        $params = $this->generateParams($quote);

        if (empty($params['signature'])) {
            $params['signature'] = ApiHelper::generateSignature(
                $params,
                $this->configHelper->getSecretKeyForPurchase()
            );
        }

        return $result->setData($params);
    }

    /**
     * Get the params for Flitt.
     *
     * @param CartInterface $quote
     * @return array
     */
    protected function generateParams(CartInterface $quote): array
    {
        return [
            'order_id' => $quote->getReservedOrderId(),
            'merchant_id' => $this->configHelper->getMerchantId(),
            'order_desc' => 'do not know yet',
            'amount' => (int)$quote->getGrandTotal(),
            'currency' => $quote->getQuoteCurrencyCode(),
            'lang' => substr($this->localeResolver->getLocale(), 0, 2),
            'preauth' => 'Y',
        ];
    }

    /**
     * Make sure that current customer can access the quote.
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Exception
     * @return CartInterface
     */
    protected function validateAndGetQuote(): CartInterface
    {
        $request = json_decode($this->context->getRequest()->getContent(), true);
        $quoteMaskId = $request['quoteMaskId'] ?: null;

        if ($this->customerSession->isLoggedIn()) {
            $quoteId = $quoteMaskId;
        } else {
            $quoteMaskId = $this->quoteIdMaskFactory->create()->load($quoteMaskId, 'masked_id');
            $quoteId = $quoteMaskId->getQuoteId();
        }

        if (!$quoteId) {
            throw new Exception('Quote ID not provided');
        }

        $quote = $this->quoteRepository->get($quoteId);
        $sessionQuote = $this->checkoutSession->getQuote();

        if ($quote->getId() !== $sessionQuote->getId()) {
            throw new Exception('Unauthorized quote access');
        }

        if (!$quote->getReservedOrderId()) {
            $quote->reserveOrderId();
            $this->quoteRepository->save($quote);
        }

        return $quote;
    }

    /**
     * @inheritdoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
