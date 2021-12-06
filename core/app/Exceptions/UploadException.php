<?php

namespace App\Exceptions;

class UploadException extends BaseException
{
    public static function failed()
    {
        return self::make('Upload failed! please try again later.', 1, 500);
    }
}
