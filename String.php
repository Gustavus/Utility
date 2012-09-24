<?php
/**
 * @package Utility
 */
namespace Gustavus\Utility;

use ArrayAccess;

/**
 * Object for working with Strings
 *
 * @package Utility
 */
class String extends Base implements ArrayAccess
{
  /** A lazily-populated mapping of long state names to abbreviations. */
  private static $states;

  /**
   * @var array Exceptions for title case
   */
  private $titleCaseExceptions = array(
    'to'   => 1,
    'a'    => 1,
    'the'  => 1,
    'of'   => 1,
    'by'   => 1,
    'and'  => 1,
    'with' => 1,

    'II'   => 1,
    'III'  => 1,
    'IV'   => 1,
    'V'    => 1,
    'VI'   => 1,
    'VII'  => 1,
    'VIII' => 1,
    'IX'   => 1,
    'X'   => 1,
  );

  // ArrayAccess methods

  /**
   * @param integer $offset
   * @return boolean
   */
  public function offsetExists($offset)
  {
    assert('is_int($offset)');

    return strlen($this->value) > $offset;
  }

  /**
   * @param integer $offset
   * @return string
   */
  public function offsetGet($offset)
  {
    assert('is_int($offset)');

    return $this->value[$offset];
  }

  /**
   * @param integer $offset
   * @param string $value
   * @return void
   */
  public function offsetSet($offset, $value)
  {
    assert('is_int($offset) && is_string($value)');

    if ($offset === 0) {
      // Offset is at the very beginning of the string
      $this->setValue($value . substr($this->value, 1));
    } else if ($offset === strlen($this->value) - 1) {
      // Offset is at the very end of the string
      $this->setValue(substr($this->value, 0, -1) . $value);
    } else if ($this->offsetExists($offset)) {
      // Offset is somewhere in the middle of the string
      $this->setValue(substr($this->value, 0, $offset) . $value . substr($this->value, $offset + 1));
    } else {
      // String is not long enough, so we need to add spaces
      $this->setValue(str_pad($this->value, $offset) . $value);
    }
  }

  /**
   * @param integer $offset
   * @return void
   */
  public function offsetUnset($offset)
  {
    assert('is_int($offset)');
    $this->offsetSet($offset, '');
  }

  /**
   * Function to overide abstract function in base to make sure the value is valid
   *
   * @param  mixed $value value passed into setValue()
   * @return boolean
   */
  final protected function valueIsValid($value)
  {
    return is_string($value);
  }

  /**
   * @return $this
   */
  public function titleCase(array $exceptions = null)
  {
    if ($exceptions === null) {
      $exceptions = $this->titleCaseExceptions;
    } else {
      // Flip array for quicker lookups
      $exceptions = array_flip($exceptions);
    }

    $string = mb_convert_case($this->value, MB_CASE_TITLE);

    $words  = preg_split('`\b`', $string, null, PREG_SPLIT_NO_EMPTY);
    $wordsWithExceptions = array();
    foreach ($words as $word) {
      if (isset($exceptions[mb_strtoupper($word)])) {
        $wordsWithExceptions[] = mb_strtoupper($word);
      } else if (isset($exceptions[mb_strtolower($word)])) {
        $wordsWithExceptions[] = mb_strtolower($word);
      } else {
        $wordsWithExceptions[] = $word;
      }
    }

    return $this->setValue(ucfirst(implode('', $wordsWithExceptions)));
  }

  /**
   * @return $this
   */
  public function lowerCase()
  {
    return $this->setValue(mb_strtolower($this->value));
  }

  /**
   * @return $this
   */
  public function upperCase()
  {
    return $this->setValue(mb_strtoupper($this->value));
  }

  /**
   * Fixes up sloppy URLs so they are correctly and uniformly formatted
   *
   * Usage:
   * <code>
   * $string = new String('gac.edu/test/');
   * echo $string->url();
   * // Outputs: http://gustavus.edu/test/
   *
   * $string = new String('google.com/testing/');
   * echo $string->url();
   * // Outputs: http://google.com/testing/
   *
   * $string = new String('www.gustavus.edu');
   * echo $string->url();
   * // Outputs: http://gustavus.edu
   * </code>
   *
   * @return $this
   */
  public function url()
  {
    $url = trim($this->value);

    // A URL must be at least 3 characters because it needs to have a domain name, a dot, and a TLD
    if (strlen($url) < 3) {
      $this->setValue('');
      return $this;
    }

    $url = preg_replace('`^(.*?):[/\\\]+`', '$1://', $url);

    if (preg_match('`^(?:.*?)://`', $url) === 0) {
      // URL does not begin with a protocol, so we default to http://
      $url  = 'http://' . $url;
    }

    $url  = preg_replace('`(?<!homepages\.)(?:www\.)?g(?>ustavus|ac)\.edu`', 'gustavus.edu', $url);

    return $this->setValue($url);
  }

  /**
   * Splits a query string into an associative array
   * @return Set
   */
  public function splitQueryString()
  {
    $split = preg_split('`\&|\?|\=`', $this->value, null, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
    $set = new Set(array());
    for ($i = 0; $i + 1 < count($split); $i += 2) {
      if (isset($split[$i + 1], $split[$i])) {
        if (strpos($split[$i], '[]') !== false) {
          // query param has multiple values, so it needs to be an array
          $index = substr($split[$i], 0, strlen($split[$i]) - 2);
          if ($set->offsetExists($index)) {
            $oldVal = $set->offsetGet($index);
          } else {
            $oldVal = [];
          }
          $set->offsetSet($index, array_merge($oldVal, [$split[$i + 1]]));
        } else {
          $set->offsetSet($split[$i], $split[$i + 1]);
        }
      }
    }
    return $set;
  }

  /**
   * Fixes up sloppy e-mail addresses so they are correctly and uniformly formatted
   *
   * Usage:
   * <code>
   * $string = new String('jerry');
   * echo $string->email();
   * // Outputs: jerry@gustavus.edu
   *
   * $string = new String('jerry@gac.edu');
   * echo $string->email();
   * // Outputs: jerry@gustavus.edu
   *
   * $string = new String('jerry@gustavus.edu');
   * echo $string->email();
   * // Outputs: jerry@gustavus.edu
   * </code>
   *
   * @return $this
   */
  public function email()
  {
    $email = trim($this->value);

    if (empty($email)) {
      return $this->setValue('');
    } else if (strpos($email, '@') === false) {
      return $this->setValue("$email@gustavus.edu");
    } else {
      return $this->setValue(str_replace('@gac.edu', '@gustavus.edu', $email));
    }
  }

  /**
   * Prevents widows in strings to be used in HTML and XHTML documents
   *
   * Example:
   * <code>
   * $string = new String('This is a test');
   * echo $string->widont();
   * // Outputs: "This is a&nbsp;test"
   * </code>
   *
   * @param integer $lastWordMaxLength
   * @return $this String with widows removed
   *
   * @author Shaun Inman <io@shauninman.com>
   * @link http://shauninman.com/archive/2007/01/03/widont_2_1_wordpress_plugin
   */
  public function widont($lastWordMaxLength = null)
  {
    assert('is_int($lastWordMaxLength) || is_null($lastWordMaxLength)');

    return $this->setValue(preg_replace("|([^\s])\s+([^\s]{1,$lastWordMaxLength})(</.+?>)*\s*$|", '$1&nbsp;$2$3', $this->value));
  }

  /**
   * Returns the possessive form of the sent string,
   * or the same string if @string is already possessive)
   *
   * Example:
   * <code>
   * $strings = array('Jesus', 'Nick', 'I', 'RYAN', 'the hero', 'my',
   *                  'HIS', 'He', 'you', 'We', 'i');
   * foreach($strings as $string) {
   *  echo '<p><strong>' . $string . ':</strong> ' .
   *  Format::possessive($string) . ' stuff</p>';
   * }
   * echo Format::possessive($string1);
   * // Will output:
   * // Jesus: Jesus' stuff
   * // Nick: Nick's stuff
   * // I: my stuff
   * // RYAN: RYAN'S stuff
   * // the hero: the hero's stuff
   * // my: my stuff
   * // HIS: HIS stuff
   * // He: His stuff
   * // you: your stuff
   * // We: Our stuff
   * // i: my stuff
   * </code>
   *
   * Note: In the case of 'I', we return 'my', so the caller should modify
   *       the result manually if it ought to be 'My' or 'MY'
   *
   * @return $this
   *
   * @todo Add proteins to the method by optionally returning possessive pronouns ('mine', 'yours', 'ours', 'theirs')
   * @todo Allow string to be HTML and possessivise its contents
   */
  public function possessive()
  {
    $stringToPossessiveise = trim($this->value);

    if (empty($stringToPossessiveise)) {
      return $this;
    }

    switch (strtolower($stringToPossessiveise)) {
      // Pronouns
      case 'i':
      case 'my':
        $this->setValue('my');
          break;

      case 'you':
      case 'your':
        $this->setValue('your');
          break;

      case 'he':
      case 'his':
        $this->setValue('his');
          break;

      case 'she':
      case 'her':
        $this->setValue('her');
          break;

      case 'it':
      case 'its':
        $this->setValue('its');
          break;

      case 'we':
      case 'our':
        $this->setValue('our');
          break;

      case 'they':
      case 'their':
        $this->setValue('their');
          break;

      // Non-pronouns
      default:
        if (substr($stringToPossessiveise, -2) === "'s" || substr($stringToPossessiveise, -2) === "s'") {
          // Already possessive
          $this->setValue($stringToPossessiveise);
        } else if (substr($stringToPossessiveise, -1) === 's' || substr($stringToPossessiveise, -1) === 'z') {
          // Ends in an s sound (should x be included here?)
          $this->setValue("$stringToPossessiveise'");
        } else {
          // Normal
          $this->setValue("$stringToPossessiveise's");
        }
          break;
    }

    // Restore capitalization
    if ($stringToPossessiveise === 'I') {
      // "I" is a special case (pardon the pun), and we don't know what
      // case "my" should have, so guess that it should be lowercase
      $this->setValue('my');
    } else if ($stringToPossessiveise === strtoupper($stringToPossessiveise)) {
      // The pronoun was ALL CAPS
      $this->upperCase();
    } else if ($stringToPossessiveise === ucfirst($stringToPossessiveise)) {
      // The pronoun was Capitalized
      $this->setValue(ucfirst($this->value));
    }

    return $this;
  }

  /**
   * Takes a state, province, or territory and abreviates it.
   *
   * @return $this Abbreviation of the state, province or territory.
   */
  public function abbreviateState()
  {
    if (empty(String::$states)) {
      String::$states = [
        'ALABAMA'                        => 'AL',
        'ALASKA'                         => 'AK',
        'AMERICAN SAMOA'                 => 'AS',
        'ARIZONA'                        => 'AZ',
        'ARKANSAS'                       => 'AR',
        'CALIFORNIA'                     => 'CA',
        'COLORADO'                       => 'CO',
        'CONNECTICUT'                    => 'CT',
        'DELAWARE'                       => 'DE',
        'DISTRICT OF COLUMBIA'           => 'DC',
        'FEDERATED STATES OF MICRONESIA' => 'FM',
        'FLORIDA'                        => 'FL',
        'GEORGIA'                        => 'GA',
        'GUAM'                           => 'GU',
        'HAWAII'                         => 'HI',
        'IDAHO'                          => 'ID',
        'ILLINOIS'                       => 'IL',
        'INDIANA'                        => 'IN',
        'IOWA'                           => 'IA',
        'KANSAS'                         => 'KS',
        'KENTUCKY'                       => 'KY',
        'LOUISIANA'                      => 'LA',
        'MAINE'                          => 'ME',
        'MARSHALL ISLANDS'               => 'MH',
        'MARYLAND'                       => 'MD',
        'MASSACHUSETTS'                  => 'MA',
        'MICHIGAN'                       => 'MI',
        'MINNESOTA'                      => 'MN',
        'MISSISSIPPI'                    => 'MS',
        'MISSOURI'                       => 'MO',
        'MONTANA'                        => 'MT',
        'NEBRASKA'                       => 'NE',
        'NEVADA'                         => 'NV',
        'NEW HAMPSHIRE'                  => 'NH',
        'NEW JERSEY'                     => 'NJ',
        'NEW MEXICO'                     => 'NM',
        'NEW YORK'                       => 'NY',
        'NORTH CAROLINA'                 => 'NC',
        'NORTH DAKOTA'                   => 'ND',
        'NORTHERN MARIANA ISLANDS'       => 'MP',
        'OHIO'                           => 'OH',
        'OKLAHOMA'                       => 'OK',
        'OREGON'                         => 'OR',
        'PALAU'                          => 'PW',
        'PENNSYLVANIA'                   => 'PA',
        'PUERTO RICO'                    => 'PR',
        'RHODE ISLAND'                   => 'RI',
        'SOUTH CAROLINA'                 => 'SC',
        'SOUTH DAKOTA'                   => 'SD',
        'TENNESSEE'                      => 'TN',
        'TEXAS'                          => 'TX',
        'UTAH'                           => 'UT',
        'VERMONT'                        => 'VT',
        'VIRGIN ISLANDS'                 => 'VI',
        'VIRGINIA'                       => 'VA',
        'WASHINGTON'                     => 'WA',
        'WEST VIRGINIA'                  => 'WV',
        'WISCONSIN'                      => 'WI',
        'WYOMING'                        => 'WY',

        //Canadian Provinces
        'ALBERTA'                        => 'AB',
        'BRITISH COLUMBIA'               => 'BC',
        'MANITOBA'                       => 'MB',
        'NEW BRUNSWICK'                  => 'NB',
        'LABRADOR'                       => 'NL',
        'NEWFOUNDLAND'                   => 'NL',
        'NORTHWEST TERRITORIES'          => 'NT',
        'NOVA SCOTIA'                    => 'NS',
        'NUNAVUT'                        => 'NU',
        'ONTARIO'                        => 'ON',
        'PRINCE EDWARD ISLAND'           => 'PE',
        'QUEBEC'                         => 'QC',
        'SASKATCHEWAN'                   => 'SK',
        'YUKON'                          => 'YT',
      ];
    }

    $key = strtoupper($this->value);

    if (isset(String::$states[$key])) {
      $this->setValue(String::$states[$key]);
    }

    return $this;
  }

  /**
   * Returns an excerpt of the string this object represents. This will strip the string of any HTML
   * tags and truncate it at the nearest whitespace character to the endpoint. Optionally, an
   * ellipsis will be appended when the string is truncated.
   * <p/>
   * <b>Note</b>: The offset and length values are calculated <i>after</i> removing HTML tags and
   * any other extraneous formatting. Additionally, as the offset and length are used to determine
   * the best location to begin truncating, the length of the resulting string may vary with
   * differing initial values.
   *
   * @param integer $length
   *  The maximum length of the excerpt. If the length of the string is greater than this value, the
   *  string will be truncated. If the specified length is negative, that many characters will be
   *  omitted from the end of the string.
   *
   * @param integer $offset
   *  The offset in the string to begin the excerpt. If the offset is negative, it will begin from
   *  the end of the string at the offset specified. If the offset is greater than the length of the
   *  string, the length of the string will be used as the offset.
   *
   * @param boolean $appendEllipsis
   *  True if we should append an ellipsis in place of any removed text.
   *
   * @throws InvalidArgumentException
   *  if $offset or $length are not integers, or $length is zero.
   *
   * @return Gustavus\Utility\String
   *  This String instance.
   */
  public function excerpt($length = 200, $offset = 0, $appendEllipsis = true)
  {
    if (!is_int($length) || $length === 0) {
      throw new \InvalidArgumentException('$length is null, not an integer or zero.');
    }

    if (!is_int($offset)) {
      throw new \InvalidArgumentException('$offset is null or not an integer.');
    }

    // Strip.
    $base = strip_tags($this->value);
    $baseLen = strlen($base);

    // Correct offset...
    if ($offset < 0) {
      $offset = $baseLen + $offset;
    }

    // Correct length...
    if ($length < 0) {
      $length = ($baseLen + $length) - $offset;
    }

    // Check if we need to truncate...
    $target = $offset + $length;

    if ($baseLen > $length) {
      $baseStr = new String($base);

      $start = $baseStr->findNearestInstance('/\s|\A/', $offset);
      $end = $baseStr->findNearestInstance('/\s|\z/', $target);

      // CHOP!
      $summary = substr($base, $start, ($end - $start));

      if (strlen($summary) < $baseLen) {
        // Remove any leading or trailing punctuation and add our chop-chop calling card...
        if ($start != 0) {
          $summary = ($appendEllipsis ? '...' : '') . preg_replace('/\A\s*([;:!\?\.,\/\-]|)+\s*/', '', $summary);
        }

        if ($end != $baseLen) {
          $summary = preg_replace('/\s*([;:!\?\.,\/\-]|)+\s*\z/', '', $summary) . ($appendEllipsis ? '...' : '');
        }
      }

      // Set!
      $this->setValue($summary);
    } else {
      // Well... We don't need to cut anything, but we still want to show that we've removed the
      // HTML and junk.
      $this->setValue($base);
    }

    // Return!
    return $this;
  }

  /**
   * Returns the position of the string closest to the specified offset. If the string is not found
   * at all, this method returns false.
   *
   * @param string $regexp
   *  A regular expression consisting of the search contents. Cannot be empty.
   *
   * @param integer $offset
   *  The offset at which to begin searching. If the offset is negative, the search will begin from
   *  that many characters from the end of the string.
   *
   * @throws InvalidArgumentException
   *  if $regexp is empty or not a string, or $offset is not an integer.
   *
   * @return mixed
   *  An integer representing the position of the nearest instance of the given expression, or false
   *  if the expression was not found.
   */
  public function findNearestInstance($regexp, $offset = 0)
  {
    if (empty($regexp) || !is_string($regexp)) {
      throw new \InvalidArgumentException('$search is empty or not a string.');
    }

    if (!is_int($offset)) {
      throw new \InvalidArgumentException('$offset is not an integer.');
    }

    // Correct offset...
    if ($offset < 0) {
      $offset = strlen($this->value) + $offset;
    }

    // Find our things... (Note: preg_match_all wraps its output in an extra array)
    if (preg_match_all($regexp, $this->value, $matches, PREG_OFFSET_CAPTURE) != false) {
      $best = false;

      // Calculate best...
      foreach ($matches[0] as $match) {
        $dist = abs($offset - $match[1]);

        if ($best === false || $dist < $best[0]) {
          $best = [$dist, $match[1]];
        }
      }

      // Return!
      return $best[1];
    }

    return false;
  }

  /**
   * Prepends the given content to the beginning of this string.
   *
   * @param string $content
   *  The given content to prepend to this string.
   *
   * @throws InvalidArgumentException
   *  if $content is null or not a string.
   *
   * @return Gustavus\Utility\String
   *  This String instance.
   */
  public function prepend($content)
  {
    if (is_null($content) || !is_string($content)) {
      throw new \InvalidArgumentException('$content is null or not a string.');
    }

    $this->setValue($content . $this->value);
    return $this;
  }

  /**
   * Appends the given content to the end of this string.
   *
   * @param string $content
   *  The given content to append to this string.
   *
   * @throws InvalidArgumentException
   *  if $content is null or not a string.
   *
   * @return Gustavus\Utility\String
   *  This String instance.
   */
  public function append($content)
  {
    if (is_null($content) || !is_string($content)) {
      throw new \InvalidArgumentException('$content is null or not a string.');
    }

    $this->setValue($this->value . $content);
    return $this;
  }

  /**
   * Wraps the contents of this string with the specified XML/HTML tag.
   *
   * @param string $tagName
   *  The name of the tag with which to wrap this string. Must be a well-formed tag name.
   *
   * @throws InvalidArgumentException
   *  if $tagName is null, not a string or not a well-formed tag name.
   *
   * @return Gustavus\Utility\String
   *  This String instance.
   */
  public function encaseInTag($tagName)
  {
    if (is_null($tagName) || !is_string($tagName)) {
      throw new \InvalidArgumentException('$tagName is null or not a string.');
    }

    if (!preg_match('/\A[a-z](?:[a-z0-9]|(?:[a-z0-9\-_][a-z0-9]))*\z/i', $tagName)) {
      throw new \InvalidArgumentException('$tagName is not a well-formed tag.');
    }

    $this->setValue('<' . $tagName . '>' . $this->value . '</' . $tagName . '>');
    return $this;
  }
}
