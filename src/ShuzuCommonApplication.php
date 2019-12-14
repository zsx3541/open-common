<?php
/**
 * Created by PhpStorm.
 * User: zsx
 * Date: 2019-12-13
 * Time: 18:06
 */
namespace Shuzu\Common;

use GuzzleHttp\Client;
use Shuzu\Common\Exception\ShuzuApplicationException;
use Shuzu\Common\Request\BaseRequest;
use Shuzu\Common\Response\TokenResponse;

class ShuzuCommonApplication
{
    private $appid;
    private $appSecret;
    private $url;
    private $token;
    private $timeout;

    public function __construct($appid,$appSecret,$url, $option = [])
    {
        $this->appid = $appid;
        $this->appSecret = $appSecret;
        $this->url = $url;

        $this->timeout = 10;
        if(!empty($option)){
            isset($option['timeout']) && $this->timeout = $option['timeout'];
        }
    }


    /**
     * @return TokenResponse
     * @throws ShuzuApplicationException
     */
    public function requestToken(){

        $fullUrl = rtrim($this->url,'/') . "/openplat/dev/refreshAccessToken?&appId=" . $this->appid ."&appScrt=". $this->appSecret ."";

        $json_resp = $this->doGet($fullUrl);

        if($json_resp['code'] != 0){
            throw new ShuzuApplicationException($json_resp['result']);
        }

        $tokenresp =  new TokenResponse();
        $tokenresp->setAccessToken(@$json_resp['result']['accessToken']);
        $tokenresp->setExpireTime(@$json_resp['result']['expires']);

        return $tokenresp;

    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }



    /**
     * @return mixed
     */
    public function getAppid()
    {
        return $this->appid;
    }

    /**
     * @param mixed $appid
     */
    public function setAppid($appid)
    {
        $this->appid = $appid;
    }

    /**
     * @return mixed
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * @param mixed $appSecret
     */
    public function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }


    /**
     * @param $fullUrl
     * @param BaseRequest $request
     * @return mixed
     * @throws ShuzuApplicationException
     */
    public function doPostJson($fullUrl, $request){
        $body = json_encode($request);
        $response = $this->doPost($fullUrl, $body,'application/json');
        $json_resp = json_decode($response,true);
        if($json_resp == null){
            throw new ShuzuApplicationException("返回数据异常");
        }
        return $json_resp;
    }

    /**
     * @param $fullUrl
     * @param string $xml_content
     * @return \Psr\Http\Message\StreamInterface
     * @throws ShuzuApplicationException
     */
    public function doPostXml($fullUrl, $xml_content){
        $resp = $this->doPost($fullUrl, $xml_content,'application/xml');
        return $resp;
    }


    /**
     * @param $fullUrl
     * @param $body
     * @return \Psr\Http\Message\StreamInterface
     * @throws ShuzuApplicationException
     */
    public function doPost($fullUrl,$body, $content_type){

        $client = new Client([
            'timeout' => $this->timeout
        ]);

        $time = time();
        $response = $client->post($fullUrl, [
            'headers' => [
                'Content-Type' => $content_type,
                'Date' => $time,
                'AppId' => $this->getAppid(),
                'Content-MD5' => base64_encode(md5($body.$time.$this->token))
            ],
            'body' => $body
        ]);

        if($response->getStatusCode() != '200'){
            throw new ShuzuApplicationException("请求数族短信平台失败");
        }

        return $response->getBody();
    }


    /**
     * @param $fullUrl
     * @return mixed
     * @throws ShuzuApplicationException
     */
    public function doGet($fullUrl){

        $client = new Client([
            'timeout' => $this->timeout
        ]);
        $response = $client->get($fullUrl);

        if($response->getStatusCode() != '200'){
            throw new ShuzuApplicationException("请求数族开放平台失败");
        }

        $json_resp = json_decode($response->getBody(),true);
        if($json_resp == null){
            throw new ShuzuApplicationException("返回数据异常");
        }

        return $json_resp;
    }


}