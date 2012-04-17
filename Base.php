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
  protected $constructorParam;

  /**
   * Object Constructor
   *
   * @param mixed $param
   */
  public function __construct($param = null)
  {
    $this->constructorParam = $param;
  }

  /**
   * Magical function to return the constructor param if the object is echoed
   *
   * @return mixed
   */
  public function __toString()
  {
    return $this->constructorParam;
  }

  /**
   * Setter for constructorParam
   *
   * @param mixed $value
   */
  public function setConstructorParam($value)
  {
    $this->constructorParam = $value;
  }
}