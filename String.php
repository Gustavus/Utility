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
    $state = array(
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
    );

    if (isset($state[strtoupper($this->value)])) {
      $this->setValue($state[strtoupper($this->value)]);
    }
    return $this;
  }
}
