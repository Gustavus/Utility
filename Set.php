<?php
/**
 * @package Utility
 */
namespace Gustavus\Utility;

/**
 * Object for working with Arrays
 *
 * @package Utility
 */
class Set extends Base
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
   * @return mixed
   */
  public function __toString()
  {
    return $this->sentence();
  }

  /**
   * @param callable $callback
   * @param array $array
   * @param array $arguments
   * @return array
   */
  public function mapRecursiveArray($callback, array $array, array $arguments)
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
      return $string->titleCase($exceptions);
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
   * @param string $pattern Pattern to format each value of the array in sprintf() pattern format
   * @param array $keyArray Array of keys to use from sub-arrays in order of usage in $pattern. Use '[key]' for the key of the array
   * @param array $callbacks Array of callback functions to perform on each iteration
   * @param int $max Number of items to display in the sentence
   * @param string $endWord e.g. 'and' or 'or'
   * @return string
   */
  public function sentence($pattern = '%s', array $keyArray = array(0), array $callbacks = null, $max = 0, $endWord = 'and')
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
          $row  = $this->sentenceCallbacks($row, $callbacks);
        }

        $parts[]  = $row;
      } else if ($pattern === '%s' && $keyArray === array(0)) {
        // Default settings, so we can take a shortcut to save time
        $row[0] = trim($row[0]);

        if (!empty($callbacks)) {
          $row[0]  = $this->sentenceCallbacks($row[0], $callbacks);
        }

        $parts[]  = $row[0];
      } else {
        $argsArray  = $this->sentenceArgumentsArray($rowkey, $row, $keyArray);

        if (!empty($callbacks)) {
          $argsArray  = $this->sentenceCallbacks($argsArray, $callbacks);
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
        $r      .= sprintf('<span class="%1$s">%2$s <small><a href="#" class="doToggle" rel="span.%1$s">(more)</a></small></span><span class="nodisplay %1$s">', $id, $set->sentence($pattern, $keyArray, $callbacks));
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

  public function newSentence($pattern = '{{ value }}', $endWord = 'and', $max = 0)
  {
    $loader = new \Twig_Loader_String();
    $twig =  new \Twig_Environment($loader, array(
      'cache'       => '/cis/www-etc/cache/Twig',
      'auto_reload' => true,
      'autoescape' => false,
    ));

    //$template = new \Twig_Loader_String($pattern);
    //$twig = new \Twig_Environment($loader, $pattern);
    //
    return \Gustavus\TwigFactory\TwigFactory::renderTwigFilesystemTemplate("/cis/lib/Gustavus/Utility/Views/Set/sentence.twig", array('values' => $this->value, 'endWord' => $endWord, 'max' => $max, 'wordUnit' => $twig->loadTemplate($pattern)));
  }
}