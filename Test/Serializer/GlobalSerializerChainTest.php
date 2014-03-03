<?php
/**
 * GlobalSerializerChainTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test\Serializer;

use Gustavus\Test\Test,
    Gustavus\Test\DelayedExecutionToken,

    Gustavus\Utility\Serializer\Serializer,
    Gustavus\Utility\Serializer\GlobalSerializerChain,
    Gustavus\Utility\Serializer\SerializerChain,

    StdClass;



/**
 * Test suite for the GlobalSerializerChain class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 *
 * @coversDefaultClass Gustavus\Utility\Serializer\GlobalSerializerChain
 */
class GlobalSerializerChainTest extends Test
{
  /**
   * @test
   * @covers ::getSerializerChain
   */
  public function testGetSerializerChain()
  {
    $chain1 = GlobalSerializerChain::getSerializerChain();
    $this->assertInstanceOf('\\Gustavus\\Utility\\Serializer\\SerializerChain', $chain1);

    $chain2 = GlobalSerializerChain::getSerializerChain();
    $this->assertInstanceOf('\\Gustavus\\Utility\\Serializer\\SerializerChain', $chain2);
    $this->assertSame($chain1, $chain2);
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

    $chain = new GlobalSerializerChain();

    $result = $chain->getSerializer($serializer);

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

      // Default serializer should handle anything not explicitly registered
      ['bacon', '\\Gustavus\\Utility\\Serializer\\PHPSerializer', null],

      [123, null, 'InvalidArgumentException'],
      [1.23, null, 'InvalidArgumentException'],
      [true, null, 'InvalidArgumentException'],
      [false, null, 'InvalidArgumentException'],
      [['array'], null, 'InvalidArgumentException'],
      [new StdClass(), null, 'InvalidArgumentException'],
      [STDOUT, null, 'InvalidArgumentException'],
    ];
  }


////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForPack
   *
   * @covers ::pack
   */
  public function testPack($serializers, $default, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    // We have to modify the serializers of the global serializer chain and clean it up later.
    $chain = GlobalSerializerChain::getSerializerChain();
    $this->assertInstanceOf('\\Gustavus\\Utility\\Serializer\\SerializerChain', $chain);

    $original = $chain->getSerializers();
    $orgdefault = $chain->getDefaultSerializer();

    $chain->setSerializers($serializers);
    if ($default) {
      $chain->setDefaultSerializer($default);
    } else {
      $chain->clearDefaultSerializer();
    }

    $token = new DelayedExecutionToken(function() use (&$chain, &$original, &$orgdefault) {
      $chain->setSerializers($original);

      if ($orgdefault) {
        $chain->setDefaultSerializer($orgdefault);
      } else {
        $chain->clearDefaultSerializer();
      }
    });


    // Do actual test stuff
    $result = GlobalSerializerChain::pack('dummy');
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for the pack tests.
   *
   * @return array
   */
  public function dataForPack()
  {
    $available = [
      new TestSerializer('ats1', true, function($v) { return 'ats1'; }),
      new TestSerializer('ats2', true, function($v) { return 'ats2'; }),
      new TestSerializer('ats3', true, function($v) { return 'ats3'; }),
    ];

    $unavailable = [
      new TestSerializer('uts1', false, function($v) { return 'uts1'; }),
      new TestSerializer('uts2', false, function($v) { return 'uts2'; }),
      new TestSerializer('uts3', false, function($v) { return 'uts3'; }),
    ];

    return [
      [null, null, null, 'RuntimeException'],
      [$available[0], null, '::[ats1]::ats1', null],
      [$available, null, '::[ats1]::ats1', null],
      [null, $available[0], '::[ats1]::ats1', null],
      [[$available[1], $available[2]], $available[0], '::[ats2]::ats2', null],

      [$unavailable[0], null, null, 'RuntimeException'],
      [null, $unavailable[0], null, 'RuntimeException'],
      [$unavailable, $unavailable[0], null, 'RuntimeException'],

      [$unavailable, $available[0], '::[ats1]::ats1', null],
      [$available[0], $unavailable[0], '::[ats1]::ats1', null],
      [$available, $unavailable[0], '::[ats1]::ats1', null],

      [[$unavailable[0], $available[0]], null, '::[ats1]::ats1', null],
      [[$unavailable[0], $available[1]], $available[0], '::[ats2]::ats2', null],
      [[$unavailable[0], $available[1]], $unavailable[0], '::[ats2]::ats2', null],
    ];
  }




  /**
   * @test
   * @dataProvider dataForUnpack
   *
   * @covers ::unpack
   */
  public function testUnpack($serializers, $default, $data, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    // We have to modify the serializers of the global serializer chain and clean it up later.
    $chain = GlobalSerializerChain::getSerializerChain();
    $this->assertInstanceOf('\\Gustavus\\Utility\\Serializer\\SerializerChain', $chain);

    $original = $chain->getSerializers();
    $orgdefault = $chain->getDefaultSerializer();

    $chain->setSerializers($serializers);
    if ($default) {
      $chain->setDefaultSerializer($default);
    } else {
      $chain->clearDefaultSerializer();
    }

    $token = new DelayedExecutionToken(function() use (&$chain, &$original, &$orgdefault) {
      $chain->setSerializers($original);

      if ($orgdefault) {
        $chain->setDefaultSerializer($orgdefault);
      } else {
        $chain->clearDefaultSerializer();
      }
    });


    // Do actual test stuff
    $result = GlobalSerializerChain::unpack($data);
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for the unpack tests.
   *
   * @return array
   */
  public function dataForUnpack()
  {
    $available = [
      new TestSerializer('ats1', true, null, function($v) { return 'ats1'; }),
      new TestSerializer('ats2', true, null, function($v) { return 'ats2'; }),
      new TestSerializer('ats3', true, null, function($v) { return 'ats3'; }),
    ];

    $unavailable = [
      new TestSerializer('uts1', false, null, function($v) { return 'uts1'; }),
      new TestSerializer('uts2', false, null, function($v) { return 'uts2'; }),
      new TestSerializer('uts3', false, null, function($v) { return 'uts3'; }),
    ];

    $datasets = [
      [null, null, null, null, 'RuntimeException'],

      [$available[0], null, '::[ats1]::ats1', 'ats1', null],
      [$available, null, '::[ats1]::ats1', 'ats1', null],
      [$unavailable[0], null, '::[ats1]::ats1', null, 'RuntimeException'],
      [$unavailable, null, '::[ats1]::ats1', null, 'RuntimeException'],
      [[$available[1], $unavailable[1]], null, '::[ats1]::ats1', null, 'RuntimeException'],
      [[$unavailable[1], $available[1]], null, '::[ats1]::ats1', null, 'RuntimeException'],

      [$available[0], $available[2], '::[ats1]::ats1', 'ats1', null],
      [$available, $available[2], '::[ats1]::ats1', 'ats1', null],
      [$unavailable[0], $available[2], '::[ats1]::ats1', 'ats3', null],
      [$unavailable, $available[2], '::[ats1]::ats1', 'ats3', null],
      [[$available[1], $unavailable[1]], $available[2], '::[ats1]::ats1', 'ats3', null],
      [[$unavailable[1], $available[1]], $available[2], '::[ats1]::ats1', 'ats3', null],

      [$available[0], $unavailable[2], '::[ats1]::ats1', 'ats1', null],
      [$available, $unavailable[2], '::[ats1]::ats1', 'ats1', null],
      [$unavailable[0], $unavailable[2], '::[ats1]::ats1', null, 'RuntimeException'],
      [$unavailable, $unavailable[2], '::[ats1]::ats1', null, 'RuntimeException'],
      [[$available[1], $unavailable[1]], $unavailable[2], '::[ats1]::ats1', null, 'RuntimeException'],
      [[$unavailable[1], $available[1]], $unavailable[2], '::[ats1]::ats1', null, 'RuntimeException'],


      [$available[0], null, '::[ats2]::ats2', null, 'RuntimeException'],
      [$available, null, '::[ats2]::ats2', 'ats2', null],
      [$unavailable[0], null, '::[ats2]::ats2', null, 'RuntimeException'],
      [$unavailable, null, '::[ats2]::ats2', null, 'RuntimeException'],
      [[$available[1], $unavailable[1]], null, '::[ats2]::ats2', 'ats2', null],
      [[$unavailable[1], $available[1]], null, '::[ats2]::ats2', 'ats2', null],

      [$available[0], $available[2], '::[ats2]::ats2', 'ats3', null],
      [$available, $available[2], '::[ats2]::ats2', 'ats2', null],
      [$unavailable[0], $available[2], '::[ats2]::ats2', 'ats3', null],
      [$unavailable, $available[2], '::[ats2]::ats2', 'ats3', null],
      [[$available[1], $unavailable[1]], $available[2], '::[ats2]::ats2', 'ats2', null],
      [[$unavailable[1], $available[1]], $available[2], '::[ats2]::ats2', 'ats2', null],

      [$available[0], $unavailable[2], '::[ats2]::ats2', null, 'RuntimeException'],
      [$available, $unavailable[2], '::[ats2]::ats2', 'ats2', null],
      [$unavailable[0], $unavailable[2], '::[ats2]::ats2', null, 'RuntimeException'],
      [$unavailable, $unavailable[2], '::[ats2]::ats2', null, 'RuntimeException'],
      [[$available[1], $unavailable[1]], $unavailable[2], '::[ats2]::ats2', 'ats2', null],
      [[$unavailable[1], $available[1]], $unavailable[2], '::[ats2]::ats2', 'ats2', null],
    ];

    foreach ([null, '', 'randomdata', 123, 3.14159, true, false, ['randomdata'], new StdClass(), STDOUT] as $input) {
      $datasets[] = [$available[0], null, $input, null, 'RuntimeException'];
      $datasets[] = [$available, null, $input, null, 'RuntimeException'];
      $datasets[] = [$unavailable[0], null, $input, null, 'RuntimeException'];
      $datasets[] = [$unavailable, null, $input, null, 'RuntimeException'];
      $datasets[] = [[$available[1], $unavailable[1]], null, $input, null, 'RuntimeException'];
      $datasets[] = [[$unavailable[1], $available[1]], null, $input, null, 'RuntimeException'];

      $datasets[] = [$available[0], $available[2], $input, 'ats3', null];
      $datasets[] = [$available, $available[2], $input, 'ats3', null];
      $datasets[] = [$unavailable[0], $available[2], $input, 'ats3', null];
      $datasets[] = [$unavailable, $available[2], $input, 'ats3', null];
      $datasets[] = [[$available[1], $unavailable[1]], $available[2], $input, 'ats3', null];
      $datasets[] = [[$unavailable[1], $available[1]], $available[2], $input, 'ats3', null];

      $datasets[] = [$available[0], $unavailable[2], $input, null, 'RuntimeException'];
      $datasets[] = [$available, $unavailable[2], $input, null, 'RuntimeException'];
      $datasets[] = [$unavailable[0], $unavailable[2], $input, null, 'RuntimeException'];
      $datasets[] = [$unavailable, $unavailable[2], $input, null, 'RuntimeException'];
      $datasets[] = [[$available[1], $unavailable[1]], $unavailable[2], $input, null, 'RuntimeException'];
      $datasets[] = [[$unavailable[1], $available[1]], $unavailable[2], $input, null, 'RuntimeException'];
    }

    return $datasets;
  }
}
