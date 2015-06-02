<?php
/**
 * @package Utility
 * @author  Nicholas Dobie <ndobie@gustavus.edu>
 */
namespace Gustavus\Utility;

/**
 * Creates a JSON or JSON-P response with correct headers.
 *
 * @package Utility
 * @author  Nicholas Dobie <ndobie@gustavus.edu>
 */
class Jsonizer
{

  /**
   * Check if the request is a JSON-P request
   *
   * @return boolean Returns `true` if JSON-P, `false` if JSON.
   */
  private static function isJSONP()
  {
    return !empty($_GET['callback']) && $_SERVER['REQUEST_METHOD'] === 'GET';
  }

  /**
   * Prevents non-Gustavus referers. This would occur if a third
   * party tried to use our API on their own site. This validation
   * will skip if the users browser dosen't set the referer.
   *
   * @param array $array Overrides the array if the origin isn't a match
   * @return array
   */
  private static function checkOrigin($array)
  {
    if (!PageUtil::hasInternalOrigin()) {
      $array = array(
          'error' =>
            array(
              'code' => 401,
              'simple' => 'Unauthorized Access',
              'reason' => 'This API can only be accessed from an official Gustavus domain.'
            )
        );
    }

    return $array;
  }

  /**
   * Sets the page headers to the correct format to ensure proper
   * handling by the client's browser.
   *
   * @return void
   */
  private static function setHeaders()
  {
    if (headers_sent()) {
      return;
    }
    if (Jsonizer::isJSONP()) {
      header('Content-Type: application/javascript; charset=utf-8');
    } else {
      header('Content-Type: application/json');
    }
  }

  /**
   * Returns a JSON or JSON-P string to be sent to the browser.
   *
   * @param  Array  $array  Array to encode into JSON format.
   * @return String         Encoded JSON or JSON-P string.
   */
  public static function toJSON($array)
  {

    $array = Jsonizer::checkOrigin($array);

    Jsonizer::setHeaders();

    $encode = json_encode($array);

    if (Jsonizer::isJSONP()) {
      return $_GET['callback'] . '(' . $encode . ')';
    } else {
      return $encode;
    }
  }

}