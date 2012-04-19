<?php
/**
 * @package Utility
 */
namespace Gustavus\Utility;
use \Format;

/**
 * Object for working with Numbers
 *
 * @package Utility
 */
class Number extends Base
{
  /**
   * @return boolean
   */
  public function isNegative()
  {
    return $this->value < 0;
  }

  /**
   * @return boolean
   */
  public function isZero()
  {
    return $this->value === 0 || $this->value === 0.0;
  }

  /**
   * Format a quantity of an object (e.g. '1 dog' or '5 dogs')
   *
   * Example:
   * <code>
   * $number = new Number(1);
   * echo $number->quantity('%s dog', '%s dogs');
   * // Outputs: "1 dog"
   *
   * $number = new Number(5);
   * echo $number->quantity('%s dog', '%s dogs');
   * // Outputs "5 dogs"
   * </code>
   *
   * @param string $singularPattern Singular form of object name (e.g. '%s dog')
   * @param string $pluralPattern Plural form of object name (e.g. '%s dogs')
   * @param string $zeroPattern Zero form of object name (e.g. 'no dogs')
   * @return string
   */
  public function quantity($singularPattern, $pluralPattern, $zeroPattern = null)
  {
    assert('is_string($singularPattern)');
    assert('is_string($pluralPattern)');
    assert('is_string($zeroPattern) || is_null($zeroPattern)');

    if ($this->value === 1 || $this->value === 1.0) {
      $pattern = $singularPattern;
    } else if ($zeroPattern !== null && ($this->value === 0 || $this->value === 0.0)) {
      $pattern = $zeroPattern;
    } else {
      $pattern = $pluralPattern;
    }

    $displayNumber = number_format($this->value);
    if (is_float($this->value)) {
      $displayNumber .= ltrim((string) $this->value, '-1234567890');
    }

    return sprintf($pattern, $displayNumber);
  }

  /**
   * Format a number as an ordinal.
   *
   * @return string e.g. '1st', '2nd', '3rd'
   * @link http://www.php.net/manual/en/ref.math.php#77609
   */
  public function ordinal()
  {
    $cardinal = (int) $this->value;
    $digit    = substr($cardinal, -1, 1);

    if ($cardinal < 100) {
      $tens = round($cardinal / 10);
    } else {
      $tens = substr($cardinal, -2, 1);
    }

    if ($tens == 1) {
      return "{$cardinal}th";
    }

    switch ($digit) {
      case 1:
        return "{$cardinal}st";
      case 2:
        return "{$cardinal}nd";
      case 3:
        return "{$cardinal}rd";
      default:
        return "{$cardinal}th";
    }
  }

  /**
   * Convert an arabic numeral to a roman numeral
   *
   * @return string Roman numeral
   * @link http://www.go4expert.com/forums/showthread.php?t=4948
   */
  public function romanNumeral()
  {
    // Make sure that we only use the integer portion of the value
    $number = (integer) $this->value;
    $result = '';

    // Declare a lookup array that we will use to traverse the number:
    $lookup = array('M' => 1000,
      'CM'  => 900,
      'D'   => 500,
      'CD'  => 400,
      'C'   => 100,
      'XC'  => 90,
      'L'   => 50,
      'XL'  => 40,
      'X'   => 10,
      'IX'  => 9,
      'V'   => 5,
      'IV'  => 4,
      'I'   => 1,
    );

    foreach ($lookup as $roman => $value) {
      // Determine the number of matches
      $matches = (integer) ($number / $value);

      // Store that many characters
      $result .= str_repeat($roman, $matches);

      // Substract that from the number
      $number  = $number % $value;
    }

    // The Roman numeral should be built, return it
    return $result;
  }
}