<?php
/**
 * BufferedStreamReaderTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test;

use Gustavus\Test\Test,
    Gustavus\Test\DelayedExecutionToken,

    Gustavus\Utility\BufferedStreamReader,
    Gustavus\Utility\Test\BufferedStreamReaderTest\BufferedStreamReaderTestImpl;



/**
 * Test suite for the BufferedStreamReader class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class BufferedStreamReaderTest extends StreamReaderTestTemplate
{
  /**
   * {@inheritDoc}
   */
  public function buildInstance($stream)
  {
    return new BufferedStreamReaderTestImpl($stream);
  }


  /**
   * @test
   * @dataProvider dataForInstantiation
   */
  public function testInstantiation($stream, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $token = new DelayedExecutionToken(function() use(&$stream) {
      if (is_resource($stream) && $stream !== STDIN && $stream !== STDOUT) {
        fclose($stream);
      }
    });

    $reader = new BufferedStreamReader($stream);
  }

  /**
   * Data provider for the instantiation test.
   *
   * @return array
   */
  public function dataForInstantiation()
  {
    $ostream = fopen('php://output', 'wb');
    $istream = fopen('php://memory', 'rb');
    $cstream = fopen('php://memory', 'rb');
    fclose($cstream);

    $ftp = ftp_connect('127.0.0.1');

    return [
      [$istream, null],
      [STDIN, null],

      [$ftp, 'InvalidArgumentException'],
      [$ostream, 'InvalidArgumentException'],
      [$cstream, 'InvalidArgumentException'],
      [null, 'InvalidArgumentException'],
      ['php://memory', 'InvalidArgumentException'],
      [123, 'InvalidArgumentException'],
      [2.71828, 'InvalidArgumentException'],
      [true, 'InvalidArgumentException'],
      [false, 'InvalidArgumentException'],
      [[$istream], 'InvalidArgumentException'],
      [$this, 'InvalidArgumentException'],
      [STDOUT, 'InvalidArgumentException'],
      [function() use (&$istream) { return $istream; }, 'InvalidArgumentException'],
    ];
  }

}

////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Gustavus\Utility\Test\BufferedStreamReaderTest;

use Gustavus\Utility\BufferedStreamReader;

/**
 * Test implementation providing access to protected methods and members.
 */
class BufferedStreamReaderTestImpl extends BufferedStreamReader
{
  /**
   * The number of bytes to attempt to read from the backing stream on each read operation. Set to
   * be extrordinarily small to ensure we do some chunking during testing.
   *
   * @var integer
   */
  const CHUNK_SIZE = 2;

  /**
   * The maximum number of bytes to keep buffered before discarding the buffer (and invalidating
   * marks). Note that due to how PHP works with strings, we will need 2-4x more memory than this
   * value to safely complete most operations.
   *
   * Set to be extrordinarily small to ensure we hit the limit during testing.
   *
   * @var integer
   */
  const MAX_BUFFER_SIZE = 256;


  public function fillBuffer($request)
  {
    return parent::fillBuffer($request);
  }
}
