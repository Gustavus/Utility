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
   * @return void
   */
  public static function redirect($path = '/')
  {
    $_POST = null;
    header('Location: ' . $path, true, 303);
    exit;
  }

  /**
   * Redirects user to new path with the specified message to be displayed if it goes through the router
   *
   * @param  string $path    path to redirect to.
   * @param  string $message message to display on redirect
   * @return void
   */
  public static function redirectWithMessage($path = '/', $message = '')
  {
    self::setSessionMessage($message, false, $path);
    static::redirect($path);
  }

  /**
   * Redirects user to new path with the specified error message to be displayed if it goes through the router
   *
   * @param  string $path    path to redirect to.
   * @param  string $message message to display on redirect
   * @return void
   */
  public static function redirectWithError($path = '/', $message = '')
  {
    self::setSessionMessage($message, true, $path);
    static::redirect($path);
  }

  /**
   * Checks so see if the session is already started, if not, it starts one.
   *
   * @return void
   */
  private static function startSessionIfNeeded()
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
   * @param  string $location Location of the requested page
   * @return string
   */
  private static function buildMessageKey($location = null)
  {
    if ($location === null) {
      $location = $_SERVER['SCRIPT_NAME'];
    } else {
      $parsed   = parse_url($location);
      $location = $parsed['path'];
      if (!strpos($location, '.php')) {
        $location = (str_replace('//', '/', $location . '/index.php'));
      }
    }
    return hash('md4', $location);
  }

  /**
   * Gets the session error message out of the session for the current page if it has one
   *
   * @return string|null null if nothing exists
   */
  public static function getSessionErrorMessage()
  {
    self::startSessionIfNeeded();
    $key = hash('md4', $_SERVER['SCRIPT_NAME']);
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
   * @return string|null null if nothing exists
   */
  public static function getSessionMessage()
  {
    self::startSessionIfNeeded();
    $key = hash('md4', $_SERVER['SCRIPT_NAME']);
    if (isset($_SESSION['messages'][$key])) {
      $message = $_SESSION['messages'][$key];
      unset($_SESSION['messages'][$key]);
      return $message;
    }
    return null;
  }
}