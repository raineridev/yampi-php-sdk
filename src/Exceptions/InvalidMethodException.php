<?php

namespace Yampi\Api\Exceptions;

use Teapot\StatusCode;
use Throwable;
use Yampi\Api\Request;

class InvalidMethodException extends BaseException
{
    /**
     * Constructor.
     *
     * @param string    $message Exception message
     * @param Request   $request Request
     * @param int       $code Exception status code
     * @param Throwable $previous Previous Exception
     */
    public function __construct(
        string $message,
        ?Request $request = null,
        int $code = StatusCode::METHOD_NOT_ALLOWED,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $request, $code, $previous);
    }
}
