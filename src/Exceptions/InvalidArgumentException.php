<?php

namespace Jw\DataSet\Exceptions;

use Throwable;

/**
 * 参数错误的异常类
 * Class InvalidSignException
 * @package Jw\DataSet\Exceptions
 */
class InvalidArgumentException extends Exception
{
    /**
     * InvalidArgumentException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}