<?php

class Punycode
{
    const TMIN=1;
    const TMAX=26;
    const BASE = 36;
    const INITIAL_N = 128;
    const INITIAL_BIAS = 72;
    const DAMP = 700;
    const SKEW = 38;
    const DELIMITER = '-';
    const MAXINT = 2147483647;

    //encode编码时候的错误返回值
    const ERROR_BAD_INPUT  = -1;
    const ERROR_BIG_OUTPUT = -2;
    const ERROR_OVERFLOW   = -3;

    /**
     * 配置
     * 1: 使用iconv
     * 2: iconv Unicode-1-1
     * 3: 使用mb_convert_encoding(较通用，需mb_string支持)
     */
    const CHARSET_MODE = 3;


    /**
     * punycode编码
     * @param string $input
     *      输入的字符传不能有空格.
     *      以.分割几段来进行处理
     *      对中文全角字母不区分大小写
     *      中文字符不能包含@#$%之类的字符
     * @param string $code 字符编码
     */
    public static function encode($input, $code="UTF-8")
    {
        $input=trim($input);
        //由于包括CNNIC在内的中国技术人员的倡导和推动，此次发布的国际标准在英文句号"."的基础上，另外增加了中文句号作为多语种域名各级之间的分隔符。
        //这主要是为了满足中文域名的独特需求，省去输入中文域名时中英文之间切换的麻烦。
        $input=str_replace("。",".",$input);
        if (!self::hasChinese($input)) return $input;
        //$input = stripslashes($input);

        $strarr=array();
        $strarr=explode(".",$input);
        $output="";
        for ($i=0;$i<count($strarr);$i++){
            $tmp_output = self::encodeHandle($strarr[$i], $code);
            if(!$tmp_output || $tmp_output<0)	return;

            if ($i!=count($strarr)-1) $tmp_output.=".";
            $output.=$tmp_output;
        }
        return $output;
    }

    /**
     * 判断域名是否包含中文字符
     * @param string $domain
     * @return bool
     */
    public static function hasChinese($domain)
    {
        $len=strlen($domain);
        for($i=0;$i<$len;$i++){
            if(ord(substr($domain,$i,1))>128)
                return true;
        }
        return false;
    }

    /**
     * unicode 编码
     * @param string $str
     * @return string $newstr
     */
    public static function unicodeEncode($str)
    {
        //$newstr = chr(255).chr(254);
        $newstr = '';
        for ($i=0; $i<strlen($str); $i+=2) {
            $newstr .= chr(ord(substr($str, $i+1, 1))).chr(ord(substr($str, $i, 1)));
        }
        return $newstr;
    }

    /**
     * 判断数字是否是ansi字符,禁止 @#$%^ 之类的符号
     * @param string $char
     * @return bool
     */
    public static function isBasic($char)
    {
        return $char<128;
        //return $aa=(($c==45) || ($c>=48 && $c<=57) || ($c>=65 && $c<=90) || ($c>=97 && $c<=122));
    }

    /**
     * 处理数字,大小写字母
     * @param string $bcp
     * @param string $flag
     */
    public static function basicEncode($bcp, $flag)
    {
        //if ($bcp-97<26) $bcp-=32;
        $bcp-=($bcp-97<26)<<5;
        return $bcp+((!$flag && ($bcp-65<26))<<5);
    }

    /**
     * 处理数字,大小写字母
     * @param char $d
     * @param char $flag
     * @return char
     */
    public static function digitEncode($d,$flag)
    {
        $s1=(int)($d<26);//0或1
        $s2=(int)($flag!=0);
        return $d+22+75*$s1-($s2<<5);
    }


    /**
     * 编码的参数
     */
    public static function adapt($delta,$numpoints,$firsttime)
    {
        $delta= $firsttime ? (int)($delta/self::DAMP) : (int)($delta/2);

        $delta+=(int)($delta/$numpoints);

        for ($k=0; $delta>(int)(((self::BASE-self::TMIN)*self::TMAX)/2); $k+=self::BASE){
            $delta = (int)($delta/(self::BASE-self::TMIN));
        }
        return $k+(int)(((self::BASE-self::TMIN+1)*$delta)/($delta+self::SKEW));
    }


    //主要的编码转换工作
    public static function encodeHandle($input, $code="UTF-8")
    {
        $oldinput=$input;
        //将输入字符转换为Unicode.cnnic用GBK,GBK是GB2312的扩展.取代GBK的是GB18030
        if (self::CHARSET_MODE == 1) {
            $input = iconv($code, "Unicode", $input);
        } elseif (self::CHARSET_MODE == 2) {
            $input = iconv($code, "Unicode-1-1", $input);
            $input = self::unicodeEncode($input);
        } elseif (self::CHARSET_MODE == 3) {
            $input = mb_convert_encoding($input, "Unicode", $code);
            $input = self::unicodeEncode($input);
        }

        //去掉前面的255和254
        if (ord(substr($input, 0)) == 255 && ord(substr($input, 1)) == 254)
            $input = substr($input, 2);

        $n        = self::INITIAL_N;
        $delta    = 0;
        $out      = 0;
        $max_out  = 256;
        $bias     = self::INITIAL_BIAS;
        $output   = "";
        $inputlen = strlen($input);
        $ar       = array();  //用一个数组来保存字的编码
        for ($i=0; $i<$inputlen; $i+=2){
            $tmpar = ord($input{$i+1}) * 256 + ord($input{$i});
            //32为空格的Unicode,空格后不作处理(参考cnnic).输入是最好限制输入不能包含空格
            if ($tmpar == 32)
                break;
            $ar[]=$tmpar;
        }
        $inputlen = count($ar);
        //将大写全角字符转为小写
        for ($i=0; $i<$inputlen; $i++) {
            if ($ar[$i] >= 65313 && $ar[$i] <= 65338)
                $ar[$i] = $ar[$i]+32;
        }
        //$arr=array();
        $case_flags = '';
        for ($j=0; $j<$inputlen; $j++){
            if (self::isBasic($ar[$j])){
                if ($max_out-$out<2)
                    return self::ERROR_BIG_OUTPUT;
                //$arr[]=$case_flags ? punycode_encode_basic($input{$j}, $case_flags{$j}) : ord($input{$j});
                $output.=$case_flags ? chr(self::basicEncode($ar[$j], $case_flags{$j})) : chr($ar[$j]);//不考虑大小写
                $out++;
            }
        }

        $h=$b=$out;
        if ($b>0) {
            $output .= self::DELIMITER;
            $out++;
        }

        while ($h<$inputlen){
            for ($m=self::MAXINT,$j=0;$j<$inputlen;$j++){
                if ($ar[$j]>=$n && $ar[$j]<$m) $m=$ar[$j];
            }

            if ($m-$n>(self::MAXINT-$delta)/($h+1)) return self::ERROR_OVERFLOW;
            $delta+=($m-$n)*($h+1);
            $n=$m;

            for ($j=0;$j<$inputlen;$j++){
                if ($ar[$j]<$n){
                    if (++$delta==0) return self::ERROR_OVERFLOW;
                }

                if ($ar[$j]==$n){
                    $q=$delta;
                    for ($k=self::BASE; ;$k+=self::BASE){
                        if ($out>=$max_out) return self::ERROR_BIG_OUTPUT;
                        $t=$k<=$bias ? self::TMIN:($k>=($bias+self::TMAX) ? self::TMAX:($k-$bias));
                        if ($q<$t) break;
                        //$arr[]=punycode_encode_digit($t+($q-$t)%(punycode_BASE-$t),0);
                        $output.=chr(self::digitEncode($t+($q-$t)%(self::BASE-$t),0));
                        $out++;
                        $q=(int)(($q-$t)/(self::BASE-$t));
                    }

                    //$arr[]=punycode_encode_digit($q,$case_flags && $case_flags{$j});
                    //$output.=chr(punycode_encode_digit($q,$case_flags && $case_flags{$j}));
                    $output.=chr(self::digitEncode($q,0));//忽略大小写（0输出小写字母,1输出大写字母）
                    $out++;
                    $bias=self::adapt($delta, $h+1, $h==$b);
                    $delta=0;
                    $h++;
                }
            }
            $delta++;
            $n++;
        }
        if (substr($output,-1,1)==self::DELIMITER) $output=substr($output,0,-1);
        if ($output!=$oldinput) $output="xn--".$output;
        return $output;
    }


//=============================================

    /**
     * punycode 解码
     * @param string $input
     * @param string $code
     * @return string
     */
    public static function decode($input, $code="UTF-8")
    {
        $input=trim($input);
        if (self::hasChinese($input))
            return $input;

        $strarr=array();
        $strarr=explode(".",$input);
        $output="";
        for ($i=0;$i<count($strarr);$i++){
            if (substr($strarr[$i],0,4)=="xn--"){
                $input=substr($strarr[$i],4);
                $outtmp=self::decodeHandle($input, $code);
                if (!$outtmp || $outtmp<0)  return;
                $output.=$outtmp;
            }else{
                $output.=$strarr[$i];
            }
            if ($i!=count($strarr)-1) $output.=".";
        }//for
        return $output;
    }

    /**
     * 主要的解码转换工作
     * @param string $input
     * @param string $code
     * @return string
     */
    public static function decodeHandle($input, $code="UTF-8")
    {
        $n        = self::INITIAL_N;
        $out      = 0;
        $i        = 0;
        $max_out  = 256;
        $bias     = self::INITIAL_BIAS;
        $inputlen = strlen($input);
        $outputa  = array();

        $b = 0;
        for ($j=0;$j<$inputlen;$j++)
            if ($input{$j}=="-")
                $b=$j;

        for ($j=0; $j<$b; $j++) {
            /*不考虑大小写
            if (ord($input{$j})-65<26)
                $case_flags[$out]="1";
            else
                $case_flags[$out]="0";
            */
            if (ord($input{$j})>128) return -1;
            //$output.=$input{$j};
            $outputa[]=ord($input{$j});
            $out++;
        }

        for ($in=$b>0?$b+1:0;$in<$inputlen;$out++) {
            $oldi=$i;
            $w=1;
            for ($k=self::BASE; ;$k+=self::BASE){
                if ($in>=$inputlen)
                    return -2;
                $digit = self::digitDecode(ord($input{$in++}));
                if ($digit>=self::BASE) return -3;
                if ($digit>(self::MAXINT-$i)/$w) return -4;
                $i=$i+$digit*$w;
                $t=$k<=$bias ? self::TMIN : ($k>=($bias+self::TMAX) ? self::TMAX:($k-$bias));
                if ($digit<$t) break;
                if ($w>self::MAXINT/(self::BASE-$t)) return -5;
                $w=$w*(self::BASE-$t);
            }

            $bias = self::adapt($i-$oldi,$out+1,$oldi==0);
            if ($i/($out+1)>self::MAXINT-$n)
                return -6;
            $n+=(int)($i/($out+1));
            $i=$i%($out+1);

            if ($out>=$max_out) return -7;

            /*不考虑大小写
                    for ($q=0;$q<$out-$i;$i++) $case_flags[$i+1+$q]= $case_flags[$i+$q];

                    if ($input[$in-1]-65<26)
                        $case_flags[$i]='1';
                    else
                        $case_flags[$i]='0';
            */
            for ($qq=0;$qq<($out-$i);$qq++) $outputa[($i+$out)-$i-$qq]=$outputa[($i+$out)-$i-$qq-1];

            $outputa[$i++]=$n;
        }

        $outputstr="";
        for ($i=0;$i<count($outputa);$i++){
            if ($outputa[$i]<128)
                $outputstr.=chr($outputa[$i]).chr(0);
            else{
                $hx=dechex($outputa[$i]);
                $gaowei=substr($hx,2,2);
                $diwei=substr($hx,0,2);
                $tmp_output=chr(hexdec($gaowei)).chr(hexdec($diwei));
                $outputstr.=$tmp_output;
            }
        }
        //echo $outputstr." <br>";
        //for ($i=0; $i<strlen($outputstr); $i++) echo ord(substr($outputstr,$i,1))." ";
        if (self::CHARSET_MODE == 1) {
            $outputstr = iconv("Unicode", $code, $outputstr);
        } elseif (self::CHARSET_MODE == 2) {
            $outputstr = self::unicodeEncode($outputstr);
            $outputstr = iconv("Unicode-1-1", $code, $outputstr);
        } elseif (self::CHARSET_MODE == 3) {
            $outputstr = self::unicodeEncode($outputstr);
            $outputstr = mb_convert_encoding($outputstr, $code, "Unicode");
        }
        return $outputstr;
    }

    /**
     * 处理数字,大小写字母
     * @param char $char
     * @return char
     */
    public static function digitDecode($char)
    {
        if (($char-48)<10) return ($char-22);
        if (($char-65)<26) return ($char-65);
        if (($char-97)<26) return ($char-97);
        return self::BASE;
    }
}