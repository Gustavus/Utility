<?php
/**
 * CURLRequest.php
 *
 * @package Utility
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility;

use InvalidArgumentException,
    RuntimeException;



/**
 * The CURLRequest class is a simple wrapper around the native cURL functions.
 *
 * While this class can slightly clean up traditional cURL requests, its primary function is to
 * provide the ability to extend and mock the cURL functions.
 *
 * As this class is simply a wrapper around an existing extension, any predefined values or
 * constants will need to come from the cURL extension, unless otherwise noted.
 *
 * @package Utility
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class CURLRequest
{
  /**
   * A reference to our cURL handle.
   *
   * @var resource
   */
  protected $handle;

  /**
   * A collection of default options with which to initiate cURL.
   *
   * @var array
   */
  protected $curl_defaults = [
    CURLOPT_HTTPGET         => true,
    CURLOPT_RETURNTRANSFER  => true
  ];

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Creates a new CURLRequest instance.
   *
   * @param string $url
   *  Optional. The url that will receive this request. If omitted, the url must be set before
   *  executing, either by
   *
   * @param array $options
   *  Optional. A mapping of options with which to configure cURL.
   *
   * @throws InvalidArgumentException
   *  if $url or any of the cURL options specified in $options cannot be set.
   */
  public function __construct($url = null, array $options = [])
  {
    assert('extension_loaded(\'curl\')');

    $this->handle = curl_init($url);
    $this->setOptions($this->curl_defaults + $options, true);
  }

  /**
   * Ensures the backing cURL handles are closed and cleaned up properly.
   */
  public function __destruct()
  {
    $this->close();
  }

  /**
   * Clones this request instance, ensuring the two instances do not affect each other.
   */
  public function __clone()
  {
    if (isset($this->handle)) {
      $this->handle = curl_copy_handle($this->handle);
    }
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Checks if this CURLRequest has been closed.
   *
   * @return boolean
   *  True if this CURLRequest instance is closed; false otherwise.
   */
  public function isClosed()
  {
    return !isset($this->handle);
  }

  /**
   * Retrieves the specified information about the previous request.
   *
   * @param integer $parameter
   *  Optional. The parameter to retrieve. The parameter should be specified using one of the
   *  CURLINFO_* constants, or its integer equivalent.
   *
   * @throws RuntimeException
   *  if this CURLRequest instance has been closed.
   *
   * @return mixed
   *  An associative array containing information about the previous request, or the value
   *  associated with the specified parameter.
   */
  public function getInfo($parameter = 0)
  {
    if ($this->isClosed()) {
      throw new RuntimeException('Illegal State: CURLRequest is closed.');
    }

    // @todo:
    // Check (and handle) what happens when we pass invalid input, no data is associated with the
    // parameter, or the handle is closed (or otherwise invalid).
    //
    // PHP's docs don't define this behavior. :/
    return curl_getinfo($this->handle, $parameter);
  }

  /**
   * Retrieves an error code for the last error to occur on this request. If an error has not yet
   * occurred, this method returns 0.
   *
   * @throws RuntimeException
   *  if this CURLRequest instance has been closed.
   *
   * @return integer
   *  The error number of the last error to occur on this request, or 0 if an error has not yet
   *  occurred.
   */
  public function getLastErrorNumber()
  {
    if ($this->isClosed()) {
      throw new RuntimeException('Illegal State: CURLRequest is closed.');
    }

    return curl_errno($this->handle);
  }

  /**
   * Retrieves a message describing the last error to occur on this request. If an error has not yet
   * occurred, this method returns an empty string.
   *
   * @throws RuntimeException
   *  if this CURLRequest instance has been closed.
   *
   * @return string
   *  An error message describing the last error to occur on this request, or an empty string if an
   *  error has not yet occurred.
   */
  public function getLastErrorMessage()
  {
    if ($this->isClosed()) {
      throw new RuntimeException('Illegal State: CURLRequest is closed.');
    }

    return curl_error($this->handle);
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Closes this CURLRequest, freeing any resources used by the backing extension. If this instance
   * has already been closed, this method returns silently.
   *
   * @return void
   */
  public function close()
  {
    if (isset($this->handle)) {
      curl_close($this->handle);
      unset($this->handle);
    }
  }

  /**
   * Configures this request instance by setting the specified cURL option.
   *
   * @param integer $option
   *  The cURL option to set. Use the CURLOPT_* constants or their integer equivalents.
   *
   * @param mixed $value
   *  The value to set for the specified option.
   *
   * @param boolean $strict
   *  Optional. If set, an exception will be thrown if the option cannot be set as specified.
   *
   * @throws InvalidArgumentException
   *  if this method is unable to set all cURL options and $strict is set.
   *
   * @throws RuntimeException
   *  if this CURLRequest instance has been closed.
   *
   * @return boolean
   *  True if the option was set successfully; false otherwise.
   */
  public function setOption($option, $value, $strict = false)
  {
    if ($this->isClosed()) {
      throw new RuntimeException('Illegal State: CURLRequest is closed.');
    }

    if (!($result = @curl_setopt($this->handle, $option, $value)) && $strict) {
      throw new InvalidArgumentException('Unable to set cURL option.');
    }

    return $result;
  }

  /**
   * Configures this request instance by setting the specified cURL options.
   *
   * <strong>Note:</strong> If any option is unable to be set, this method immediately returns false
   * and <em>stops processing further options.</em> This can potentially prevent the entire options
   * collection from being set. As such, it is highly recommended that the $strict option be set
   * to ensure any oversights are caught immediately, rather than potentially propogating an error
   * further into the application.
   *
   * @param array $options
   *  A mapping of options with which to configure cURL. The keys must consist of valid cURL option
   *  constants or their integer equivalents, with values matching the expected data type for the
   *  option.
   *
   * @param boolean $strict
   *  Optional. If set, an exception will be thrown if any of the options cannot be set as
   *  specified.
   *
   * @throws InvalidArgumentException
   *  if this method is unable to set all cURL options and $strict is set.
   *
   * @throws RuntimeException
   *  if this CURLRequest instance has been closed.
   *
   * @return boolean
   *  True if all options were set successfully; false otherwise.
   */
  public function setOptions(array $options, $strict = false)
  {
    if ($this->isClosed()) {
      throw new RuntimeException('Illegal State: CURLRequest is closed.');
    }

    if (!($result = @curl_setopt_array($this->handle, $options)) && $strict) {
      throw new InvalidArgumentException('Unable to set all cURL options.');
    }

    return $result;
  }

  /**
   * Executes this request, returning the response as a string.
   *
   * <strong>Note:</strong> Each execution <em>does not</em> clear any existing/remaining data from
   * any previous executions. Subsequent response data will simply be merged with the previous data.
   *
   * @param string $url
   *  Optional. The URL to which to send the request. If omitted, the request will go to the
   *  currently configured URL.
   *
   * @throws InvalidArgumentException
   *  if $url is specified, but cannot be set.
   *
   * @throws RuntimeException
   *  if this CURLRequest instance has been closed.
   *
   * @return string|boolean
   *  The response as a string, or false if the request could not be completed.
   */
  public function execute($url = null)
  {
    if ($this->isClosed()) {
      throw new RuntimeException('Illegal State: CURLRequest is closed.');
    }

    if (!empty($url) && (!is_string($url) || !$this->setOption(CURLOPT_URL, $url))) {
      throw new InvalidArgumentException('Unable to set URL: ' . (is_scalar($url) ? $url : '(' . gettype($url) . ')'));
    }

    return curl_exec($this->handle);
  }

}
