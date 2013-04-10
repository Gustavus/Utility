<?php
/**
 * CURLRequestTest.php
 */
namespace Gustavus\Utility\Test;

use Gustavus\Utility\CURLRequest;



class CURLRequestTest extends \Gustavus\Test\Test
{
  /**
   * @test
   * @dataProvider dataForConstruction
   */
  public function testConstruction($url, $options, $success)
  {
    try {
      $curl = new CURLRequest($url, $options);

      if (!$success) {
        $this->fail('An expected exception was not thrown.');
      }
    } catch (\Exception $e) {
      if ($success) {
        throw $e;
      }
    }
  }

  /**
   * Data provider for the like-named test.
   */
  public function dataForConstruction()
  {
    return [
      [null, [], true],
      ['gustavus.edu', [], true],
      ['gustavus.edu', [CURLOPT_HTTPGET => true], true],

      [123, [], false],
      ['gustavus.edu', ['badoption' => 'isbad'], false]
    ];
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   */
  public function testClone()
  {
    $ca = new CURLRequestDummy();
    $cb = clone $ca;

    $this->assertNotEquals($ca->getHandle(), $cb->getHandle());
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForSetOption
   */
  public function testSetOption($curl, $option, $value, $strict, $expected, $success)
  {
    try {
      $result = $curl->setOption($option, $value, $strict);

      $this->assertEquals($expected, $result);

      if (!$success) {
        $this->fail('An expected exception was not thrown.');
      }
    } catch (\Exception $e) {
      if ($success) {
        throw $e;
      }
    }
  }

  /**
   * Data provider for the like-named test.
   */
  public function dataForSetOption()
  {
    $ocurl = new CURLRequest();
    $ccurl = new CURLRequest();
    $ccurl->close();

    return [
      [$ocurl, CURLOPT_HTTPGET, true, false, true, true],
      [$ocurl, CURLOPT_INFILESIZE, 50, false, true, true],
      [$ocurl, CURLOPT_URL, 'http://gustavus.edu', false, true, true],
      [$ocurl, CURLOPT_HTTPHEADER, ['Content-type: text/plain', 'Content-length: 100'], false, true, true],
      [$ocurl, CURLOPT_STDERR, STDOUT, false, true, true],
      [$ocurl, CURLOPT_READFUNCTION, function($a, $b, $c) { /* do nothing */ }, false, true, true],

      [$ocurl, CURLOPT_HTTPGET, true, true, true, true],
      [$ocurl, CURLOPT_INFILESIZE, 50, true, true, true],
      [$ocurl, CURLOPT_URL, 'http://gustavus.edu', true, true, true],
      [$ocurl, CURLOPT_HTTPHEADER, ['Content-type: text/plain', 'Content-length: 100'], true, true, true],
      [$ocurl, CURLOPT_STDERR, STDOUT, true, true, true],
      [$ocurl, CURLOPT_READFUNCTION, function($a, $b, $c) { /* do nothing */ }, true, true, true],

      [$ocurl, CURLOPT_HTTPHEADER, 'nope', false, false, true],
      [$ocurl, CURLOPT_STDERR, 123, false, false, true],
      [$ocurl, CURLOPT_READFUNCTION, 'not a closure', false, true, true],

      [$ocurl, CURLOPT_HTTPHEADER, 'nope', true, false, false],
      [$ocurl, CURLOPT_STDERR, 123, true, false, false],
      [$ocurl, CURLOPT_READFUNCTION, 'not a closure', true, true, false],

      [$ccurl, CURLOPT_HTTPGET, true, false, false, false]
    ];
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForSetOptions
   */
  public function testSetOptions($curl, $options, $strict, $expected, $success)
  {
    try {
      $result = $curl->setOptions($options, $strict);

      $this->assertEquals($expected, $result);

      if (!$success) {
        $this->fail('An expected exception was not thrown.');
      }
    } catch (\Exception $e) {
      if ($success) {
        throw $e;
      }
    }
  }

  /**
   * Data provider for the like-named test.
   */
  public function dataForSetOptions()
  {
    $ocurl = new CURLRequest();
    $ccurl = new CURLRequest();
    $ccurl->close();

    return [
      [
        $ocurl,
        [
          CURLOPT_HTTPGET       => true,
          CURLOPT_INFILESIZE    => 50,
          CURLOPT_URL           => 'http://gustavus.edu',
          CURLOPT_HTTPHEADER    => ['Content-type: text/plain', 'Content-length: 100'],
          CURLOPT_STDERR        => STDOUT,
          CURLOPT_READFUNCTION  => function($a, $b, $c) { /* do nothing */ }
        ],
        false,
        true,
        true
      ],

      [
        $ocurl,
        [
          CURLOPT_HTTPGET       => true,
          CURLOPT_INFILESIZE    => 50,
          CURLOPT_URL           => 'http://gustavus.edu',
          CURLOPT_HTTPHEADER    => ['Content-type: text/plain', 'Content-length: 100'],
          CURLOPT_STDERR        => STDOUT,
          CURLOPT_READFUNCTION  => function($a, $b, $c) { /* do nothing */ }
        ],
        true,
        true,
        true
      ],

      [
        $ocurl,
        [
          CURLOPT_HTTPHEADER    => 'nope',
          CURLOPT_STDERR        => 123,
          CURLOPT_READFUNCTION  => 'not a closure'
        ],
        false,
        false,
        true
      ],

      [
        $ocurl,
        [
          CURLOPT_HTTPHEADER    => 'nope',
          CURLOPT_STDERR        => 123,
          CURLOPT_READFUNCTION  => 'not a closure'
        ],
        true,
        false,
        false
      ],

      [
        $ccurl,
        [
          CURLOPT_HTTPGET       => true,
          CURLOPT_INFILESIZE    => 50,
          CURLOPT_URL           => 'http://gustavus.edu',
          CURLOPT_HTTPHEADER    => ['Content-type: text/plain', 'Content-length: 100'],
          CURLOPT_STDERR        => STDOUT,
          CURLOPT_READFUNCTION  => function($a, $b, $c) { /* do nothing */ }
        ],
        false,
        false,
        false
      ]
    ];
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Illegal State: CURLRequest is closed.
   */
  public function testGetLastErrorNumber()
  {
    $ocurl = new CURLRequest();
    $ccurl = new CURLRequest();
    $ccurl->close();

    $this->assertTrue(is_int($ocurl->getLastErrorNumber()));

    $ccurl->getLastErrorNumber();
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Illegal State: CURLRequest is closed.
   */
  public function testGetLastErrorMessage()
  {
    $ocurl = new CURLRequest();
    $ccurl = new CURLRequest();
    $ccurl->close();

    $this->assertTrue(is_string($ocurl->getLastErrorMessage()));

    $ccurl->getLastErrorMessage();
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForGetInfo
   */
  public function testGetInfo($curl, $parameter, $expected, $success)
  {
    try {
      $result = $curl->getInfo($parameter);

      $this->assertEquals($expected, $result);

      if (!$success) {
        $this->fail('An expected exception was not thrown.');
      }
    } catch (\Exception $e) {
      if ($success) {
        throw $e;
      }
    }
  }

  /**
   * Data provider for the like-named test
   */
  public function dataForGetInfo()
  {
    $ocurl = new CURLRequest();
    $ccurl = new CURLRequest();
    $ccurl->close();

    $defaults = [
      'url'                       => '',
      'content_type'              => null,
      'http_code'                 => 0,
      'header_size'               => 0,
      'request_size'              => 0,
      'filetime'                  => 0,
      'ssl_verify_result'         => 0,
      'redirect_count'            => 0,
      'total_time'                => 0.0,
      'namelookup_time'           => 0.0,
      'connect_time'              => 0.0,
      'pretransfer_time'          => 0.0,
      'size_upload'               => 0.0,
      'size_download'             => 0.0,
      'speed_download'            => 0.0,
      'speed_upload'              => 0.0,
      'download_content_length'   => -1.0,
      'upload_content_length'     => -1.0,
      'starttransfer_time'        => 0.0,
      'redirect_time'             => 0.0,
      'certinfo'                  => [],
      'primary_ip'                => '',
      'redirect_url'              => ''
    ];

    return [
      [$ocurl, CURLINFO_EFFECTIVE_URL, null, true],
      [$ocurl, 8675309, null, true],
      [$ocurl, null, $defaults, true],

      [$ocurl, new \StdClass(), null, false],
      [$ocurl, ['arrays no good'], null, false],
      [$ocurl, 'neither are strings', null, false],

      [$ccurl, CURLINFO_EFFECTIVE_URL, false, false],
      [$ccurl, 8675309, false, false],
      [$ccurl, null, null, false],
    ];
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForExecute
   */
  public function testExecute($url, $expected, $success)
  {
    try {
      $curl = new CURLRequest();

      $result = $curl->execute($url);

      if ($expected) {
        $this->assertTrue(is_string($result));
      } else {
        $this->assertFalse($result);
      }

      if (!$success) {
        $this->fail('An expected exception was not thrown.');
      }
    } catch (\Exception $e) {
      if ($success) {
        throw $e;
      }
    }
  }

  /**
   * Data provider for the like-named test.
   */
  public function dataForExecute()
  {
    return [
      ['gustavus.edu', true, true],
      [null, false, true],

      [123, false, false],
      [1.23, false, false],
      [['array!'], false, false],
      [false, false, false],
      [new \StdClass(), false, false]
    ];
  }


  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Illegal State: CURLRequest is closed.
   */
  public function testExecuteOnClosed()
  {
    $curl = new CURLRequest();
    $curl->close();
    $curl->execute();
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

}


/**
 * Dummy class to use for the cloning test.
 */
class CURLRequestDummy extends CURLRequest
{
  public function getHandle()
  {
    return $this->handle;
  }
}
