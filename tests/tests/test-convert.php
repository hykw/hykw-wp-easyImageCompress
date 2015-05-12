<?php

class UT_hykwEasyImageCompress extends hykwEasyUT {

  public function test_splitURL()
  {
    $url = 'http://example.co.jp/wp-content/uploads/2015/05/foo.png';
    $suffix = '_auto_';
    $width = 100;
    $height = 50;
    $retValueOnError = TRUE;

    $obj = new hykwEasyImageCompressClass($url, $suffix, $width, $height, $retValueOnError);
    $this->assertEquals(array('2015/05', 'foo.png'), $obj->splitURL_to_subdirAndFilename($url));

    $url = 'http://example.co.jp/wp-content/themes/test.theme/img/news/test.png';
    $obj = new hykwEasyImageCompressClass($url, $suffix, $width, $height, $retValueOnError);
    $this->assertEquals(FALSE, $obj->splitURL_to_subdirAndFilename($url));
  }

  public function test_get_build_fileinfo()
  {
    $file = 'foo.png';
    $suffix = '__auto__';

    $obj = new hykwEasyImageCompressClass(FALSE, $suffix, 0, 0, FALSE);
    $this->assertEquals(array('foo__auto__', 'png'), $obj->get_build_fileinfo($file, $suffix));

    $file_error = 'foo';
    $this->assertEquals(FALSE, $obj->get_build_fileinfo($file_error, $suffix));
  }

}


