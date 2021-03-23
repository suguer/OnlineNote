最近项目上需要吧后台的链接升级,开启https并且设置到8443端口,在laravel的翻页以及跳转的页面上,会把端口号给隐藏了,
原因在于laravel/vendor/symfony/http-foundation/Request.php

```php
public function getPort()
    {
        if ($this->isFromTrustedProxy()) {
            if (self::$trustedHeaders[self::HEADER_CLIENT_PORT] && $port = $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_PORT])) {
                return $port;
            }

            if (self::$trustedHeaders[self::HEADER_CLIENT_PROTO] && 'https' === $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_PROTO], 'http')) {
                return 443;
            }
        }

        if ($host = $this->headers->get('HOST')) {
            if ($host[0] === '[') {
                $pos = strpos($host, ':', strrpos($host, ']'));
            } else {
                $pos = strrpos($host, ':');
            }

            if (false !== $pos) {
                return (int) substr($host, $pos + 1);
            }

            return 'https' === $this->getScheme() ? 443 : 80;
            // 443 => 8443
        }

        return $this->server->get('SERVER_PORT');
    }
```
