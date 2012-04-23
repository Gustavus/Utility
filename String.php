<?php
/**
 * @package Utility
 */
namespace Gustavus\Utility;

/**
 * Object for working with Strings
 *
 * @package Utility
 */
class String extends Base
{
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
   * @return string
   */
  public function titleCase()
  {
    // @todo Make this smarter by having it ignore words that should not be capitalized
    return ucwords($this->lowerCase());
  }

  /**
   * @return string
   */
  public function lowerCase()
  {
    return strtolower($this->value);
  }

  /**
   * @return string
   */
  public function upperCase()
  {
    return strtoupper($this->value);
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
   * @return string
   */
  public function url()
  {
    $url = trim($this->value);

    // A URL must be at least 3 characters because it needs to have a domain name, a dot, and a TLD
    if (strlen($url) < 3) {
      return '';
    }

    $url = preg_replace('`^(.*?):[/\\\]+`', '$1://', $url);

    if (preg_match('`^(?:.*?)://`', $url) === 0) {
      // URL does not begin with a protocol, so we default to http://
      $url  = 'http://' . $url;
    }

    $url  = preg_replace('`(?<!homepages\.)(?:www\.)?g(?>ustavus|ac)\.edu`', 'gustavus.edu', $url);

    return $url;
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
   * @return string
   */
  public function email()
  {
    $email = trim($this->value);

    if (empty($email)) {
      return '';
    } else if (strpos($email, '@') === false) {
      return "$email@gustavus.edu";
    } else {
      return str_replace('@gac.edu', '@gustavus.edu', $email);
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
   * @return string String with widows removed
   * @author Shaun Inman <io@shauninman.com>
   * @link http://shauninman.com/archive/2007/01/03/widont_2_1_wordpress_plugin
   */
  public function widont($lastWordMaxLength = null)
  {
    assert('is_int($lastWordMaxLength) || is_null($lastWordMaxLength)');

    return preg_replace("|([^\s])\s+([^\s]{1,$lastWordMaxLength})(</.+?>)*\s*$|", '$1&nbsp;$2$3', $this->value);
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
   * @return string
   *
   * @todo Add proteins to the method by optionally returning possessive pronouns ('mine', 'yours', 'ours', 'theirs')
   * @todo Allow string to be HTML and possessivise its contents
   */
  public function possessive()
  {
    $stringToPossessiveise = trim($this->value);

    if (empty($stringToPossessiveise)) {
      return $this->value;
    }

    switch (strtolower($stringToPossessiveise)) {
      // Pronouns
      case 'i':
      case 'my':
        $result = 'my';
          break;

      case 'you':
      case 'your':
        $result = 'your';
          break;

      case 'he':
      case 'his':
        $result = 'his';
          break;

      case 'she':
      case 'her':
        $result = 'her';
          break;

      case 'it':
      case 'its':
        $result = 'its';
          break;

      case 'we':
      case 'our':
        $result = 'our';
          break;

      case 'they':
      case 'their':
        $result = 'their';
          break;

      // Non-pronouns
      default:
        if (substr($stringToPossessiveise, -2) === "'s" || substr($stringToPossessiveise, -2) === "s'") {
          // Already possessive
          $result = $stringToPossessiveise;
        } else if (substr($stringToPossessiveise, -1) === 's' || substr($stringToPossessiveise, -1) === 'z') {
          // Ends in an s sound (should x be included here?)
          $result = "$stringToPossessiveise'";
        } else {
          // Normal
          $result = "$stringToPossessiveise's";
        }
          break;
    }

    // Restore capitalization

    if ($stringToPossessiveise === 'I') {
      // "I" is a special case (pardon the pun), and we don't know what
      // case "my" should have, so guess that it should be lowercase
      return 'my';
    } else if ($stringToPossessiveise === strtoupper($stringToPossessiveise)) {
      // The pronoun was ALL CAPS
      return strtoupper($result);
    } else if ($stringToPossessiveise === ucfirst($stringToPossessiveise)) {
      // The pronoun was Capitalized
      return ucfirst($result);
    } else {
      return $result;
    }
  }

  /**
   * Takes a state, province, or territory, or an abbreviation. If it isn't abbreviated, it abreviates it. If it is abbreviated, it expands it.
   *
   * @return string Abbreviation of the state, province or territory.
   */
  public function state()
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

    if (strlen($this->value) == 2) {
      return ucwords(strtolower(array_search(strtoupper($this->value), $state)));
    } else {
      return ucwords($state[strtoupper($this->value)]);
    }
  }
}
