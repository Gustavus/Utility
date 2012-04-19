<?php
/**
 * @package Utility
 */
namespace Gustavus\Utility;

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

    if ($number === 0) {
      return 'N';
    }

    $result = $this->isNegative() ? '-' : '';
    $number = abs($number);

    // Declare a lookup array that we will use to traverse the number:
    $lookup = array(
      'M'   => 1000,
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

  /**
   * @param string $number
   * @return string
   */
  private function sentenceProcessNumber($number)
  {
    assert('is_string($number)');

    $groupDesignators = array(
      '',
      'Thousand',
      'Million',
      'Billion',
      'Trillion',
      'Quadrillion',
      'Quintillion',
      'Sextillion',
      'Septillion',
    );

    $numbers = array(
      1  => 'One',
      2  => 'Two',
      3  => 'Three',
      4  => 'Four',
      5  => 'Five',
      6  => 'Six',
      7  => 'Seven',
      8  => 'Eight',
      9  => 'Nine',
      10 => 'Ten',
      11 => 'Eleven',
      12 => 'Twelve',
      13 => 'Thirteen',
      14 => 'Fourteen',
      15 => 'Fifteen',
      16 => 'Sixteen',
      17 => 'Seventeen',
      18 => 'Eighteen',
      19 => 'Nineteen',
      20 => 'Twenty',
      30 => 'Thirty',
      40 => 'Forty',
      50 => 'Fifty',
      60 => 'Sixty',
      70 => 'Seventy',
      80 => 'Eighty',
      90 => 'Ninety',
    );

    // We already know that we have a numeric string.  Process it in groups of three characters

    $return   = array();
    $depth    = 0;
    $position = strlen($number);

    while ($position >= 1) {
      $processed        = array();

      $previousPosition = $position;
      $position         = max(0, $position - 3);
      $length           = $previousPosition - $position;
      $chunk            = substr($number, $position, $length);

      if (strlen($chunk) === 3) {
        $processed[] = sprintf('%s Hundred', $numbers[(integer) $chunk[0]]);
        $chunk       = substr($chunk, 1);
      }

      if (isset($numbers[(integer) $chunk])) {
        $processed[] = $numbers[(integer) $chunk];
      } else {
        // We're dealing with a number greater than 20 and not divisible by 10:
        $processed[] = sprintf(
            '%s %s',
            $numbers[$chunk[0] * 10],
            $numbers[(integer) substr($chunk, 1, 1)]
        );
      }

      $processed[] = $groupDesignators[$depth];
      $return[]    = implode(' ', $processed);
      ++$depth;
    }

    return implode(' ', array_reverse($return));
  }

  /**
   * @param string $number
   * @return string
   */
  private function sentenceProcessDecimal($number)
  {
    assert('is_string($number)');

    $suffix = array(
      'Tenth',
      'Hundreth',
      'Thousandth',
      'Ten Thousandth',
      'Hundred Thousandth',
      'Millionth',
      //enough
    );

    $numberSuffix = $suffix[strlen($number) - 1];
    if ($number !== '1') {
      $numberSuffix .= 's';
    }

    return sprintf(
        ' and %s%s',
        $this->sentenceProcessNumber($number),
        $numberSuffix
    );
  }

  /**
   * Takes a number and spells it out
   *
   * Example:
   * <code>
   * $number = new Number(1);
   * echo $number->sentence();
   * // Outputs: One
   *
   * $number = new Number(101);
   * echo $number->sentence();
   * // Outputs: One Hunbred One
   * </code>
   *
   * @return string
   */
  public function sentence()
  {
    if ($this->isZero()) {
      return 'Zero';
    }

    $number         = (string) abs($this->value);
    $splitByDecimal = explode('.', $number);

    return trim(preg_replace(
        '`\s+`',
        ' ',
        sprintf(
            '%s%s %s',
            ($this->isNegative()) ? 'Negative ' : '',
            $this->sentenceProcessNumber($splitByDecimal[0]),
            (count($splitByDecimal) > 1) ? $this->sentenceProcessDecimal($splitByDecimal[1]) : ''
        )
    ));
  }
}