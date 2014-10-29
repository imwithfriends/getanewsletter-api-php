<?php

namespace Gan;

use Httpful\Response;

class ApiException extends \Exception
{
    private static function parse_errors($body)
    {
        if (!is_object($body)) {
            return 'Unknown error';
        }

        $errors = '';
        $vars = get_object_vars($body);
        foreach ($vars as $error) {
            if (is_object($error)) {
                $errors .= self::parse_errors($error) . ' ';
            } else {
                $errors .= $error . ' ';
            }
        }

        return trim($errors);
    }

    public function __construct(Response $response)
    {
        print $response->body->detail;
        $message = '(' . $response->code . ') ' . self::parse_errors($response->body);
        return parent::__construct($message, $response->code, null);
    }
}
