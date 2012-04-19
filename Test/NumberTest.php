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
   * @dataProvider negativeZeroData
   */
  public function isNegative($isNegative, $isZero, $value)
  {
    $this->number->setValue($value);
    $this->assertSame($isNegative, $this->number->isNegative());
  }

  /**
   * @test
   * @dataProvider negativeZeroData
   */
  public function isZero($isNegative, $isZero, $value)
  {
    $this->number->setValue($value);
    $this->assertSame($isZero, $this->number->isZero());
  }

  /**
   * @return array
   */
  public static function negativeZeroData()
  {
    return array(
      array(true, false, -2),
      array(true, false, -1.1),
      array(true, false, -1),
      array(true, false, -0.1),
      array(true, false, -0.00000001),

      array(false, true, -0.0000),
      array(false, true, -0.0),
      array(false, true, -0),
      array(false, true, 0),

      array(false, false, 0.000000001),
      array(false, false, 0.1),
      array(false, false, 1),
      array(false, false, 1.0),
      array(false, false, 1.1),
    );
  }

  /**
   * @test
   * @dataProvider QuantityData
   */
  public function Quantity($expected, $value, $singular, $plural, $zero = null)
  {
    $this->number->setValue($value);
    $this->assertSame($expected, $this->number->quantity($singular, $plural, $zero));
  }

  /**
   * @return array
   */
  public static function QuantityData()
  {
    return array(
      array('-10,000.15 tests', -10000.15, '%s test', '%s tests'),
      array('-10,000.1 tests', -10000.1, '%s test', '%s tests'),
      array('-10,000 tests', -10000, '%s test', '%s tests'),
      array('-2 tests', -2, '%s test', '%s tests'),
      array('-1.15 tests', -1.15, '%s test', '%s tests'),
      array('-1.1 tests', -1.1, '%s test', '%s tests'),
      array('-1 tests', -1, '%s test', '%s tests'),

      array('0 tests', 0, '%s test', '%s tests'),

      array('1 test', 1, '%s test', '%s tests'),
      array('1.1 tests', 1.1, '%s test', '%s tests'),
      array('1.15 tests', 1.15, '%s test', '%s tests'),
      array('2 tests', 2, '%s test', '%s tests'),
      array('10,000 tests', 10000, '%s test', '%s tests'),
      array('10,000.1 tests', 10000.1, '%s test', '%s tests'),
      array('10,000.15 tests', 10000.15, '%s test', '%s tests'),

      // with zero parameter
      array('-1 tests', -1, '%s test', '%s tests', '%s tests (none)'),
      array('0 tests (none)', 0, '%s test', '%s tests', '%s tests (none)'),
      array('1 test', 1, '%s test', '%s tests', '%s tests (none)'),
      array('1.1 tests', 1.1, '%s test', '%s tests', '%s tests (none)'),

      // without showing number
      array('tests', -1, 'test', 'tests', 'none'),
      array('none', 0, 'test', 'tests', 'none'),
      array('test', 1, 'test', 'tests', 'none'),
      array('tests', 1.1, 'test', 'tests', 'none'),
    );
  }

  /**
   * @test
   * @dataProvider OrdinalRomanNumeralData
   */
  public function Ordinal($ordinal, $romanNumeral, $value)
  {
    $this->number->setValue($value);
    $this->assertSame($ordinal, $this->number->ordinal());
  }

  /**
   * @test
   * @dataProvider OrdinalRomanNumeralData
   */
  public function RomanNumeral($ordinal, $romanNumeral, $value)
  {
    $this->number->setValue($value);
    $this->assertSame($romanNumeral, $this->number->romanNumeral());
  }

  /**
   * @return array
   */
  public static function OrdinalRomanNumeralData()
  {
    return array(
      array('1st', 'I', 1),
      array('2nd', 'II', 2),
      array('3rd', 'III', 3),
      array('4th', 'IV', 4),
      array('5th', 'V', 5),
      array('6th', 'VI', 6),
      array('7th', 'VII', 7),
      array('8th', 'VIII', 8),
      array('9th', 'IX', 9),
      array('10th', 'X', 10),
      array('11th', 'XI', 11),
      array('12th', 'XII', 12),
      array('13th', 'XIII', 13),
      array('14th', 'XIV', 14),
      array('101st', 'CI', 101),
      array('102nd', 'CII', 102),
      array('103rd', 'CIII', 103),
      array('104th', 'CIV', 104),
    );
  }
}