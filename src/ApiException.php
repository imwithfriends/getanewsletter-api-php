<?php

namespace Gan;

use Httpful\Response;

/**
 * Represents the exception from a failed API call.
 */
class ApiException extends \Exception
{
    public $response;

    /**
     * Returns a string representation of the error information.
     *
     * @param stdClass $body The data object to parse.
     * @return string String representation of the error.
     */
    private static function parse_errors($body)
    {
        if (!is_object($body)) {
            return 'Unknown error';
        }

        $errors = '';
        $vars = is_object($body) ? get_object_vars($body) : $body;
        foreach ($vars as $field => $error) {
            if (is_object($error)) {
                $errors .= self::parse_errors($error) . ' ';
            } else if (is_array($error)) {
                $errors .= $field . ': ';
                foreach ($error as $_error) {
                    $errors .= $_error . ' ';
                }
            } else {
                $errors .= $field . ': ' . $error . ' ';
            }
        }

        return trim($errors);
    }

    /**
     * Constructor.
     *
     * @param Response $response The raw response from the API.
     * @return ApiException
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
        $message = '(' . $response->code . ') ' . self::parse_errors($response->body);
        return parent::__construct($message, $response->code, null);
    }
}
