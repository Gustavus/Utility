<?php
/**
 * GlobalSerializerChain.php
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Serializer;



/**
 * The GlobalSerializerChain class maintains global accessiblity to a single SerializerChain
 * instance.
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class GlobalSerializerChain
{
  /**
   * Our global serializer chain instance.
   *
   * @var SerializerChain
   */
  protected static $chain;

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Retrieves the global SerializerChain instance, building a new instance as necessary.
   *
   * @return SerializerChain
   *  The global SerializerChain instance.
   */
  public static function getSerializerChain()
  {
    if (!isset(static::$chain)) {
      static::$chain = new SerializerChain();

      // Add serializers here.
      static::$chain->addSerializer(new IGBinarySerializer());

      // Use the PHP serializer as our default.
      static::$chain->setDefaultSerializer(new PHPSerializer());
    }

    return static::$chain;
  }

  /**
   * Retrieves a Serializer instance to use for performing serialization. If no Serializer is
   * available, this method returns null.
   *
   * @param string $serializer
   *  <em>Optional</em>.
   *  The name of the serializer to retrieve. If omitted, the first available serializer will be
   *  used.
   *
   * @throws InvalidArgumentException
   *  if $serializer is specified but is not a string.
   *
   * @return Serializer
   */
  public static function getSerializer($serializer = null)
  {
    $chain = static::getSerializerChain();
    assert('$chain instanceof \\Gustavus\\Utility\\Serializer\\SerializerChain');

    return $chain->getSerializer($serializer);
  }

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
  public static function pack($value)
  {
    $chain = static::getSerializerChain();
    assert('$chain instanceof \\Gustavus\\Utility\\Serializer\\SerializerChain');

    return $chain->pack($value);
  }

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
  public static function unpack($serialized)
  {
    $chain = static::getSerializerChain();
    assert('$chain instanceof \\Gustavus\\Utility\\Serializer\\SerializerChain');

    return $chain->unpack($serialized);
  }
}
