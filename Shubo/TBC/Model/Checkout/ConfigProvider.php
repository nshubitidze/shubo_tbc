<?php
declare(strict_types=1);

namespace Shubo\TBC\Model\Checkout;

use Shubo\TBC\Helpers\Config;
use Shubo\TBC\Helpers\Constants;
use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @param Config $config
     */
    public function __construct(
        protected Config $config
    ) {
    }

    /**
     * Set the TBC payment config in window object.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                Constants::PAYMENT_CODE => [
                    'active' => $this->config->getIsActive(),
                    'params' => [
                        'merchant_id' => $this->config->getMerchantId(),
                    ],
                    'options' => [
                        'methods_disabled' => ['banks', 'most_popular', 'wallets'],
                        'api_domain' => 'pay.flitt.com',
                        'full_screen' => false,
                        'card_icons' => [],
                        'show_amount' => false,
                        'show_email' => false,
                        'show_fee' => false,
                        'show_lang' => false,
                        'show_link' => false,
                        'show_order_desc' => false,
                        'show_pay_button_amount' => true,
                        'show_secure_message' => false,
                        'show_test_mode' => false,
                        'show_title' => false,
                        'show_pay_button' => false,
                        'theme' => [
                            'type' => 'light',
                            'preset' => 'reset',
                            'layout' => 'plain',
                        ],
                    ]
                ]
            ]
        ];
    }
}
