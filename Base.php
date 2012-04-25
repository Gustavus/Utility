<?php
/**
 * @package Utility
 */
namespace Gustavus\Utility;

/**
 * Gets extended by other classes to use common functions
 *
 * @package Utility
 */
abstract class Base
{
  /**
   * @var mixed
   */
  protected $value;

  /**
   * Object Constructor
   *
   * @param mixed $param
   */
  public function __construct($param = null)
  {
    if ($param !== null) {
      $this->setValue($param);
    }
  }

  /**
   * Magical function to return the constructor param if the object is echoed
   *
   * @return mixed
   */
  public function __toString()
  {
    return (string) $this->value;
  }

  /**
   * Setter for value
   *
   * @param mixed $value
   * @return $this
   */
  public function setValue($value)
  {
    if ($this->valueIsValid($value)) {
      $this->value = $value;
      return $this;
    } else {
      throw new \DomainException('The value does not belong to the domain of this object.');
    }
  }

  /**
   * @return mixed $value
   */
  public function getValue()
  {
    return $this->value;
  }

  abstract protected function valueIsValid($value);
}