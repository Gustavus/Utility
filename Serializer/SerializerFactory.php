<?php
/**
 * SerializerFactory.php
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Serializer;

use InvalidArgumentException;


/**
 * The SerializerFactor class is responsible for creating new Serializer instances in a transparent
 * manner.
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class SerializerFactory
{
  /**
   * A collection of known serializers.
   *
   * @var array
   */
  protected $serializers;

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Builds a new SerializerFactory instance.
   */
  public function __construct()
  {
    $this->serializers = [];

    // @todo:
    // If we care at some point in the future, perhaps upgrade this so it's not hard-coded to look
    // for a couple implementations.
    $serializer = new IGBinarySerializer();
    $this->serializers[strtolower($serializer->getName())] = $serializer;

    $serializer = new PHPSerializer();
    $this->serializers[strtolower($serializer->getName())] = $serializer;
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

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
  public function getSerializer($serializer = null)
  {
    $result = null;

    if (isset($serializer)) {
      if (!is_string($serializer)) {
        throw new InvalidArgumentException('$serializer is not a string.');
      }

      $serializer = strtolower($serializer);
      $serializer = isset($this->serializers[$serializer]) ? $this->serializers[$serializer] : null;

      if ($serializer instanceof Serializer && $serializer->isAvailable()) {
        $result = $serializer;
      }
    } else {
      foreach ($this->serializers as $serializer) {
        if ($serializer instanceof Serializer && $serializer->isAvailable()) {
          $result = $serializer;
          break;
        }
      }
    }

    return $result;
  }
}
