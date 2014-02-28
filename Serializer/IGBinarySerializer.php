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

    // Tag our data so we can identify it later.
    $tag = '::<' . self::SERIALIZER_NAME . '>::';
    $serialized = $tag . $serialized;

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

    $tag = '::<' . self::SERIALIZER_NAME . '>::';
    $len = strlen($tag);
    $result = false;

    if (strlen($serialized) > $len && substr($serialized, 0, $len) == $tag) {
      $serialized = substr($serialized, $len);
      $result = igbinary_unserialize($serialized);
    } else {
      trigger_error('Serialization format tag absent or malformed.', E_USER_WARNING);
    }

    return $result;
  }

}
