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
   * echo Format::quantity(1, 'dog', 'dogs');
   * // Outputs: "1 dog"
   *
   * echo Format::quantity(5, 'dog', 'dogs');
   * // Outputs "5 dogs"
   * </code>
   *
   * @param float $number Number of object
   * @param string $singular Singular form of object name (e.g. 'dog')
   * @param string $plural Plural form of object name (e.g. 'dogs')
   * @param string|boolean $showCount Possible values are 'all' (or true), 'plural', or 'none' (or false)
   * @return string
   */
  public function quantity($number, $singular, $plural, $showCount = 'all')
  {
    assert('is_int($number) || is_float($number) || is_numeric($number)');
    assert('is_string($singular)');
    assert('is_string($plural)');
    assert('is_string($showCount) || is_bool($showCount)');

    $number = (float) $number;
    $r      = ($number === 1.0) ? $singular : $plural;

    if (is_bool($showCount)) {
      $showCount  = ($showCount) ? 'all' : 'none';
    }

    if (!is_string($showCount) || !in_array(strtolower($showCount), array('all', 'plural', 'none'))) {
      $showCount  = 'all';
    }

    if ($showCount === 'all' || ($showCount === 'plural' && $number !== 1.0)) {
      $displayNumber  = number_format($number);

      if (!is_int($number)) {
        $displayNumber  .= ltrim((string) $number, '-1234567890');
      }

      $r  = "$displayNumber $r";
    }

    return $r;
  }
}