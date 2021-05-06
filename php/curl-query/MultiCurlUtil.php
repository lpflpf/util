<?php

/**
 * MultiCurl.php
 *
 * @author   lipengfei
 * @created  2017/8/14 18:29
 */
class MultiCurlUtil
{
    private $_handle;
    private $_maps;

    public function __construct()
    {
        $this->_handle = curl_multi_init();
    }

    /**
     * @param CurlUtil $curlUtil
     */
    public function add(CurlUtil $curlUtil)
    {
        curl_multi_add_handle($this->_handle, $curlUtil->getHandle());
        $this->_maps[] = $curlUtil;
    }

    public function exec()
    {
        $active = null;
        do {
            $mrc = curl_multi_exec($this->_handle, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            while(curl_multi_exec($this->_handle, $active) === CURLM_CALL_MULTI_PERFORM);

            if (curl_multi_select($this->_handle) != -1) {
                do {
                    $mrc = curl_multi_exec($this->_handle, $active);
                } while( $mrc == CURLM_CALL_MULTI_PERFORM);
            } else {
                usleep(10);
            }
        }

        /**
         * @var $subHandle CurlUtil
         */
        foreach($this->_maps as $key => $subHandle){
            $subHandle->setResponse(curl_multi_getcontent($subHandle->getHandle()));
            $subHandle->dealResult();
            curl_multi_remove_handle($this->_handle, $subHandle->getHandle());
        }

        curl_multi_close($this->_handle);
    }

    public function __destruct()
    {
        /**
         * @var $subHandle CurlUtil
         */
        foreach ($this->_maps as $key => $subHandle) {
            $subHandle->close();
        }
        if (is_resource($this->_handle)) {
            curl_multi_close($this->_handle);
        }
    }
}