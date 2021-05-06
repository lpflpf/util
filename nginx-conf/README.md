��̬�ļ���
```
    http {
    # �����Ϊ���ļ�ָ�����棬Ĭ����û�����õģ�max ָ������������
    # ����ʹ��ļ���һ�£�inactive ��ָ�����೤ʱ���ļ�û�������ɾ�����档
    open_file_cache max=204800 inactive=20s;
    # open_file_cache ָ���е�inactive ����ʱ�����ļ�������ʹ�ô�����
    # �������������֣��ļ�������һֱ���ڻ����д򿪵ģ��������������һ��
    # �ļ���inactive ʱ����һ��û��ʹ�ã��������Ƴ���
    open_file_cache_min_uses 1;
    # �����ָ�೤ʱ����һ�λ������Ч��Ϣ
    open_file_cache_valid 30s;
    # Ĭ������£�Nginx��gzipѹ���ǹرյģ� gzipѹ�����ܾ��ǿ��������ʡ��
    # �ٴ������ǻ����ӷ�����CPU�Ŀ���Ŷ��NginxĬ��ֻ��text/html����ѹ�� ��
    # ���Ҫ��html֮������ݽ���ѹ�����䣬������Ҫ�ֶ������á�
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
* log_format �����ֶΣ�

| �ֶ� |	���� |
| :--: |:-- |
|$remote_addr��$http_x_forwarded_for | ��¼�ͻ���IP��ַ|
| $remote_user | ��¼�ͻ����û�����|
|$request      |��¼�����URI��HTTPЭ�� |
|$status       |��¼����״̬|
|$body_bytes_sent |���͸��ͻ��˵��ֽ�������������Ӧͷ�Ĵ�С|
|$bytes_sent |	���͸��ͻ��˵����ֽ���|
|$connection |	���ӵ����к�|
|$connection_requests	| ��ǰͨ��һ�����ӻ�õ���������|
|$msec	|��־д��ʱ�䡣��λΪ�룬�����Ǻ���|
|$pipe |	���������ͨ��HTTP��ˮ��(pipelined)���ͣ�pipeֵΪ��p��������Ϊ��.��|
|$http_referer	|��¼���ĸ�ҳ�����ӷ��ʹ�����|
|$http_user_agent|	��¼�ͻ�������������Ϣ|
|$request_length |	����ĳ��ȣ����������У�����ͷ���������ģ�|
|$request_time	|������ʱ�䣬��λΪ�룬���Ⱥ���|
|$time_iso8601 |	ISO8601��׼��ʽ�µı���ʱ��|
|$time_local |	��¼����ʱ����ʱ��|



## Nginx limit ��ز�����

### ngx_stream_limit_conn_module ��������������
��������ÿ��key�����������ر������ÿ��ip��������

### limit_conn_zone#
* �﷨: `limit_conn_zone $variable zone=name:size;`
* Ĭ��ֵ: none
*���ö�: http
*��ָ�������Ự״̬�洢���򡣼���״̬�б����˵�ǰ������������ֵ�������ض��������κηǿ�ֵ����ֵ�����ᱻ���ǣ���  
`$variable`�������`zone=name`�����������ƣ������`limit_conn`ָ����õ��ġ�size��������������ڴ�ռ��С���磺

```
limit_conn_zone $binary_remote_addr zone=addr:10m;
```

ע�ͣ��ͻ��˵�IP��ַ��Ϊ����ע�⣬����ʹ�õ���$binary_remote_addr������������$remote_addr������  
`$remote_addr` �����ĳ���Ϊ7�ֽڵ�15�ֽڣ����洢״̬��32λƽ̨��ռ��32�ֽڻ�64�ֽڣ���64λƽ̨��ռ��64�ֽڡ�  
`$binary_remote_addr` �����ĳ����ǹ̶���4�ֽڣ��洢״̬��32λƽ̨��ռ��32�ֽڻ�64�ֽڣ���64λƽ̨��ռ��64�ֽڡ�  
1M����ռ���Ա���3.2���32λ��״̬��1.6���64λ��״̬��
��������ڴ�ռ䱻�ľ�������������Ժ������е����󷵻� 503 (Service Temporarily Unavailable) ����
limit_zone ָ���limit_conn_zoneָ��ͬ����˼���Ѿ������ã��Ͳ�����˵���ˡ�

### limit_conn_log_level
* �﷨��`limit_conn_log_level info | notice | warn | error`
* Ĭ��ֵ��error
* ���öΣ�http, server, location
���ﵽ��������������󣬼�¼��־�ĵȼ���


### limit_conn
* �﷨��`limit_conn zone_name number`
* Ĭ��ֵ��none
* ���öΣ�http, server, location
* ָ��ÿ��������ֵ�����ͬʱ���������������������ʱ������503 (Service Temporarily Unavailable)�����磺
```
limit_conn_zone $binary_remote_addr zone=addr:10m;
server {
    location /www.ttlsa.com/ {
        limit_conn addr 1;
    }
}
```
ͬһIPͬһʱ��ֻ������һ�����ӡ�
����� limit_conn ָ�����ʱ�����е����������ƶ�����Ч�����磬�������ò��������Ƶ�һIP��Դ����������ͬʱҲ�����Ƶ�һ���������������������

### limit_conn_status
* �﷨: `limit_conn_status code;`
* Ĭ��ֵ: limit_conn_status 503;
* ���ö�: http, server, location
* ��ָ����1.3.15�汾����ġ�ָ������������ʱ�����ص�״̬�롣Ĭ����503��

### limit_rate
* �﷨��`limit_rate rate`
* Ĭ��ֵ��0
* ���öΣ�http, server, location, if in location
��ÿ�����ӵ��������ơ�����rate�ĵ�λ���ֽ�/�룬����Ϊ0���ر����١� ���������ٶ����ǰ�IP���ƣ�������ĳ���ͻ���ͬʱ�������������ӣ���ô�ͻ��˵���������������ָ������ֵ��2����

### ngx_http_limit_req_module  ��������������

���÷�����
    limit_req_zone    [http]
    �﷨��
```
    limit_req_zone key zone=name:size rate=rate;
    rate �ĵ�λ������r/m ������ r/s
```
���ӣ�
    ����һ�鹲���ڴ����������������ֵ��״̬������ �ر��Ǳ����˵�ǰ��������������� ����ֵ����ָ���ı�������ֵ���ᱻ���㣩����

### limit_req_zone $binary_remote_addr zone=one:10m rate=1r/s;
* ˵������������Ϊone����СΪ10m��ƽ�����������Ƶ�ʲ��ܳ���ÿ��һ�Ρ�
* ��ֵ�ǿͻ���IP��
ʹ��$binary_remote_addr������ ���Խ�ÿ��״̬��¼�Ĵ�С���ٵ�64���ֽڣ�����1M���ڴ���Ա����Լ1��6ǧ��64�ֽڵļ�¼��
���������Ĵ洢�ռ�ľ��ˣ����ں����������󣬷��������᷵�� 503 (Service Temporarily Unavailable)����

### limit_req   [http, server, location]
�﷨��
```
    limit_req zone=name [burst=number] [nodelay];
```
           
### limit_req_log_level
�﷨��
```
     limit_req_log_level info | notice | warn | error;
```
#### ���ö�: http, server, location
#### ��������ϣ������־���𣬵���������ΪƵ�ʹ��߾ܾ������ӳٴ�������ʱ���Լ�����Ӧ�������־���ӳټ�¼����־����Ⱦܾ��ĵ�һ�����𣻱��磬 ������á�limit_req_log_level notice���� �ӳٵ���־����info����

### limit_req_status
�﷨��
```
    limit_req_status code;
```
* Ĭ��ֵ: limit_req_status 503;
* ���ö�: http, server, location  
* ���þܾ��������Ӧ״̬�롣