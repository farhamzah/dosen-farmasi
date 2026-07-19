<?php

namespace App\Exceptions;

use RuntimeException;

class IntegrationProcessingException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $category = 'HANDLER_EXCEPTION',
        public readonly bool $retryable = true,
    ) {
        parent::__construct($message);
    }
}
