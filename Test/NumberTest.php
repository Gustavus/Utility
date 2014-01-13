<?php
/**
 * @package Utility
 * @subpackage Test
 */

namespace Gustavus\Utility\Test;

use Gustavus\Utility,
  Gustavus\Test\Test,
  Gustavus\Test\TestObject;

/**
 * @package Utility
 * @subpackage Test
 */
class NumberTest extends Test
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
    $this->number = new Utility\Number();
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
  public function toString()
  {
    $number = new Utility\Number(1001);

    $this->expectOutputString('1001');
    echo $number;
  }

  /**
   * @test
   * @expectedException DomainException
   */
  public function setValue()
  {
    $this->assertInstanceOf('DomainException', new Utility\Number('hello'));
  }

  /**
   * @test
   */
  public function valueIsValid()
  {
    $number = new TestObject($this->number);
    $this->assertTrue($number->valueIsValid(1));
    $this->assertTrue($number->valueIsValid(1.0));
    $this->assertFalse($number->valueIsValid('1'));
    $this->assertFalse($number->valueIsValid(array(1)));
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
  public function toQuantity($expected, $value, $singular, $plural, $zero = null)
  {
    $this->number->setValue($value);
    $this->assertSame($expected, $this->number->toQuantity($singular, $plural, $zero)->getValue());
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
   * @dataProvider OrdinalRomanNumeralSentenceData
   */
  public function toOrdinal($ordinal, $romanNumeral, $sentence, $value)
  {
    $this->number->setValue($value);
    $this->assertSame($ordinal, $this->number->toOrdinal()->getValue());
  }

  /**
   * @test
   * @dataProvider OrdinalRomanNumeralSentenceData
   */
  public function toRomanNumeral($ordinal, $romanNumeral, $sentence, $value)
  {
    $this->number->setValue($value);
    $this->assertSame($romanNumeral, $this->number->toRomanNumeral()->getValue());
  }

  /**
   * @test
   * @dataProvider OrdinalRomanNumeralSentenceData
   */
  public function toSentence($ordinal, $romanNumeral, $sentence, $value)
  {
    $this->number->setValue($value);
    $this->assertSame($sentence, $this->number->toSentence()->getValue());
  }

  /**
   * @return array
   */
  public static function OrdinalRomanNumeralSentenceData()
  {
    return array(
      array('-1st', '-I', 'Negative One', -1),
      array('0th', 'N', 'Zero', 0),
      array('0th', 'N', 'Zero', 0.0),
      array('1st', 'I', 'One', 1),
      array('1st', 'I', 'One and One Tenth', 1.1),
      array('1st', 'I', 'One and Two Tenths', 1.2),
      array('2nd', 'II', 'Two', 2),
      array('3rd', 'III','Three', 3),
      array('4th', 'IV', 'Four', 4),
      array('5th', 'V', 'Five', 5),
      array('6th', 'VI', 'Six', 6),
      array('7th', 'VII', 'Seven', 7),
      array('8th', 'VIII', 'Eight', 8),
      array('9th', 'IX', 'Nine', 9),
      array('10th', 'X', 'Ten', 10),
      array('11th', 'XI', 'Eleven', 11),
      array('12th', 'XII', 'Twelve', 12),
      array('13th', 'XIII', 'Thirteen', 13),
      array('14th', 'XIV', 'Fourteen', 14),
      array('101st', 'CI', 'One Hundred One', 101),
      array('102nd', 'CII', 'One Hundred Two', 102),
      array('103rd', 'CIII', 'One Hundred Three', 103),
      array('104th', 'CIV', 'One Hundred Four', 104),
      array('12345th', 'MMMMMMMMMMMMCCCXLV', 'Twelve Thousand Three Hundred Forty Five', 12345),
      array('99999th', 'MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMCMXCIX', 'Ninety Nine Thousand Nine Hundred Ninety Nine', 99999),
    );
  }

  /**
   * @test
   * @dataProvider durationData
   */
  public function toDuration($expected, $value)
  {
    $this->number->setValue($value);
    $this->assertSame($expected, $this->number->toDuration()->getValue());
  }

  /**
   * @return array
   */
  public static function durationData()
  {
    return array(
      array('1<abbr title="seconds">s</abbr>', 1),
      array('1<abbr title="minutes">m</abbr>', 60),
      array('1<abbr title="minutes">m</abbr> 1<abbr title="seconds">s</abbr>', 61),
      array('1<abbr title="hours">h</abbr>', 3600),
      array('1<abbr title="hours">h</abbr> 1<abbr title="seconds">s</abbr>', 3601),
      array('1<abbr title="hours">h</abbr> 1<abbr title="minutes">m</abbr>', 3660),
      array('1<abbr title="hours">h</abbr> 1<abbr title="minutes">m</abbr> 1<abbr title="seconds">s</abbr>', 3661),
      array('1<abbr title="days">d</abbr>', 86400),
      array('1<abbr title="days">d</abbr> 1<abbr title="seconds">s</abbr>', 86401),
      array('1<abbr title="days">d</abbr> 1<abbr title="minutes">m</abbr>', 86460),
      array('1<abbr title="days">d</abbr> 1<abbr title="minutes">m</abbr> 1<abbr title="seconds">s</abbr>', 86461),
      array('1<abbr title="days">d</abbr> 1<abbr title="hours">h</abbr>', 90000),
      array('1<abbr title="days">d</abbr> 1<abbr title="hours">h</abbr> 1<abbr title="seconds">s</abbr>', 90001),
      array('1<abbr title="weeks">w</abbr>', 604800),
      array('1<abbr title="weeks">w</abbr> 1<abbr title="seconds">s</abbr>', 604801),
    );
  }

  /**
   * @test
   * @dataProvider shortYearData
   */
  public function shortYear($year, $expected)
  {
    $this->number->setValue($year);
    $this->assertSame($expected, $this->number->shortYear()->getValue());
  }

  /**
   * Data provider for shortYear
   *
   * @return array
   */
  public function shortYearData()
  {
    return [
      [1999, '’99'],
      [2000, '’00'],
      [2015, '’15'],
      [1915, '1915'],
      [2, ''],
      [20, '’20'],
    ];
  }
}