local _M = {}

local function is_wan_ip(ip)

    if ip == nil or ip == '' or ip =='127.0.0.1' or #ip < 7 or ip == 'unknown' then
        return false
    end
	
    local dot1loc = string.find(ip, ".", 0 , true)
    local dot2loc = string.find(ip, ".", dot1loc + 1, true)
    local dot3loc = string.find(ip, ".", dot2loc + 1, true)
    local ip4number = tonumber(string.sub(ip, 1, dot1loc)) * 0x1000000
                    + tonumber(string.sub(ip, dot1loc + 1, dot2loc - 1)) * 0x10000
                    + tonumber(string.sub(ip, dot2loc + 1, dot3loc - 1)) * 0x100
                    + tonumber(string.sub(ip, dot3loc + 1))

    -- local address
    if (ip4number >= 0x0A000000 and ip4number <= 0x0AFFFFFF )
         or (ip4number >= 0xAC1000000 and ip4number <= 0xAC1FFFFF)
         or (ip4number >= 0xC0A800000 and ip4number <= 0xC0A8FFFF) then
        return false
    end

    return ip
end


local function check_ip(ip)
    local begin = 1
    repeat
        local sep = string.find(ip, ",", begin, true)
        local sub_ip = ''
        if (sep == nil) then
            sub_ip = string.sub(ip, begin)
        else
            sub_ip = string.sub(ip, begin, sep - 1)
            begin = sep + 2
        end
        if is_wan_ip(sub_ip) then
            return sub_ip
        end
    until sep == nil

    return false
end

function _M.get_client_ip(self)
    local ip = ngx.var.http_x_forwarded_for
    if ip == nil then
        ip = ngx.var.remote_addr
    end
    if ip == nil then
        return false
    end
    return check_ip(ip)
end
return _M
