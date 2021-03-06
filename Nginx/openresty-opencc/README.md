这个利用nginx（openresty）+lua+opencc，写10行代码左右就可以实现的类似繁简通的功能
可充分利用nginx的快反应和可靠性

用到的项目
https://github.com/BYVoid/OpenCC
https://github.com/Finalcheat/lua-resty-opencc/blob/master/lib/resty/opencc.lua

opencc在centos7可以用yum安装，但版本很旧
yum install doxygen opencc opencc-tools opencc-devel
centos7上编译新版本可能要升级gcc，比较麻烦，我在centos8上编译过新版本
参考：
yum install cmake gcc-c++ bison flex
http://www.doxygen.nl/download.html
https://bintray.com/byvoid/opencc/OpenCC
ln -s /user/bin/python3.6 /user/bin/python
ln -s /usr/lib/libopencc.so.2 /usr/lib64/libopencc.so.2

安装openresty后opencc.lua放到/usr/local/openresty/lualib/resty/

和checkapi和whois的配置方式差不多