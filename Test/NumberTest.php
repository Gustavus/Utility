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
   * function to test quantity from an array
   * @param  array  $array
   * @return void
   */
  public function testQuantityFromArray(array $array = array())
  {
    foreach ($array as $key => $value) {
      $number = new Utility\Number($value[0]);
      $singular = (isset($value[1])) ? $value[1] : '';
      $plural = (isset($value[2])) ? $value[2] : '';
      $showCount = (isset($value[3])) ? $value[3] : 'all';
      $this->assertSame($key, $number->quantity($singular, $plural, $showCount));
    }
  }

  /**
   * @test
   */
  public function QuantityWithUnspecifiedShowcount()
  {
    $array = array(
      '-10,000.15 tests' => array(-10000.15, 'test', 'tests'),
      '-10,000.1 tests' => array(-10000.1, 'test', 'tests'),
      '-10,000 tests' => array(-10000, 'test', 'tests'),
      '-2 tests' => array(-2, 'test', 'tests'),
      '-1.15 tests' => array(-1.15, 'test', 'tests'),
      '-1.1 tests' => array(-1.1, 'test', 'tests'),
      '-1 tests' => array(-1, 'test', 'tests'),

      '0 tests' => array(0, 'test', 'tests'),

      '1 test' => array(1, 'test', 'tests'),
      '1.1 tests' => array(1.1, 'test', 'tests'),
      '1.15 tests' => array(1.15, 'test', 'tests'),
      '2 tests' => array(2, 'test', 'tests'),
      '10,000 tests' => array(10000, 'test', 'tests'),
      '10,000.1 tests' => array(10000.1, 'test', 'tests'),
      '10,000.15 tests' => array(10000.15, 'test', 'tests'),
    );
    $this->testQuantityFromArray($array);
  }

  /**
   * @test
   */
  public function QuantityWithPluralShowcount()
  {
    $array = array(
      '-1 tests' => array(-1, 'test', 'tests', 'plural'),

      '0 tests' => array(0, 'test', 'tests', 'plural'),

      'test' => array(1, 'test', 'tests', 'plural'),

      '1.1 tests' => array(1.1, 'test', 'tests', 'plural'),
    );
    $this->testQuantityFromArray($array);
  }

  /**
   * @test
   */
  public function QuantityWithNoneShowcount()
  {
    $array = array(
      'tests' => array(-1, 'test', 'tests', 'none'),
      'tests' => array(0, 'test', 'tests', 'none'),
      'test' => array(1, 'test', 'tests', 'none'),
      'tests' => array(1.1, 'test', 'tests', 'none'),
    );
    $this->testQuantityFromArray($array);
  }

  /**
   * @test
   */
  public function QuantityWithAllShowcount()
  {
    $array = array(
      '-1 tests' => array(-1, 'test', 'tests', 'all'),
      '0 tests' => array(0, 'test', 'tests', 'all'),
      '1 test' => array(1, 'test', 'tests', 'all'),
      '1.1 tests' => array(1.1, 'test', 'tests', 'all'),
    );
    $this->testQuantityFromArray($array);
  }

  /**
   * @test
   */
  public function QuantityWithTrueShowcount()
  {
    $array = array(
      '-1 tests' => array(-1, 'test', 'tests', true),
      '0 tests' => array(0, 'test', 'tests', true),
      '1 test' => array(1, 'test', 'tests', true),
      '1.1 tests' => array(1.1, 'test', 'tests', true),
    );
    $this->testQuantityFromArray($array);
  }

  /**
   * @test
   */
  public function QuantityWithFalseShowcount()
  {
    $array = array(
      'tests' => array(-1, 'test', 'tests', false),
      'tests' => array(0, 'test', 'tests', false),
      'test' => array(1, 'test', 'tests', false),
      'tests' => array(1.1, 'test', 'tests', false),
    );
    $this->testQuantityFromArray($array);
  }

  /**
   * @test
   */
  public function QuantityWithWeirdShowcount()
  {
    $array = array(
      '-1 tests' => array(-1, 'test', 'tests', 'arstarstarst'),
      '0 tests' => array(0, 'test', 'tests', 'arstarstarst'),
      '1 test' => array(1, 'test', 'tests', 'arstarstarst'),
      '1.1 tests' => array(1.1, 'test', 'tests', 'arstarstarst'),
    );
    $this->testQuantityFromArray($array);
  }

}