<?php

namespace App\Exceptions;

use Exception;

class ThesaurusImportException extends Exception
{
    // Les codes d'erreur spécifiques
    public const ERROR_FILE_NOT_READABLE = 100;
    public const ERROR_JSON_DECODE = 101;
    public const ERROR_INVALID_STRUCTURE = 102;
    public const ERROR_SCHEME_NOT_FOUND = 103;
}
