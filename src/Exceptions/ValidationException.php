<?php

namespace Yampi\Api\Exceptions;

use Exception;
use Throwable;
use Yampi\Api\Request;
use Yampi\Api\Response;

class ValidationException extends RequestException
{
    private $errors;

    /**
     * Thrown when Yampi API returns validation error for any field
     *
     * @param string    $message Exception message
     * @param Request   $request Yampi API Request
     * @param Response  $response Yampi API Response
     * @param array     $errors Errors list
     * @param int       $code Exception status code
     * @param Throwable $previous Previous Exception
     */
    public function __construct(
        string $message,
        ?Request $request = null,
        ?Response $response = null,
        array $errors = [],
        int $code = 400,
        ?Throwable $previous = null
    ) {
        $this->errors = $errors;

        parent::__construct($message, $request, $response, $code, $previous);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
