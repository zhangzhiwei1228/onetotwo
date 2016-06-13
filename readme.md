## 系统安装说明

### 一、运行环境 
本系统使用基于PHP + MySQL环境开发的SucoPHP框架搭建。

版本要求
`Apache 2+`
`MySQL5.0+`
`PHP5+`

###二、php.ini 环境设置

打开短标签符号

`short_open_tag: On` 

 设置SESSION文件保存路径，注意目录的写入权限

`session.save_path = "/tmp" `

请打开以下模块扩展


	extension=php_curl.dll
	extension=php_gd2.dll
	extension=php_mbstring.dll
	extension=php_exif.dll
	extension=php_mysql.dll
	extension=php_mysqli.dll
	extension=php_soap.dll
	extension=php_sockets.dll
	extension=php_xmlrpc.dll

###四、Web Server 设置

请将Apache或Nginx 主目录指向到 ROOT/wwwroot


###三、目录结构说明

程序目录结构说明 

`ROOT` 代表文件包根目录 

	ROOT
	├ appdata						应用程序数据
		├ caches					缓存文件目录
			├ db.conf.php			数据库配置文件
			├ rewrite.conf.php		URL 重写规则
		├ assets					资源包