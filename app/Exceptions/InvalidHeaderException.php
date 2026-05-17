<?php

namespace App\Exceptions;

use Exception;
use App\Traits\ApiResponser;

class InvalidHeaderException extends Exception
{
    use ApiResponser;

    protected $msgError = [
        'header' => "Headers is not correct, headers must contain app-key, please check it again."
    ];

    public function render()
    {
        return $this->responseError($this->msgError, 400);
    }
}
