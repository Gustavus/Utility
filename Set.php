<?php
/**
 * @package Utility
 */
namespace Gustavus\Utility;

use Gustavus\TwigFactory\TwigFactory,
  ArrayAccess;

/**
 * Object for working with Arrays
 *
 * @package Utility
 */
class Set extends Base implements ArrayAccess
{
  /**
   * Function to overide abstract function in base to make sure the value is valid
   *
   * @param  mixed $value value passed into setValue()
   * @return boolean
   */
  final protected function valueIsValid($value)
  {
    return is_array($value);
  }

  /**
   * Magical function to return the constructor param if the object is echoed
   *
   * @return String
   */
  public function __toString()
  {
    return $this->toSentence()->getValue();
  }

  // ArrayAccess functions

  /**
   * @param mixed $offset
   * @return boolean
   */
  public function offsetExists($offset)
  {
    return isset($this->value[$offset]);
  }

  /**
   * @param mixed $offset
   * @return mixed
   */
  public function offsetGet($offset)
  {
    return $this->value[$offset];
  }

  /**
   * @param mixed $offset
   * @param mixed $value
   * @return void
   */
  public function offsetSet($offset, $value)
  {
    $this->value[$offset] = $value;
  }

  /**
   * @param mixed $offset
   * @return void
   */
  public function offsetUnset($offset)
  {
    unset($this->value[$offset]);
  }

  /**
   * @param callable $callback
   * @param array $array
   * @param array $arguments
   * @return array
   */
  protected function mapRecursiveArray($callback, array $array, array $arguments)
  {
    $mappedValues = array();

    foreach ($array as $key => $value) {
      $mappedValues[$key] = is_array($value)
        ? $this->mapRecursiveArray($callback, $value, $arguments)
        : call_user_func_array($callback, (array) $value + $arguments);
    }

    return $mappedValues;
  }

  /**
   * @param callable $callback
   * @return $this
   */
  public function mapRecursive($callback)
  {
    $arguments = func_get_args();
    // Drop the first argument, because that is is our callback function
    array_shift($arguments);

    $this->value = $this->mapRecursiveArray($callback, $this->value, $arguments);

    return $this;
  }

  /**
   * Converts the set to title case
   *
   * @param array $exceptions
   * @return $this
   */
  public function titleCase(array $exceptions = null)
  {
    $this->mapRecursive(function ($value) use ($exceptions) {
      $string = new String($value);
      return $string->titleCase($exceptions)->getValue();
    });

    return $this;
  }

  /**
   * Gets the value of the array at the specified position, regardless of its key
   *
   * @param int $position
   * @return mixed Value in $this->value
   */
  public function at($position = 0)
  {
    assert('is_int($position)');
    assert('is_array($this->value)');
    if ($position === 0) {
      return reset($this->value);
    } else if ($position === count($this->value) - 1) {
      return end($this->value);
    }

    $keys = array_keys($this->value);
    if (isset($keys[$position])) {
      return $this->value[$keys[$position]];
    } else {
      return false;
    }
  }

  /**
   * @param string|integer $rowkey
   * @param mixed $row
   * @param array $keyArray
   * @return array
   */
  private function sentenceArgumentsArray($rowkey, $row, array $keyArray)
  {
    assert('is_string($rowkey) || is_int($rowkey)');

    $argsArray  = array();

    if (is_string($row)) {
      foreach ($keyArray as $key) {
        if ($key === '[key]') {
          $argsArray[]  = trim($rowkey);
        } else {
          $argsArray[]  = trim($row);
        }
      } // foreach
    } else {
      foreach ($keyArray as $position => $key) {
        if ($key === '[key]') {
          $argsArray[]  = trim($rowkey);
        } else if (isset($row[$key])) {
          $argsArray[]  = trim($row[$key]);
        } else if (is_array($row)) {
          $set = new Set($row);
          $argsArray[]  = trim($set->at($position));
        } else {
          $argsArray[]  = trim($row);
        }
      } // foreach
    } // if

    return $argsArray;
  }

  /**
   * Applies callbacks to the arguments array
   *
   * @param mixed $args
   * @param array $callbacks
   * @return array
   */
  private function sentenceCallbacks($args, array $callbacks)
  {
    if (!empty($callbacks)) {
      foreach ($callbacks as $callback) {
        if (is_array($args)) {
          $args = array_map($callback, $args);
        } else {
          $args = call_user_func($callback, $args);
        }
      }
    } // if

    return $args;
  }

  /**
   * Convert an array to sentence list format (e.g. 'Apples, Cats, and Houses')
   *
   * Usage:
   * <code>
   * $set = new Set(array('Apples', 'Cats', 'Houses'));
   * echo $set->sentence();
   * // Outputs "Apples, Cats, and Houses"
   * </code>
   *
   * echo $set->sentence({{ key }}-{{ value }});
   * // Outputs "0-Apples, 1-Cats, and 2-Houses"
   * </code>
   *
   * @param  string  $templateString twig code for how you want each word parsed.
   * @param  string  $endWord        e.g. 'and' or 'or'
   * @param  integer $max            Number of items to display in the sentence
   * @return String
   */
  public function toSentence($templateString = '{{ value }}', $endWord = 'and', $max = 0)
  {
    $twig = TwigFactory::getTwigFilesystem("/cis/lib/Gustavus/Utility/Views/Set/");

    $templateString = "{% autoescape false %}$templateString{% endautoescape %}";

    return new String($twig->render('sentence.twig', array('values' => $this->value, 'endWord' => $endWord, 'max' => $max, 'wordUnit' => $templateString)));
  }
}
