```php
 /**
     * 生成CSR和KEY
     */
    public function CreateCsr()
    {
        $all = request()->all();
        $dn = [
            "countryName" => trim(strtoupper($all['countryName'])),                                  //所在国家
            "stateOrProvinceName" => trim($all['stateOrProvinceName']),                    //所在省份
            "localityName" => trim($all['localityName']),                        //所在城市
            "organizationName" => trim($all['organizationName']),         //注册人姓名
            "organizationalUnitName" => trim($all['organizationalUnitName']),   //组织名称
            "commonName" => trim($all['commonName']),                          //公共名称
        ];
        if (isset($all['emailAddress']) && $all['emailAddress']) {
            $dn['emailAddress'] = $all['emailAddress'];
        }
        $config = [
            "private_key_bits" => isset($all['keysize']) && $all['keysize'] ? $all['keysize'] : 2048,                     //字节数    512 1024  2048   4096 等
            "private_key_type" => OPENSSL_KEYTYPE_RSA,     //加密类型
            'digest_alg' => 'sha256',
        ];
        $privkey = openssl_pkey_new($config);
        if ($privkey === false) {
            $config['config'] = "D:/phpstudy_pro/Extensions/Apache2.4.39/conf/openssl.cnf";
//            $config['config'] = "/etc/pki/tls/openssl.cnf";
            $privkey = openssl_pkey_new($config);
        }
        $csr = openssl_csr_new($dn, $privkey, $config);
//        $sscert = openssl_csr_sign($csr, null, $privkey, 365, $csr,$config);
//        openssl_x509_export($sscert, $csrkey); //将公钥证书存储到一个变量 $csrkey，由 PEM 编码格式命名。
        openssl_csr_export($csr, $csrout);

        openssl_pkey_export($privkey, $privatekey, null, $config);
        return $this->returnResponse([
            'certificate' => $csrout,
            'privatekey' => $privatekey
        ]);
    }
    
    /**
    检测域名的证书实际有效时间
    **/
      function get_cert_info($domain)
        {
    
            $context = stream_context_create(['ssl' => [
                'capture_peer_cert' => true,
                'capture_peer_cert_chain' => true,
            ],
            ]);
            try {
                $client = stream_socket_client("ssl://" . $domain . ":443", $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $context);
            }catch (\Exception $e){
                return false;
            }
            if ($client == false) {
                return false;
            }
            $params = stream_context_get_params($client);
            $cert = $params['options']['ssl']['peer_certificate'];
            $cert_info = openssl_x509_parse($cert);
            return $cert_info;
        }
```