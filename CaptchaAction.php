<?php
/**
 * @link https://github.com/softark/yii2-mb-captcha
 * @copyright Copyright (c) 2013 - 2015 Nobuo Kihara
 * @license https://github.com/softark/yii2-mb-captcha/blob/master/LICENSE
 * @author Nobuo Kihara <softark@gmail.com>
 */

namespace softark\mbcaptcha;

use Yii;
use yii\web\Response;
use yii\helpers\Url;

/**
 * softark\mbcaptcha\CaptchaAction is an extension to [[yii\captcha\CaptchaAction]].
 *
 * While [[yii\captcha\CaptchaAction]] renders a CAPTCHA image only with English alphabets,
 * softark\mbcaptcha\CaptchaAction can render it with multibyte characters (Japanese Hirakana
 * by default, but you may use any multibyte characters by providing the appropriate font).
 *
 * softark\mbcaptcha\CaptchaAction must be used together with softark\mbcaptcha\Captcha
 * and [[yii\validators\CaptchaValidator]] to provide its feature.
 */
class CaptchaAction extends \yii\captcha\CaptchaAction
{
    /**
     * The name of the GET parameter indicating whether the CAPTCHA type (multibyte character/alphabet) should be toggled.
     */
    const TOGGLE_GET_VAR = 'toggle';

    /**
     * @var integer the minimum length for randomly generated word. Defaults to 5.
     */
    public $mbMinLength = 5;

    /**
     * @var integer the maximum length for randomly generated word. Defaults to 5.
     */
    public $mbMaxLength = 5;

    /**
     * @var integer the offset between characters. Defaults to 2. You can adjust this property
     * in order to decrease or increase the readability of the non alphabetical captcha.
     **/
    public $mbOffset = 2;

    /**
     * @var boolean whether to use multibyte characters. Defaults to true.
     */
    public $useMbChars = true;

    /**
     * @var string multibyte font file. Defaults to seto-mini.ttf, a subset of
     * setofont.ttf (http://setofont.sourceforge.jp/) created and shared
     * by Nozomi Seto.. Special thanks to Nozomi Seto for the wonderful font.
     * Note that seto-mini.ttf supports only ASCII, Hirakana and Katakana.
     */
    public $mbFontFile;

    /**
     * @var boolean whether to render the captcha image with a fixed angle. Defaults to false.
     * You may want to set this to true if you have trouble rendering your font.
     */
    public $fixedAngle = false;

    /**
     * @var string The string used for generating the random string of captcha.
     * Defaults to a series of Japanese Hirakana characters. You may want to set your own.
     */
    public $seeds;

    /**
     * Runs the action.
     */
    public function run()
    {
        // Character type ... defaults to non alphabetical characters
        $session = Yii::$app->session;
        $session->open();
        $name = $this->getSessionKey();
        if ($session[$name . 'type'] !== null && $session[$name . 'type'] === 'abc') {
            $this->useMbChars = false;
        }

        $refresh = Yii::$app->request->getQueryParam(self::REFRESH_GET_VAR);
        $toggle = Yii::$app->request->getQueryParam(self::TOGGLE_GET_VAR);
        if ($refresh !== null || $toggle !== null) {
            if ($toggle !== null) {
                $this->useMbChars = !$this->useMbChars;
            }
            // AJAX request for regenerating code
            $code = $this->getVerifyCode(true);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'hash1' => $this->generateValidationHash($code),
                'hash2' => $this->generateValidationHash(strtolower($code)),
                // we add a random 'v' parameter so that FireFox can refresh the image
                // when src attribute of image tag is changed
                'url' => Url::to([$this->id, 'v' => uniqid()]),
            ];
        } else {
            $this->setHttpHeaders();
            Yii::$app->response->format = Response::FORMAT_RAW;
            return $this->renderImage($this->getVerifyCode());
        }
    }

    /**
     * Generates a hash code that can be used for client side validation.
     * @param string $code the CAPTCHA code
     * @return int a hash code generated from the CAPTCHA code
     */
    public function generateValidationHash($code)
    {
        $hash = 0;
        for ($i = mb_strlen($code) - 1; $i >= 0; --$i) {
            $char = mb_substr($code, $i, 1, 'UTF-8');
            $char = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
            $hash += (int)hexdec(bin2hex($char)) << $i;
        }
        return $hash;
    }

    /**
     * Gets the verification code.
     * @param boolean $regenerate whether the verification code should be regenerated.
     * @return string the verification code.
     */
    public function getVerifyCode($regenerate = false)
    {
        if ($this->fixedVerifyCode !== null) {
            return $this->fixedVerifyCode;
        }

        $session = Yii::$app->getSession();
        $session->open();
        $name = $this->getSessionKey();
        if ($session[$name] === null || $regenerate) {
            $session[$name] = $this->generateVerifyCode();
            $session[$name . 'type'] = $this->useMbChars ? 'mb' : 'abc';
            $session[$name . 'count'] = 1;
        }
        return $session[$name];
    }

    /**
     * Generates a new verification code.
     * @return string the generated verification code
     */
    protected function generateVerifyCode()
    {
        // alphabets ?
        if (!$this->useMbChars) {
            return parent::generateVerifyCode();
        }

        $seeds = $this->getCaptchaSeeds();
        $len = mb_strlen($seeds, 'UTF-8');

        for ($code = '', $i = 0; $i < $this->getCaptchaLength(); ++$i) {
            $code .= mb_substr($seeds, mt_rand(0, $len - 1), 1, 'UTF-8');
        }

        return $code;
    }

    /**
     * @return int length of the CAPTCHA string
     */
    private function getCaptchaLength()
    {
        if ($this->mbMinLength < 3) {
            $this->mbMinLength = 3;
        }
        if ($this->mbMaxLength > 20) {
            $this->mbMaxLength = 20;
        }
        if ($this->mbMinLength >= $this->mbMaxLength) {
            return $this->mbMaxLength;
        } else {
            return mt_rand($this->mbMinLength, $this->mbMaxLength);
        }
    }

    /**
     * @return string the seeds of the CAPTCHA string
     */
    private function getCaptchaSeeds()
    {
        if ($this->seeds !== null) {
            return $this->seeds;
        } else {
            return 'あいうえおかきくけこがぎぐげごさしすせそざじずぜぞたちつてとだぢづでどなにぬねのはひふへほはひふへほはひふへほばびぶべぼぱぴぷぺぽまみむめもやゆよらりるれろわをん';
        }
    }

    /**
     * Renders the CAPTCHA image.
     * @param string $code the verification code
     * @return string image contents
     */
    protected function renderImage($code)
    {
        // alphabets ?
        if (!$this->useMbChars) {
            return parent::renderImage($code);
        }

        // font defaults to seto-mini.ttf
        if ($this->mbFontFile === null) {
            $this->mbFontFile = __DIR__ . DIRECTORY_SEPARATOR . 'seto-mini.ttf';
        }

        $encoding = 'UTF-8';

        if (Captcha::checkRequirements() === 'gd') {
            // check if conversion to Shift_JIS is needed
            $gd_info = gd_info();
            if ($gd_info['JIS-mapped Japanese Font Support']) {
                $code = mb_convert_encoding($code, 'SJIS', 'UTF-8');
                $encoding = 'SJIS';
            }
            return $this->mbRenderImageByGD($code, $encoding);
        } else {
            return $this->mbRenderImageByImagick($code, $encoding);
        }
    }

    /**
     * Renders the CAPTCHA image based on the code using GD library.
     * @param string $code the verification code
     * @param string $encoding character encoding
     * @return string image contents in PNG format.
     */
    protected function mbRenderImageByGD($code, $encoding)
    {
        $image = imagecreatetruecolor($this->width, $this->height);

        $backColor = imagecolorallocate($image,
            (int)($this->backColor % 0x1000000 / 0x10000),
            (int)($this->backColor % 0x10000 / 0x100),
            $this->backColor % 0x100);
        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $backColor);
        imagecolordeallocate($image, $backColor);

        if ($this->transparent) {
            imagecolortransparent($image, $backColor);
        }

        $foreColor = imagecolorallocate($image,
            (int)($this->foreColor % 0x1000000 / 0x10000),
            (int)($this->foreColor % 0x10000 / 0x100),
            $this->foreColor % 0x100);

        $length = mb_strlen($code, $encoding);
        $box = imagettfbbox(30, 0, $this->mbFontFile, $code);
        $w = $box[4] - $box[0] + $this->mbOffset * ($length - 1);
        $h = $box[1] - $box[5];
        if ($h <= 0) {
            $h = $w / $length;
        }
        $scale = min(($this->width - $this->padding * 2) / $w, ($this->height - $this->padding * 2) / $h);
        $x = 8;
        // font size and angle
        $fontSize = (int)(30 * $scale * 0.90);
        $angle = 0;
        // base line
        $yBottom = $this->height - $this->padding * 4;
        $yTop = (int)($h * $scale * 0.95) + $this->padding * 4;
        if ($yTop > $yBottom) {
            $yTop = $yBottom;
        }
        for ($i = 0; $i < $length; ++$i) {
            $letter = mb_substr($code, $i, 1, $encoding);
            $y = mt_rand($yTop, $yBottom);
            if (!$this->fixedAngle) {
                $angle = mt_rand(-15, 15);
            }
            $box = imagettftext($image, $fontSize, $angle, $x, $y, $foreColor, $this->mbFontFile, $letter);
            $x = $box[2] + $this->mbOffset;
        }

        imagecolordeallocate($image, $foreColor);

        ob_start();
        imagepng($image);
        imagedestroy($image);
        return ob_get_clean();
    }

    /**
     * Renders the CAPTCHA image based on the code using ImageMagick library.
     * @param string $code the verification code
     * @param string $encoding character encoding
     * @return string image contents in PNG format.
     */
    protected function mbRenderImageByImagick($code, $encoding)
    {
        $backColor = $this->transparent ? new \ImagickPixel('transparent') : new \ImagickPixel(sprintf('#%06x', $this->backColor));
        $foreColor = new \ImagickPixel(sprintf('#%06x', $this->foreColor));

        $image = new \Imagick();
        $image->newImage($this->width, $this->height, $backColor);

        $draw = new \ImagickDraw();
        $draw->setFont($this->mbFontFile);
        $draw->setFontSize(30);
        $fontMetrics = $image->queryFontMetrics($draw, $code);

        $length = mb_strlen($code, $encoding);
        $w = (int)($fontMetrics['textWidth']) + $this->mbOffset * ($length - 1);
        $h = (int)($fontMetrics['textHeight']);
        $scale = min(($this->width - $this->padding * 2) / $w, ($this->height - $this->padding * 2) / $h);
        $x = 8;
        // font size and angle
        $fontSize = (int)(30 * $scale * 0.90);
        $angle = 0;
        // base line
        $yBottom = $this->height - $this->padding * 4;
        $yTop = (int)($h * $scale * 0.95) + $this->padding * 4;
        if ($yTop > $yBottom) {
            $yTop = $yBottom;
        }
        for ($i = 0; $i < $length; ++$i) {
            $letter = mb_substr($code, $i, 1, $encoding);
            $y = mt_rand($yTop, $yBottom);
            if (!$this->fixedAngle) {
                $angle = mt_rand(-15, 15);
            }
            $draw = new \ImagickDraw();
            $draw->setFont($this->mbFontFile);
            $draw->setFontSize($fontSize);
            $draw->setFillColor($foreColor);
            $image->annotateImage($draw, $x, $y, $angle, $letter);
            $fontMetrics = $image->queryFontMetrics($draw, $letter);
            $x += (int)($fontMetrics['textWidth']) + $this->mbOffset;
        }

        $image->setImageFormat('png');
        return $image->getImageBlob();
    }
}
