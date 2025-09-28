<?php
declare(strict_types=1);

namespace Shubo\TBC\Helpers;

use Shubo\TBC\Model\Source\Mode;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    protected const CONFIG_PREFIX = 'payment/shubo_tbc/';
    protected const CONFIG_ACTIVE = 'active';
    protected const CONFIG_MODE = 'mode';
    protected const CONFIG_MERCHANT_ID_TEST = 'merchant_id_test';
    protected const CONFIG_MERCHANT_ID = 'merchant_id';
    protected const CONFIG_SECRET_KET_FOR_PURCHASES_TEST = 'secret_key_for_purchases_test';
    protected const CONFIG_SECRET_KET_FOR_PURCHASES = 'secret_key_for_purchases';
    protected const CONFIG_AUTO_CAPTURE = 'auto_capture';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Check if the payment method in enabled.
     *
     * @return bool
     */
    public function getIsActive(): bool
    {
        return (bool)$this->getConfig(self::CONFIG_ACTIVE);
    }

    /**
     * Check if the test mode in enabled.
     *
     * @return bool
     */
    public function getIsTestMode(): bool
    {
        return $this->getConfig(self::CONFIG_MODE) === Mode::TEST_MODE_VALUE;
    }

    /**
     * Check if auto capture is enabled.
     *
     * @return bool
     */
    public function isAutoCapture(): bool
    {
        return (bool) $this->getConfig(self::CONFIG_AUTO_CAPTURE);
    }

    /**
     * Get the merchant id provided by Flitt.
     *
     * @return int|null
     */
    public function getMerchantId(): ?int
    {
        if ($this->getIsTestMode()) {
            return (int)$this->getConfig(self::CONFIG_MERCHANT_ID_TEST);
        }

        return (int)$this->getConfig(self::CONFIG_MERCHANT_ID);
    }

    /**
     * Get the secret key provided by Flitt.
     *
     * @return string|null
     */
    public function getSecretKeyForPurchase(): ?string
    {
        if ($this->getIsTestMode()) {
            return $this->getConfig(self::CONFIG_SECRET_KET_FOR_PURCHASES_TEST);
        }

        return $this->getConfig(self::CONFIG_SECRET_KET_FOR_PURCHASES);
    }

    /**
     * Generic function to get the module config.
     *
     * @param string $field
     * @return mixed
     */
    protected function getConfig(string $field): mixed
    {
        return $this->scopeConfig->getValue(self::CONFIG_PREFIX . $field);
    }
}
