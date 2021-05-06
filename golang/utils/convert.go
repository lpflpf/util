package com

import (
	"crypto/md5"
	"encoding/hex"
	"fmt"
	"golang.org/x/text/encoding/simplifiedchinese"
	"math"
	"strconv"
	"strings"
	"time"
)

func Int(src interface{}) int {
	return int(Int64(src))
}

func Int64(src interface{}) (dst int64) {
	var ok bool
	if dst, ok = src.(int64); !ok {
		switch src := src.(type) {
		case []byte:
			dst, _ = strconv.ParseInt(string(src), 10, 64)
		case string:
			dst, _ = strconv.ParseInt(src, 10, 64)
		case float32, float64:
			str := fmt.Sprintf("%v", src)
			f64, _ := strconv.ParseFloat(str, 64)
			dst = int64(math.Floor(f64))
		case nil:
			dst = 0
		case bool:
			if src {
				dst = 1
			}
		default:
			str := fmt.Sprintf("%v", src)
			dst, _ = strconv.ParseInt(str, 10, 64)
		}
	}
	return
}

func Boolean(src interface{}) bool {
	return Int64(src) != 0
}

func Float64(src interface{}, prec int) (dst float64) {
	switch src := src.(type) {
	case float32, float64:
		str := fmt.Sprintf("%v", src)
		dst, _ = strconv.ParseFloat(str, 64)
	case nil:
		dst = 0
	case []byte:
		dst, _ = strconv.ParseFloat(string(src), 64)
	case bool:
		if src {
			dst = 1
		} else {
			dst = 0
		}
	default:
		str := fmt.Sprintf("%v", src)
		dst, _ = strconv.ParseFloat(str, 64)
	}
	str := strconv.FormatFloat(dst, 'f', prec, 64)
	dst, _ = strconv.ParseFloat(str, 64)
	return dst
}

func String(src interface{}) string {
	switch val := src.(type) {
	case int:
		return strconv.Itoa(val)
	case []byte:
		return string(val)
	case nil:
		return ""
	default:
		return fmt.Sprintf("%v", src)
	}
}

func StringSlice(src interface{}) (dst []string) {
	for _, val := range src.([]interface{}) {
		dst = append(dst, String(val))
	}
	return
}

func GBK2U8(src string) string {
	result, _ := simplifiedchinese.GBK.NewDecoder().String(src)
	return result
}

func U82GBK(src string) string {
	result, _ := simplifiedchinese.GBK.NewEncoder().String(src)
	return result
}

func TimestampToUnixDate(unixTime int64) string {
	return time.Unix(unixTime, 0).Format("2006-01-02")
}

func TimestampToSortDate(unixTime int64) string {
	return time.Unix(unixTime, 0).Format("20060102")
}

func UnixDateToTime(unixDate string) (t time.Time) {
	t, _ = time.Parse("2006-01-02", unixDate)
	return t
}
