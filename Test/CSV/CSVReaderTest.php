<?php
/**
 * CSVReaderTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test\CSV;

use Gustavus\Test\Test,
    Gustavus\Test\DelayedExecutionToken,

    Gustavus\Utility\CSV\CSVReader;



/**
 * Test suite for the CSVReader class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 *
 * @coversDefaultClass Gustavus\Utility\CSV\CSVReader
 */
class CSVReaderTest extends Test
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
  public function buildStream($data = null)
  {
    $stream = fopen('php://memory', 'rb+');

    $this->tokens[] = new DelayedExecutionToken(function() use (&$stream) {
      if (is_resource($stream)) {
        fclose($stream);
      }
    });

    if (!empty($data)) {
      fwrite($stream, $data);
      rewind($stream);
    }

    return $stream;
  }

  /**
   * Builds a new CSVReader instance to be used during testing.
   *
   * @param resource $stream
   *  The stream from which to read raw CSV data.
   *
   * @return CSVReader
   *  The new CSVReader instance to use during testing.
   */
  public function buildCSVReader($stream)
  {
    return new CSVReader($stream);
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForInstantiation
   *
   * @covers ::__construct
   */
  public function testInstantiation($stream, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $token = new DelayedExecutionToken(function() use(&$stream) {
      if (is_resource($stream) && $stream !== STDOUT && $stream !== STDIN) {
        fclose($stream);
      }
    });

    $reader = $this->buildCSVReader($stream);
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

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForGetStreamReader
   *
   * @covers ::getStreamReader
   */
  public function testGetStreamReader($stream, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $token = new DelayedExecutionToken(function() use(&$stream) {
      if (is_resource($stream) && $stream !== STDIN && $stream !== STDOUT) {
        fclose($stream);
      }
    });

    $builder = $this->getMockBuilder('\\Gustavus\\Utility\\CSV\\CSVReader')
      ->disableOriginalConstructor()
      ->setMethods(null);

    $reader = $builder->getMock();

    $result = $this->call($reader, 'getStreamReader', [$stream]);
    $this->assertInstanceOf('\\Gustavus\\Utility\\StreamReader', $result);
  }

  /**
   * Data provider for the instantiation test.
   *
   * @return array
   */
  public function dataForGetStreamReader()
  {
    $ostream = fopen('php://output', 'wb');
    $istream = fopen('php://memory', 'rb');
    $cstream = fopen('php://memory', 'rb');
    fclose($cstream);

    $ftp = ftp_connect('127.0.0.1');

    return [
      [$istream, null],
      [STDIN, false],

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

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   *
   * @covers ::isEOF
   */
  public function testIsEOFNoData()
  {
    $stream = $this->buildStream(null);
    $reader = $this->buildCSVReader($stream);

    $result = $reader->isEOF();
    $this->assertFalse($result);

    $result = $reader->readValue();
    $this->assertFalse($result);

    $result = $reader->isEOF();
    $this->assertTrue($result);
  }

  /**
   * @test
   *
   * @covers ::isEOF
   */
  public function testIsEOFWithData()
  {
    $stream = $this->buildStream('test, data');
    $reader = $this->buildCSVReader($stream);

    $result = $reader->isEOF();
    $this->assertFalse($result);

    $result = $reader->readValue();
    $this->assertNotFalse($result);

    $result = $reader->isEOF();
    $this->assertFalse($result);

    $result = $reader->readValue();
    $this->assertNotFalse($result);

    $result = $reader->isEOF();
    $this->assertTrue($result);
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForReadValue
   *
   * @covers ::readValue
   */
  public function testReadValue($data, $expected, $expectedeor, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $stream = $this->buildStream($data);
    $reader = $this->buildCSVReader($stream);

    $result = $reader->readValue($endofrow);
    $this->assertSame($expected, $result);
    $this->assertSame($expectedeor, $endofrow);
  }

  /**
   * Data provider for the readValue test.
   *
   * @return array
   */
  public function dataForReadValue()
  {
    return [
      [null, false, false, null],
      ['value', 'value', true, null],
      ['value1, value2', 'value1', false, null],
      ['"value1"', 'value1', true, null],
      ['"value1, value2", actual value 2', 'value1, value2', false, null],
      ['"much malformed!"   very recovered!, wow!', '"much malformed!"   very recovered!', false, null],
      ['va"ue', 'va"ue', true, null],
      ["value1\nvalue2", 'value1', true, null],
      ["value1\r\nvalue2", 'value1', true, null],
      ["  \"value1\nmore1\"  , value2", "value1\nmore1", false, null],
      ["  \"value1\r\nmore1\"  , value2", "value1\r\nmore1", false, null],
    ];
  }

  /**
   * @test
   * @dataProvider dataForConsecutiveValueReads
   *
   * @covers ::readValue
   */
  public function testConsecutiveValueReads($data, array $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $stream = $this->buildStream($data);
    $reader = $this->buildCSVReader($stream);

    foreach ($expected as $value) {
      $result = $reader->readValue();
      $this->assertSame($value, $result);
    }
  }

  /**
   * Data provider for the consecutive readValue test.
   *
   * @return array
   */
  public function dataForConsecutiveValueReads()
  {
    return [
      [null, [false, false, false], null],
      ['v1', ['v1', false, false, false], null],
      ['v1, v2, v3', ['v1', 'v2', 'v3', false, false, false], null],
      ["v1, v2, v3\r\nv4, v5, v6\r\n", ['v1', 'v2', 'v3', 'v4', 'v5', 'v6', false, false, false], null],
      ["v1, \"v\r\n2\", v3", ['v1', "v\r\n2", 'v3'], null],
      ["v1, \"v\r\n2\", v3\r\nv4, \"v\r\n5\", v6", ['v1', "v\r\n2", 'v3', 'v4', "v\r\n5", 'v6'], null],
    ];
  }


////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForReadRow
   *
   * @covers ::readRow
   */
  public function testReadRow($data, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $stream = $this->buildStream($data);
    $reader = $this->buildCSVReader($stream);

    $result = $reader->readRow();
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for the readRow test
   *
   * @return array
   */
  public function dataForReadRow()
  {
    return [
      [null, false, null],
      ['value', ['value'], null],
      ['value1, value2, value3', ['value1', 'value2', 'value3'], null],
      ["v1, v2, v3\n", ['v1', 'v2', 'v3'], null],
      ["v1, v2, v3\r\n", ['v1', 'v2', 'v3'], null],
      ["v1, v2, v3\nv4, v5, v6\n", ['v1', 'v2', 'v3'], null],
      ["v1, v2, v3\r\nv4, v5, v6\r\n", ['v1', 'v2', 'v3'], null],
      ["\"v1\", \"v\n2\", v3\nv4, v5, v6", ['v1', "v\n2", 'v3'], null],
      ["\"v1\", \"v\r\n2\", v3\r\nv4, v5, v6", ['v1', "v\r\n2", 'v3'], null]
    ];
  }

  /**
   * @test
   * @dataProvider dataForConsecutiveRowReads
   *
   * @covers ::readValue
   */
  public function testConsecutiveRowReads($data, array $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $stream = $this->buildStream($data);
    $reader = $this->buildCSVReader($stream);

    foreach ($expected as $row) {
      $result = $reader->readRow();
      $this->assertSame($row, $result);
    }
  }

  /**
   * Data provider for the consecutive readRow test.
   *
   * @return array
   */
  public function dataForConsecutiveRowReads()
  {
    return [
      [null, [false, false, false], null],
      ['v1', [['v1'], false, false, false], null],
      ['v1, v2, v3', [['v1', 'v2', 'v3'], false, false, false], null],
      ["v1, v2, v3\r\nv4, v5, v6\r\n", [['v1', 'v2', 'v3'], ['v4', 'v5', 'v6'], false, false, false], null],
      ["v1, \"v\r\n2\", v3", [['v1', "v\r\n2", 'v3']], null],
      ["v1, \"v\r\n2\", v3\r\nv4, \"v\r\n5\", v6", [['v1', "v\r\n2", 'v3'], ['v4', "v\r\n5", 'v6']], null],
    ];
  }
////////////////////////////////////////////////////////////////////////////////////////////////////

}
