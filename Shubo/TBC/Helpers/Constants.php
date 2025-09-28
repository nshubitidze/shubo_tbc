<?php
declare(strict_types=1);

namespace Shubo\TBC\Helpers;

class Constants
{
    public const PAYMENT_CODE = 'shubo_tbc';
    public const PAYMENT_RESPONSE_FIELD = 'payment_response';
    public const TBC_TRANSACTION_ID_FIELD = 'rrn';

    public const FLIT_API_ENDPOINT_PREFIX = 'https://pay.flitt.com/api/';
    public const FLIT_API_STATUS_ENDPOINT = self::FLIT_API_ENDPOINT_PREFIX . 'status/order_id';
    public const FLIT_API_CAPTURE_ENDPOINT = self::FLIT_API_ENDPOINT_PREFIX . 'capture/order_id';
    public const FLIT_API_REVERSE_ENDPOINT = self::FLIT_API_ENDPOINT_PREFIX . 'reverse/order_id';

    public const RESPONSE_SUCCESS = 'success';
    public const RESPONSE_ERROR_MESSAGE = 'error_message';

    public const CAPTURE_STATUS_HOLD = 'hold';
}
