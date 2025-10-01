<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum HTTPMethod: string implements HasLabel
{
    use ExtraMethods;

    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case OPTIONS = 'OPTIONS';
    case HEAD = 'HEAD';

    public function getLabel(): string
    {
        return match ($this) {
            self::GET => __('http_methods.get'),
            self::POST => __('http_methods.post'),
            self::PUT => __('http_methods.put'),
            self::PATCH => __('http_methods.patch'),
            self::DELETE => __('http_methods.delete'),
            self::OPTIONS => __('http_methods.options'),
            self::HEAD => __('http_methods.head'),
        };
    }
}
