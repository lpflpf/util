local _M = {}

-- cachekey  threshold
local cache_maps = {
    t1_ = 100,
    t2_ = 200,
}

function redis_init()
    -- redis conf
    local redis_host = "127.0.0.1"
    local redis_port = "6379"
    local redis_timeout_time = 200 -- ms
    local redis = require "redis"
    local instance = redis:new()
    instance:set_timeout(redis_timeout_time)
    local ok, err = instance:connect(redis_host, redis_port)
    if not ok then
        return
    end
    return instance
end

-- nginx setting.
-- access_by_lua_block {
-- require("throttle"):set(1000, "PIP:", 60)
-- }
-- expire_time : redis cache expire time.
-- cache_key : PIP:
function _M.set(self, cache_key, cache_cycle)

    -- Baiduspider
    if ngx.var.http_user_agent == "Baiduspider" then
        return
    end

    -- get client IP.
    local util = require("util");
    local client_ip = util:get_client_ip()
    if client_ip == false then
        return
    end

    -- get value.
    local key = cache_key .. client_ip
    local keepalive_timeout = 1000 -- ms
    local keepalive_count = 5
    local instance = redis_init()
    if nil == instance then
        return
    end
    local res, err = instance:get(key)
    if not res then -- redis error.
        return
    end

    ngx.update_time()
    local current_time = ngx.time()

    -- count begin at every integral minutes.
    local expire_time = math.ceil(current_time / cache_cycle) * cache_cycle

    -- not exceed threshold, then update ip times.
    if res == ngx.null or tonumber(res) < cache_maps[cache_key] then
        -- match threshold, then timeout will be one day.
        instance:incr(key)
        instance:expireat(key, expire_time)
        instance:set_keepalive(keepalive_timeout, keepalive_count)
        return
    end

    -- cookie 10 minutes.
    if tonumber(res) < 100000 then
        instance:incrby(key, 100000)
        instance:expire(key, blacklist_expire_time)
        instance:set_keepalive(keepalive_timeout, keepalive_count)
    end

--    log data & write result.
--
--    location = /lua_log {
--        internal;
--        access_log "/var/log/httpd/lua_access_log" main;
--
--        access_by_lua_block{
--            ngx.header.content_type = "text/html"
--            ngx.say('GOTO <a href="http://www.baidu.com">www.baidu.com</a>')
--            ngx.exit(ngx.HTTP_OK)
--        }
--    }

    ngx.exec("/lua_log")
end

function print_page(content)
    ngx.say(content)
    ngx.exit(ngx.HTTP_OK)
end

-- nginx setting.
--
--location = /blacklist {
--    access_by_lua_block {
--        require("throttle"):blacklist()
--    }
--}

function _M.blacklist(self)
    ngx.header.content_type = "text/html"
    local ip = ngx.var.arg_ip
    if ip == nil then
        print_page('ip params error')
    end

    local instance = redis_init()
    local result = {}

    ngx.update_time()
    for key, expires in ipairs(cache_maps) do
        result.key = instance:get(key .. ip)
        if result ~= ngx.null and tonumber(result) >= expires then
            local time = os.date("%Y-%m-%d %H:%M:%S", current_time + tonumber(instance:ttl("CIP:" .. ip) - blacklist_expire_time))
            result.insert(ip .. ' <b> in </b> ' .. key .. '. Time is ' .. time);
        end
    end

    instance:set_keepalive(keepalive_timeout, keepalive_count)
    local current_time = ngx.time()

    print_page(result.concat("</br>"))
end

return _M
