WordPressのアップロードされた画像を縮小するプラグイン
----------

サムネイル画像など、wp-content/uploads 以下のファイルのURLを渡すと、サイズを変更（主に縮小？）した画像を生成して、その画像へのURLを返します。

# 使い方

```php
$url = 'http://example.com/wp-content/uploads/2015/05/foo.png';
$suffix = '_auto_';
$width = 80;
$height = 80;

$ret = hykwEasyImageCompress($url, $suffix, $width, $height, TRUE);
# → 80x80の画像を生成し、 'http://example.com/wp-content/uploads/2015/05/foo_auto_.png'; が返ります
# 画像生成に失敗した場合、$url に指定した値がそのまま返ります

$retValueOnError = '12345';
$ret = hykwEasyImageCompress($url, $suffix, $width, $height, $retValueOnError);
# → 画像生成に失敗した場合、'12345'が返ります

```

何らかのエラー（画像生成に失敗、指定URLに対応する画像ファイルが無い、など）が発生した場合、$retValueOnError に TRUE を指定した場合は $url の値をそのまま返します（TRUE 以外の値を指定した場合はその値を返す）
