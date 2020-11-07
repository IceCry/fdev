<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/3/31
 * Time: 16:14
 */
namespace sensen\exceptions;

use Throwable;

class AuthException extends \RuntimeException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (is_array($message)) {
            $errInfo = $message;
            $message = $errInfo[0] ?? '未知错误';
            $code = $errInfo[1] ?? 400;
        }

        parent::__construct($message, $code, $previous);
    }
}