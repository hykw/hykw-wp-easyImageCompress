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
    try {
      ### URLを分割
      # ['2015/05', 'test.png']
      $work = $this->splitURL_to_subdirAndFilename($this->url);
      if ($work == FALSE)
        throw new Exception();

      list($subdir, $thumbnail_filename) = $work;


      ### 生成するファイル名を取得
      # 'foo.png' -> ['foo_xxxx_', 'png']
      $work = $this->get_build_fileinfo($thumbnail_filename, $this->suffix);
      if ($work == FALSE)
        throw new Exception();

      list($thumbnail_new_basefile, $thumbnail_ext) = $work;


      ### 各種パスの取得
      $wp_upload_dirs = wp_upload_dir();
      $wp_upload_basedir = $wp_upload_dirs['basedir'];  # /var/www/wp/wordpress/wp-content/uploads
      $wp_upload_baseurl = $wp_upload_dirs['baseurl'];  # http://example.co.jp/wp-content/uploads

      ### 画像を生成
      $basedir = sprintf('%s/%s', $wp_upload_basedir, $subdir);
      $thumbnail_new_filename = sprintf('%s.%s', $thumbnail_new_basefile, $thumbnail_ext);
      if ($this->build_img($basedir, $thumbnail_filename, $thumbnail_new_filename, $thumbnail_ext, $this->width, $this->height)) {

        # 正常に生成できたので、生成後のURLを返す
        $ret = sprintf('%s/%s/%s.%s', $wp_upload_baseurl, $subdir, $thumbnail_new_basefile, $thumbnail_ext);
        return $ret;
      } else {
        throw new Exception();
      }

    } catch (Exception $e) {
      # エラー時対応
      if ($this->retValueOnError)
        return $this->url;
      else
        return $this->retValueOnError;
    }
  }

  ##################################################
  /**
   * splitURL_to_subdirAndFilename URLからsubdir と ファイル名を分割して返す
   * 
   * @param string $url 
   * @return array [subdir, filename] 不正なURLの場合、FALSE
   */
  function splitURL_to_subdirAndFilename($url)
  {
    if (!preg_match('/wp-content\/uploads\/(.*)/', $url, $match))
      return FALSE;

    $path = $match[1];
    $split_path = explode('/', $path);

    $end = array_pop($split_path);
    $ret = array(
      implode('/', $split_path),
      $end,
    );

    return $ret;
  }


  /**
   * get_build_fileinfo 生成するファイル名と拡張子を返す
   * 
   * @param mixed $filename 
   * @param mixed $suffix 
   * @return array [basefile名, 拡張子] 変な値の場合 FALSE
   */
  function get_build_fileinfo($filename, $suffix)
  {
    $work = explode('.', $filename);
    # 空もしくは拡張子無し
    if (count($work) <= 1)
      return FALSE;

    $ext = array_pop($work);
    $base = implode('.', $work);

    $ret = array(
      sprintf('%s%s', $base, $suffix),
      $ext);

    return $ret;
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

}

##################################################

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

