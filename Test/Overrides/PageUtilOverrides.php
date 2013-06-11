<?php
namespace Gustavus\Utility;

/**
 * Override that removes header functionality and returns the header we are trying to set
 * @return  string
 */
function header($value)
{
  return $value;
}