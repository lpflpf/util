<?php

/**
 * Format.php
 *
 * @author   lipengfei
 * @created  2017/4/22 15:27
 */
class Format
{
    private static $_format = "json";

    /**
     * 设置默认的format格式
     *
     * @param $format
     */
    public static function setDefaultFormat($format)
    {
        if (method_exists(get_class(), $format)) {
            static::$_format = $format;
        } else {
            Logger::error("set default format type error!");
        }
    }

    /**
     * 格式化输出结果。
     * 默认选择从参数$type获取返回格式，若未设置，则通过静态变量$format获取，若不存在，则输出json格式。
     *
     * @param int    $code
     * @param string $message
     * @param array  $data
     * @param string $type
     */
    public static function show($code, $message, $data = [])
    {
        $type = static::getFormat();
        if (method_exists(get_class(), $type)) {
            echo static::{$type}($code, $message, $data);
            return;
        }
        echo static::json($code, $message, $data);
    }

    private static function getArrayData($code, $message, $data = [])
    {
        if (!is_numeric($code)) {
            return '';
        }

        return [
            'errCode' => $code,
            'msg' => $message,
            'data' => $data
        ];
    }

    public static function serialize($code, $message, $data = [])
    {
        $result = static::getArrayData($code, $message, $data);
        return serialize($result);
    }

    /**
     * 将数据格式化为json数据，并返回
     *
     * @param       $code
     * @param       $message
     * @param array $data
     *
     * @return string
     */
    public static function json($code, $message, $data = [])
    {
        $result = static::getArrayData($code, $message, $data);
        return json_encode($result);
    }

    /**
     * 将数据转换成msgpack的格式，并返回
     *
     * @param       $code
     * @param       $message
     * @param array $data
     *
     * @return mixed
     */
    public static function msgpack($code, $message, $data = [])
    {
        $result = static::getArrayData($code, $message, $data);
        return msgpack_pack($result);
    }

    /**
     * 将数据转换为bjson的格式
     *
     * @param       $code
     * @param       $message
     * @param array $data
     *
     * @return string
     */
    public static function bjson($code, $message, $data = [])
    {
        $result = static::getArrayData($code, $message, $data);
        return MongoDB\BSON\fromPHP($result);
    }


    /**
     * 格式化数据为xml
     *
     * @param       $code
     * @param       $message
     * @param array $data
     *
     * @return string
     */
    public static function xml($code, $message, $data = [])
    {
        $result = static::getArrayData($code, $message, $data);
        $xml = "<?xml version='1.0' encoding='UTF-8'?>";
        $xml .= "<root>";
        $xml .= static::xmlToEncode($result);
        $xml .= "</root>";

        return $xml;
    }

    private static function xmlToEncode($data)
    {
        $xml = '';
        foreach ($data as $key => $value) {
            $attr = "";
            if (is_numeric($key)) {
                $attr = " id='{$key}'";
                $key = "item";
            }
            $xml .= "<{$key}{$attr}>";
            if (is_array($value)){
                $xml .= self::xmlToEncode($value);
            }else if (is_bool($value)){
                $xml .= $value ? 'true' : 'false';
            }else {
                $xml .= "{$value}";
            }
            $xml .= "</{$key}>";
        }
        return $xml;
    }

    private static function getFormat()
    {
        $map = [
            'application/xml' => 'xml',
            'text/xml' => 'xml',
            'application/json' => 'json',
            'text/json' => 'json',
            'application/vnd.php.serialized' => 'serialize',
            'application/bjson' => 'bjson',
            'application/msgpack' => 'msgpack',
        ];
        $accept = strtolower($_SERVER['HTTP_ACCEPT']);

        foreach ($map as $key => $value) {
            if (false === strstr($accept, $key)) {
                continue;
            }
            return $value;
        }

        return static::$_format;
    }

    public static function setHeader(){
        $outputMap = [
            'xml' => "Content-type: text/xml",
            'json' => "Content-type: application/json",
        ];


        $type = static::getFormat();

        if (isset($outputMap[$type])){
            header($outputMap[$type]);
        }
    }
}