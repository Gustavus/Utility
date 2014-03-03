<?php
/**
 * IGBinarySerializerTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test\Serializer;

use Gustavus\Test\Test,
    Gustavus\Utility\Serializer\IGBinarySerializer,

    StdClass;



/**
 * Test suite for the IGBinarySerializer class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 *
 * @requires extension igbinary
 * @coversDefaultClass Gustavus\Utility\Serializer\IGBinarySerializer
 */
class IGBinarySerializerTest extends Test
{
  /**
   * @test
   * @covers ::getName
   */
  public function testGetName()
  {
    $serializer = new IGBinarySerializer();

    // Should always return the constant.
    $this->assertSame(IGBinarySerializer::SERIALIZER_NAME, $serializer->getName());
  }

  /**
   * @test
   * @covers ::isAvailable
   */
  public function testIsAvailable()
  {
    $serializer = new IGBinarySerializer();

    // IGBinarySerializer is always available.
    $this->assertTrue($serializer->isAvailable());
  }

  /**
   * @test
   * @covers ::pack
   * @covers ::unpack
   */
  public function testPackUnpack()
  {
    $input = [
      null,
      'string',
      123,
      -987,
      2.17828,
      -3.14159,
      true,
      false,
      ['array'],
      new stdClass(),
      STDOUT
    ];

    $output = [
      null,
      'string',
      123,
      -987,
      2.17828,
      -3.14159,
      true,
      false,
      ['array'],
      new stdClass(),
      0
    ];

    $serializer = new IGBinarySerializer();

    $serialized = $serializer->pack($input);
    $this->assertTrue(is_string($serialized));
    $this->assertNotEmpty($serialized);

    $unserialized = $serializer->unpack($serialized);
    $this->assertNotEmpty($unserialized);
    $this->assertEquals($output, $unserialized);
  }

  /**
   * @test
   * @covers ::pack
   * @expectedException RuntimeException
   */
  public function testPackWhenUnavailable()
  {
    $serializer = new TestIGBinarySerializer();
    $serialized = $serializer->pack('input'); // Kaboom.
  }

  /**
   * @test
   * @covers ::unpack
   * @expectedException RuntimeException
   */
  public function testUnpackWhenUnavailable()
  {
    $serializer = new TestIGBinarySerializer();
    $serialized = $serializer->unpack('input'); // Kaboom.
  }
}

////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Test implementation that claims it is not available
 */
class TestIGBinarySerializer extends IGBinarySerializer
{
  public function isAvailable() {
    return false;
  }
}
