<?php
/**
 * Created by PhpStorm.
 * User: zsx
 * Date: 2019-12-13
 * Time: 18:06
 */
namespace Shuzu\Common;

use GuzzleHttp\Client;
use Shuzu\Common\exception\ShuzuApplicationException;
use Shuzu\Common\Response\TokenResponse;

class ShuzuCommonApplication
{
    private $appid;
    private $appSecret;
    private $url;

    public function __construct($appid,$appSecret,$url)
    {
        $this->appid = $appid;
        $this->appSecret = $appSecret;
        $this->url = $url;
    }

    public function getToken(){

        $fullUrl = rtrim($this->url,'/') . "/openplat/dev/refreshAccessToken?&appId=" . $this->appid ."&appScrt=". $this->appSecret ."";

        $client = new Client([
            'timeout' => 10
        ]);
        $response = $client->get($fullUrl);

        if($response->getStatusCode() != '200'){
            throw new ShuzuApplicationException("请求数族开放平台失败");
        }

        $json_resp = json_decode($response->getBody(),true);
        if($json_resp == null){
            throw new ShuzuApplicationException("返回数据异常");
        }

        if($json_resp['code'] != 0){
            throw new ShuzuApplicationException($json_resp['result']);
        }

        $tokenresp =  new TokenResponse();
        $tokenresp->setAccessToken(@$json_resp['result']['accessToken']);
        $tokenresp->setExpireTime(@$json_resp['result']['expires']);

        return $tokenresp;

    }
}