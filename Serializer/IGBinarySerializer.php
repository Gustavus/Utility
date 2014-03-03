<?php
/**
 * IGBinarySerializer.php
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Serializer;

use RuntimeException;



/**
 * The IGBinarySerializer class implements the Serializer interface using IGBinary's serialization
 * implementation.
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class IGBinarySerializer implements Serializer
{
  /**
   * The name of this Serializer.
   *
   * @var string
   */
  const SERIALIZER_NAME = 'igbinary';

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * {@inheritDoc}
   */
  public function getName()
  {
    return self::SERIALIZER_NAME;
  }

  /**
   * {@inheritDoc}
   */
  public function isAvailable()
  {
    return extension_loaded('igbinary') && function_exists('igbinary_serialize') && function_exists('igbinary_unserialize');
  }

  /**
   * {@inheritDoc}
   */
  public function pack($value)
  {
    if (!$this->isAvailable()) {
      throw new RuntimeException('IGBinary is not available in the current environment.');
    }

    // Perform initial serialization
    $serialized = igbinary_serialize($value);

    // Return!
    return $serialized;
  }

  /**
   * {@inheritDoc}
   */
  public function unpack($serialized)
  {
    if (!$this->isAvailable()) {
      throw new RuntimeException('IGBinary is not available in the current environment.');
    }

    return igbinary_unserialize($serialized);
  }

}
