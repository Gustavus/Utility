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
    $stream = fopen('php://memory', 'rb+');

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

    $result = $reader->isClosed();
    $this->assertFalse($result);

    $result = $reader->close();
    $this->assertTrue($result);

    $result = $reader->isClosed();
    $this->assertTrue($result);

    $result = $reader->close();
    $this->assertFalse($result);

    $result = $reader->isClosed();
    $this->assertTrue($result);
  }

  /**
   * @test
   */
  public function testAvailable()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    fwrite($stream, str_repeat('testdata', 10));
    rewind($stream);

    // Initially, nothing should be available, as there hasn't been a reason to buffer any data.
    // After a one char/byte read operation, there should be /something/ available.

    $result = $reader->available();
    $this->assertSame(0, $result);

    $reader->read();

    $result = $reader->available();
    $this->assertGreaterThan(0, $result);
  }

  /**
   * @test
   */
  public function testReadSingleCharacter()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $input = str_repeat('0123456789abcdefghij', 5);
    $offset = 0;
    $length = strlen($input);
    $chunksize = 1;

    fwrite($stream, $input);
    rewind($stream);

    for ($i = 0; $i < 10; ++$i) {
      $expected = substr($input, $offset, $chunksize);

      $result = $reader->read($chunksize, $read);
      $this->assertSame($expected, $result);
      $this->assertSame($chunksize, $read);

      $offset += $chunksize;
    }
  }

  /**
   * @test
   */
  public function testReadMultipleCharacters()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $input = str_repeat('0123456789abcdefghij', 5);
    $offset = 0;

    fwrite($stream, $input);
    rewind($stream);

    for ($i = 0; $i < 10; ++$i) {
      $chunksize = mt_rand(2, 9);
      $expected = substr($input, $offset, $chunksize);

      $result = $reader->read($chunksize, $read);
      $this->assertSame($expected, $result);
      $this->assertSame($chunksize, $read);

      $offset += $chunksize;
    }
  }

  /**
   * @test
   */
  public function testPeekSingleCharacter()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $input = str_repeat('0123456789abcdefghij', 5);
    $offset = 0;
    $chunksize = 1;

    fwrite($stream, $input);
    rewind($stream);

    for ($i = 0; $i < 10; ++$i) {
      $expected = substr($input, $offset, $chunksize);

      $result = $reader->peek($chunksize, $read);
      $this->assertSame($expected, $result);
      $this->assertSame($chunksize, $read);
    }
  }

  /**
   * @test
   */
  public function testPeekMultipleCharacters()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $input = str_repeat('0123456789abcdefghij', 5);
    $offset = 0;

    fwrite($stream, $input);
    rewind($stream);

    for ($i = 0; $i < 10; ++$i) {
      $chunksize = mt_rand(2, 9);
      $expected = substr($input, $offset, $chunksize);

      $result = $reader->peek($chunksize, $read);
      $this->assertSame($expected, $result);
      $this->assertSame($chunksize, $read);
    }
  }

  /**
   * @test
   */
  public function testSkipSingleCharacter()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $input = str_repeat('0123456789abcdefghij', 5);
    $chunksize = 1;
    $offset = 0;

    fwrite($stream, $input);
    rewind($stream);

    for ($i = 0; $i < 10; ++$i) {
      $expected = substr($input, $offset, $chunksize);

      $reader->skip($chunksize, $read);
      $this->assertSame($chunksize, $read);

      $result = $reader->peek($chunksize, $read);
      $this->assertSame($expected, $result);
      $this->assertSame($chunksize, $read);

      $offset += $chunksize;
    }
  }

  /**
   * @test
   */
  public function testSkipMultipleCharacters()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $input = str_repeat('0123456789abcdefghij', 5);
    $offset = 0;

    fwrite($stream, $input);
    rewind($stream);

    for ($i = 0; $i < 10; ++$i) {
      $chunksize = mt_rand(2, 9);
      $expected = substr($input, $offset, $chunksize);

      $reader->skip($chunksize, $read);
      $this->assertSame($chunksize, $read);

      $result = $reader->peek($chunksize, $read);
      $this->assertSame($expected, $result);
      $this->assertSame($chunksize, $read);

      $offset += $chunksize;
    }
  }

  /**
   * @test
   */
  public function testCanMark()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $result = $reader->canMark();
    $this->assertTrue(is_boolean($result));
  }

  /**
   * @test
   */
  public function testCanRewind()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $result = $reader->canRewind();
    $this->assertTrue(is_boolean($result));
  }

  /**
   * @test
   */
  public function testMark()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    if (!$stream->canMark()) {
      $this->setExpectedException('RuntimeException');
    }

    $result = $stream->mark();
    $this->assertTrue($result);
  }

  /**
   * @test
   */
  public function testRewind()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $input = str_repeat('0123456789abcdefghij', 5);
    $offset = 0;

    fwrite($stream, $input);
    rewind($stream);

    if (!$stream->canRewind()) {
      $this->setExpectedException('RuntimeException');
    }

    for ($i = 0; $i < 10; ++$i) {
      $chunksize = mt_rand(2, 9);
      $expected = substr($input, $offset, $chunksize);

      $result = $reader->read($chunksize, $read);
      $this->assertSame($expected, $result);
      $this->assertSame($chunksize, $read);

      $offset += $chunksize;
    }

    $result = $reader->rewind();
    $this->assertTrue($result);

    $offset = 0;
    for ($i = 0; $i < 10; ++$i) {
      $chunksize = mt_rand(2, 9);
      $expected = substr($input, $offset, $chunksize);

      $result = $reader->read($chunksize, $read);
      $this->assertSame($expected, $result);
      $this->assertSame($chunksize, $read);

      $offset += $chunksize;
    }
  }





}
