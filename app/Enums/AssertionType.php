<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AssertionType: string implements HasLabel
{
    use ExtraMethods;

    case RESPONSE_TIME = 'response.time';
    case RESPONSE_CODE = 'response.code';
    case RESPONSE_BODY = 'response.body'; // TODO: not implemented yet
    case RESPONSE_JSON = 'response.json'; // TODO: not implemented yet
    case RESPONSE_HEADER = 'response.header'; // TODO: not implemented yet
    case SSL_CERTIFICATE_EXPIRES_IN = 'ssl_certificate.expires_in'; // TODO: not implemented yet

    public function getLabel(): string
    {
        return match ($this) {
            self::RESPONSE_TIME => __('assertion_types.response_time'),
            self::RESPONSE_CODE => __('assertion_types.response_code'),
            self::RESPONSE_BODY => __('assertion_types.response_body'),
            self::RESPONSE_JSON => __('assertion_types.response_json'),
            self::RESPONSE_HEADER => __('assertion_types.response_header'),
            self::SSL_CERTIFICATE_EXPIRES_IN => __('assertion_types.ssl_certificate_expires_in'),
        };
    }
}
