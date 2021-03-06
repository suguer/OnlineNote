<?php
/**
 * 使用前 composer require endroid/qr-code
 * User: staff
 * Date: 2018/7/13
 * Time: 19:32
 */


use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

class LogoQrcode
{
    private $logopath ="logo图片的绝对路径";

    public function createLogoQrcode()
    {
        $url = request()->get("url");
        $text = request()->get("text");
        $slogan = request()->get("slogan");
        $logo = request()->get("logo",'show');
        $qrcode = new QrCode();
        $qrcode->setText($url);
        $qrcode->setMargin(15);
        $qrcode->setSize(300);
        $qrcode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
        if ($slogan)
            $qrcode->setLabel("{$text}\n{$slogan}", 12, null, null, ['t' => 0, 'r' => 10, 'b' => 30, 'l' => 10,]);
        else
            $qrcode->setLabel($text, 14);
        if($logo=='show') {
            $qrcode->setLogoPath($this->logopath);
            $qrcode->setLogoWidth(64);
        }
        header('Content-Type: ' . $qrcode->getContentType());
        echo $qrcode->writeString();
        exit;
    }

    public function composeQrcode()
    {
        $fingerprint = "plugins/myuplus/rent/assets/images/fingerprint1.jpg";
        $house_id = request()->get("house_id");
        $house = House::find($house_id);
        $qrcode = $house['qrcode'];
        $img1 = imagecreatefromjpeg($qrcode);
        $imgsize1 = getimagesize($qrcode);
        $img2 = imagecreatefromjpeg($fingerprint);
        $imgsize2 = getimagesize($fingerprint);
        $image = imagecreatetruecolor($imgsize1[0] * 2, $imgsize1[1]);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        imagecopyresampled($image, $img1, 0, 0, 0, 0, $imgsize1[0], $imgsize1[1], $imgsize1[0], $imgsize1[1]);
        imagecopyresampled($image, $img2, $imgsize1[0], 0, 0, 0, $imgsize1[0], $imgsize1[1], $imgsize2[0], $imgsize2[1]);
        header('Content-Type: image/jpeg');
        imagejpeg($image);
        imagedestroy($img1);
        imagedestroy($img2);
        imagedestroy($image);

    }
}