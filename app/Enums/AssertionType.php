<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AssertionType: string implements HasLabel
{
    use ExtraMethods;

    case RESPONSE_TIME = 'response.time';
    case RESPONSE_CODE = 'response.code';
    // case RESPONSE_BODY = 'response.body';
    // case RESPONSE_JSON = 'response.json';
    // case RESPONSE_HEADER = 'response.header';
    // case SSL_CERTIFICATE_EXPIRES_IN = 'ssl_certificate.expires_in';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
