<?php
/**
 * @link https://github.com/softark/yii2-mb-captcha
 * @copyright Copyright (c) 2013, 2014 softark.net
 * @license https://github.com/softark/yii2-mb-captcha/blob/master/LICENSE
 * @author Nobuo Kihara <softark@gmail.com>
 */

namespace softark\mbcaptcha;

use yii\helpers\Html;
use yii\helpers\Json;
use Yii;

/**
 * softark\mbcaptcha\Captcha is an extension to [[yii\captcha\Captcha]].
 *
 * While [[yii\captcha\Captcha]] renders a CAPTCHA image only with English alphabets,
 * softark\mbcaptcha\Captcha can render it with multi-byte characters (Japanese Hirakana
 * by default, but you may use any multi-byte characters by providing the appropriate font).
 *
 * Optionally softark\mbcaptcha\Captcha may render a link next to the CAPTCHA image.
 * Clicking on it will toggle the CAPTCHA type from the multi-byte character to English
 * alphabet, and vice versa.
 *
 * softark\mbcaptcha\Captcha must be used together with softark\mbcaptcha\CaptchaAction
 * and [[yii\validators\CaptchaValidator]] to provide its feature.
 */
class Captcha extends \yii\captcha\Captcha
{
	/**
	 * @var string the template for arranging the CAPTCHA image tag,
	 * the text input tag and the type toggling link tag.
	 * In this template, the token `{image}` will be replaced with the actual image tag, while `{input}` will be
	 * replaced with the text input tag and `{link}` will be replaced with the the type toggling link tag.
	 * Note that `{link}` must be a sibling of `{image}` in the DOM tree, otherwise the toggling link won't work.
	 * You may omit `{link}` token if you don't want the type toggling link tag.
	 */
	public $template = '{image} {link} {input}';

	/**
	 * @var string the label of the type toggle link.
	 * Defaults to "かな/abc" ("Japanese Hirakana/lower-case alphabet").
	 */
	public $toggleLinkLabel = 'かな/abc';

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		$this->registerClientScript();
		if ($this->hasModel()) {
			$input = Html::activeTextInput($this->model, $this->attribute, $this->options);
		} else {
			$input = Html::textInput($this->name, $this->value, $this->options);
		}
		$url = Yii::$app->getUrlManager()->createUrl($this->captchaAction, ['v' => uniqid()]);
		$image = Html::img($url, $this->imageOptions);
		$link = Html::a($this->toggleLinkLabel, '#');
		echo strtr($this->template, [
			'{input}' => $input,
			'{image}' => $image,
			'{link}' => $link,
		]);
	}

	/**
	 * Registers the needed JavaScript.
	 */
	public function registerClientScript()
	{
		$options = $this->getClientOptions();
		$options = empty($options) ? '' : Json::encode($options);
		$id = $this->imageOptions['id'];
		$view = $this->getView();
		CaptchaAsset::register($view);
		$view->registerJs("jQuery('#$id').yiiMbCaptcha($options);");
	}

	/**
	 * Returns the options for the captcha JS widget.
	 * @return array the options
	 */
	protected function getClientOptions()
	{
		$options = [
			'refreshUrl' => Html::url(['/' . $this->captchaAction, CaptchaAction::REFRESH_GET_VAR => 1]),
			'toggleUrl' => Html::url(['/' . $this->captchaAction, CaptchaAction::TOGGLE_GET_VAR => 1]),
			'hashKey' => "yiiCaptcha/{$this->captchaAction}",
			// We have to use 'yiiCaptcha' instead of 'yiiMbCaptcha'
			// because yii\captcha\Captcha uses 'yiiCaptcha' for 'hashKey'.
		];
		return $options;
	}
}
