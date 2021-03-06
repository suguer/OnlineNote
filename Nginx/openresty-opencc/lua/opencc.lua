if (ngx.var.sent_http_content_type ~= nil and string.sub(ngx.var.sent_http_content_type, 1, 4) == "text") then
	local data=ngx.ctx.data or ""
	local cdata=ngx.arg[1]
	data=data..cdata
	ngx.ctx.data=data
	if ngx.arg[2] then
		local opencc = require("resty.opencc")
		-- local o = opencc:new("zhs2zht.ini") -- opencc 0.4.x
		local o = opencc:new("s2hk.json")
		ngx.log(ngx.ERR, " ngx.arg[1]:", data)
		local c = o:convert(data)
		o:close()
		ngx.arg[1] = c
	else
		ngx.arg[1] = ""
	end
end