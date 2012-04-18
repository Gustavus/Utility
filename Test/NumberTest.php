<?php
/**
 * @package Utility
 * @subpackage Test
 */

namespace Gustavus\Utility\Test;
use Gustavus\Utility;

/**
 * @package Utility
 * @subpackage Test
 */
class NumberTest extends \Gustavus\Test\Test
{
  /**
   * @var Utility\Number
   */
  private $number;

  /**
   * sets up the object for each test
   * @return void
   */
  public function setUp()
  {
    $this->number = new \Gustavus\Test\TestObject(new Utility\Number());
  }

  /**
   * destructs the object after each test
   * @return void
   */
  public function tearDown()
  {
    unset($this->number);
  }

  /**
   * @test
   */
  public function QuantityWithUnspecifiedShowcount()
  {
    $this->assertSame('-10,000.15 tests', $this->number->quantity(-10000.15, 'test', 'tests'));
    $this->assertSame('-10,000.1 tests', $this->number->quantity(-10000.1, 'test', 'tests'));
    $this->assertSame('-10,000 tests', $this->number->quantity(-10000, 'test', 'tests'));
    $this->assertSame('-2 tests', $this->number->quantity(-2, 'test', 'tests'));
    $this->assertSame('-1.15 tests', $this->number->quantity(-1.15, 'test', 'tests'));
    $this->assertSame('-1.1 tests', $this->number->quantity(-1.1, 'test', 'tests'));
    $this->assertSame('-1 tests', $this->number->quantity(-1, 'test', 'tests'));

    $this->assertSame('0 tests', $this->number->quantity(0, 'test', 'tests'));

    $this->assertSame('1 test', $this->number->quantity(1, 'test', 'tests'));
    $this->assertSame('1.1 tests', $this->number->quantity(1.1, 'test', 'tests'));
    $this->assertSame('1.15 tests', $this->number->quantity(1.15, 'test', 'tests'));
    $this->assertSame('2 tests', $this->number->quantity(2, 'test', 'tests'));
    $this->assertSame('10,000 tests', $this->number->quantity(10000, 'test', 'tests'));
    $this->assertSame('10,000.1 tests', $this->number->quantity(10000.1, 'test', 'tests'));
    $this->assertSame('10,000.15 tests', $this->number->quantity(10000.15, 'test', 'tests'));
  }

  /**
   * @test
   */
  public function QuantityWithPluralShowcount()
  {
    $this->assertSame('-1 tests', $this->number->quantity(-1, 'test', 'tests', 'plural'));
    $this->assertSame('0 tests', $this->number->quantity(0, 'test', 'tests', 'plural'));
    $this->assertSame('test', $this->number->quantity(1, 'test', 'tests', 'plural'));
    $this->assertSame('1.1 tests', $this->number->quantity(1.1, 'test', 'tests', 'plural'));
  }

  /**
   * @test
   */
  public function QuantityWithNoneShowcount()
  {
    $this->assertSame('tests', $this->number->quantity(-1, 'test', 'tests', 'none'));
    $this->assertSame('tests', $this->number->quantity(0, 'test', 'tests', 'none'));
    $this->assertSame('test', $this->number->quantity(1, 'test', 'tests', 'none'));
    $this->assertSame('tests', $this->number->quantity(1.1, 'test', 'tests', 'none'));
  }

  /**
   * @test
   */
  public function QuantityWithAllShowcount()
  {
    $this->assertSame('-1 tests', $this->number->quantity(-1, 'test', 'tests', 'all'));
    $this->assertSame('0 tests', $this->number->quantity(0, 'test', 'tests', 'all'));
    $this->assertSame('1 test', $this->number->quantity(1, 'test', 'tests', 'all'));
    $this->assertSame('1.1 tests', $this->number->quantity(1.1, 'test', 'tests', 'all'));
  }

  /**
   * @test
   */
  public function QuantityWithTrueShowcount()
  {
    $this->assertSame('-1 tests', $this->number->quantity(-1, 'test', 'tests', true));
    $this->assertSame('0 tests', $this->number->quantity(0, 'test', 'tests', true));
    $this->assertSame('1 test', $this->number->quantity(1, 'test', 'tests', true));
    $this->assertSame('1.1 tests', $this->number->quantity(1.1, 'test', 'tests', true));
  }

  /**
   * @test
   */
  public function QuantityWithFalseShowcount()
  {
    $this->assertSame('tests', $this->number->quantity(-1, 'test', 'tests', false));
    $this->assertSame('tests', $this->number->quantity(0, 'test', 'tests', false));
    $this->assertSame('test', $this->number->quantity(1, 'test', 'tests', false));
    $this->assertSame('tests', $this->number->quantity(1.1, 'test', 'tests', false));
  }

  /**
   * @test
   */
  public function QuantityWithWeirdShowcount()
  {
    $this->assertSame('-1 tests', $this->number->quantity(-1, 'test', 'tests', 'arstarstarst'));
    $this->assertSame('0 tests', $this->number->quantity(0, 'test', 'tests', 'arstarstarst'));
    $this->assertSame('1 test', $this->number->quantity(1, 'test', 'tests', 'arstarstarst'));
    $this->assertSame('1.1 tests', $this->number->quantity(1.1, 'test', 'tests', 'arstarstarst'));
  }

}