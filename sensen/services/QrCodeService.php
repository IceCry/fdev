<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/21
 * Time: 13:54
 */

namespace sensen\services;


use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

class QrCodeService
{
    /**
     * 生成二维码
     * @param string $data 数据
     * @param int $size 尺寸
     * @param string $level 容错
     * @param string $savePath 保存位置
     * @param array $fg 前景
     * @param array $bg 背景
     * @param string $logoPath 中间logo
     * @param string $logoSize logo大小
     */
    public static function createQr($data='', $savePath='', $size=200, $level='MEDIUM', $fg=['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0], $bg=['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0], $logoPath='', $logoSize=[])
    {
        $qrCode = new QrCode($data);
        $qrCode->setSize($size);
        $qrCode->setMargin(1);

        // Set advanced options
        $qrCode->setWriterByName('png');
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::MEDIUM());
        $qrCode->setForegroundColor($fg);
        $qrCode->setBackgroundColor($bg);
        //$qrCode->setLabel('Scan the code', 16, __DIR__.'/../assets/fonts/noto_sans.otf', LabelAlignment::CENTER());
        if($logoPath){
            $qrCode->setLogoPath($logoPath);
            $qrCode->setLogoSize($logoSize[0], $logoSize[1]);
        }
        $qrCode->setValidateResult(false);
        // Round block sizes to improve readability and make the blocks sharper in pixel based outputs (like png).
        // There are three approaches:
        //$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_MARGIN); // The size of the qr code is shrinked, if necessary, but the size of the final image remains unchanged due to additional margin being added (default)
        //$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_ENLARGE); // The size of the qr code and the final image is enlarged, if necessary
        //$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_SHRINK); // The size of the qr code and the final image is shrinked, if necessary

        // Set additional writer options (SvgWriter example)
        //$qrCode->setWriterOptions(['exclude_xml_declaration' => true]);

        // Save it to a file
        if($savePath){
            $qrCode->writeFile($savePath);
        }else{
            // Directly output the QR code
            header('Content-Type: '.$qrCode->getContentType());
            echo $qrCode->writeString();
        }

        // Generate a data URI to include image data inline (i.e. inside an <img> tag)
        //$dataUri = $qrCode->writeDataUri();
    }
}