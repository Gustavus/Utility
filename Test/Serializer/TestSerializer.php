<?php
/**
 * TestSerializer.php
 *
 * @package Utility
 * @subpackage Test\Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test\Serializer;

use Gustavus\Utility\Serializer\Serializer;



/**
 * Test serializer implementation for testing instantiation and management of serializers.
 */
class TestSerializer implements Serializer
{
  protected $name;
  protected $available;
  protected $packer;
  protected $unpacker;

  /**
   * Creates a new TestSerializer instance with the specified configuration.
   *
   * @param string $name
   *  The name of this serializer. Defaults to 'test'.
   *
   * @param boolean $available
   *  Whether or not this serializer is available. Defaults to true.
   *
   * @param callable $packer
   *  The function to call for performing pack operations. Defaults to null.
   *
   * @param callable $unpacker
   *  The function to call for performing unpack operations. Defaults to null.
   */
  public function __construct($name = 'test', $available = true, callable $packer = null, callable $unpacker = null)
  {
    $this->name = $name;
    $this->available = $available;
    $this->packer = $packer;
    $this->unpacker = $unpacker;
  }

  /**
   * {@inheritDoc}
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * {@inheritDoc}
   */
  public function isAvailable()
  {
    return !!$this->available;
  }

  /**
   * {@inheritDoc}
   */
  public function pack($value)
  {
    $callback = $this->packer;
    return $callback ? $callback($value) : null;
  }

  /**
   * {@inheritDoc}
   */
  public function unpack($serialized)
  {
    $callback = $this->unpacker;
    return $callback ? $callback($serialized) : false;
  }
}
