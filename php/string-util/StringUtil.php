<?php

/**
 * StringUtil.php
 *
 * @author   lipengfei
 * @created  2017/8/14 16:47
 */
class StringUtil
{
    /**
     * 截取带链接的中文字符串
     *
     * @param string $str
     * @param string $width
     * @param string $encode
     *
     * @return string
     */
    public static function cutWords($str, $width)
    {
        $strip_str = strip_tags($str);
        $strip_str = str_replace(array(" ", "\t", "\n", "\r"), array("", "", "", ""), $strip_str);
        $wlen = mb_strwidth(strip_tags($strip_str), 'utf-8');
        if ($wlen < $width) {
            return $str;
        }
        $len = strlen($str);
        preg_match_all("/<\s*a(.*?)>(.*?)<\/a>/is", $str, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $match_len = count($matches);
        $begin = 0;
        $end = $match_len == 0 ? $len : $matches [0] [0] [1];
        $i = 0;
        $words = array();
        $k = 0;
        while ($i < $match_len && $len > $begin) {
            // if no other words, then set href
            if ($end - $begin == 0) {
                if ($i < $match_len - 1) {
                    $end = $matches [$i + 1] [0] [1];
                }
                $begin = $matches [$i] [0] [1] + strlen($matches [$i] [0] [0]);
                $words [$k] = array(
                    "type" => "1",
                    "str" => str_replace(array(
                        " ",
                        "\t",
                        "\n",
                        "\r"
                    ), array(
                        "",
                        "",
                        "",
                        ""
                    ), $matches [$i] [2] [0]),
                    "attr" => $matches [$i] [1] [0]
                );
                $k = $k + 1;
                $i = $i + 1;
                continue;
            }
            $words [$k] = array(
                "type" => "0",
                "str" => str_replace(array(
                    " ",
                    "\t",
                    "\n",
                    "\r"
                ), array(
                    "",
                    "",
                    "",
                    ""
                ), substr($str, $begin, $end - $begin))
            );
            $begin = $end;
            if ($words [$k] ["str"] == "") {
                unset ($words [$k]);
            } else {
                $k = $k + 1;
            }
        }
        if ($begin < $len) {
            $words [$k] = array(
                "type" => "0",
                "str" => str_replace(array(" ", "\t", "\n", "\r"), array("", "", "", ""), substr($str, $begin, $len - $begin))
            );
        }
        for ($i = count($words) - 1; $i >= 0; $i--) {
            $tmp = mb_strwidth($words [$i] ["str"], 'utf-8');
            if ($wlen - $tmp > $width) {
                $wlen -= $tmp;
                $words [$i] ["str"] = "";
                continue;
            } else {
                $words [$i] ["str"] = mb_strcut($words [$i] ["str"], 0, $tmp - $wlen + $width, 'utf-8');
                if ($wlen - $width > 0) // 即 $tmp > $tmp - $wlen + $width
                {
                    $words [$i] ["str"] .= '...';
                }
                break;
            }
        }
        $result = '';
        foreach ($words as $k => $value) {
            if ($value["type"] == 1) {
                if ($value["str"] == "") {
                    break;
                }
                $result .= '<a' . $value["attr"] . '>' . $value["str"] . '</a>';
            } else {
                if ($value["str"] == "") {
                    break;
                }
                $result .= $value["str"];
            }
        }
        return $result;
    }

    /**
     * 将字符按照等宽切为数组
     *
     * @return array
     */
    public static function cutWords2Array($words, $width)
    {
        $results = [];
        $words = strip_tags($words);
        $words = str_replace(array("\t", "\n", "\r"), array("", "", "", ""), $words);
        $totalWidth = mb_strwidth($words, 'utf-8') / 2;
        $splitCount = ceil(floatval($totalWidth) / $width);
        $begin = 0;

        for ($i = 0; $i < $splitCount; $i++) {
            $substr = mb_strimwidth($words, $begin , $width * 2);
            $begin = $begin + mb_strlen($substr);
            $results [] = $substr;
        }

        return $results;
    }
}