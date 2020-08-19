<?php

namespace Yampi\Api\Exceptions;

use Exception;
use Teapot\StatusCode;
use Throwable;
use Yampi\Api\Request;

abstract class BaseException extends Exception
{
    private $request;

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
        int $code = StatusCode::NOT_ACCEPTABLE,
        ?Throwable $previous = null
    ) {
        $this->request = $request;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getRequest()
    {
        return $this->request;
    }
}
