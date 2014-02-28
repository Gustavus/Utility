<?php
/**
 * SerializerFactoryTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test\Serializer;

use Gustavus\Test\Test,
    Gustavus\Utility\Serializer\SerializerFactory,

    StdClass;



/**
 * Test suite for the SerializerFactory class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 *
 * @requires extension igbinary
 * @coversDefaultClass Gustavus\Utility\Serializer\SerializerFactory
 */
class SerializerFactoryTest extends Test
{
  /**
   * @test
   * @covers ::__construct
   */
  public function testInstantiation()
  {
    $factory = new SerializerFactory();

    // Nothing special to do here. Yet.
  }

  /**
   * @test
   * @dataProvider dataForGetSerializer
   *
   * @covers ::getSerializer
   */
  public function testGetSerializer($serializer, $expectedclass, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $factory = new SerializerFactory();

    $result = $factory->getSerializer($serializer);

    if ($expectedclass) {
      $this->assertInstanceOf($expectedclass, $result);
    } else {
      $this->assertNull($result);
    }
  }

  /**
   * Data provider for the getSerializer test.
   *
   * @return array
   */
  public function dataForGetSerializer()
  {
    return [
      [null, '\\Gustavus\\Utility\\Serializer\\Serializer', null],
      ['php', '\\Gustavus\\Utility\\Serializer\\PHPSerializer', null],
      ['bacon', null, null],

      [123, null, 'InvalidArgumentException'],
      [1.23, null, 'InvalidArgumentException'],
      [true, null, 'InvalidArgumentException'],
      [false, null, 'InvalidArgumentException'],
      [['array'], null, 'InvalidArgumentException'],
      [new StdClass(), null, 'InvalidArgumentException'],
      [STDOUT, null, 'InvalidArgumentException'],
    ];
  }


}
