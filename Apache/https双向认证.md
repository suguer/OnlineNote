Country Name (2 letter code) [AU]:cn  
State or Province Name (full name) [Some-State]:gd  
Locality Name (eg, city) []:zh  
Organization Name (eg, company) [Internet Widgits Pty Ltd]:company  
Organizational Unit Name (eg, section) []:it  
Common Name (e.g. server FQDN or YOUR name) []:root  

参考链接:  
阿里云指导openssl签发流程
https://help.aliyun.com/document_detail/160093.html?spm=5176.10695662.1996646101.searchclickresult.480f29d22TQ6uz  
php官方文档  
https://www.php.net/manual/en/function.openssl-csr-sign.php  
$cacert = "file://path/to/ca.crt"; 文档中此处值得ca.crt是下面所指的root.pem,不是根证书的root.crt,不一样的~~  


使用工具 openssl

创建根证书私钥：
openssl genrsa -out root.key 1024

创建根证书请求文件：
openssl req -new -out root.csr -key root.key

创建根证书：
openssl x509 -req -in root.csr -out root.crt -signkey root.key -CAcreateserial -days 36500

创建根证书.pem
openssl req -new -x509 -key root.key -out root.pem -days 36500

在创建证书请求文件的时候需要注意三点，下面生成服务器请求文件和客户端请求文件均要注意这三点： 根证书的Common Name填写root就可以，所有客户端和服务器端的证书这个字段需要填写域名，一定要注意的是，根证书的这个字段和客户端证书、服务器端证书不能一样； 其他所有字段的填写，根证书、服务器端证书、客户端证书需保持一致 最后的密码可以直接回车跳过。

php签发个人证书的流程
```php
$dn = array(
    "countryName" => 'cn',                                  //所在国家
    "stateOrProvinceName" => 'gd',                    //所在省份
    "localityName" => 'zh',                        //所在城市
    "organizationName" => 'company',         //注册人姓名
    "organizationalUnitName" => 'it',   //组织名称
    "commonName" => 'account',                          //公共名称
);

$config = array(
    'config'=>"openssl.cnf",
);

$privkey = openssl_pkey_new($config);
$csr = openssl_csr_new($dn, $privkey, $config);

//php的openssl_csr_sign的时候,调用公钥证书为个人证书签名的时候,要调用root.pem
$cart=openssl_csr_sign($csr,"file://conf/ssl/root.pem","file://conf/ssl/root.key",365,$config);

openssl_pkey_export($privkey, $privatekey, null, $config);

$resp = openssl_pkcs12_export_to_file($cart, "/data/ssl/{$account}.p12", $privatekey, "",$config);

```

apache配置流程  
```apacheconfig
<VirtualHost *:443>
    DocumentRoot "D:/phpserver/website/public"
    ServerName domain.cn
    ServerAlias 
    SSLEngine on
    #单向认证
    SSLCertificateFile "D:/phpstudy_pro/Extensions/Apache2.4.39/conf/ssl/.crt"
    SSLCertificateKeyFile "D:/phpstudy_pro/Extensions/Apache2.4.39/conf/ssl/.key"
    SSLCertificateChainFile "D:/phpstudy_pro/Extensions/Apache2.4.39/conf/ssl/.cnchain.crt"
   
    #双向认证
   	SSLCACertificateFile "D:/phpserver/website/public/conf/ssl/root.crt"
   	SSLVerifyClient require
   	SSLVerifyDepth  1
    
    
	SSLCertificateFile "C:/Users/staff/Desktop/ssl/cacert.pem"
    SSLCertificateKeyFile "C:/Users/staff/Desktop/ssl/server.key"
    FcgidInitialEnv PHPRC "D:/phpstudy_pro/Extensions/php/php7.3.4nts"
    AddHandler fcgid-script .php
    FcgidWrapper "D:/phpstudy_pro/Extensions/php/php7.3.4nts/php-cgi.exe" .php
	
  <Directory "D:/phpserver/website/public">
      Options FollowSymLinks ExecCGI
      AllowOverride All
      Order allow,deny
      Allow from all
      Require all granted
	  DirectoryIndex index.php index.html error/index.html
  </Directory>
</VirtualHost>

```