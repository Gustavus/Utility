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
    $this->value = $param;
  }

  /**
   * Magical function to return the constructor param if the object is echoed
   *
   * @return mixed
   */
  public function __toString()
  {
    return $this->value;
  }

  /**
   * Setter for value
   *
   * @param mixed $value
   */
  public function setConstructorParam($value)
  {
    $this->value = $value;
  }
}