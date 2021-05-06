<?php
/**
 * Created by PhpStorm.
 * User: lipengfei5
 * Date: 2018/12/21
 * Time: 11:44
 */

/**
 * 命令行模式下，获取本地IP
 * @return string
 */
function getLocalIp(){
    return gethostbyname(php_uname('n'));
}