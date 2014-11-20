<?php
/**
 * @package Utility
 * @author  Billy Visto
 */
namespace Gustavus\Utility;

/**
 * Class for page utility functions
 *
 * @package Utility
 * @author  Billy Visto
 */
class PageUtil
{
  /**
   * Redirects to the specified path
   *
   * @param  string $path path to redirect to.
   * @param  integer $statusCode Redirection status code
   * @return void
   */
  public static function redirect($path = '/', $statusCode = 303)
  {
    $_POST = [];
    header('Location: ' . $path, true, $statusCode);
    exit;
  }

  /**
   * Redirects user to new path with the specified message to be displayed if it goes through the router
   *
   * @param  string $path    path to redirect to.
   * @param  string $message message to display on redirect
   * @param  integer $statusCode Redirection status code
   * @return void
   */
  public static function redirectWithMessage($path = '/', $message = '', $statusCode = 303)
  {
    self::setSessionMessage($message, false, $path);
    static::redirect($path, $statusCode);
  }

  /**
   * Redirects user to new path with the specified error message to be displayed if it goes through the router
   *
   * @param  string $path    path to redirect to.
   * @param  string $message message to display on redirect
   * @param  integer $statusCode Redirection status code
   * @return void
   */
  public static function redirectWithError($path = '/', $message = '', $statusCode = 303)
  {
    self::setSessionMessage($message, true, $path);
    static::redirect($path, $statusCode);
  }

  /**
   * Checks so see if the session is already started, if not, it starts one.
   *
   * @return void
   */
  public static function startSessionIfNeeded()
  {
    if (session_id() === '') {
      session_start();
    }
  }

  /**
   * Sets the message to be displayed on the next time the page is loaded
   *
   * @param string  $message message to display
   * @param boolean $isError whether this is an error message or not
   * @param string $location Location of the page the message is to be displayed on
   * @return  void
   */
  public static function setSessionMessage($message = '', $isError = false, $location = null)
  {
    self::startSessionIfNeeded();
    $location = self::buildMessageKey($location);

    if ($isError) {
      $_SESSION['errorMessages'][$location] = $message;
    } else {
      $_SESSION['messages'][$location] = $message;
    }
  }

  /**
   * Builds the key to use in the session messages for the requested page
   *   Uses then current page if no location is specified
   *
   * @param  string $location Location of the requested page. Uses $_SERVER['REQUEST_URI'] then $_SERVER['SCRIPT_NAME'] if nothing set.
   * @return string
   */
  public static function buildMessageKey($location = null)
  {
    if ($location === null) {
      $location = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'];
    }

    $parsed   = parse_url($location);
    $location = isset($parsed['path']) ? $parsed['path'] : '/';
    if (!strpos($location, '.php')) {
      // we want to be as specific as possible, so if there isn't a php in the location, we need to add it in.
      $location = (str_replace('//', '/', $location . '/index.php'));
    }
    if (isset($parsed['query'])) {
      $location .= sprintf('?%s', $parsed['query']);
    }
    return hash('md4', $location);
  }

  /**
   * Gets the session error message out of the session for the current page if it has one
   *
   * @param  string $location Location of the current page. Uses $_SERVER['SCRIPT_NAME'] if nothing set.
   * @return string|null null if nothing exists
   */
  public static function getSessionErrorMessage($location = null)
  {
    self::startSessionIfNeeded();
    $key = self::buildMessageKey($location);

    if (isset($_SESSION['errorMessages'][$key])) {
      $message = $_SESSION['errorMessages'][$key];
      unset($_SESSION['errorMessages'][$key]);
      return $message;
    }
    return null;
  }

  /**
   * Gets the session message out of the session for the current page if it has one
   *
   * @param  string $location Location of the current page. Uses $_SERVER['SCRIPT_NAME'] if nothing set.
   * @return string|null null if nothing exists
   */
  public static function getSessionMessage($location = null)
  {
    self::startSessionIfNeeded();
    $key = self::buildMessageKey($location);

    if (isset($_SESSION['messages'][$key])) {
      $message = $_SESSION['messages'][$key];
      unset($_SESSION['messages'][$key]);
      return $message;
    }
    return null;
  }

  /**
   * Gets the referer for the current request
   *   Checks HTTP_REFERER and then HTTP_ORIGIN
   *
   * @return string Returns the referer or null if no referer is set.
   */
  public static function getReferer()
  {
    if (isset($_SERVER['HTTP_REFERER'])) {
      return $_SERVER['HTTP_REFERER'];
    } else if (isset($_SERVER['HTTP_ORIGIN'])) {
      return $_SERVER['HTTP_ORIGIN'];
    } else {
      return null;
    }
  }

  /**
   * Gets the origin for the current request
   *   Checks HTTP_ORIGIN and then HTTP_REFERER
   *
   * @return string Returns the origin or null if no origin is set.
   */
  public static function getOrigin()
  {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
      return $_SERVER['HTTP_ORIGIN'];
    } else if (isset($_SERVER['HTTP_REFERER'])) {
      return $_SERVER['HTTP_REFERER'];
    } else {
      return null;
    }
  }

  /**
   * Checks if the request originates from Gustavus domain to prevent.
   * This function will assume browsers that don't set a refer are from
   * Gustavus, so that older browsers don't have any issues and directly
   * visiting the URL works.
   *
   * @return boolean Returns true if request originates from a Gustavus domain.
   */
  public static function hasInternalOrigin()
  {
    $refer = static::getOrigin();
    return empty($refer) || strpos($refer, 'gustavus.edu') !== false || strpos($refer, 'gac.edu') !== false;
  }

  /**
   * Renders page not found
   *
   * @param boolean $returnPage Whether to return the page output or directly display it.
   * @return void
   */
  public static function renderPageNotFound($returnPage = false)
  {
    // we don't want the auxbox to be displayed
    $GLOBALS['templatePreferences']['auxBox'] = false;
    if (!defined('GUSTAVUS_AUTO_CORRECT_REQUESTED_PAGE')) {
      define('GUSTAVUS_AUTO_CORRECT_REQUESTED_PAGE', false);
    }
    header('HTTP/1.0 404 Not Found');
    ob_start();

    $_SERVER['REDIRECT_STATUS'] = 404;
    if (!isset($_SERVER['REDIRECT_URL'])) {
      $_SERVER['REDIRECT_URL']    = false;
    }
    include '/cis/www/errorPages/error.php';

    if ($returnPage) {
      return ob_get_clean();
    }

    exit;
  }

  /**
   * Render bad request page
   *
   * @param  boolean $returnPage Whether to return the page output or directly display it.
   * @return void
   */
  public static function renderBadRequest($returnPage = false)
  {
    // we don't want the auxbox to be displayed
    $GLOBALS['templatePreferences']['auxBox'] = false;
    if (!defined('GUSTAVUS_AUTO_CORRECT_REQUESTED_PAGE')) {
      define('GUSTAVUS_AUTO_CORRECT_REQUESTED_PAGE', false);
    }
    header('HTTP/1.0 400 Bad Request');
    ob_start();

    $_SERVER['REDIRECT_STATUS'] = 400;
    if (!isset($_SERVER['REDIRECT_URL'])) {
      $_SERVER['REDIRECT_URL']    = false;
    }
    include '/cis/www/errorPages/error.php';

    if ($returnPage) {
      return ob_get_clean();
    }

    exit;
  }


  /**
   * Renders access denied page
   *
   * @param boolean $returnPage Whether to return the page output or directly display it.
   * @return void
   */
  public static function renderAccessDenied($returnPage = false)
  {
    // we don't want the auxbox to be displayed
    $GLOBALS['templatePreferences']['auxBox'] = false;
    header('HTTP/1.0 403 Forbidden');
    ob_start();

    $_SERVER['REDIRECT_STATUS'] = 403;
    $_SERVER['REDIRECT_URL'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['SCRIPT_NAME'];
    include '/cis/www/errorPages/error.php';

    if ($returnPage) {
      return ob_get_clean();
    }

    exit;
  }
}