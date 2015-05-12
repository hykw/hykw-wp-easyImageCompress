<?php

  /*
    Plugin Name: HYKW easy Image Compress
    Plugin URI: https://github.com/hykw/hykw-wp-easyImageCompress
    Description: 指定URLの画像を圧縮するプラグイン
    Author: Hitoshi Hayakawa
    version: 1.0.0
  */

class hykwEasyImageCompressClass
{
  function __construct($url, $suffix, $width, $height, $retValueOnError = TRUE)
  {
    $this->url = $url;
    $this->suffix = $suffix;
    $this->width = $width;
    $this->height = $height;
    $this->retValueOnError = $retValueOnError;
  }


  /**
   * convert 画像変換
   * 
   * @return string
   */
  public function convert()
  {
    ### OS上のパスの取得
    $wp_upload_dirs = wp_upload_dir();
    $wp_upload_basedir = $wp_upload_dirs['basedir'];  # /var/www/wp/wordpress/wp-content/uploads
    $wp_upload_baseurl = $wp_upload_dirs['baseurl'];  # http://example.co.jp/wp-content/uploadso

    list($upload_basedir, $upload_yearmonth, $org_name) = $this->get_thumbnail_basedirANDpath($wp_upload_basedir, $this->url);
    /* array(
        /var/www/wp/wordpress/wp-content/uploads
        2015/05
        test.png
       )
     */


    ### 生成後のファイル名の取得(foo.png -> foo【suffix】.png)
    list($base, $ext_filename) = $this->get_thumbnail_built_name($org_name, $this->suffix);
    $new_name = sprintf('%s.%s', $base, $ext_filename);


    ### 画像を生成
    $upload_basdirAndYearMonth = sprintf('%s/%s', $upload_basedir, $upload_yearmonth);
    if ($this->build_img($upload_basdirAndYearMonth, $org_name, $new_name, $ext_filename, $this->width, $this->height)) {
      # 画像のパスを差し替え
      $new_thumbnail = sprintf('%s/%s/%s', $wp_upload_baseurl, $upload_yearmonth, $new_name);

      return $new_thumbnail;
    }

    # error
    if ($this->retValueOnError)
      return $this->url;
    else
      return $this->retValueOnError;

  }

  /**
   * build_img 画像を生成
   * 
   * @param mixed $basedir 
   * @param mixed $org_name 
   * @param mixed $new_name 
   * @param mixed $width 
   * @param mixed $height 
   * @return boolean TRUE:生成成功、FALSE:失敗
   */
  function build_img($basedir, $org_name, $new_name, $new_ext, $width, $height)
  {
    $org_fullpath = sprintf('%s/%s', $basedir, $org_name);
    $new_fullpath = sprintf('%s/%s', $basedir, $new_name);

    # 既にファイルが生成されている場合
    if (file_exists($new_fullpath))
      return TRUE;

    # http://www.24w.jp/study_contents.php?bid=php&iid=php&sid=graphic&cid=002
    $canvas = imagecreatetruecolor($width, $height);

    switch($new_ext) {
    case 'jpg':
    case 'jpeg':
      $image = imagecreatefromjpeg($org_fullpath);
      break;

    case 'png':
      $image = imagecreatefrompng($org_fullpath);
      break;

    case 'gif':
      $image = imagecreatefromgif($org_fullpath);
      break;

    default:
      return FALSE;
    }


    list($image_w, $image_h) = getimagesize($org_fullpath);

    imagecopyresampled($canvas,  // 背景画像
      $image,   // コピー元画像
      0,        // 背景画像の x 座標
      0,        // 背景画像の y 座標
      0,        // コピー元の x 座標
      0,        // コピー元の y 座標
      $width,   // 背景画像の幅
      $height,  // 背景画像の高さ
      $image_w, // コピー元画像ファイルの幅
      $image_h  // コピー元画像ファイルの高さ
    );

    // 画像を出力する
    imagejpeg($canvas,
      $new_fullpath,
      100
    );

    imagedestroy($canvas);

    return TRUE;
  }



  /**
   * get_thumbnail_built_name 自動生成した画像のファイル名を返す
   * 
   * @param string $org_name
   * @param string $suffix
   * @return string
   */
  function get_thumbnail_built_name($org_name, $suffix)
  {
    $work = explode('.', $org_name);
    $ext = array_pop($work);
    $base = implode('.', $work);

    $ret = array(
      sprintf('%s%s', $base, $suffix),
      $ext);

    return $ret;
  }


  /**
   * get_thumbnail_basedirANDpath 指定URLから、OS上のbasedirとファイルパスを返す
   *
   * @param string $wp_upload_basedir(e.g. /var/www/wp/wordpress/wp-content/uploads)
   * @param string $thumbnail_url URL(例：http://example.co.jp/wp-content/uploads/2015/05/test.png)
   * @return array パス(例：
   array(
     '/var/www/wp/wp-content/uploads',
     '2015/05',
     'test.png'
   )
   *         エラー時は array('', '', '')
   */
  function get_thumbnail_basedirANDpath($wp_upload_basedir, $thumbnail_url)
  {
    if (!preg_match('/wp-content\/uploads\/(.*)/', $thumbnail_url, $match))
      return array('', '', '');

    $path = $match[1];
    $split_path = explode('/', $path);

    $ret = array();
    array_push($ret, $wp_upload_basedir);
    array_push($ret, sprintf('%s/%s', $split_path[0], $split_path[1]));
    array_push($ret, $split_path[2]);

    return $ret;
  }

}


/**
 * hykwEasyImageCompress 
 * 
 * @param string $url 変換元画像のURL(例：'http://example.co.jp/wp-content/uploads/2015/05/foo.png')
 * @param string $suffix 変換後の画像のbaseファイル名につけるsuffix(例：'_auto_' の場合、foo_auto_png とかになる）
 * @param string $width 変換後ファイルのwidth
 * @param string $height 変換後ファイルのheight
 * @param string $retValueOnError エラーが発生した場合、TRUEの場合$urlの値をそのまま返す。それ以外の値の場合はこの引数の値を返す
 * @return string 変換後の画像のURL(例：'http://example.co.jp/wp-content/uploads/2015/05/foo_auto_.png')、エラーが発生した場合、$retValueOnErrorの値により$urlの値もしくは$retValueOnErrorの値を返す
 */
function hykwEasyImageCompress($url, $suffix, $width, $height, $retValueOnError = TRUE)
{
  $objICC = new hykwEasyImageCompressClass($url, $suffix, $width, $height, $retValueOnError);
  $ret = $objICC->convert();

  return $ret;
}

