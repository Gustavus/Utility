<?php
/**
 * StreamReaderTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test;

use Gustavus\Test\Test,
    Gustavus\Test\DelayedExecutionToken,

    Gustavus\Utility\StreamReader;



/**
 * Test suite for the StreamReader class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
abstract class StreamReaderTestTemplate extends Test
{
  protected $tokens = [];

  public function tearDown()
  {
    foreach ($this->tokens as &$token) {
      if (is_resource($token) && get_resource_type($token) === 'GTO Override Token') {
        override_revert($token);
      }

      $token = null;
    }

    $this->tokens = [];
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
      if (is_resource($stream)) {
        fclose($stream);
      }
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
   * @expectedException RuntimeException
   */
  public function testAvailableClosedException()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $reader->close();
    $reader->available();
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
      $chunksize = mt_rand(3, 9);
      $expected = substr($input, $offset, $chunksize);

      $result = $reader->read($chunksize, $read);
      $this->assertSame($expected, $result);
      $this->assertSame($chunksize, $read);

      $offset += $chunksize;
    }
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function testReadClosedException()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $reader->close();
    $reader->read();
  }

  /**
   * @test
   * @dataProvider invalidCountProvider
   * @expectedException InvalidArgumentException
   */
  public function testReadInvalidCount($count)
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $reader->read($count);
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
      $chunksize = mt_rand(3, 9);
      $expected = substr($input, $offset, $chunksize);

      $result = $reader->peek($chunksize, $read);
      $this->assertSame($expected, $result);
      $this->assertSame($chunksize, $read);
    }
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function testPeekClosedException()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $reader->close();
    $reader->peek();
  }

  /**
   * @test
   * @dataProvider invalidCountProvider
   * @expectedException InvalidArgumentException
   */
  public function testPeekInvalidCount($count)
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $reader->peek($count);
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
      $expected = substr($input, $offset + $chunksize, $chunksize);

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
      $chunksize = mt_rand(3, 9);
      $expected = substr($input, $offset + $chunksize, $chunksize);

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
   * @expectedException RuntimeException
   */
  public function testSkipClosedException()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $reader->close();
    $reader->skip();
  }

  /**
   * @test
   * @dataProvider invalidCountProvider
   * @expectedException InvalidArgumentException
   */
  public function testSkipInvalidCount($count)
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $reader->skip($count);
  }

  /**
   * Data provider for tests that require invalid counts.
   *
   * @return array
   */
  public function invalidCountProvider()
  {
    return [
      [null],
      [''],
      ['1'],
      [true],
      [false],
      [[1]],
      [$this],
      [function() { return 1; }],
      [STDOUT],
    ];
  }



  /**
   * @test
   */
  public function testIsEOF()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $input = str_repeat('0123456789abcdefghij', 5);

    fwrite($stream, $input);
    rewind($stream);

    // Initially, nothing should be available, as there hasn't been a reason to buffer any data.
    // After a one char/byte read operation, there should be /something/ available.

    $result = $reader->isEOF();
    $this->assertFalse($result);

    $result = $reader->read(strlen($input));
    $this->assertSame($input, $result);

    $result = $reader->isEOF();
    $this->assertTrue($result);
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function testIsEOFClosedException()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $reader->close();
    $reader->isEOF();
  }

  /**
   * @test
   */
  public function testCanMark()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $result = $reader->canMark();
    $this->assertSame('boolean', gettype($result));
  }

  /**
   * @test
   */
  public function testCanRewind()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $result = $reader->canRewind();
    $this->assertSame('boolean', gettype($result));
  }

  /**
   * @test
   */
  public function testMark()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    if (!$reader->canMark()) {
      $this->setExpectedException('RuntimeException');
    }

    $result = $reader->mark();
    $this->assertTrue($result);
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function testMarkClosedException()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $reader->close();
    $reader->mark();
  }

  /**
   * @test
   */
  public function testIsMarked()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $result = $reader->isMarked();
    $this->assertFalse($result);

    if ($reader->canMark()) {
      $result = $reader->mark();
      $this->assertTrue($result);

      $result = $reader->isMarked();
      $this->assertTrue($result);
    }
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function testIsMarkedClosedException()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $reader->close();
    $reader->isMarked();
  }

  /**
   * @test
   */
  public function testClearMark()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $result = $reader->isMarked();
    $this->assertFalse($result);

    $result = $reader->clearMark();
    $this->assertFalse($result);

    $result = $reader->isMarked();
    $this->assertFalse($result);


    if ($reader->canMark()) {
      $result = $reader->mark();
      $this->assertTrue($result);

      $result = $reader->isMarked();
      $this->assertTrue($result);

      $result = $reader->clearMark();
      $this->assertTrue($result);

      $result = $reader->isMarked();
      $this->assertFalse($result);
    }
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function testClearMarkClosedException()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $reader->close();
    $reader->clearMark();
  }

  /**
   * @test
   */
  public function testRewindNoMark()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $input = str_repeat('0123456789abcdefghij', 5);
    $offset = 0;

    fwrite($stream, $input);
    rewind($stream);

    if (!$reader->canRewind()) {
      $this->setExpectedException('RuntimeException');
    }

    for ($i = 0; $i < 10; ++$i) {
      $chunksize = mt_rand(3, 9);
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
      $chunksize = mt_rand(3, 9);
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
  public function testRewindWithMark()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $input = str_repeat('0123456789abcdefghij', 5);
    $offset = 0;

    fwrite($stream, $input);
    rewind($stream);

    if (!$reader->canRewind() || !$reader->canMark()) {
      $this->setExpectedException('RuntimeException');
    }


    $chunksize = mt_rand(3, 9);
    $expected = substr($input, $offset, $chunksize);

    $result = $reader->read($chunksize, $read);
    $this->assertSame($expected, $result);
    $this->assertSame($chunksize, $read);

    $offset += $chunksize;
    $mark = $offset;

    $result = $reader->mark();
    $this->assertTrue($result);

    $result = $reader->isMarked();
    $this->assertTrue($result);

    $chunksize = mt_rand(3, 9);
    $expected = substr($input, $offset, $chunksize);

    $result = $reader->read($chunksize, $read);
    $this->assertSame($expected, $result);
    $this->assertSame($chunksize, $read);

    if ($reader->isMarked()) {
      $offset = $mark;
    } else {
      $offset = 0;
    }

    $result = $reader->rewind();
    $this->assertTrue($result);

    $chunksize = mt_rand(3, 9);
    $expected = substr($input, $offset, $chunksize);

    $result = $reader->read($chunksize, $read);
    $this->assertSame($expected, $result);
    $this->assertSame($chunksize, $read);
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function testRewindClosedException()
  {
    $stream = $this->buildStream();
    $reader = $this->buildInstance($stream);

    $reader->close();
    $reader->rewind();
  }

}
