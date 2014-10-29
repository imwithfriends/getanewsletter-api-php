<?php

namespace Gan;

use \Httpful\Request;

class Api
{
    protected static $defaultBaseUri = 'https://api.getanewsletter.com/v3/';

    private $requestTemplate;
    private $baseUri;

    public function __construct($token, $baseUri = null) {
        // // The JSON response is going to be deserialized to associative arrays:
        // $json_handler = new Httpful\Handlers\JsonHandler(array('decode_as_array' => true));
        // Httpful\Httpful::register('application/json', $json_handler);

        $this->baseUri = $baseUri ? rtrim($baseUri, '/') . '/' : self::$defaultBaseUri;

        $this->requestTemplate = Request::init()
            ->addHeader('Accept', 'application/json;')
            ->addHeader('Authorization', 'Token ' . $token)
            ->expects('application/json')
            ->sendsJson();
    }

    public function call($method, $resourcePath, $payload = [])
    {
        $uri = $this->baseUri . rtrim($resourcePath, '/') . '/';

        var_export($this->requestTemplate);
        Request::ini($this->requestTemplate);
        $request = Request::init($method)->uri($uri);

        if ($payload) {
            $request->body($payload);
        }

        try {
            $response = $request->send();
        } finally {
            Request::resetIni();
        }

        if (floor($response->code / 100) != 2) {
            throw new ApiException($response);
        }

        return $response;
    }
}
