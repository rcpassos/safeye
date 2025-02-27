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
        return $this->value;
    }
}
