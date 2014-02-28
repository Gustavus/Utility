<?php
/**
 * PHPSerializer.php
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Serializer;

use RuntimeException;



/**
 * The PHPSerializer class implements the Serializer interface using PHP's default serializion
 * implementation, with patches for known serialization issues.
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class PHPSerializer implements Serializer
{
  /**
   * The name of this Serializer.
   *
   * @var string
   */
  const SERIALIZER_NAME = 'php';

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
    return true;
  }

  /**
   * {@inheritDoc}
   */
  public function pack($value)
  {
    // Perform initial serialization
    $serialized = serialize($value);

    // Patch for PHP bug #65967 (SplObjectStorage adds corrupt data to serialization streams)
    $serialized = str_replace("\x00gcdata", '_gcdata', $serialized);

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
    $tag = '::<' . self::SERIALIZER_NAME . '>::';
    $len = strlen($tag);
    $result = false;

    if (strlen($serialized) > $len && substr($serialized, 0, $len) == $tag) {
      $serialized = substr($serialized, $len);
      $result = unserialize($serialized);
    } else {
      trigger_error('Serialization format tag absent or malformed.', E_USER_WARNING);
    }

    return $result;
  }

}
