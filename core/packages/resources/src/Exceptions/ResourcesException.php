<?php


namespace Nila\Resources\Exceptions;


use Exception;
use Throwable;

class ResourcesException extends Exception
{
    private int $errorCode;

    public function __construct(string $message, int $errorCode, $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

}
