<?php
/**
 * @package Utility
 *
 * @author Joe Lencioni
 * @author Billy Visto
 * @author Chris Rog
 */
namespace Gustavus\Utility;

use ArrayAccess,
    Gustavus\Utility\Abbreviations;

/**
 * Object for working with Strings
 *
 * @package Utility
 *
 * @author Joe Lencioni
 * @author Billy Visto
 * @author Chris Rog
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
   * Converts the casing of this string to that of a well-formed title. That is, this method
   * capitalizes the first letter of every word in the string.
   *
   * @param array $exceptions
   *  A collection of words to not capitalize while converting. If omitted, a default set of
   *  exceptions will be used.
   *
   * @return \Gustavus\Utility\String
   *  This String instance.
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
   * Converts a camel-cased string into a space separated lowercase string
   *
   * @return $this
   */
  public function unCamelCase()
  {
    $newString = preg_replace('`([A-Z])`', ' $1', $this->value);
    return $this->setValue(strtolower(trim($newString)));
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
   * Makes a url adding the queryParams specified to the end
   *
   * @param  array  $queryParams Associative array of parameters
   * @return String
   */
  public function addQueryString(array $queryParams = array())
  {
    if (!empty($queryParams)) {
      $urlParts = parse_url($this->value);

      if (isset($urlParts['query'])) {
        // we need to add the query parts with our queryParams
        $this->value = $urlParts['query'];
        $queryParts  = $this->splitQueryString()->getValue();
        $queryParams = array_merge($queryParts, $queryParams);
      }
      $this->value = sprintf('%s?%s', $urlParts['path'], http_build_query($queryParams));
    }
    return $this;
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
   * @return \Gustavus\Utility\String
   *  This String instance.
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
        // @todo: Remove repeated calls to substr -- it's really really slow.

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
    if (!empty($this->value) && is_string($this->value)) {
      $this->setValue(Abbreviations::abbreviate($this->value, [Abbreviations::US_STATE, Abbreviations::CA_PROVINCE]));
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
   * @throws \InvalidArgumentException
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
   * @throws \InvalidArgumentException
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
   * @throws \InvalidArgumentException
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
   * @throws \InvalidArgumentException
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
   * @throws \InvalidArgumentException
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


  /**
   * Format a phone number
   *
   * @param string $format Format type
   * @param bool $plainText true if returned string should be plain text false if HTML is okay
   * @param string $defaultAreaCode
   * @param string $defaultExchangeCode
   * @return string Formatted phone number
   *
   * @todo  Look into if this can be updated
   */
  public function phone($format = 'short', $plainText = false, $defaultAreaCode = '507', $defaultExchangeCode = '933')
  {
    assert('is_string($format)');
    assert('is_bool($plainText)');
    assert('is_string($defaultAreaCode)');
    assert('is_string($defaultExchangeCode)');

    $this->stripNonDigits();

    if ($this->value === '0') {
      $this->setValue('');
      return $this;
    }

    if (!$plainText) {
      $formats  = array(
        'long'      => '%d%d%d-%d%d%d-%d%d%d%d',
        'medium'    => '<span class="nodisplay">%1$d%2$d%3$d-</span>%4$d%5$d%6$d-%7$d%8$d%9$d%10$d',
        'short'     => '<span class="nodisplay">%1$d%2$d%3$d-%4$d%5$d%6$d-</span><span class="noprint">x</span>%7$d%8$d%9$d%10$d',
        'tickeTrak' => '(%d%d%d)%d%d%d-%d%d%d%d',
        'mobile'    => '<a href="tel:1-%1$d%2$d%3$d-%4$d%5$d%6$d-%7$d%8$d%9$d%10$d">%1$d%2$d%3$d-%4$d%5$d%6$d-%7$d%8$d%9$d%10$d</a>',
      );
    } else {
      $formats  = array(
        'long'      => '%d%d%d-%d%d%d-%d%d%d%d',
        'medium'    => '%4$d%5$d%6$d-%7$d%8$d%9$d%10$d',
        'short'     => 'x%7$d%8$d%9$d%10$d',
        'tickeTrak' => '(%d%d%d)%d%d%d-%d%d%d%d',
      );
    } // if

    // determine the format for the phone number
    $length = strlen($this->value);

    if ($length < 4) {
      $this->setValue($this->value);
      return $this;
    }

    // I don't think this section does what it is supposed to do -Joe
    // I agree -Rudi
    // That's what she said -Jerry
    if ($length > 10 || $format === 'international') {
      if ($format === 'tickeTrak') {
        $this->setValue('');
        return $this;
      }
      $this->setValue($this->value);
      return $this;
    }

    $this->setValue(substr("{$defaultAreaCode}{$defaultExchangeCode}", 0, 10 - $length) . $this->value);

    if ((PHP_SAPI !== 'cli' && !\Config::isUserOnCampus()) || !isset($formats[$format])) {
      $format = 'long';
    } else if ($format === 'short' && substr($this->value, 0, 3) === $defaultAreaCode && substr($this->value, 3, 3) !== $defaultExchangeCode) {
      $format = 'medium';
    } else if ($format === 'short' && substr($this->value, 0, 6) !== "{$defaultAreaCode}{$defaultExchangeCode}") {
      $format = 'long';
    } else if ($format === 'medium' && substr($this->value, 0, 3) !== $defaultAreaCode) {
      $format = 'long';
    } else if ($format === 'short' && !$this->isOncampusPhoneNumber()) {
      $format = 'long';
    }

    $this->setValue(vsprintf($formats[$format], str_split($this->value)));
    return $this;
  }

  /**
   * Identifies oncampus phone numbers
   *
   * @return boolean
   */
  private function isOncampusPhoneNumber()
  {
    $this->stripNonDigits();
    if (strlen($this->value) === 4) {
      $this->setValue('507933' . $this->value);
    } else if (strlen($this->value) === 7) {
      $this->setValue('507' . $this->value);
    }

    if (strlen($this->value) !== 10) {
      return false;
    }

    // not 100% correct, but identifies most of the cases
    if ($this->value > 5079336000 and $this->value < 5079339999) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Removes characters that are not digits from $this->value
   *
   * @return string
   */
  private function stripNonDigits()
  {
    $this->value = preg_replace('/[\D]/', '', $this->value);
    return $this->value;
  }

  /**
   * Converts a byte string to number of bytes
   *
   * Format:
   *
   * number, optional G or M or K, optional B
   *
   * Example strings:
   *
   * - 20G  (20  Gigabytes)
   * - 532m (532 Megabytes)
   * - 71K  (71  Kilobytes)
   * - 54   (54  Bytes, This needs to be a string)
   *
   * @return Number
   * @throws \DomainException If isn't a valid byte string
   */
  public function toBytes()
  {

    $val = strtolower($this->value);

    // Checks that only a Positive Number and G(b), M(b), or K(b) are in the string.
    if (preg_match('/\A(\d+)(g|m|k)?b?\z/i', $val, $matches) == 0) {
      throw new \DomainException('Must be a byte string. Check documentation for expected format.');
    }

    static $multipliers = [
      'g' => 1073741824,
      'm' => 1048576,
      'k' => 1024
    ];

    return new Number(((int) $matches[1]) * (isset($matches[2]) ? $multipliers[$matches[2]] : 1));
  }


  /**
   * Create a summary from a chunk of text
   *
   * Example:
   * <code>
   * $content = 'Phasellus aliquam imperdiet leo. Suspendisse accumsan enim et ipsum.
   *   Nullam vitae augue non ipsum aliquam sagittis. Nullam sed velit. Nunc magna est,
   *   lacinia eget, tristique sit amet, pretium sed, turpis. Nulla faucibus aliquet
   *   libero. Mauris metus risus, auctor ut, gravida hendrerit, pharetra amet.';
   * echo (new String($content))->summary(50)->getValue();
   * // Will output "Phasellus aliquam imperdiet leo. Suspendisse accumsan…"
   * </code>
   *
   * @param int $baselength Number of characters to aim for in the summary
   * @param string $wrapperElement (X)HTML element name to wrap around summary (e.g. "div")
   * @param string $append Content to append to summary
   * @param boolean $plainText
   * @param string $newline Character used to signify a new line
   * @return String
   *
   * @todo add a switch that enables removing words from the middle or beginning of the text instead of the end
   */
  public function summary($baselength = 200, $wrapperElement = '', $append = '', $plainText = false, $newline = ' ')
  {
    assert('is_int($baselength) || is_null($baselength)');
    assert('is_string($wrapperElement)');
    assert('is_string($append)');
    assert('is_bool($plainText)');
    assert('is_string($newline)');

    $text = trim(preg_replace('`[\s]+`', ' ', strip_tags($this->value)));

    if (!empty($text)) {
      // replace newlines
      $text = preg_replace('`[\s]*[\r\n]+[\s]+`', $newline, $text);

      if ($baselength < 1) {
        $r = '';
      } else if (strlen($text) <= $baselength) {
        $r = $text;
      } else {
        // find the first space following the desired length
        $nextSpace = strpos($text, ' ', $baselength);

        if ($nextSpace !== false) {
          // if there is a space
          // truncate the text at the given space
          $r = substr($text, 0, $nextSpace);

          // strip punctuation from the end of the text
          $r = preg_replace('/[\s]*([;:!\?\.,\/\-]|' . trim($newline) . ')*$/', '', $r);

          // add the XML-safe ellipsis and anything we should append
          $r .= ($plainText) ? '...' : '&#8230;';
          $r .= $append;
        } else {
          // there are no more spaces so we will return the entire text
          $r = $text;
        }
      }

      if (!$plainText && !empty($wrapperElement)) {
        $r = sprintf('<%1$s>%2$s</%1$s>', $wrapperElement, $r);
      }

      $this->setValue($r);
    } else {
      $this->setValue('');
    }
    return $this;
  }

  /**
   * Formats a name.
   * First name comes from the string used to construct this class
   *
   * @param string $middle Middle name
   * @param string $last Last name
   * @param string $preferred Preferred name
   * @param string $method 'short', 'full', or 'verbose'
   * @param bool $lastNameFirst
   * @param bool $lastNameInitialOnly
   * @param integer $graduationYear
   * @param string $maiden Maiden name
   * @param string $beforeMaiden
   * @param string $afterMaiden
   * @return string
   */
  public function name($middle, $last, $preferred = '', $method = 'short', $lastNameFirst = false, $lastNameInitialOnly = false, $graduationYear = null, $maiden = '', $beforeMaiden = '(', $afterMaiden = ')')
  {
    assert('is_string($this->value)');
    assert('is_string($middle) || is_null($middle)');
    assert('is_string($last) || is_null($last)');
    assert('is_string($preferred) || is_null($preferred)');
    assert('is_string($method) || is_null($method)');
    assert('is_bool($lastNameFirst) || is_null($lastNameFirst)');
    assert('is_bool($lastNameInitialOnly) || is_null($lastNameInitialOnly)');
    assert('is_int($graduationYear) || is_string($graduationYear) || is_null($graduationYear)');
    assert('is_string($maiden)');
    assert('is_string($beforeMaiden)');
    assert('is_string($afterMaiden)');

    if (!in_array($method, array('short', 'full', 'verbose'))) {
      $method = 'short';
    }

    $first          = trim($this->value);
    $middle         = trim($middle);
    $last           = trim($last);
    $preferred      = trim($preferred);
    $maiden         = trim($maiden);
    $graduationYear = (integer) $graduationYear;

    $f  = '';

    switch ($method) {
      case 'full':
        $f  = ($first != '') ? $first : $preferred;
        if ($middle != '') {
          $f  .= " $middle";
        }
          break;

      case 'verbose':
        if ($first != '' && $preferred != '') {
          $f  = "$first ($preferred)";
        } else if ($first != '') {
          $f  = $first;
        } else {
          $f  = $preferred;
        }

        if ($middle != '') {
          $f  .= " $middle";
        }
          break;

      case 'short':
      default:
        $f  = ($preferred != '') ? $preferred : $first;
    }

    if ($lastNameInitialOnly === true) {
      $last = $last[0];
    }

    if ($maiden === $last) {
      $maiden = '';
    }

    $name = $f;
    $name = ($lastNameFirst) ? "$last, $name" : "$name $last";

    $year = (new Number($graduationYear))->shortYear()->getValue();

    if (!empty($year)) {
      $name .= " $year";
    }

    if (!empty($maiden)) {
      $name .= " $beforeMaiden$f $maiden$afterMaiden";
    }

    $this->setValue(trim($name));
    return $this;
  }
}
