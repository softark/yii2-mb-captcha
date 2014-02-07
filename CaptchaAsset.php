<?php
/**
 * @link https://github.com/softark/yii2-mb-captcha
 * @copyright Copyright (c) 2013, 2014 softark.net
 * @license https://github.com/softark/yii2-mb-captcha/blob/master/LICENSE
 * @author Nobuo Kihara <softark@gmail.com>
 */

namespace softark\mbcaptcha;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files needed for the softark\mbcaptcha\Captcha widget.
 */
class CaptchaAsset extends AssetBundle
{
	public $sourcePath = '@vendor/softark/yii2-mb-captcha/assets';
	public $js = [
		'yii.mb-captcha.js',
	];
	public $depends = [
		'yii\web\YiiAsset',
	];
}
