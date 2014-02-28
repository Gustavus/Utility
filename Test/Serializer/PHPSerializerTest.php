<?php
/**
 * PHPSerializerTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test\Serializer;

use Gustavus\Test\Test,
    Gustavus\Utility\Serializer\PHPSerializer,

    StdClass;



/**
 * Test suite for the PHPSerializer class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 *
 * @coversDefaultClass Gustavus\Utility\Serializer\PHPSerializer
 */
class PHPSerializerTest extends Test
{
  /**
   * @test
   * @covers ::getName
   */
  public function testGetName()
  {
    $serializer = new PHPSerializer();

    // Should always return the constant.
    $this->assertSame(PHPSerializer::SERIALIZER_NAME, $serializer->getName());
  }

  /**
   * @test
   * @covers ::isAvailable
   */
  public function testIsAvailable()
  {
    $serializer = new PHPSerializer();

    // PHPSerializer is always available.
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

    $serializer = new PHPSerializer();

    $serialized = $serializer->pack($input);
    $this->assertTrue(is_string($serialized));
    $this->assertNotEmpty($serialized);

    $unserialized = $serializer->unpack($serialized);
    $this->assertNotEmpty($unserialized);
    $this->assertEquals($output, $unserialized);
  }

  /**
   * @test
   * @covers ::unpack
   */
  public function testUnpackWithMalformedData()
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

    $serializer = new PHPSerializer();

    $serialized = serialize($input);

    // This should fail without our identifier.
    $unserialized = @$serializer->unpack($serialized);
    $this->assertFalse($unserialized);
  }

}
