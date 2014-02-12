yii2-mb-captcha
===============

アルファベット以外の文字(たとえば平仮名や漢字)を表示できる Yii framework 2.0 用の CAPTCHA です。

![Multibyte Captcha in Action](images/mb-captcha.png "Multibyte Captcha in Action")

![Multibyte Captcha using Chinese characters](images/mb-captcha-c.png "Multibyte Captcha using Chinese characters")

[README in English](README.md)

概要
---

**softark\mbcaptcha\Captcha** は **yii\captcha\Captcha** を拡張したものです。

**yii\captcha\Captcha** は**英語のアルファベット**だけで CAPTCHA 画像を表示しますが、
**softark\mbcaptcha\Captcha** は**マルチバイト文字**を表示することが出来ます ... 既定では日本語のひらがなを表示しますが、
適切なフォントを用意すればどのような文字でも表示可能です。

オプションで、CAPTCHA 画像のとなりに、CAPTCHA のタイプを切り替えるためのリンクを表示することが出来ます。
このリンクをクリックすると、CAPTCHA の文字がマルチバイト文字から英語アルファベットへ、また、英語アルファベットから
マルチバイト文字へと切り替ります。

**softark\mbcaptcha\Captcha** は、その機能を提供するために、**softark\mbcaptcha\CaptchaAction** と共に使用される必要があります。

動作条件
-------
+ Yii Version 2.0.0 以降
+ PHP GD + FreeType 拡張 または ImageMagick 拡張

使用方法
--------
1. `softark/yii2-mb-captcha` を `composer.json` に追加し、Composer でプロジェクトを構成します。

	```php
	"require": {
		"php": ">=5.4.0",
		"yiisoft/yii2": "*",
		"yiisoft/yii2-bootstrap": "*",
		"yiisoft/yii2-swiftmailer": "*",
		"softark/yii2-mb-captcha": "dev-master"
	},
	```

2. ビューで `yii\captcha\Captcha` の代りに `softark\mbcaptcha\Captcha` を使います。

	```php
	/* use yii\captcha\Captcha; */
	use softark\mbcaptcha\Captcha;
	...
	<?=
		$form->field($model, 'verifyCode')->widget(Captcha::className(), [
			'template' => '<div class="row"><div class="col-lg-3">{image}</div><div class="col-lg-6">{input}</div></div>',
		]) ?>
	```

	オプションで `{link}` トークンをテンプレートに含めたい場合があるかも知れません。
	```php
	/* use yii\captcha\Captcha; */
	use softark\mbcaptcha\Captcha;
	<?=
		$form->field($model, 'verifyCode')->widget(Captcha::className(), [
			'template' => '<div class="row"><div class="col-lg-3">{image} {link}</div><div class="col-lg-6">{input}</div></div>',
		]) ?>
	```

3. コントローラで `yii\captcha\CaptchaAction` の代りに `softark\mbcaptcha\CaptchaAction` を使います。

	```php
	public function actions()
	{
		return [
			'captcha' => [
				/* 'class' => 'yii\captcha\CaptchaAction', */
				'class' => 'softark\mbcaptcha\CaptchaAction',
				'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
			],
			...
		];
	}
	```

softark\mbcaptcha\Captcha のプロパティ
-------------------------------------
`softark\mbcaptcha\Captcha` は `yii\captcha\Captcha` のプロパティに加えて、以下のプロパティをサポートしています。
**(*)** マークを付けた項目は、変更することが多いと思われる基本的なオプションです。

1. **template (*)** @var string

	CAPTCHA ウィジェットを配置するためのテンプレート。既定値は `'{image} {link} {input}'`

	このプロパティは親クラスから継承されており、タイプ変更リンクをサポートするために拡張されている。
	このテンプレートの `{image}` は実際の画像に、`{input}` はテキスト入力フィールドに、
	そして、`{link}` はタイプ変更リンクに置き換えられる。

	`{link}` は `{image}` と DOM ツリー上で兄弟関係の要素でなければならない。そうでない場合、タイプ変更リンクは動作しない。

	タイプ変更リンクが不要な場合は、テンプレートから `{link}` トークンを省略すると良い。

2. **toggleLinkLabel (*)** @var string

	タイプ変更リンクのラベル。既定値は "かな/abc"。漢字や非日本語を使う場合は、適切に書き換えること。

softark\mbcaptcha\CaptchaAction のプロパティ
-------------------------------------------
`softark\mbcaptcha\CaptchaAction` は `yii\captcha\CaptchaAction` のプロパティに加えて、以下のプロパティをサポートしています。
**(*)** マークを付けた項目は、変更することが多いと思われる基本的なオプションです。

1. **mbFontFile (*)** @var string

	マルチバイト文字を表示するためのフォントファイル。既定値は seto-mini.ttf。

	**既定のフォントは英数字と平仮名、片仮名しかサポートしていない** ことに注意。

	漢字などを表示したい場合は、別途、適切なフォントファイルが必要になる。

2. **seeds (*)** @var string

	ランダムな単語を生成するための種文字列。

	既定値は、"あいうえおかきくけこがぎぐげごさしすせそざじずぜぞたちつてとだぢづでどなにぬねのはひふへほはひふへほはひふへほばびぶべぼぱぴぷぺぽまみむめもやゆよらりるれろわをん" という文字列。
	この中からランダムに文字を選んで CAPTCHA の単語を生成する。

	好みに合わせて変更することができる。ただし、`mbFontFile` がサポートしている文字だけを使用する必要がある。

3. mbMinLength @var integer

	ランダムに生成されるマルチバイト文字列の最小文字数。既定値は 5

4. mbMaxLength @var integer

	ランダムに生成されるマルチバイト文字列の最大文字数。既定値は 5

5. mbOffset @var integer

	マルチバイト文字間のオフセット。既定値は 2。
	このプロパティを調整して、文字の読み取りやすさを増減することができる。

6. fixedAngle @var boolean

	文字にランダムな回転を与えずに表示するか否か。既定値は false。
	動作環境や使用するフォントによっては、 true に設定する必要があるかも知れない。

7. checkSJISConversion @var boolean

	true の場合、UTF-8 から Shift_JIS への変換が必要か否かをチェックして、それに従う。
	既定値は false で、UTF-8 のまま文字を描画する。動作環境によっては、 true に設定する必要があるかも知れない。

カスタマイズ
-----------

以下は、`softark\mbcaptcha\Captcha` と `softark\mbcaptcha\CaptchaAction` をカスタマイズする方法を示すサンプルです。
ここでは中国語(簡体字)の CAPTCHA を表示します。

ビュー:

```php
use softark\mbcaptcha\Captcha;
...
<?=
	$form->field($model, 'verifyCode')->widget(Captcha::className(), [
		'template' => '<div class="row"><div class="col-lg-3">{image} {link}</div><div class="col-lg-6">{input}</div></div>',
		'toggleLinkLabel' => '漢字/abc',
	]) ?>
```

コントローラ:

```php
public function actions()
{
	return [
		'captcha' => [
			'class' => 'softark\mbcaptcha\CaptchaAction',
			'seeds' => '几乎所有的应用程序都是建立在数据库之上虽然可以非常灵活的' .
				'操作数据库但有些时候一些设计的选择可以使它更便于使用首先应用程序' .
				'广泛使用了设计的考虑主要围绕优化使用而不是组成复杂语句实际上大多' .
				'的设计是使用友好的模式来解决实践中的问题最常用的方式是创建易于被' .
				'人阅读和理解的代码例如使用命名来传达意思但是这很难做到',
			'mbFontFile' => '@frontend/fonts/gbsn00lp.ttf',
		],
		...
	];
}
```

上記のサンプルコードでは、使用するフォントファイルを frontend アプリケーションディレクトリの 'fonts' サブディレクトリに配置しているものとしています。

"seeds" に指定する文字に、使用するフォントで表示できない文字を含めないように注意して下さい。

履歴
----

+ Version 1.0.0 (2014-02-08)
	+ 最初のリリース
	+ Yii 1.1.x 用の [JCaptcha](https://github.com/softark/JCaptcha) 1.0.3. から移植

謝辞
----
[瀬戸フォント setofont.ttf](http://nonty.net/item/font/setofont.php) をシェアして下さっている[瀬戸のぞみさん](http://nonty.net/about/) に感謝の意を表します。既定のフォント "seto-mini.ttf" は、setofont.ttf のサブセットです。
