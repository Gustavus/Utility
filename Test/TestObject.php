<?php
namespace Gustavus\Utility\Test;
class TestObject{
  public $name;

  public function __construct($value)
  {
    $this->name = $value;
  }

  public function getName()
  {
    return $this->name;
  }
}