<?php

namespace App\Exception;

final class QuardlockApiException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?string $endpoint = null,
        private readonly ?int $httpStatus = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }
}
