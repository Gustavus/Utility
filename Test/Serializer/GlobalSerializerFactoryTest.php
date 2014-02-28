<?php
/**
 * GlobalSerializerFactoryTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test\Serializer;

use Gustavus\Test\Test,
    Gustavus\Utility\Serializer\GlobalSerializerFactory,
    Gustavus\Utility\Serializer\SerializerFactory,

    StdClass;



/**
 * Test suite for the GlobalSerializerFactory class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 *
 * @requires extension igbinary
 * @coversDefaultClass Gustavus\Utility\Serializer\GlobalSerializerFactory
 */
class GlobalSerializerFactoryTest extends Test
{
  /**
   * @test
   * @covers ::getFactory
   */
  public function testGetFactory()
  {
    $factory1 = GlobalSerializerFactory::getFactory();
    $this->assertInstanceOf('\\Gustavus\\Utility\\Serializer\\SerializerFactory', $factory1);

    $factory2 = GlobalSerializerFactory::getFactory();
    $this->assertInstanceOf('\\Gustavus\\Utility\\Serializer\\SerializerFactory', $factory2);
    $this->assertSame($factory1, $factory2);
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

    $factory = new GlobalSerializerFactory();

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
