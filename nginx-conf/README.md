静态文件：
```
    http {
    # 这个将为打开文件指定缓存，默认是没有启用的，max 指定缓存数量，
    # 建议和打开文件数一致，inactive 是指经过多长时间文件没被请求后删除缓存。
    open_file_cache max=204800 inactive=20s;
    # open_file_cache 指令中的inactive 参数时间内文件的最少使用次数，
    # 如果超过这个数字，文件描述符一直是在缓存中打开的，如上例，如果有一个
    # 文件在inactive 时间内一次没被使用，它将被移除。
    open_file_cache_min_uses 1;
    # 这个是指多长时间检查一次缓存的有效信息
    open_file_cache_valid 30s;
    # 默认情况下，Nginx的gzip压缩是关闭的， gzip压缩功能就是可以让你节省不
    # 少带宽，但是会增加服务器CPU的开销哦，Nginx默认只对text/html进行压缩 ，
    # 如果要对html之外的内容进行压缩传输，我们需要手动来设置。
    gzip on;
    gzip_min_length 1k;
    gzip_buffers 4 16k;
    gzip_http_version 1.0;
    gzip_comp_level 2;
    gzip_types text/plain application/x-javascript text/css application/xml;
 server {
     listen 80;
     server_name www.test.com;
     charset utf-8;
     root /data/www.test.com;
     index index.html index.htm;
 }
}     
```
* log_format 可用字段：

| 字段 |	作用 |
| :--: |:-- |
|$remote_addr与$http_x_forwarded_for | 记录客户端IP地址|
| $remote_user | 记录客户端用户名称|
|$request      |记录请求的URI和HTTP协议 |
|$status       |记录请求状态|
|$body_bytes_sent |发送给客户端的字节数，不包括响应头的大小|
|$bytes_sent |	发送给客户端的总字节数|
|$connection |	连接的序列号|
|$connection_requests	| 当前通过一个连接获得的请求数量|
|$msec	|日志写入时间。单位为秒，精度是毫秒|
|$pipe |	如果请求是通过HTTP流水线(pipelined)发送，pipe值为“p”，否则为“.”|
|$http_referer	|记录从哪个页面链接访问过来的|
|$http_user_agent|	记录客户端浏览器相关信息|
|$request_length |	请求的长度（包括请求行，请求头和请求正文）|
|$request_time	|请求处理时间，单位为秒，精度毫秒|
|$time_iso8601 |	ISO8601标准格式下的本地时间|
|$time_local |	记录访问时间与时区|



## Nginx limit 相关参数：

### ngx_stream_limit_conn_module （限制连接数）
用于限制每个key的连接数。特别是针对每个ip的连接数

### limit_conn_zone#
* 语法: `limit_conn_zone $variable zone=name:size;`
* 默认值: none
*配置段: http
*该指令描述会话状态存储区域。键的状态中保存了当前连接数，键的值可以是特定变量的任何非空值（空值将不会被考虑）。  
`$variable`定义键，`zone=name`定义区域名称，后面的`limit_conn`指令会用到的。size定义各个键共享内存空间大小。如：

```
limit_conn_zone $binary_remote_addr zone=addr:10m;
```

注释：客户端的IP地址作为键。注意，这里使用的是$binary_remote_addr变量，而不是$remote_addr变量。  
`$remote_addr` 变量的长度为7字节到15字节，而存储状态在32位平台中占用32字节或64字节，在64位平台中占用64字节。  
`$binary_remote_addr` 变量的长度是固定的4字节，存储状态在32位平台中占用32字节或64字节，在64位平台中占用64字节。  
1M共享空间可以保存3.2万个32位的状态，1.6万个64位的状态。
如果共享内存空间被耗尽，服务器将会对后续所有的请求返回 503 (Service Temporarily Unavailable) 错误。
limit_zone 指令和limit_conn_zone指令同等意思，已经被弃用，就不再做说明了。

### limit_conn_log_level
* 语法：`limit_conn_log_level info | notice | warn | error`
* 默认值：error
* 配置段：http, server, location
当达到最大限制连接数后，记录日志的等级。


### limit_conn
* 语法：`limit_conn zone_name number`
* 默认值：none
* 配置段：http, server, location
* 指定每个给定键值的最大同时连接数，当超过这个数字时被返回503 (Service Temporarily Unavailable)错误。如：
```
limit_conn_zone $binary_remote_addr zone=addr:10m;
server {
    location /www.ttlsa.com/ {
        limit_conn addr 1;
    }
}
```
同一IP同一时间只允许有一个连接。
当多个 limit_conn 指令被配置时，所有的连接数限制都会生效。比如，下面配置不仅会限制单一IP来源的连接数，同时也会限制单一虚拟服务器的总连接数：

### limit_conn_status
* 语法: `limit_conn_status code;`
* 默认值: limit_conn_status 503;
* 配置段: http, server, location
* 该指定在1.3.15版本引入的。指定当超过限制时，返回的状态码。默认是503。

### limit_rate
* 语法：`limit_rate rate`
* 默认值：0
* 配置段：http, server, location, if in location
对每个连接的速率限制。参数rate的单位是字节/秒，设置为0将关闭限速。 按连接限速而不是按IP限制，因此如果某个客户端同时开启了两个连接，那么客户端的整体速率是这条指令设置值的2倍。

### ngx_http_limit_req_module  （限制请求数）

配置方法：
    limit_req_zone    [http]
    语法：
```
    limit_req_zone key zone=name:size rate=rate;
    rate 的单位可以是r/m 或者是 r/s
```
例子：
    设置一块共享内存限制域用来保存键值的状态参数。 特别是保存了当前超出请求的数量。 键的值就是指定的变量（空值不会被计算）。如

### limit_req_zone $binary_remote_addr zone=one:10m rate=1r/s;
* 说明：区域名称为one，大小为10m，平均处理的请求频率不能超过每秒一次。
* 键值是客户端IP。
使用$binary_remote_addr变量， 可以将每条状态记录的大小减少到64个字节，这样1M的内存可以保存大约1万6千个64字节的记录。
如果限制域的存储空间耗尽了，对于后续所有请求，服务器都会返回 503 (Service Temporarily Unavailable)错误。

### limit_req   [http, server, location]
语法：
```
    limit_req zone=name [burst=number] [nodelay];
```
           
### limit_req_log_level
语法：
```
     limit_req_log_level info | notice | warn | error;
```
#### 配置段: http, server, location
#### 设置你所希望的日志级别，当服务器因为频率过高拒绝或者延迟处理请求时可以记下相应级别的日志。延迟记录的日志级别比拒绝的低一个级别；比如， 如果设置“limit_req_log_level notice”， 延迟的日志就是info级别。

### limit_req_status
语法：
```
    limit_req_status code;
```
* 默认值: limit_req_status 503;
* 配置段: http, server, location  
* 设置拒绝请求的响应状态码。