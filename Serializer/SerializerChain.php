<?php
/**
 * SerializerChain.php
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Serializer;

use SplObjectStorage,
    Traversable,

    InvalidArgumentException,
    RuntimeException;


/**
 * The SerializerChain class is uses a collection of serializers to pack and unpack data into
 * transmittable strings.
 *
 * When serializing, the first available serializer will be used, and the data will be tagged
 * accordingly. When unserializing, however, the tag will be processed to attempt to determine
 * which serializer
 *
 * @package Utility
 * @subpackage Serializer
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class SerializerChain implements Serializer
{
  /**
   * The name of this Serializer.
   *
   * @var string
   */
  const SERIALIZER_NAME = 'chain';

  /**
   * A collection of known serializers.
   *
   * @var array
   */
  protected $serializers;

  /**
   * The default serializer to use when no other serializer is available to process data. Used
   * primarily for unpacking.
   *
   * @var Serializer
   */
  protected $default;

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Builds a new SerializerChain instance, optionally populating it with the specified serializers.
   *
   * @param mixed $serializers
   *  <em>Optional</em>.
   *  The serializers to add. May be specified as a single Serializer instance or a collection of
   *  serializers as an array or Traversable object.
   *
   * @param Serializer $default
   *  <em>Optional</em>
   *  The serializer to use as the default serializer. If omitted, no default will be set.
   */
  public function __construct($serializers = null, Serializer $default = null)
  {
    $this->serializers = new SplObjectStorage();

    if (isset($serializers)) {
      $this->setSerializers($serializers);
    }

    if (isset($default)) {
      $this->setDefaultSerializer($default);
    }
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Retrieves a Serializer instance to use for performing serialization operations. If no
   * serializer is available, this method returns null.
   *
   * @param string $name
   *  <em>Optional</em>.
   *  The name of a serializer to retrieve. If omitted, the first available serializer will be
   *  retrieved.
   *
   * @throws InvalidArgumentException
   *  if $serializer is specified but is not a string.
   *
   * @return Serializer
   *  A serializer instance to use for performing serialization operations, or null if no
   *  serializers are available.
   */
  public function getSerializer($name = null)
  {
    $serializer = (isset($this->default) && $this->default->isAvailable()) ? $this->default : null;

    if (isset($name)) {
      if (!is_string($name)) {
        throw new InvalidArgumentException('$name is not a string.');
      }

      $name = strtolower($name);
      foreach ($this->serializers as $candidate) {
        if ($candidate->isAvailable() && (strtolower($candidate->getName()) == $name)) {
          $serializer = $candidate;
          break;
        }
      }
    } else {
      foreach ($this->serializers as $candidate) {
        if ($candidate->isAvailable()) {
          $serializer = $candidate;
          break;
        }
      }
    }

    return $serializer;
  }

  /**
   * Retrieves a collection of serializers currently used by this serializer chain, excluding the
   * default serializer.
   *
   * Note:
   *  The collection returned by this method is in no way connected to the collection backing this
   *  object. Changes made to the returned collection will not be reflected by this class or in
   *  other collections returned by this method.
   *
   * @return array
   *  A collection of serializers currently used by this serializer chain.
   */
  public function getSerializers()
  {
    $serializers = [];

    foreach ($this->serializers as $serializer) {
      $serializers[] = $serializer;
    }

    return $serializers;
  }

  /**
   * Checks if this serializer chain is using the specified serializer.
   *
   * Note:
   *  This method does not check the default serializer.
   *
   * @param Serializer $serializer
   *  The serializer for which to check.
   *
   * @return boolean
   *  True if the specified serializer is part of this serializer chain; false otherwise.
   */
  public function hasSerializer(Serializer $serializer)
  {
    return $this->serializers->contains($serializer);
  }

  /**
   * Adds the specified serializer to this serializer chain. If the serializer has already been
   * added to this serializer chain, this method returns false.
   *
   * @param Serializer $serializer
   *  The serializer to add to this serializer chain.
   *
   * @return boolean
   *  True if the serializer was added successfully; false otherwise.
   */
  public function addSerializer(Serializer $serializer)
  {
    $result = false;

    if (!$this->serializers->contains($serializer)) {
      $this->serializers->attach($serializer);
      $result = true;
    }

    return $result;
  }

  /**
   * Removes the specified serializer from this serializer chain. If the serializer has not been
   * added to this serializer chain, this method returns false.
   *
   * Note:
   *  This method will not affect the default serializer.
   *
   * @param Serializer $serializer
   *  The serializer to remove from this serializer chain.
   *
   * @return boolean
   *  True if the serializer was removed successfully; false otherwise.
   */
  public function removeSerializer(Serializer $serializer)
  {
    $result = false;

    if ($this->serializers->contains($serializer)) {
      $this->serializers->detach($serializer);
      $result = true;
    }

    return $result;
  }

  /**
   * Adds the specified serializers to this serializer chain. If a given serializer has already been
   * added to this serializer chain, it will be silently ignored.
   *
   * @param mixed $serializers
   *  The serializers to add. May be specified as a single Serializer instance or a collection of
   *  serializers as an array or Traversable object.
   *
   * @return integer
   *  The number of serializers added as a result of this operation.
   */
  public function addSerializers($serializers)
  {
    $count = 0;

    switch (true) {
      case ($serializers instanceof Serializer):
        $serializers = [$serializers];

      case (is_array($serializers)):
      case ($serializers instanceof Traversable):
        foreach ($serializers as $serializer) {
          $count += (int) $this->addSerializer($serializer);
        }
    }

    return $count;
  }

  /**
   * Removes the specified serializers from this serializer chain. If a given serializer has not
   * been added to this serializer chain, it will be silently ignored.
   *
   * Note:
   *  This method will not affect the default serializer.
   *
   * @param mixed $serializers
   *  The serializers to remove. May be specified as a single Serializer instance or a collection of
   *  serializers as an array or Traversable object.
   *
   * @return integer
   *  The number of serializers removed as a result of this operation.
   */
  public function removeSerializers($serializers)
  {
    $count = 0;

    switch (true) {
      case ($serializers instanceof Serializer):
        $serializers = [$serializers];

      case (is_array($serializers)):
      case ($serializers instanceof Traversable):
        foreach ($serializers as $serializer) {
          $count += (int) $this->removeSerializer($serializer);
        }
    }

    return $count;
  }

  /**
   * Sets the serializer to use in this serializer chain, clearing any previously assigned
   * serializers.
   *
   * @param Serializer $serializer
   *  The serializer to assign to this serializer chain.
   *
   * @return boolean
   *  True if the serializer was assigned successfully; false otherwise.
   */
  public function setSerializer(Serializer $serializer)
  {
    $this->removeAllSerializers();
    return $this->addSerializer($serializer);
  }

  /**
   * Sets the serializers to use in this serializer chain, clearing any previously assigned
   * serializers.
   *
   * @param mixed $serializers
   *  The serializers to add. May be specified as a single Serializer instance or a collection of
   *  serializers as an array or Traversable object.
   *
   * @return integer
   *  The number of serializers assigned as a result of this operation.
   */
  public function setSerializers($serializers)
  {
    $this->removeAllSerializers();
    return $this->addSerializers($serializers);
  }

  /**
   * Removes all serializers, excluding the default, from this serializer chain.
   *
   * @return integer
   *  The number of serializers removed as a result of this operation.
   */
  public function removeAllSerializers()
  {
    $count = count($this->serializers);
    $this->serializers = new SplObjectStorage();

    return $count;
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Sets the default serializer to use when no other serializer is available. The default, itself
   * must also be available to be used.
   *
   * @param Serializer $serializer
   *  The serializer to use as the default serializer.
   *
   * @return SerializerChain
   *  This SerializerChain instance.
   */
  public function setDefaultSerializer(Serializer $serializer)
  {
    $this->default = $serializer;
    return $this;
  }

  /**
   * Retrieves the current default serializer. If a default serializer has not yet been set, this
   * method returns null.
   *
   * @return Serializer
   *  The default serializer for this chain, or null if a default has not yet been set.
   */
  public function getDefaultSerializer()
  {
    return $this->default;
  }

  /**
   * Clears the default serializer. If a default serializer has not yet been set, this method does
   * nothing.
   *
   * @return SerializerChain
   *  This SerializerChain instance.
   */
  public function clearDefaultSerializer()
  {
    $this->default = null;
    return $this;
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * {@inheritdoc}
   */
  public function getName()
  {
    return self::SERIALIZER_NAME;
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable()
  {
    $available = false;

    foreach ($this->serializers as $serializer) {
      if ($serializer->isAvailable()) {
        $available = true;
        break;
      }
    }

    if (!$available && $this->default) {
      $available = $this->default->isAvailable();
    }

    return $available;
  }

  /**
   * {@inheritdoc}
   */
  public function pack($value)
  {
    $serializer = $this->getSerializer();

    if (!$serializer) {
      throw new RuntimeException('No serializers are available.');
    }

    // Serialize
    $serialized = $serializer->pack($value);

    // Add a tag so we can recognize it later.
    $tag = '::[' . $serializer->getName() . ']::';
    $serialized = $tag . $serialized;

    // Return!
    return $serialized;
  }

  /**
   * {@inheritdoc}
   */
  public function unpack($serialized)
  {
    $serializer = null;

    if (is_string($serialized) && preg_match('/^::\\[(.+?)\\]::/', $serialized, $matches)) {
      // Remove the tag
      $serialized = substr($serialized, strlen($matches[0]));

      // Try to find the serializer that claims it can process the data.
      $serializer = $this->getSerializer($matches[1]);
    } else {
      $serializer = (isset($this->default) && $this->default->isAvailable()) ? $this->default : null;
    }

    if (!$serializer) {
      throw new RuntimeException('No serializers are available.');
    }

    return $serializer->unpack($serialized);
  }
}
