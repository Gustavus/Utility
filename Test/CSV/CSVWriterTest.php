<?php
/**
 * CSVWriterTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test\CSV;

use Gustavus\Test\Test,
    Gustavus\Test\DelayedExecutionToken,

    Gustavus\Utility\CSV\CSVWriter,
    Gustavus\Utility\Test\CSV\CSVWriterTest\CSVWriterTestImpl,

    StdClass;



/**
 * Test suite for the CSVWriter class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 *
 * @coversDefaultClass Gustavus\Utility\CSV\CSVWriter
 */
class CSVWriterTest extends Test
{
  /**
   * @test
   * @dataProvider dataForInitialization
   *
   * @covers ::__construct
   */
  public function testInitialization($stream, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $token = new DelayedExecutionToken(function() use(&$stream) {
      if (is_resource($stream) && $stream !== STDOUT) {
        fclose($stream);
      }
    });

    $writer = new CSVWriter($stream);
  }

  /**
   * Data provider for the initialization tests.
   *
   * @return array
   */
  public function dataForInitialization()
  {
    $ostream = fopen('php://memory', 'wb');
    $istream = fopen('php://memory', 'rb');
    $cstream = fopen('php://memory', 'wb');
    fclose($cstream);

    $ftp = ftp_connect('127.0.0.1');

    return [
      [$ostream, null],
      [STDOUT, null],

      [$ftp, 'InvalidArgumentException'],
      [$istream, 'InvalidArgumentException'],
      [$cstream, 'InvalidArgumentException'],
      [null, 'InvalidArgumentException'],
      ['php://memory', 'InvalidArgumentException'],
      [123, 'InvalidArgumentException'],
      [2.71828, 'InvalidArgumentException'],
      [true, 'InvalidArgumentException'],
      [false, 'InvalidArgumentException'],
      [[$ostream], 'InvalidArgumentException'],
      [$this, 'InvalidArgumentException'],
      [STDIN, 'InvalidArgumentException'],
      [function() use (&$ostream) { return $ostream; }, 'InvalidArgumentException'],
    ];
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForWriteToStream
   *
   * @covers ::writeToStream
   */
  public function testWriteToStream($input, $expected, $written, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $stream = fopen('php://memory', 'wb');
    $token = new DelayedExecutionToken(function() use(&$stream) {
      fclose($stream);
    });

    $writer = new CSVWriter($stream);

    $result = $this->call($writer, 'writeToStream', [$input]);
    $this->assertSame($expected, $result);

    $result = stream_get_contents($stream, -1, 0);
    $this->assertSame($written, $result);
  }

  /**
   * Data provider for the write to stream test.
   *
   * @return array
   */
  public function dataForWriteToStream()
  {
    return [
      [null, true, '', null],
      ['', true, '', null],
      ['data', true, 'data', null],
      [123, true, '123', null],
      [3.14159, true, '3.14159', null],
      [true, true, '1', null],
      [false, true, '', null],

      [['data'], false, '', null],
      [$this, false, '', null],
      [function() { return 'data'; }, false, '', null],
    ];
  }


  /**
   * @test
   *
   * @covers ::writeToStream
   */
  public function testWriteToStreamBadPipe()
  {
    $stream = fopen('php://memory', 'wb');

    $ortoken = override_function('fwrite', function($handle, $string, $length = null) use (&$stream, &$ortoken) {
      if ($handle === $stream) {
        return false;
      } else {
        return call_overridden_func($ortoken, null, $handle, $string, $length);
      }
    });

    $token = new DelayedExecutionToken(function() use(&$stream, &$ortoken) {
      fclose($stream);
      override_revert($ortoken);
    });


    $writer = new CSVWriter($stream);

    $result = $this->call($writer, 'writeToStream', ['test data']);
    $this->assertFalse($result);

    $result = stream_get_contents($stream, -1, 0);
    $this->assertEmpty($result);
  }

  /**
   * @test
   *
   * @covers ::writeToStream
   */
  public function testWriteToStreamDeadPipe()
  {
    $stream = fopen('php://memory', 'wb');

    $ortoken = override_function('fwrite', function($handle, $string, $length = null) use (&$stream, &$ortoken) {
      if ($handle === $stream) {
        return 0;
      } else {
        return call_overridden_func($ortoken, null, $handle, $string, $length);
      }
    });

    $token = new DelayedExecutionToken(function() use(&$stream, &$ortoken) {
      fclose($stream);
      override_revert($ortoken);
    });


    $writer = new CSVWriter($stream);

    $result = $this->call($writer, 'writeToStream', ['test data']);
    $this->assertFalse($result);

    $result = stream_get_contents($stream, -1, 0);
    $this->assertEmpty($result);
  }


////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForSerializeValue
   *
   * @covers ::serializeValue
   */
  public function testSerializeValue($input, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $stream = fopen('php://memory', 'wb');
    $token = new DelayedExecutionToken(function() use(&$stream) {
      fclose($stream);
    });

    $writer = new CSVWriter($stream);

    $result = $this->call($writer, 'serializeValue', [$input]);
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for the serializeValue test.
   *
   * @return array
   */
  public function dataForSerializeValue()
  {
    return [
      [null, '', null],
      ['', '', null],
      ['test value', 'test value', null],
      ['  test value  ', '  test value  ', null],
      ['  test, value  ', '"  test, value  "', null],
      ['  "test" value  ', '  ""test"" value  ', null],
      ['  "test", value  ', '"  ""test"", value  "', null],
      ["  \"test\"\r\nvalue  ", "\"  \"\"test\"\"\r\nvalue  \"", null],
      [123, '123', null],
      [1.61803, '1.61803', null],
      [true, '1', null],
      [false, '0', null],

      [['test value'], null, 'InvalidArgumentException'],
      [$this, null, 'InvalidArgumentException'],
      [STDOUT, null, 'InvalidArgumentException'],
      [function() { return 'test value'; }, null, 'InvalidArgumentException'],
    ];
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   *
   * @covers ::writeColumnDelimiter
   */
  public function testWriteColumnDelimiter()
  {
    $stream = fopen('php://memory', 'wb');
    $token = new DelayedExecutionToken(function() use(&$stream) {
      fclose($stream);
    });

    $writer = new CSVWriter($stream);
    $result = $writer->writeColumnDelimiter();
    $this->assertTrue($result);

    $result = stream_get_contents($stream, -1, 0);
    $this->assertSame(',', $result);
  }

  /**
   * @test
   *
   * @covers ::writeColumnDelimiter
   */
  public function testWriteColumnDelimiterImmediateStreamFailure()
  {
    $stream = fopen('php://memory', 'wb');
    $token = new DelayedExecutionToken(function() use(&$stream) {
      fclose($stream);
    });

    $writer = new CSVWriterTestImpl($stream);
    $writer->successfulWrites = 0;

    $result = $writer->writeColumnDelimiter();
    $this->assertFalse($result);

    $result = stream_get_contents($stream, -1, 0);
    $this->assertEmpty($result);
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   *
   * @covers ::writeRowDelimiter
   */
  public function testWriteRowDelimiter()
  {
    $stream = fopen('php://memory', 'wb');
    $token = new DelayedExecutionToken(function() use(&$stream) {
      fclose($stream);
    });

    $writer = new CSVWriter($stream);
    $result = $writer->writeRowDelimiter();
    $this->assertTrue($result);

    $result = stream_get_contents($stream, -1, 0);
    $this->assertSame("\r\n", $result);
  }

  /**
   * @test
   *
   * @covers ::writeRowDelimiter
   */
  public function testWriteRowDelimiterImmediateStreamFailure()
  {
    $stream = fopen('php://memory', 'wb');
    $token = new DelayedExecutionToken(function() use(&$stream) {
      fclose($stream);
    });

    $writer = new CSVWriterTestImpl($stream);
    $writer->successfulWrites = 0;

    $result = $writer->writeRowDelimiter();
    $this->assertFalse($result);

    $result = stream_get_contents($stream, -1, 0);
    $this->assertEmpty($result);
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForWriteValue
   *
   * @covers ::writeValue
   */
  public function testWriteValue($value, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $stream = fopen('php://memory', 'wb');
    $token = new DelayedExecutionToken(function() use(&$stream) {
      fclose($stream);
    });

    $writer = new CSVWriter($stream);
    $result = $writer->writeValue($value);
    $this->assertTrue($result);

    $result = stream_get_contents($stream, -1, 0);
    $this->assertSame($expected, $result);
  }

  /**
   * @test
   * @dataProvider dataForWriteValue
   *
   * @covers ::writeValue
   */
  public function testWriteValueImmediateStreamFailure($value, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $stream = fopen('php://memory', 'wb');
    $token = new DelayedExecutionToken(function() use(&$stream) {
      fclose($stream);
    });

    $writer = new CSVWriterTestImpl($stream);
    $writer->successfulWrites = 0;

    $result = $writer->writeValue($value);
    $this->assertFalse($result);

    $result = stream_get_contents($stream, -1, 0);
    $this->assertEmpty($result);
  }

  /**
   * Data provider for the writeValue test.
   *
   * @return array
   */
  public function dataForWriteValue()
  {
    return [
      [null, '', null],
      ['', '', null],
      ['test value', 'test value', null],
      ['test, value', '"test, value"', null],
      ['"test" value', '""test"" value', null],
      ["test\r\nvalue", "\"test\r\nvalue\"", null],
      [123, '123', null],
      [3.14159, '3.14159', null],
      [true, '1', null],
      [false, '0', null],

      [['test value'], null, 'InvalidArgumentException'],
      [$this, null, 'InvalidArgumentException'],
      [STDOUT, null, 'InvalidArgumentException'],
      [function() { return 'test value'; }, null, 'InvalidArgumentException'],
    ];
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForWriteRow
   *
   * @covers ::writeRow
   */
  public function testWriteRow($row, $count, $offset, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $stream = fopen('php://memory', 'wb');
    $token = new DelayedExecutionToken(function() use(&$stream) {
      fclose($stream);
    });

    $writer = new CSVWriter($stream);
    $result = $writer->writeRow($row, $count, $offset);
    $this->assertTrue($result);

    $result = stream_get_contents($stream, -1, 0);
    $this->assertSame($expected, $result);
  }

  /**
   * @test
   * @dataProvider dataForWriteRow
   *
   * @covers ::writeRow
   */
  public function testWriteRowImmediateStreamFailure($row, $count, $offset, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $stream = fopen('php://memory', 'wb');
    $token = new DelayedExecutionToken(function() use(&$stream) {
      fclose($stream);
    });

    $writer = new CSVWriterTestImpl($stream);
    $writer->successfulWrites = 0;

    $result = $writer->writeRow($row, $count, $offset);
    $this->assertFalse($result);

    $result = stream_get_contents($stream, -1, 0);
    $this->assertEmpty($result);
  }

  /**
   * @test
   * @dataProvider dataForWriteRow
   *
   * @covers ::writeRow
   */
  public function testWriteRowDelayedStreamFailure($row, $count, $offset, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $stream = fopen('php://memory', 'wb');
    $token = new DelayedExecutionToken(function() use(&$stream) {
      fclose($stream);
    });

    $writer = new CSVWriterTestImpl($stream);
    $writer->successfulWrites = 1;

    $result = $writer->writeRow($row, $count, $offset);

    if ($writer->writeCount > 1) {
      $this->assertFalse($result);
    } else {
      $this->assertTrue($result);
    }
  }

  /**
   * Data provider for the writeRow test.
   *
   * @return array
   */
  public function dataForWriteRow()
  {
    return [
      [null, null, null, "\r\n", null],
      ['', null, null, "\r\n", null],
      ['test value', null, null, "test value\r\n", null],
      ['test, value', null, null, "\"test, value\"\r\n", null],
      ['"test" value', null, null, "\"\"test\"\" value\r\n", null],
      ["test\r\nvalue", null, null, "\"test\r\nvalue\"\r\n", null],
      [123, null, null, "123\r\n", null],
      [3.14159, null, null, "3.14159\r\n", null],
      [true, null, null, "1\r\n", null],
      [false, null, null, "0\r\n", null],

      [['v1'], null, null, "v1\r\n", null],
      [['v1', 'v2', 'v3'], null, null, "v1,v2,v3\r\n", null],
      [['1,2,3,4,5', '6 "afraid" of 7', '7,8,9'], null, null, "\"1,2,3,4,5\",6 \"\"afraid\"\" of 7,\"7,8,9\"\r\n", null],

      [null, 3, null, ",,\r\n", null],
      ['', 3, null, ",,\r\n", null],
      ['v1', 3, null, "v1,,\r\n", null],
      [123, 3, null, "123,,\r\n", null],
      [1.61803, 3, null, "1.61803,,\r\n", null],
      [true, 3, null, "1,,\r\n", null],
      [false, 3, null, "0,,\r\n", null],
      [['v1'], 3, null, "v1,,\r\n", null],
      [[1, 2], 3, null, "1,2,\r\n", null],
      [[1, 2, 3, 4, 5], 3, null, "1,2,3\r\n", null],

      [null, null, 3, "\r\n", null],
      ['', null, 3, "\r\n", null],
      ['v1', null, 3, "\r\n", null],
      [321, null, 3, "\r\n", null],
      [2.71828, null, 3, "\r\n", null],
      [true, null, 3, "\r\n", null],
      [false, null, 3, "\r\n", null],
      [[1], null, 3, "\r\n", null],
      [[1,2], null, 3, "\r\n", null],
      [[1,2,3,4,5], null, 3, "4,5\r\n", null],

      [null, 3, 3, ",,\r\n", null],
      ['', 3, 3, ",,\r\n", null],
      ['v1', 3, 3, ",,\r\n", null],
      [321, 3, 3, ",,\r\n", null],
      [2.71828, 3, 3, ",,\r\n", null],
      [true, 3, 3, ",,\r\n", null],
      [false, 3, 3, ",,\r\n", null],
      [[1], 3, 3, ",,\r\n", null],
      [[1,2], 3, 3, ",,\r\n", null],
      [[1,2,3,4,5], 3, 3, "4,5,\r\n", null],

      [$this, null, null, null, 'InvalidArgumentException'],
      [STDOUT, null, null, null, 'InvalidArgumentException'],
      [function() { return 'test value'; }, null, null, null, 'InvalidArgumentException'],

      ['v1', '1', null, null, 'InvalidArgumentException'],
      ['v1', -1, null, null, 'InvalidArgumentException'],
      ['v1', 3.14159, null, null, 'InvalidArgumentException'],
      ['v1', true, null, null, 'InvalidArgumentException'],
      ['v1', false, null, null, 'InvalidArgumentException'],
      ['v1', [1], null, null, 'InvalidArgumentException'],
      ['v1', $this, null, null, 'InvalidArgumentException'],
      ['v1', STDOUT, null, null, 'InvalidArgumentException'],
      ['v1', function() { return 1; }, null, null, 'InvalidArgumentException'],

      ['v1', null, '1', null, 'InvalidArgumentException'],
      ['v1', null, -1, null, 'InvalidArgumentException'],
      ['v1', null, 3.14159, null, 'InvalidArgumentException'],
      ['v1', null, true, null, 'InvalidArgumentException'],
      ['v1', null, false, null, 'InvalidArgumentException'],
      ['v1', null, [1], null, 'InvalidArgumentException'],
      ['v1', null, $this, null, 'InvalidArgumentException'],
      ['v1', null, STDOUT, null, 'InvalidArgumentException'],
      ['v1', null, function() { return 1; }, null, 'InvalidArgumentException'],
    ];
  }

}

////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Gustavus\Utility\Test\CSV\CSVWriterTest;

use Gustavus\Utility\CSV\CSVWriter;


/**
 * Test implementation that allows access to protected methods and members.
 */
class CSVWriterTestImpl extends CSVWriter
{
  public $successfulWrites = null;
  public $writeCount = 0;

  public function writeToStream($data)
  {
    ++$this->writeCount;

    if (isset($this->successfulWrites)) {
      return ($this->successfulWrites-- > 0) ? parent::writeToStream($data) : false;
    } else {
      return parent::writeToStream($data);
    }
  }
}
