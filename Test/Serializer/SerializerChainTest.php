<?php
/**
 * SerializerChainTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test\Serializer;

use Gustavus\Test\Test,
    Gustavus\Utility\Serializer\Serializer,
    Gustavus\Utility\Serializer\SerializerChain,

    StdClass;



/**
 * Test suite for the SerializerChain class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 *
 * @coversDefaultClass Gustavus\Utility\Serializer\SerializerChain
 */
class SerializerChainTest extends Test
{
  /**
   * @test
   *
   * @covers ::__construct
   */
  public function testSetupDefaultChain()
  {
    return new SerializerChain();
  }

  /**
   * @test
   * @depends testSetupDefaultChain
   *
   * @covers ::getSerializers
   */
  public function testGetSerializersBeforePopulation($chain)
  {
    $result = $chain->getSerializers();
    $this->assertTrue(is_array($result));
    $this->assertEmpty($result);

    return $chain;
  }

  /**
   * @test
   * @depends testGetSerializersBeforePopulation
   *
   * @covers ::addSerializer
   * @covers ::addSerializers
   */
  public function testAddSerializers($chain)
  {
    $serializers = [
      new TestSerializer('ts1'),
      new TestSerializer('ts2'),
      new TestSerializer('ts3'),
      new TestSerializer('ts4'),
      new TestSerializer('ts5'),
    ];

    $result = $chain->addSerializer($serializers[0]);
    $this->assertTrue($result);

    $result = $chain->addSerializer($serializers[0]);
    $this->assertFalse($result);

    $result = $chain->addSerializers($serializers[1]);
    $this->assertSame(1, $result);

    $result = $chain->addSerializers($serializers[1]);
    $this->assertSame(0, $result);

    $result = $chain->addSerializers($serializers);
    $this->assertSame(count($serializers) - 2, $result);

    $result = $chain->addSerializers($serializers);
    $this->assertSame(0, $result);

    return [$chain, $serializers];
  }

  /**
   * @test
   * @depends testAddSerializers
   *
   * @covers ::hasSerializer
   * @covers ::getSerializer
   * @covers ::getSerializers
   */
  public function testGetSerializersAfterPopulation(array $testdata)
  {
    list($chain, $serializers) = $testdata;

    $result = $chain->getSerializers();
    $this->assertTrue(is_array($result));
    $this->assertNotEmpty($result);
    $this->assertCount(count($serializers), $result);

    for ($i = 0; $i < count($serializers); ++$i) {
      $this->assertArrayHasKey($i, $result);
      $this->assertSame($result[$i], $serializers[$i]);
    }

    $result = $chain->getSerializer();
    $this->assertSame($serializers[0], $result);

    for ($i = 0; $i < count($serializers); ++$i) {
      $result = $chain->getSerializer($serializers[$i]->getName());
      $this->assertSame($serializers[$i], $result);
    }

    // Build a serializer name from all the names of the serializers to ensure we have a name that
    // can't possibly exist in the chain.
    $name = '';
    foreach ($serializers as $serializer) {
      $name .= $serializer->getName();
    }

    $result = $chain->getSerializer($name);
    $this->assertNull($result);

    return $testdata;
  }

  /**
   * @test
   * @depends testGetSerializersAfterPopulation
   *
   * @covers ::hasSerializer
   */
  public function testHasSerializer(array $testdata)
  {
    list($chain, $serializers) = $testdata;

    foreach ($serializers as $serializer) {
      // Ensure the chain has this serializer
      $result = $chain->hasSerializer($serializer);
      $this->assertTrue($result);

      // Ensure the check is locked to instances, not names
      $result = $chain->hasSerializer(new TestSerializer($serializer->getName()));
      $this->assertFalse($result);
    }

    // One more should-not-exist test.
    $result = $chain->hasSerializer(new TestSerializer());
    $this->assertFalse($result);

    return $testdata;
  }

  /**
   * @test
   * @depends testHasSerializer
   *
   * @covers ::setSerializer
   * @covers ::setSerializers
   */
  public function testAssignSerializers(array $testdata)
  {
    list($chain, $serializers) = $testdata;

    $serializers2 = [
      new TestSerializer('tsA'),
      new TestSerializer('tsB'),
      new TestSerializer('tsC'),
      new TestSerializer('tsD'),
      new TestSerializer('tsE'),
    ];

    $result = $chain->setSerializer($serializers2[0]);
    $this->assertTrue($result);

    $result = $chain->setSerializer($serializers2[0]);
    $this->assertTrue($result);

    $result = $chain->setSerializers($serializers2[1]);
    $this->assertSame(1, $result);

    $result = $chain->setSerializers($serializers2[1]);
    $this->assertSame(1, $result);

    $result = $chain->setSerializers($serializers2);
    $this->assertSame(count($serializers2), $result);

    $result = $chain->setSerializers($serializers2);
    $this->assertSame(count($serializers2), $result);

    return [$chain, $serializers2];
  }

  /**
   * @test
   * @depends testAssignSerializers
   *
   * @covers ::getSerializer
   * @covers ::getSerializers
   */
  public function testGetSerializersAfterAssignment(array $testdata)
  {
    list($chain, $serializers) = $testdata;

    $result = $chain->getSerializers();
    $this->assertTrue(is_array($result));
    $this->assertNotEmpty($result);
    $this->assertCount(count($serializers), $result);

    for ($i = 0; $i < count($serializers); ++$i) {
      $this->assertArrayHasKey($i, $result);
      $this->assertSame($result[$i], $serializers[$i]);
    }

    $result = $chain->getSerializer();
    $this->assertSame($serializers[0], $result);

    for ($i = 0; $i < count($serializers); ++$i) {
      $result = $chain->getSerializer($serializers[$i]->getName());
      $this->assertSame($serializers[$i], $result);
    }

    // Build a serializer name from all the names of the serializers to ensure we have a name that
    // can't possibly exist in the chain.
    $name = '';
    foreach ($serializers as $serializer) {
      $name .= $serializer->getName();
    }

    $result = $chain->getSerializer($name);
    $this->assertNull($result);

    return $testdata;
  }

  /**
   * @test
   * @depends testGetSerializersAfterAssignment
   *
   * @covers ::removeSerializer
   * @covers ::removeSerializers
   */
  public function testRemoveSerializers(array $testdata)
  {
    list($chain, $serializers) = $testdata;

    $result = $chain->removeSerializer($serializers[0]);
    $this->assertTrue($result);

    $result = $chain->removeSerializer($serializers[0]);
    $this->assertFalse($result);

    $result = $chain->removeSerializers($serializers[1]);
    $this->assertSame(1, $result);

    $result = $chain->removeSerializers($serializers[1]);
    $this->assertSame(0, $result);

    $result = $chain->removeSerializers($serializers);
    $this->assertSame(count($serializers) - 2, $result);

    $result = $chain->removeSerializers($serializers);
    $this->assertSame(0, $result);

    return $testdata;
  }

  /**
   * @test
   * @depends testRemoveSerializers
   *
   * @covers ::getSerializers
   */
  public function testGetSerializersAfterRemoval(array $testdata)
  {
    list($chain, $serializers) = $testdata;

    $result = $chain->getSerializers();
    $this->assertTrue(is_array($result));
    $this->assertEmpty($result);
  }


  /**
   * @test
   *
   * @covers ::removeAllSerializers
   */
  public function testRemoveAllSerializers()
  {
    $chain = new SerializerChain([new TestSerializer('ts1'), new TestSerializer('ts2'), new TestSerializer('ts3')]);

    // Verify we have some serializers to remove...
    $result = $chain->getSerializers();
    $this->assertTrue(is_array($result));
    $this->assertCount(3, $result);

    // Remove
    $result = $chain->removeAllSerializers();
    $this->assertSame(3, $result);

    // Verify we no longer have any serializers
    $result = $chain->getSerializers();
    $this->assertTrue(is_array($result));
    $this->assertEmpty($result);
  }


////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   *
   * @covers ::getDefaultSerializer
   * @covers ::setDefaultSerializer
   * @covers ::clearDefaultSerializer
   */
  public function testGetSetClearDefaultSerializer()
  {
    $chain = new SerializerChain();
    $serializier = new TestSerializer();

    $result = $chain->getDefaultSerializer();
    $this->assertNull($result);

    $result = $chain->setDefaultSerializer($serializier);
    $this->assertSame($chain, $result);

    $result = $chain->getDefaultSerializer();
    $this->assertSame($serializier, $result);

    $result = $chain->clearDefaultSerializer();
    $this->assertSame($chain, $result);

    $result = $chain->getDefaultSerializer();
    $this->assertNull($result);
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForInstantiation
   *
   * @covers ::__construct
   */
  public function testInstantiation($serializers, $default, $expserializers, $expdefault, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $chain = new SerializerChain($serializers, $default);

    $this->assertEquals($expserializers, $chain->getSerializers());
    $this->assertEquals($expdefault, $chain->getDefaultSerializer());

    return $chain;
  }

  /**
   * Data provider for the instantiation tests.
   *
   * @return array
   */
  public function dataForInstantiation()
  {
    $serializers = [
      new TestSerializer('ts1'),
      new TestSerializer('ts2'),
      new TestSerializer('ts3'),
      new TestSerializer('ts4'),
      new TestSerializer('ts5'),
    ];

    return [
      [null, null, [], null, null],
      [$serializers[0], null, [$serializers[0]], null, null],
      [$serializers, null, $serializers, null, null],

      [null, $serializers[0], [], $serializers[0], null],
      [$serializers[0], $serializers[0], [$serializers[0]], $serializers[0], null],
      [$serializers, $serializers[0], $serializers, $serializers[0], null],
    ];
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForGetSerializer
   *
   * @covers ::getSerializer
   */
  public function testGetSerializer($serializers, $default, $serializer, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $chain = new SerializerChain($serializers, $default);

    $result = $chain->getSerializer($serializer);
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for the getSerializer test.
   *
   * @return array
   */
  public function dataForGetSerializer()
  {
    $available = [
      new TestSerializer('ats1', true),
      new TestSerializer('ats2', true),
      new TestSerializer('ats3', true),
    ];

    $unavailable = [
      new TestSerializer('uts1', false),
      new TestSerializer('uts2', false),
      new TestSerializer('uts3', false),
    ];

    return [
      [null, null, null, null, null],
      [null, null, 'test', null, null],

      [$available, null, null, $available[0], null],
      [null, $available[0], null, $available[0], null],
      [null, $available[0], 'test', $available[0], null],

      [$unavailable, null, null, null, null],
      [null, $unavailable[0], null, null, null],
      [null, $unavailable[0], 'test', null, null],
      [$unavailable, $unavailable[0], 'test', null, null],

      [[$unavailable[0], $available[0]], null, null, $available[0], null],
      [$available, null, 'ats2', $available[1], null],
      [$unavailable, $available[2], null, $available[2], null],

      [[$unavailable[0], $unavailable[1], $available[2]], $available[0], null, $available[2], null],
      [[$unavailable[0], $unavailable[1], $available[2]], $available[0], 'test', $available[0], null],
      [[$unavailable[0], $unavailable[1], $available[2]], $unavailable[0], 'test', null, null],
      [[$unavailable[0], $unavailable[1], $available[2]], $available[0], 'uts1', $available[0], null],
      [[$unavailable[0], $unavailable[1], $available[2]], null, 'uts1', null, null],
      [[$unavailable[0], $unavailable[1], $available[2]], $unavailable[0], 'uts1', null, null],

      [null, null, 123, null, 'InvalidArgumentException'],
      [null, null, 1.23, null, 'InvalidArgumentException'],
      [null, null, true, null, 'InvalidArgumentException'],
      [null, null, false, null, 'InvalidArgumentException'],
      [null, null, ['array'], null, 'InvalidArgumentException'],
      [null, null, new StdClass(), null, 'InvalidArgumentException'],
      [null, null, STDOUT, null, 'InvalidArgumentException'],

      [$available[0], null, 123, null, 'InvalidArgumentException'],
      [$available[0], null, 1.23, null, 'InvalidArgumentException'],
      [$available[0], null, true, null, 'InvalidArgumentException'],
      [$available[0], null, false, null, 'InvalidArgumentException'],
      [$available[0], null, ['array'], null, 'InvalidArgumentException'],
      [$available[0], null, new StdClass(), null, 'InvalidArgumentException'],
      [$available[0], null, STDOUT, null, 'InvalidArgumentException'],

      [null, $available[0], 123, null, 'InvalidArgumentException'],
      [null, $available[0], 1.23, null, 'InvalidArgumentException'],
      [null, $available[0], true, null, 'InvalidArgumentException'],
      [null, $available[0], false, null, 'InvalidArgumentException'],
      [null, $available[0], ['array'], null, 'InvalidArgumentException'],
      [null, $available[0], new StdClass(), null, 'InvalidArgumentException'],
      [null, $available[0], STDOUT, null, 'InvalidArgumentException'],

      [$available, $available[0], 123, null, 'InvalidArgumentException'],
      [$available, $available[0], 1.23, null, 'InvalidArgumentException'],
      [$available, $available[0], true, null, 'InvalidArgumentException'],
      [$available, $available[0], false, null, 'InvalidArgumentException'],
      [$available, $available[0], ['array'], null, 'InvalidArgumentException'],
      [$available, $available[0], new StdClass(), null, 'InvalidArgumentException'],
      [$available, $available[0], STDOUT, null, 'InvalidArgumentException'],
    ];
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   *
   * @covers ::getName()
   */
  public function testGetName()
  {
    $chain = new SerializerChain();

    $result = $chain->getName();
    $this->assertSame(SerializerChain::SERIALIZER_NAME, $result);
  }

  /**
   * @test
   * @dataProvider dataForIsAvailable
   *
   * @covers ::isAvailable
   */
  public function testIsAvailable($serializers, $default, $expected)
  {
    $chain = new SerializerChain($serializers, $default);

    $result = $chain->isAvailable();
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for the isAvailable test.
   *
   * @return array
   */
  public function DataForIsAvailable()
  {
    $available = [
      new TestSerializer('ats1', true),
      new TestSerializer('ats2', true),
      new TestSerializer('ats3', true),
    ];

    $unavailable = [
      new TestSerializer('uts1', false),
      new TestSerializer('uts2', false),
      new TestSerializer('uts3', false),
    ];

    return [
      [null, null, false],
      [$available[0], null, true],
      [$available, null, true],
      [null, $available[0], true],

      [$unavailable[0], null, false],
      [null, $unavailable[0], false],
      [$unavailable, $unavailable[0], false],

      [$unavailable, $available[0], true],
      [$available[0], $unavailable[0], true],
      [$available, $unavailable[0], true],

      [[$unavailable[0], $available[0]], null, true],
      [[$unavailable[0], $available[0]], $available[0], true],
      [[$unavailable[0], $available[0]], $unavailable[0], true],
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

    $chain = new SerializerChain($serializers, $default);

    $result = $chain->pack('dummy');
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

    $chain = new SerializerChain($serializers, $default);

    $result = $chain->unpack($data);
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

