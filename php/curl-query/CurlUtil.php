<?php

/**
 * CurlUtil.php
 *
 * @author   lipengfei
 * @created  2017/8/14 17:03
 */
class CurlUtil
{
    private $_handle;
    const POST_JSON = 0;
    const POST_BINARY = 1;
    const POST_URLENCODE = 2;
    const DEFAULT_TIME_OUT = 3;

    private $_response = null;
    private $_result = null;
    private $_header = null;
    private $_formatResponseHeaders = [];
    private $_errorCode;
    private $_errorMessage;
    private $_httpCode;
    private $_curlInfo;
    private $_url;
    private $_method;
    private $_hasRequest = false;

    private $_requestSucceed = true;


    public function __construct()
    {
        $this->_handle = curl_init();
        $this->setOpt(CURLOPT_RETURNTRANSFER, 1);
        $this->setOpt(CURLOPT_HEADER, true);
        $this->setTimeout(CurlUtil::DEFAULT_TIME_OUT);
    }

    private function setOpt($option, $value)
    {
        curl_setopt($this->_handle, $option, $value);
    }

    private function setURL($url, $data = array())
    {
        $this->_url = $url;
        if (!empty($data)) {
            $this->_url = $this->_url . "?" . http_build_query($data);
        }
        $this->setOpt(CURLOPT_URL, $this->_url);
    }

    public function get($url = '', $data = array())
    {
        if (!empty($url)) {
            $this->setURL($url, $data);
        }
        $this->_method = 'get';
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
        $this->setOpt(CURLOPT_HTTPGET, true);
        $this->setOpt(CURLOPT_FOLLOWLOCATION, 1);
    }

    /**
     * @param string $url
     * @param array  $data
     * @param int    $type
     */
    public function post($url = '', $data = array(), $type = self::POST_JSON)
    {
        if (!empty($url)) {
            $this->setURL($url, $data);
        }

        $this->_method = 'post';
        $this->setOpt(CURLOPT_CUSTOMREQUEST, "POST");
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, $this->buildPostData($data, $type));
    }

    public function head($url, $data = array())
    {
        if (!empty($url)) {
            $this->setURL($url, $data);
        }
        $this->_method = 'head';
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'HEAD');
        $this->setOpt(CURLOPT_NOBODY, true);
    }

    /**
     * 执行exec，处理返回信息
     *
     * @return mixed
     */
    public function getResult()
    {
        if ($this->_hasRequest) {
            return $this->_result;
        }
        $this->_response = curl_exec($this->_handle);
        $this->dealResult();
        return $this->_result;
    }

    /**
     * @param $data
     * @param $type int  上传文件请使用POST_BINARY
     *
     * @return string
     */
    private function buildPostData($data, $type)
    {
        switch ($type) {
            case self::POST_JSON:
                $data = json_encode($data);
                $this->setOpt('Content-Type', 'application/json; charset=utf-8');
                $this->setOpt('Content-Length', strlen($data));
                break;

            case self::POST_URLENCODE:
                $data = http_build_url($data);
        }

        return $data;
    }

    public function setResponse($result)
    {
        $this->_response = $result;
        $this->_hasRequest = true;
    }

    public function dealResult()
    {
        $this->_errorCode = curl_errno($this->_handle);
        $this->_errorMessage = curl_error($this->_handle);
        $this->_httpCode = curl_getinfo($this->_handle, CURLINFO_HTTP_CODE);
        $this->_httpCode = ($this->_httpCode === 0) ? 499 : $this->_httpCode;

        $curlError = !($this->_errorCode === 0);
        $httpError = in_array($this->_httpCode, array(302,)) || in_array(floor($this->_httpCode / 100), array(4, 5));
        $this->_requestSucceed = !($curlError || $httpError);
        $this->_curlInfo = curl_getinfo($this->_handle);
        $header_size = curl_getinfo($this->_handle, CURLINFO_HEADER_SIZE);
        $this->_header = substr($this->_response, 0, $header_size);
        $this->_result = substr($this->_response, $header_size);
    }

    public function requestSucceed()
    {
        return $this->_requestSucceed;
    }

    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    public function getHttpCode()
    {
        return $this->_httpCode;
    }

    public function setHeader($key, $value)
    {
        curl_setopt($this->_handle, $key, $value);
    }

    public function setHeaders($headers)
    {
        foreach ($headers as $key => $value) {
            curl_setopt($this->_handle, $key, $value);
        }
    }

    public function getResponseHeader($key)
    {
        $key = trim($key);
        $key = strtolower($key);
        if (empty($this->_formatResponseHeaders)) {
            $this->getResponseHeaders();
        }

        return $this->_formatResponseHeaders[$key] ?? '';
    }

    public function getResponseHeaders($format = true)
    {
        if ($format == false) {
            return $this->_header;
        }
        $rawHeaders = preg_split('/\r\n/', $this->_header, null, PREG_SPLIT_NO_EMPTY);

        $numRawHeaders = count($rawHeaders);
        for ($i = 1; $i < $numRawHeaders; $i++) {
            $line = $rawHeaders[$i];
            list($key, $value) = explode(':', $line, 2);
            $key = strtolower(trim($key));
            $value = trim($value);

            if (isset($this->_formatResponseHeaders[$key])) {
                $this->_formatResponseHeaders[$key] .= $value;
            } else {
                $this->_formatResponseHeaders[$key] = $value;
            }
        }

        return $this->_formatResponseHeaders;
    }

    public function setTimeout($seconds)
    {
        $this->setOpt(CURLOPT_TIMEOUT, $seconds);
    }


    /**
     * @return resource
     */
    public function getHandle()
    {
        return $this->_handle;
    }

    public function close()
    {
        if (is_resource($this->_handle)) {
            curl_close($this->_handle);
        }
    }
}