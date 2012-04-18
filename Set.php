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
  private function toSentenceArgumentsArray($rowkey, $row, array $keyArray)
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
  private function toSentenceCallbacks($args, array $callbacks)
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
   * echo $set->toSentence();
   * // Outputs "Apples, Cats, and Houses"
   * </code>
   *
   * @param string $pattern Pattern to format each value of the array in sprintf() pattern format
   * @param array $keyArray Array of keys to use from sub-arrays in order of usage in $pattern. Use '[key]' for the key of the array
   * @param array $callbacks Array of callback functions to perform on each iteration
   * @param int $max Number of items to display in the sentence
   * @param string $endWord e.g. 'and' or 'or'
   * @return string
   */
  public function toSentence($pattern = '%s', array $keyArray = array(0), array $callbacks = null, $max = 0, $endWord = 'and')
  {
    assert('is_string($pattern)');
    assert('is_int($max)');
    assert('is_string($endWord)');
    assert('is_array($this->value)');

    // Filter out nulls and empty strings
    //$this->value  = array_filter($this->value);

    // Build parts of the sentence
    $parts    = array();

    foreach ($this->value as $rowkey => $row) {
      if ($pattern === '%s' && is_string($row)) {
        $row = trim($row);
        if (!empty($callbacks)) {
          $row  = $this->toSentenceCallbacks($row, $callbacks);
        }

        $parts[]  = $row;
      } else if ($pattern === '%s' && $keyArray === array(0)) {
        // Default settings, so we can take a shortcut to save time
        $row[0] = trim($row[0]);

        if (!empty($callbacks)) {
          $row[0]  = $this->toSentenceCallbacks($row[0], $callbacks);
        }

        $parts[]  = $row[0];
      } else {
        $argsArray  = $this->toSentenceArgumentsArray($rowkey, $row, $keyArray);

        if (!empty($callbacks)) {
          $argsArray  = $this->toSentenceCallbacks($argsArray, $callbacks);
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
        $smallArray = array_slice($this->value, 0, $max);

        $set = new Set($smallArray);
        $r      .= sprintf('<span class="%1$s">%2$s <small><a href="#" class="doToggle" rel="span.%1$s">(more)</a></small></span><span class="nodisplay %1$s">', $id, $this->toSentence($pattern, $keyArray, $callbacks));
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