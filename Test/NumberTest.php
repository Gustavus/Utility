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
   * @dataProvider QuantityData
   */
  public function Quantity($expected, $value, $singular, $plural, $showCount = 'all')
  {
    $this->number->setValue($value);
    $this->assertSame($expected, $this->number->quantity($singular, $plural, $showCount));
  }

  public static function QuantityData()
  {
    return array(
      array('-10,000.15 tests', -10000.15, 'test', 'tests'),
      array('-10,000.1 tests', -10000.1, 'test', 'tests'),
      array('-10,000 tests', -10000, 'test', 'tests'),
      array('-2 tests', -2, 'test', 'tests'),
      array('-1.15 tests', -1.15, 'test', 'tests'),
      array('-1.1 tests', -1.1, 'test', 'tests'),
      array('-1 tests', -1, 'test', 'tests'),

      array('0 tests', 0, 'test', 'tests'),

      array('1 test', 1, 'test', 'tests'),
      array('1.1 tests', 1.1, 'test', 'tests'),
      array('1.15 tests', 1.15, 'test', 'tests'),
      array('2 tests', 2, 'test', 'tests'),
      array('10,000 tests', 10000, 'test', 'tests'),
      array('10,000.1 tests', 10000.1, 'test', 'tests'),
      array('10,000.15 tests', 10000.15, 'test', 'tests'),

      // with plural showcount
      array('-1 tests', -1, 'test', 'tests', 'plural'),

      array('0 tests', 0, 'test', 'tests', 'plural'),

      array('test', 1, 'test', 'tests', 'plural'),

      array('1.1 tests', 1.1, 'test', 'tests', 'plural'),

      // with none showcount
      array('tests', -1, 'test', 'tests', 'none'),
      array('tests', 0, 'test', 'tests', 'none'),
      array('test', 1, 'test', 'tests', 'none'),
      array('tests', 1.1, 'test', 'tests', 'none'),

      // with all showcount
      array('-1 tests', -1, 'test', 'tests', 'all'),
      array('0 tests', 0, 'test', 'tests', 'all'),
      array('1 test', 1, 'test', 'tests', 'all'),
      array('1.1 tests', 1.1, 'test', 'tests', 'all'),

      // with true showcount
      array('-1 tests', -1, 'test', 'tests', true),
      array('0 tests', 0, 'test', 'tests', true),
      array('1 test', 1, 'test', 'tests', true),
      array('1.1 tests', 1.1, 'test', 'tests', true),

      // with false showcount
      array('tests', -1, 'test', 'tests', false),
      array('tests', 0, 'test', 'tests', false),
      array('test', 1, 'test', 'tests', false),
      array('tests', 1.1, 'test', 'tests', false),

      // weird showcount
      array('-1 tests', -1, 'test', 'tests', 'arstarstarst'),
      array('0 tests', 0, 'test', 'tests', 'arstarstarst'),
      array('1 test', 1, 'test', 'tests', 'arstarstarst'),
      array('1.1 tests', 1.1, 'test', 'tests', 'arstarstarst'),
    );
  }

}