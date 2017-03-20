<?php

namespace BigSea\Gulfstream\Admin\API;

class Client implements ClientInterface
{
    private $baseUrl;
    private $token;
    private $logger;

    public function __construct($baseUrl, $logger)
    {
        $this->baseUrl = $baseUrl;
        $this->logger = $logger;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Calls API endpoint.
     *
     * @param string $method
     * @param string $route
     * @param array $params Associative array of parameters to pass in request body.
     * @access private
     * @throws Exception if curl errors out.
     * @return ApiResponse
     */
    private function call($method, $route, $params = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $route);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $logMsg = "API client calling $method $route";

        $headers = [ "Content-Type: application/json; charset=utf-8" ];
        if ($this->token !== null) {
            $headers[] = "Authorization: ".$this->token;
            $logMsg .= ' (authorized)';
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (null !== $params) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            $logMsg .= ' with data: ' . json_encode($params);
        }

        $this->logger->debug($logMsg);
        $resp = curl_exec($ch);

        if (!$resp) {
            $this->logger->debug('API request failed: '.curl_error($ch));
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new \Exception('Error: "' . $error . '" - Code: ' . $errno);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseObject = json_decode($resp);
        $response = new Response($statusCode, $responseObject);
        $this->logger->debug("API response: ($statusCode) $resp");

        curl_close($ch);
        return $response;
    }

    public function get($route)
    {
        return $this->call('get', $route);
    }

    public function post($route, $params)
    {
        return $this->call('post', $route, $params);
    }

    public function put($route, $params)
    {
        return $this->call('put', $route, $params);
    }

    public function delete($route)
    {
        return $this->call('delete', $route);
    }
}
