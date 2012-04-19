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
}