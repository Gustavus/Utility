<?php
namespace Gustavus\Utility\Test;
class TestObject
{
  public $name;
  public $property;

  public function __construct($value, $property = null)
  {
    $this->name = $value;
    $this->property = $property;
  }

  public function getName()
  {
    return $this->name;
  }
  public function getProperty()
  {
    return $this->property;
  }
}
