<?php

namespace app\modules\Planificacion\common\exceptions;

use Exception;
use Throwable;

class ValidationException extends Exception {
    public string|array $errors;

    public function __construct(string $message, string|array $errors, int $code = 0, Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): string|array
    {
        return $this->errors;
    }
}