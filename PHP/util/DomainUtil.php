<?php
namespace core\util;

use core\util\domain\Punycode;

class DomainUtil
{
    /**
     * 获取域名的tld
     * @param $domain
     * @return string
     */
    public static function getTld($domain)
    {
        $parts = explode('.', $domain);
        $count = count($parts);
        return $count > 1 ? $parts[$count - 1] : '';
    }

    /**
     * 是否包含中文
     * @param $domain
     * @return bool
     */
    public static function hasChinese($domain)
    {
        $len = strlen($domain);
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($domain, $i, 1)) > 128)
                return true;
        }
        return false;
    }

    /**
     * punycode编码
     * @param $domain
     * @return string
     */
    public static function punycodeEncode($domain) {
        if (!self::hasChinese($domain)) {
            return $domain;
        }
        return Punycode::encode($domain);
    }

    /**
     * punycode解码
     * @param $punycode
     * @return string
     */
    public static function punycodeDecode($punycode) {
        return Punycode::decode($punycode);
    }

    /**
     * 简单用正则判断是否正确格式的域名
     * @param $domain
     * @return bool
     */
    public static function isRegularDomain($domain)
    {
        if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})*\.[a-zA-Z0-9-]{2,}$/', $domain)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 生成域名复杂密码
     *  @param int $pwlength 密码的长度
     *  @param boolean $IncludeSpecialChar 密码是否包含特殊字符
     *  @param array $SpecialCharArr 自由设定可包含的特殊字符
     * @return string
     */
    public static function generateDomainPassword($pwlength = 6, $IncludeSpecialChar = true, $SpecialCharArr = ['~', '!', '@', '#', '$', '%', '^', '*', '(', ')', '_', '|'])
    {
        $minSecureRandomPWLen = 6;
        $RandomPWChrList = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        //用来存储随机生成的抽取的分类字符数组--将每种字符预先生成一个
        $arrUpperChar = [$RandomPWChrList[mt_rand(0, 25)]];
        $arrLowerChar = [$RandomPWChrList[mt_rand(26, 51)]];
        $arrNumberChar = [$RandomPWChrList[mt_rand(52, 61)]];

        //设置生成密码范围是否包含特殊字符区域
        if ($IncludeSpecialChar) {
            $RandomPWChrList = array_merge($RandomPWChrList, $SpecialCharArr); //生成字符内容添加特殊字符集
            $arrSpecialChar = [$RandomPWChrList[mt_rand(62, count($RandomPWChrList) - 1)]];
            $startPos = 4;

        } else {
            $arrSpecialChar = [];
            $startPos = 3;
        }

        //最小长度不能小于6位
        if ($pwlength < $minSecureRandomPWLen) {
            $pwlength = $minSecureRandomPWLen;
        }

        for ($i = $startPos; $i < $pwlength; $i++) {
            $RandomIndex = mt_rand(0, count($RandomPWChrList) - 1);
            if ($RandomIndex < 26) {
                $arrUpperChar[] = $RandomPWChrList[$RandomIndex];
            } else if ($RandomIndex < 52) {
                $arrLowerChar[] = $RandomPWChrList[$RandomIndex];
            } else if ($RandomIndex < 62) {
                $arrNumberChar[] = $RandomPWChrList[$RandomIndex];
            } else {
                $arrSpecialChar[] = $RandomPWChrList[$RandomIndex];
            }
        }

        $pwArray = array_merge($arrLowerChar, $arrSpecialChar, $arrNumberChar, $arrUpperChar);
        shuffle($pwArray);
        return implode("", $pwArray);
    }

    /**
     * 获取域名后缀
     * @param string $domain
     * @return string
     */
    public static function domainSuffix($domain = null)
    {
        if (is_string($domain)) {
            return (($pos = strpos($domain, '.')) && $pos !== false) ? substr($domain, $pos + 1) : '';
        }
        return '';
    }

    /**
     * 获取域名Tld
     * @param string $domain
     * @return string
     */
    public static function getDomainTld($domain = null)
    {
        if (is_string($domain)) {
            $parts = explode('.', $domain);
            $count = count($parts);
            return $count > 0 ? $parts[$count - 1] : '';
        }
        return '';
    }

    /**
     * 检查域名是否符合单独调价优惠格式（规则是字母、数字、有两种以上，长度为7位以上的）
     * @param $domainName
     * @param $format [长度,类型数量,类型]
     * @return bool
     */
    public static function isDomainAdjustmentPriceRules($domainName,$format=[7,2,'']){
        $dmPart = explode('.', $domainName);
        //汉字，英文a-z，A-Z，数字0-9和-等均算一个字符
        $dmPartCount = count($dmPart);
        $domain = '';
        if($dmPartCount == 2){
            $domain = trim($dmPart[0]);
        }else if($dmPartCount>2){
            $domain = trim($dmPart[$dmPartCount-3]);
        }
        if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $domain)>0) {
            $len = mb_strlen($domain);
        }else{
            $len = strlen($domain);
        }
        if($len>=$format[0]){
            $typeCount = 0;
            if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $domain)>0) {
                $typeCount++;
            }
            if(preg_match('/\d+/',$domain)) {
                $typeCount++;
            }
            if (preg_match('/[a-zA-Z]/',$domain)){
                $typeCount++;
            }

            if(is_numeric($format[1]) && $format[1]>1) {
                if ($typeCount >= $format[1]) {
                    return true;
                }
            }else if($format[1]<2 && $format[2]=='c'){
                //指定位数的字符类型，这种不包括指定位数的纯数字，
                if(is_numeric($domain) && $len==$format[0]){
                    return false;
                }else{
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 获取IP
     * @return array|false|mixed|string
     */
    public static function get_real_ip(){
        $real_ip="";
        $temp_user_headers=getallheaders();
        if(isset($_SERVER["HTTP_CDN_SRC_IP"])){
            $real_ip=$_SERVER["HTTP_CDN_SRC_IP"];
        }else if(isset($temp_user_headers["Cdn-Real-Ip"])){
            $real_ip=$temp_user_headers["Cdn-Real-Ip"];
        }else if(isset($temp_user_headers["Client-ip"])){
            $real_ip=$temp_user_headers["Client-ip"];
        }else if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $real_ip=getenv('HTTP_CLIENT_IP');
        }else if(isset($temp_user_headers["X-Forwarded-For"])){
            $ip=$temp_user_headers["X-Forwarded-For"];
            $ips=explode (',',$ip);
            for ($i=0; $i < count($ips); $i++){
                if(!preg_match("/^10.|^172.16.|^192.168./i",trim($ips[$i]))){
                    $real_ip=trim($ips[$i]);
                    break;
                }
            }
        }else if(getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')){
            $ip=getenv("HTTP_X_FORWARDED_FOR");
            $ips=explode (',',$ip);
            for ($i=0; $i < count($ips); $i++){
                if(!preg_match("/^10.|^172.16.|^192.168./i",trim($ips[$i]))){
                    $real_ip=trim($ips[$i]);
                    break;
                }
            }
        }else if(getenv("REMOTE_ADDR")){
            $real_ip=getenv("REMOTE_ADDR");
        }
        return $real_ip;
    }

}