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
    // DateTime will throw an exception if the value isn't supported. No need to do our own checks.
    return true;
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

}