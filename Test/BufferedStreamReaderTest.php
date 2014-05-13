<?php
/**
 * StreamReaderTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test\CSV;

use Gustavus\Test\Test,
    Gustavus\Test\DelayedExecutionToken,

    Gustavus\Utility\CSV\StreamReader,
    // Gustavus\Utility\Test\CSV\StreamReaderTest\StreamReaderTestImpl,

    StdClass;



/**
 * Test suite for the StreamReader class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
abstract class StreamReaderTest extends Test
{
  protected $tokens;



  public function setup()
  {
    $this->tokens = [];
  }

  public function tearDown()
  {
    foreach ($this->tokens as $token) {
      override_revert($token);
    }
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Builds a stream to be used during testing.
   *
   * @return resource
   *  A stream usable during testing.
   */
  public function buildStream()
  {
    $stream = fopen('php://memory', 'wb+');

    $this->tokens[] = new DelayedExecutionToken(function() use (&$stream) {
      fclose($stream);
    });

    return $stream;
  }

  /**
   * Builds the StreamReader instance to test, using the specified stream as the backing input
   * stream.
   *
   * @param resource $stream
   *  The stream from which the newly built StreamReader should read.
   *
   * @return StreamReader
   *  The StreamReader instance to test.
   */
  public abstract function buildInstance($stream);

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   */
  public function testClose()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);



  }

  /**
   * @test
   */
  public function testAvailable()
  {

  }

  /**
   * @test
   */
  public function testRead()
  {

  }

  /**
   * @test
   */
  public function testPeek()
  {

  }

  /**
   * @test
   */
  public function testSkip()
  {

  }

  /**
   * @test
   */
  public function testCanMark()
  {

  }

  /**
   * @test
   */
  public function testCanRewind()
  {

  }

  /**
   * @test
   */
  public function testMark()
  {

  }

  /**
   * @test
   */
  public function testRewind()
  {

  }





}
