<?php
/**
 * GlobalSerializerFactory.php
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Serializer;



/**
 * The GlobalSerializerFactory class maintains global accessiblity to a single SerializerFactory
 * instance.
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class GlobalSerializerFactory
{
  /**
   * Our global factory instance.
   *
   * @var SerializerFactory
   */
  protected static $factory;

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Retrieves the global SerializerFactory instance, building a new instance as necessary.
   *
   * @return SerializerFactory
   *  The global SerializerFactory instance.
   */
  public static function getFactory()
  {
    if (!isset(static::$factory)) {
      static::$factory = new SerializerFactory();
    }

    return static::$factory;
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
    $factory = static::getFactory();
    assert('$factory instanceof \\Gustavus\\Utility\\Serializer\\SerializerFactory');

    return $factory->getSerializer($serializer);
  }

}
