<?php
declare(strict_types=1);

namespace Shubo\TBC\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    public const TEST_MODE_VALUE = '1';
    public const PRODUCTION_MODE_VALUE = '0';

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::PRODUCTION_MODE_VALUE, 'label' => __('Production Mode')],
            ['value' => self::TEST_MODE_VALUE, 'label' => __('Sandbox Mode')]
        ];
    }
}
