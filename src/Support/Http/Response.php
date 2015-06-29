<?php

namespace Greg\Support\Http;

use Greg\Support\Arr;
use Greg\Support\Type;

class Response
{
    const CODES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    static public function sendCode($code, $die = false)
    {
        if (Type::isNaturalNumber($code) and Arr::has($codes = static::CODES, $code)) {
            $code .= ' ' . $codes[$code];
        }

        header('HTTP/1.1 ' . $code);

        $die && die;

        return true;
    }

    static public function sendRedirect($url = '/', $code = null, $die = false)
    {
        if ($code !== null) {
            static::sendCode($code);
        }

        if (!$url) {
            $url = '/';
        }

        header('Location: ' . $url, false, $code);

        $die && die;

        return true;
    }

    static public function sendRefresh($die = false)
    {
        return static::sendRedirect(Request::uri(), null, $die);
    }

    static public function sendReferrerRedirect($die = false)
    {
        return static::sendRedirect(Request::referrer(), null, $die);
    }

    static public function sendJson($param = [], $die = false)
    {
        static::sendContentType('application/json');

        echo json_encode($param);

        $die && die;

        return true;
    }

    static public function sendHtml($html, $die = false)
    {
        static::sendContentType('text/html');

        echo $html;

        $die && die;

        return true;
    }

    static public function sendText($text, $die = false)
    {
        static::sendContentType('text/plain');

        echo $text;

        $die && die;

        return true;
    }

    static public function sendJpeg($image, $quality = 75, $die = false)
    {
        static::sendContentType('image/jpeg');

        imagejpeg($image, null, $quality);

        $die && die;

        return true;
    }

    static public function sendGif($image, $die = false)
    {
        static::sendContentType('image/gif');

        imagegif($image);

        $die && die;

        return true;
    }

    static public function sendPng($image, $die = false)
    {
        static::sendContentType('image/png');

        imagepng($image);

        $die && die;

        return true;
    }

    static public function sendContentType($type, $die = false)
    {
        header('Content-Type: ' . $type);

        $die && die;

        return true;
    }

    static public function isModifiedSince($timestamp, $maxAge = 0, $die = false)
    {
        if (!Type::isNaturalNumber($timestamp)) {
            $timestamp = strtotime($timestamp);
        }

        $modifiedSince = Request::modifiedSince();

        if ($maxAge > 0) {
            $serverTime = Request::time();

            if ($modifiedSince) {
                $modifiedSinceTime = new \DateTime($modifiedSince, new \DateTimeZone('UTC'));

                $modifiedSinceTime = strtotime($modifiedSinceTime->format('Y-m-d H:i:s'));

                if ($modifiedSinceTime < $serverTime - $maxAge) {
                    $timestamp = $serverTime;
                } elseif ($timestamp < $modifiedSinceTime) {
                    $timestamp = $modifiedSinceTime;
                }
            } else {
                $timestamp = $serverTime;
            }
        }

        $lastModified = substr(date('r', $timestamp), 0, -5) . 'GMT';

        $eTag = '"' . md5($lastModified) . '"';

        // Send the headers
        header('Last-Modified: ' . $lastModified);

        header('ETag: ' . $eTag);

        $match = Request::match();

        // See if the client has provided the required headers
        if (!$modifiedSince && !$match) {
            return false;
        }

        // At least one of the headers is there - check them
        if ($match && $match != $eTag) {
            return false; // eTag is there but doesn't match
        }

        if ($modifiedSince && $modifiedSince != $lastModified) {
            return false; // if-modified-since is there but doesn't match
        }

        // Nothing has changed since their last request - serve a 304
        static::sendCode(304);

        $die && die;

        return true;
    }
}