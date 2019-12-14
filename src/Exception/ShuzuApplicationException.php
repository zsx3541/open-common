<?php
/**
 * Created by PhpStorm.
 * User: zsx
 * Date: 2019-12-13
 * Time: 18:23
 */

namespace Shuzu\Common\Exception;


use Throwable;

class ShuzuApplicationException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}