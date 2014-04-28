<?php
/**
 * MimeUtilTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test\Serializer;

use Gustavus\Test\Test,
    Gustavus\Test\DelayedExecutionToken,

    Gustavus\Utility\MimeUtil,

    stdClass;



/**
 * Test suite for the MimeUtil class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 *
 * @coversDefaultClass Gustavus\Utility\MimeUtil
 */
class MimeUtilTest extends Test
{
  /**
   * @test
   * @dataProvider dataForGetMimeType
   *
   * @covers ::getMimeType
   */
  public function testGetMimeType($file, $readable, $isfile, $reportedmime, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $token1 = override_function('is_readable', function($filename) use (&$token1, &$file, &$readable) {
      if ($filename === $file) {
        return $readable;
      } else {
        return call_overridden_func($token1, null, $filename);
      }
    });

    $token2 = override_function('is_file', function($filename) use (&$token2, &$file, &$isfile) {
      if ($filename === $file) {
        return $isfile;
      } else {
        return call_overridden_func($token2, null, $filename);
      }
    });

    $token3 = override_function('finfo_file', function($finfo, $filename, $options = FILEINFO_NONE, $context = null) use (&$token3, &$file, &$reportedmime) {
      if ($filename === $file) {
        return $reportedmime;
      } else {
        return call_overridden_func($token3, null, $finfo, $filename, $options, $context);
      }
    });

    $ctoken = new DelayedExecutionToken(function() use (&$token1, &$token2, &$token3) {
      override_revert($token1);
      override_revert($token2);
      override_revert($token3);
    });


    $mu = new MimeUtil();

    $result = $mu->getMimeType($file);
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for the getMimeType test.
   *
   * @return array
   */
  public function dataForGetMimeType()
  {
    return [
      ['mimetestfile', true, true, 'text/plain', 'text/plain', null],

      ['mimetestfile', false, true, 'text/plain', null, null],
      ['mimetestfile', true, false, 'text/plain', null, null],
      ['mimetestfile', true, true, false, null, null],
      ['mimetestfile', false, false, 'text/plain', null, null],
      ['mimetestfile', false, true, false, null, null],
      ['mimetestfile', true, false, false, null, null],
      ['mimetestfile', false, false, false, null, null],

      [null, true, true, 'text/plain', null, 'InvalidArgumentException'],
      ['', true, true, 'text/plain', null, 'InvalidArgumentException'],
      [161803, true, true, 'text/plain', null, 'InvalidArgumentException'],
      [2.71828, true, true, 'text/plain', null, 'InvalidArgumentException'],
      [true, true, true, 'text/plain', null, 'InvalidArgumentException'],
      [false, true, true, 'text/plain', null, 'InvalidArgumentException'],
      [['mimetestfile'], true, true, 'text/plain', null, 'InvalidArgumentException'],
      [new stdClass(), true, true, 'text/plain', null, 'InvalidArgumentException'],
      [STDOUT, true, true, 'text/plain', null, 'InvalidArgumentException'],
      [function() {}, true, true, 'text/plain', null, 'InvalidArgumentException'],
    ];
  }

  /**
   * @test
   * @dataProvider dataForGetExtensionForMimeType
   *
   * @covers ::getExtensionForMimeType
   */
  public function testGetExtensionForMimeType($mime, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $mu = new MimeUtil();

    $result = $mu->getExtensionForMimeType($mime);
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for the getExtensionForMimeType test.
   *
   * @return array
   */
  public function dataForGetExtensionForMimeType()
  {
    return [
      ['text/plain', 'txt', null],
      ['text/html', 'html', null],
      ['image/gif', 'gif', null],
      ['image/png', 'png', null],
      ['image/jpeg', 'jpg', null],
      ['application/pdf', 'pdf', null],
      ['application/zip', 'zip', null],

      [null, null, 'InvalidArgumentException'],
      ['', null, 'InvalidArgumentException'],
      [161803, null, 'InvalidArgumentException'],
      [2.71828, null, 'InvalidArgumentException'],
      [true, null, 'InvalidArgumentException'],
      [false, null, 'InvalidArgumentException'],
      [['mimetestfile'], null, 'InvalidArgumentException'],
      [new stdClass(), null, 'InvalidArgumentException'],
      [STDOUT, null, 'InvalidArgumentException'],
      [function() {}, null, 'InvalidArgumentException'],
    ];
  }

  /**
   * @test
   * @dataProvider dataForValidateMimeType
   *
   * @covers ::validateMimeType
   */
  public function testValidateMimeType($mime, $whitelist, $blacklist, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $mu = new MimeUtil();

    $result = $mu->validateMimeType($mime, $whitelist, $blacklist);
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for the validateMimeType test.
   *
   * @return array
   */
  public function dataForValidateMimeType()
  {
    return [
      // Test note:
      // This first group may need to change depending on the default whitelist/blacklist.
      ['text/html', null, null, true, null],
      ['text/php', null, null, false, null],

      // Test note:
      // These, however, should not be changed unless implementation details change.
      ['text/plain', '/plain/', '/application/', true, null],
      ['text/plain', '/zip/', '/application/', false, null],
      ['text/plain', '/plain/', '/text/', false, null],

      [161803, null, null, false, 'InvalidArgumentException'],
      [2.71828, null, null, false, 'InvalidArgumentException'],
      [true, null, null, false, 'InvalidArgumentException'],
      [false, null, null, false, 'InvalidArgumentException'],
      [['mimetestfile'], null, null, false, 'InvalidArgumentException'],
      [new stdClass(), null, null, false, 'InvalidArgumentException'],
      [STDOUT, null, null, false, 'InvalidArgumentException'],
      [function() {}, null, null, false, 'InvalidArgumentException'],

      ['text/plain', '', null, false, 'InvalidArgumentException'],
      ['text/plain', 'not a valid regex', null, false, 'InvalidArgumentException'],
      ['text/plain', 161803, null, false, 'InvalidArgumentException'],
      ['text/plain', 2.71828, null, false, 'InvalidArgumentException'],
      ['text/plain', true, null, false, 'InvalidArgumentException'],
      ['text/plain', false, null, false, 'InvalidArgumentException'],
      ['text/plain', ['mimetestfile'], null, false, 'InvalidArgumentException'],
      ['text/plain', new stdClass(), null, false, 'InvalidArgumentException'],
      ['text/plain', STDOUT, null, false, 'InvalidArgumentException'],
      ['text/plain', function() {}, null, false, 'InvalidArgumentException'],

      ['text/plain', null, '', false, 'InvalidArgumentException'],
      ['text/plain', null, 'not a valid regex', false, 'InvalidArgumentException'],
      ['text/plain', null, 161803, false, 'InvalidArgumentException'],
      ['text/plain', null, 2.71828, false, 'InvalidArgumentException'],
      ['text/plain', null, true, false, 'InvalidArgumentException'],
      ['text/plain', null, false, false, 'InvalidArgumentException'],
      ['text/plain', null, ['mimetestfile'], false, 'InvalidArgumentException'],
      ['text/plain', null, new stdClass(), false, 'InvalidArgumentException'],
      ['text/plain', null, STDOUT, false, 'InvalidArgumentException'],
      ['text/plain', null, function() {}, false, 'InvalidArgumentException'],
    ];
  }

  /**
   * @test
   * @dataProvider dataForValidateFileMimeType
   *
   * @covers ::validateFileMimeType
   */
  public function testValidateFileMimeType($file, $readable, $isfile, $reportedmime, $whitelist, $blacklist, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $token1 = override_function('is_readable', function($filename) use (&$token1, &$file, &$readable) {
      if ($filename === $file) {
        return $readable;
      } else {
        return call_overridden_func($token1, null, $filename);
      }
    });

    $token2 = override_function('is_file', function($filename) use (&$token2, &$file, &$isfile) {
      if ($filename === $file) {
        return $isfile;
      } else {
        return call_overridden_func($token2, null, $filename);
      }
    });

    $token3 = override_function('finfo_file', function($finfo, $filename, $options = FILEINFO_NONE, $context = null) use (&$token3, &$file, &$reportedmime) {
      if ($filename === $file) {
        return $reportedmime;
      } else {
        return call_overridden_func($token3, null, $finfo, $filename, $options, $context);
      }
    });

    $ctoken = new DelayedExecutionToken(function() use (&$token1, &$token2, &$token3) {
      override_revert($token1);
      override_revert($token2);
      override_revert($token3);
    });


    $mu = new MimeUtil();

    $result = $mu->validateFileMimeType($file, $whitelist, $blacklist);
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for the validateFileMimeType test.
   *
   * @return array
   */
  public function dataForValidateFileMimeType()
  {
    return [
      // Test note:
      // This first group may need to change depending on the default whitelist/blacklist.
      ['mimetestfile', true, true, 'text/html', null, null, true, null],
      ['mimetestfile', false, true, 'text/html', null, null, false, null],
      ['mimetestfile', true, false, 'text/html', null, null, false, null],
      ['mimetestfile', true, true, false, null, null, false, null],
      ['mimetestfile', false, false, 'text/html', null, null, false, null],
      ['mimetestfile', false, true, false, null, null, false, null],
      ['mimetestfile', true, false, false, null, null, false, null],
      ['mimetestfile', false, false, 'text/html', null, null, false, null],

      ['mimetestfile', true, true, 'text/php', null, null, false, null],
      ['mimetestfile', false, true, 'text/php', null, null, false, null],
      ['mimetestfile', true, false, 'text/php', null, null, false, null],
      ['mimetestfile', true, true, false, null, null, false, null],
      ['mimetestfile', false, false, 'text/php', null, null, false, null],
      ['mimetestfile', false, true, false, null, null, false, null],
      ['mimetestfile', true, false, false, null, null, false, null],
      ['mimetestfile', false, false, 'text/php', null, null, false, null],

      // Test note:
      // These, however, should not be changed unless implementation details change.
      ['mimetestfile', true, true, 'text/plain', '/plain/', '/application/', true, null],
      ['mimetestfile', false, true, 'text/plain', '/plain/', '/application/', false, null],
      ['mimetestfile', true, false, 'text/plain', '/plain/', '/application/', false, null],
      ['mimetestfile', true, true, false, '/plain/', '/application/', false, null],
      ['mimetestfile', false, false, 'text/plain', '/plain/', '/application/', false, null],
      ['mimetestfile', false, true, false, '/plain/', '/application/', false, null],
      ['mimetestfile', true, false, false, '/plain/', '/application/', false, null],
      ['mimetestfile', false, false, 'text/plain', '/plain/', '/application/', false, null],

      ['mimetestfile', true, true, 'text/plain', '/zip/', '/application/', false, null],
      ['mimetestfile', false, true, 'text/plain', '/zip/', '/application/', false, null],
      ['mimetestfile', true, false, 'text/plain', '/zip/', '/application/', false, null],
      ['mimetestfile', true, true, false, '/zip/', '/application/', false, null],
      ['mimetestfile', false, false, 'text/plain', '/zip/', '/application/', false, null],
      ['mimetestfile', false, true, false, '/zip/', '/application/', false, null],
      ['mimetestfile', true, false, false, '/zip/', '/application/', false, null],
      ['mimetestfile', false, false, 'text/plain', '/zip/', '/application/', false, null],

      ['mimetestfile', true, true, 'text/plain', '/plain/', '/text/', false, null],
      ['mimetestfile', false, true, 'text/plain', '/plain/', '/text/', false, null],
      ['mimetestfile', true, false, 'text/plain', '/plain/', '/text/', false, null],
      ['mimetestfile', true, true, false, '/plain/', '/text/', false, null],
      ['mimetestfile', false, false, 'text/plain', '/plain/', '/text/', false, null],
      ['mimetestfile', false, true, false, '/plain/', '/text/', false, null],
      ['mimetestfile', true, false, false, '/plain/', '/text/', false, null],
      ['mimetestfile', false, false, 'text/plain', '/plain/', '/text/', false, null],

      [161803, true, true, 'text/plain', null, null, false, 'InvalidArgumentException'],
      [2.71828, true, true, 'text/plain', null, null, false, 'InvalidArgumentException'],
      [true, true, true, 'text/plain', null, null, false, 'InvalidArgumentException'],
      [false, true, true, 'text/plain', null, null, false, 'InvalidArgumentException'],
      [['mimetestfile'], true, true, 'text/plain', null, null, false, 'InvalidArgumentException'],
      [new stdClass(), true, true, 'text/plain', null, null, false, 'InvalidArgumentException'],
      [STDOUT, true, true, 'text/plain', null, null, false, 'InvalidArgumentException'],
      [function() {}, true, true, 'text/plain', null, null, false, 'InvalidArgumentException'],

      ['mimetestfile', true, true, 'text/plain', '', null, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', 'not a valid regex', null, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', 161803, null, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', 2.71828, null, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', true, null, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', false, null, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', ['mimetestfile'], null, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', new stdClass(), null, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', STDOUT, null, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', function() {}, null, false, 'InvalidArgumentException'],

      ['mimetestfile', true, true, 'text/plain', null, '', false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', null, 'not a valid regex', false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', null, 161803, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', null, 2.71828, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', null, true, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', null, false, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', null, ['mimetestfile'], false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', null, new stdClass(), false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', null, STDOUT, false, 'InvalidArgumentException'],
      ['mimetestfile', true, true, 'text/plain', null, function() {}, false, 'InvalidArgumentException'],
    ];
  }
}
