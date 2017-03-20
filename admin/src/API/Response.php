<?php

namespace BigSea\Gulfstream\Admin\API;

class Response
{
    /** @var object $responseData */
    private $responseData;

    /** @var int $statusCode */
    public $statusCode;

    public function __construct($statusCode, $responseData)
    {
        $this->responseData = $responseData;
        $this->statusCode = intval($statusCode);
    }

    /**
     * Is this response an error?
     *
     * @access public
     * @return bool
     */
    public function isError()
    {
        if (null === $this->responseData) {
            return true;
        }
        
        if (!property_exists($this->responseData, 'status')) {
            return true;
        }

        return $this->responseData->status === false;
    }

    /**
     * Returns the error text (or null if this is not an error).
     *
     * @access public
     * @return string
     */
    public function errorText()
    {
        if (!property_exists($this->responseData, 'message')) {
            return '';
        }
        return $this->responseData->message;
    }

    /**
     * Returns the raw decoded response object.
     *
     * @access public
     * @return object
     */
    public function dataObject()
    {
        return $this->responseData;
    }

    /**
     * Returns the response data as an associative array.
     *
     * @access public
     * @return array
     */
    public function toArray()
    {
        // re-decode as assoc array
        return json_decode(json_encode($this->responseData), true);
    }

    /**
     * isStatusOk
     *
     * @access public
     * @return bool
     */
    public function isStatusOk()
    {
        return $this->statusCode === 200;
    }
}
