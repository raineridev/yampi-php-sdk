<?php

namespace Yampi\Api\Exceptions;

use Exception;
use Teapot\StatusCode;
use Throwable;
use Yampi\Api\Request;
use Yampi\Api\Response;

class RequestException extends Exception
{
    private $request;

    private $response;

    /**
     * Thrown when Yampi API returns Request Exception
     *
     * @param string    $message Exception message
     * @param Request   $request Yampi API Request
     * @param Response  $response Yampi API Response
     * @param int       $code Exception status code
     * @param Throwable $previous Previous Exception
     */
    public function __construct(
        string $message,
        ?Request $request = null,
        ?Response $response = null,
        int $code = StatusCode::BAD_REQUEST,
        ?Throwable $previous = null
    ) {
        $this->request = $request;
        $this->response = $response;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return int
     */
    public function getResponse()
    {
        return $this->response;
    }
}
