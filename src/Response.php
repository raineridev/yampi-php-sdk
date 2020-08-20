<?php

namespace Yampi\Api;

class Response
{
    protected $response = [];

    protected $data = [];

    protected $meta = [];

    /**
     * Response constructor.
     *
     * @param string|array $response
     */
    public function __construct($response)
    {
        if (!is_array($response)) {
            $response = json_decode($response, true);
        }

        $this->setResponse($response);

        if (isset($response['meta'])) {
            $this->setMeta($response['meta']);
        }

        if (isset($response['data'])) {
            $this->setData($response['data']);
        }
    }

    /**
     * getMeta
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * pagination
     * @return array
     */
    public function pagination()
    {
        return new Pagination($this->getMeta()['pagination']);
    }

    /**
     * Get Yampi responses HTTP status code.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        if (isset($this->response['status_code'])) {
            return $this->response['status_code'];
        }

        return 0;
    }

    /**
     * Gets Yampi entire response.
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Gets Yampi data key.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets Yampi entire response.
     *
     * @param array $response
     */
    protected function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Sets Yampi data key.
     *
     * @param array $data
     */
    protected function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Sets Yampi meta data key
     *
     * @param array $value
     */
    protected function setMeta($value)
    {
        $this->meta = $value;
    }
}
