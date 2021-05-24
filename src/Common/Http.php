<?php
/**
 * Created by PhpStorm.
 * User: etocrm
 * Date: 2015/5/5
 * Time: 13:37
 */
namespace Ws\SyncMenu\Common;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Http {
    protected static $guzzle = [];

    /**
     * 释放资源
     */
    public static function closeAllConns()
    {
        if (count(self::$guzzle) === 0) {
            return true;
        }
        foreach (self::$guzzle as $conn) {
            $conn = null;
        }
    }



    /**
     * 实例化guzzle(单例)
     * @param $base_uri  $uri
     * @return
     */
    protected static function init()
    {

        if (empty(self::$guzzle)) {
            self::$guzzle = new Client([
                // You can set any number of default request options.
                'timeout' => 5.0,
                // https请求
                'verify' => false
            ]);
        }

        return self::$guzzle;
    }

    /**
     * 获取guzzle实例
     * @param $base_uri   $uri
     * @return Client | bool
     */
    public static function getGuzzle()
    {
        $ret = self::init();
        if ($ret == false) {
            return false;
        }
        return $ret;
    }

    public static function get($url)
    {
        $guzzle_ins = self::getGuzzle();
        try{
            $data = [
                'headers' => [],
            ];
            $response = $guzzle_ins->get($url, $data);
            $response_code = $response->getStatusCode();
            if (intval($response_code) == 200) {
                $ret = $response->getBody()->getContents();
                return $ret;
            } else {
                return false;
            }
        }catch (RequestException $e){
            return false;
        }
    }

    public static function getWithHeader($url,$header = [])
    {
        $guzzle_ins = self::getGuzzle();
        try{
            $data = [
                'headers' => $header,
            ];
            $response = $guzzle_ins->get($url, $data);
            $response_code = $response->getStatusCode();
            if (intval($response_code) == 200) {
                $ret = $response->getBody()->getContents();
                return $ret;
            } else {
                return false;
            }
        }catch (RequestException $e){
            return false;
        }
    }

    public static function post($url,$param,$post_file=false, $header = [])
    {
        $guzzle_ins = self::getGuzzle();
        try {
            if (is_string($param) || $post_file) {
                $strPOST = $param;
            } else {
                $aPOST = array();
                foreach($param as $key=>$val){
                    $aPOST[] = $key."=".urlencode($val);
                }
                $strPOST =  join("&", $aPOST);
            }

            $data = [
                'headers' => $header,
                'body' => $strPOST
            ];
            $response = $guzzle_ins->post($url, $data);
            $response_code = $response->getStatusCode();
            if (intval($response_code) == 200) {
                $ret = $response->getBody()->getContents();
                return $ret;
            } else {
                return false;
            }
        } catch (RequestException $e) {
            return false;
        }
    }

    public static function httpPost($url,$param,$post_file=false, $header = [])
    {
        $guzzle_ins = self::getGuzzle();
        try {
            $response = $guzzle_ins->post($url, [
                'json' => $param
            ]);
            $response_code = $response->getStatusCode();
            if (intval($response_code) == 200) {
                $ret = $response->getBody()->getContents();
                return $ret;
            } else {
                return false;
            }
        } catch (RequestException $e) {
            return false;
        }
    }

    public static function postJson($url, $param, $header = [])
    {
        $guzzle_ins = self::getGuzzle();
        try {
            $response = $guzzle_ins->post($url, [
                'json' => $param,
                'headers' => $header,
            ]);

            $response_code = $response->getStatusCode();
            if (intval($response_code) == 200) {
                $ret = $response->getBody()->getContents();
                return $ret;
            } else {
                return false;
            }
        } catch (RequestException $e) {
            return false;
        }
    }
}