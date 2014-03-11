<?php
/**
 * Serializer.php
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Serializer;



/**
 * The Serializer interface provides an abstraction layer to the serialization process to allow
 * seamless transition between serialization implementations.
 *
 * Implementation Note:
 *  The semi-obscure names are to prevent potential conflicts with PHP's Serializable interface.
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
interface Serializer
{
  /**
   * Retrieves the name of this serializer. Can be used to determine which implementation is being
   * used.
   *
   * @return string
   *  The name of this serializer.
   */
  public function getName();

  /**
   * Checks if this serializer is available in the current environment.
   *
   * @return boolean
   *  True if the serializer is available for use in the current environment; false otherwise.
   */
  public function isAvailable();

  /**
   * Serializes the specified value, converting it to a binary string.
   *
   * @param mixed $value
   *  The value to serialize.
   *
   * @throws RuntimeException
   *  if this serializer is not available in the current environment.
   *
   * @return string
   *  A binary string representing the serialized value.
   */
  public function pack($value);

  /**
   * Unserializes the specified data and returns the original data.
   *
   * @param string $serialized
   *  A binary string representing the serialized data.
   *
   * @throws RuntimeException
   *  if this serializer is not available in the current environment.
   *
   * @return mixed
   *  The unserialized data, or false if the data could not be serialized.
   */
  public function unpack($serialized);
}
