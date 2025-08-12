<?php

namespace app\modules\Planificacion\common\helpers;

class ResponseHelper
{
    public static function success($data = null, string $message = 'Ok'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => null
        ];
    }

    public static function error(string $message = 'Ocurrió un error', string|array|null $errors = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors
        ];
    }
}

