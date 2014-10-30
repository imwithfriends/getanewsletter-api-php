<?php

namespace Gan;

use \Httpful\Request;

/**
 * Handles the connection to the API.
 */
class Api
{
    /**
     * The default API base URI.
     * @var string
     */
    protected static $defaultBaseUri = 'https://api.getanewsletter.com/v3/';

    private $requestTemplate;
    private $baseUri;

    /**
     * Initializes the API connection.
     *
     * @param string $token The security token.
     * @param string $baseUri (optional) Alternative API base URI.
     */
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

    /**
     * Makes a call to the API.
     *
     * This method will make the actial API call by the given arguments. It
     * will return the response on success (200) or will throw an exception
     * on failure.
     *
     * @param string $method The HTTP method to use (e.g. Http::GET, Http::POST, etc.).
     * @param string $resourcePath The path to the resource (e.g. contacts/john@example.com/)
     * @param string $payload The data that is sent to the service. Not used for GET or DELETE.
     * @return \Httpful\Response The response object from the service.
     * @throws \Gan\ApiException
     */
    public function call($method, $resourcePath, $payload = [])
    {
        $uri = $this->baseUri . $resourcePath;

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
