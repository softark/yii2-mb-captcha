/**
 * Yii Multibyte Captcha widget.
 *
 * This is the JavaScript used by the softark\mbcaptcha\Captcha widget, which is
 * an extension to yii\captcha\Captcha.
 *
 * @link https://github.com/softark/yii2-mb-captcha
 * @copyright Copyright (c) 2013, 2014 softark.net
 * @license https://github.com/softark/yii2-mb-captcha/blob/master/LICENSE
 * @author Nobuo Kihara <softark@gmail.com>
 */
(function ($) {
	$.fn.yiiMbCaptcha = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.yiiMbCaptcha');
			return false;
		}
	};

	var defaults = {
		refreshUrl: undefined,
		toggleUrl: undefined,
		hashKey: undefined
	};

	var methods = {
		init: function (options) {
			return this.each(function () {
				var $e = $(this);
				var settings = $.extend({}, defaults, options || {});
				$e.data('yiiMbCaptcha', {
					settings: settings
				});

				$e.on('click.yiiMbCaptcha', function () {
					methods.refresh.apply($e);
					return false;
				});

				var $b = $e.siblings('a');
				$b.on('click.yiiMbCaptcha', function () {
					methods.typeChange.apply($e);
					return false;
				});

			});
		},

		refresh: function () {
			var $e = this,
				settings = this.data('yiiMbCaptcha').settings;
			$.ajax({
				url: $e.data('yiiMbCaptcha').settings.refreshUrl,
				dataType: 'json',
				cache: false,
				success: function (data) {
					$e.attr('src', data.url);
					$('body').data(settings.hashKey, [data.hash1, data.hash2]);
				}
			});
		},

		typeChange: function () {
			var $e = this,
				settings = this.data('yiiMbCaptcha').settings;
			$.ajax({
				url: $e.data('yiiMbCaptcha').settings.toggleUrl,
				dataType: 'json',
				cache: false,
				success: function (data) {
					$e.attr('src', data.url);
					$('body').data(settings.hashKey, [data.hash1, data.hash2]);
				}
			});
		},

		destroy: function () {
			return this.each(function () {
				$(window).unbind('.yiiMbCaptcha');
				$(this).removeData('yiiMbCaptcha');
			});
		},

		data: function () {
			return this.data('yiiMbCaptcha');
		}
	};
})(window.jQuery);

