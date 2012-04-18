<?php
/**
 * @package Utility
 */
namespace Gustavus\Utility;
use \Format;

/**
 * Object for working with Arrays
 *
 * @package Utility
 */
class Set extends Base
{
  /**
   * Gets the value of the array at the specified position, regardless of its key
   *
   * Example:
   * <code>
   * $test  = array('a' => 0, 'b' => 'swiftly', 'c' => 1, 'd' => 'owls');
   * echo Format::array_at($test, 1);
   * // Outputs: swiftly
   * </code>
   *
   * @param array $array
   * @param int $position
   * @return mixed Value in $arrayToFormat
   */
  public function array_at(array $array, $position = 0)
  {
    assert('is_int($position)');

    if ($position === 0) {
      return reset($array);
    } else if ($position === count($array) - 1) {
      return end($array);
    }

    $keys = array_keys($array);
    if (isset($keys[$position])) {
      return $array[$keys[$position]];
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
  private function arrayToSentenceArgumentsArray($rowkey, $row, array $keyArray)
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
          $argsArray[]  = trim($this->array_at($row, $position));
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
  private function arrayToSentenceCallbacks($args, array $callbacks)
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
   * echo Format::arrayToSentence(array('Apples', 'Cats', 'Houses'));
   * // Outputs "Apples, Cats, and Houses"
   * </code>
   *
   * @param array $arrayToFormat Array to format as a sentence
   * @param string $pattern Pattern to format each value of the array in sprintf() pattern format
   * @param array $keyArray Array of keys to use from sub-arrays in order of usage in $pattern. Use '[key]' for the key of the array
   * @param array $callbacks Array of callback functions to perform on each iteration
   * @param int $max Number of items to display in the sentence
   * @param string $endWord e.g. 'and' or 'or'
   * @return string
   */
  public function arrayToSentence(array $arrayToFormat, $pattern = '%s', array $keyArray = array(0), array $callbacks = null, $max = 0, $endWord = 'and')
  {
    assert('is_string($pattern)');
    assert('is_int($max)');
    assert('is_string($endWord)');

    // Filter out nulls and empty strings
    //$arrayToFormat  = array_filter($arrayToFormat);

    // Build parts of the sentence
    $parts    = array();

    foreach ($arrayToFormat as $rowkey => $row) {
      if ($pattern === '%s' && is_string($row)) {
        $row = trim($row);
        if (!empty($callbacks)) {
          $row  = $this->arrayToSentenceCallbacks($row, $callbacks);
        }

        $parts[]  = $row;
      } else if ($pattern === '%s' && $keyArray === array(0)) {
        // Default settings, so we can take a shortcut to save time
        $row[0] = trim($row[0]);

        if (!empty($callbacks)) {
          $row[0]  = $this->arrayToSentenceCallbacks($row[0], $callbacks);
        }

        $parts[]  = $row[0];
      } else {
        $argsArray  = $this->arrayToSentenceArgumentsArray($rowkey, $row, $keyArray);

        if (!empty($callbacks)) {
          $argsArray  = $this->arrayToSentenceCallbacks($argsArray, $callbacks);
        }

        if (!empty($argsArray)) {
          $parts[]  = vsprintf($pattern, $argsArray);
        }
      }
    }

    // Filter out the blank parts
    $parts  = array_filter($parts);

    // Put the sentence together
    $totalRows  = count($parts);
    $r      = '';
    $id     = '';

    if ($totalRows > 0) {
      // Determine what to use as the delimiter -- "," for most sentences, or ";" for sentences that contain a comma
      $delimiter  = null;
      if ($totalRows > 2) {
        if (count(preg_grep('`,`', array_map('strip_tags', $parts))) === 0) {
          $delimiter  = ',';
        } else {
          $delimiter  = ';';
        }
      }

      if ($max > 0 && $totalRows > $max) {
        $id     = 's' . uniqid(rand());
        $smallArray = array_slice($arrayToFormat, 0, $max);

        $r      .= sprintf('<span class="%1$s">%2$s <small><a href="#" class="doToggle" rel="span.%1$s">(more)</a></small></span><span class="nodisplay %1$s">', $id, $this->arrayToSentence($smallArray, $pattern, $keyArray, $callbacks));
      }

      if ($totalRows < 3) {
        $r  .= implode(rtrim(" $endWord") . ' ', $parts);
      } else {
        $ending   = trim("$delimiter $endWord") . ' ' . array_pop($parts);
        $r  .= implode("$delimiter ", $parts) . $ending;
      }

      if (!empty($id)) {
        $r  .= sprintf(' <small><a href="#" class="doToggle" rel="span.%s">(less)</a></small></span>', $id);
      }
    } // if

    return $r;
  }
}