<?php
/**
 * @package Utility
 * @author  Billy Visto
 */
namespace Gustavus\Utility;

use Gustavus\TwigFactory\TwigFactory,

    Twig_Extension_StringLoader,
    ArrayAccess;

/**
 * Object for working with Arrays
 *
 * @package Utility
 * @author  Billy Visto
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
    return is_array($value) || $value instanceof ArrayAccess;
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
   * @param  string  $separator      How values should be separated
   * @return String
   */
  public function toSentence($templateString = '{{ value }}', $endWord = 'and', $max = 0, $separator = ',')
  {
    $twig = TwigFactory::getTwigFilesystem('/cis/lib/Gustavus/Utility/Views/Set/');

    // Add the string loader extension if necessary.
    $ext = new Twig_Extension_StringLoader();
    if (!$twig->hasExtension($ext->getName())) {
      $twig->addExtension($ext);
    }

    // Disable autoescaping for our input template
    $templateString = "{% autoescape false %}{$templateString}{% endautoescape %}";

    // Render!
    return new String($twig->render('sentence.twig', array('values' => $this->value, 'endWord' => $endWord, 'max' => $max, 'wordUnit' => $templateString, 'separator' => $separator)));
  }

  /**
   * Takes an array and returns an array of all the values of any nested arrays
   *
   * @return Set
   */
  public function arrayValues()
  {
    $oldArray = $this->value;
    $this->setValue(array());
    array_walk_recursive($oldArray,
        function($value, $key)
        {
          $this->value[] = $value;
        }
    );
    return $this;
  }

  /**
   * Gets an array of synonyms given an array of items to get synonyms for
   *
   * @return Set
   */
  public function getSynonyms()
  {
    $dbal = \Gustavus\Doctrine\DBAL::getDBAL('synonyms');
    $qb = $dbal->createQueryBuilder();

    $qb->select('s2.synonym')
      ->from('synonyms', 's1')
      ->innerJoin('s1', 'synonymPools', 'p1', 'p1.synonymId = s1.synonymId')
      ->innerJoin('s1', 'synonymPools', 'p2', 'p2.poolId = p1.poolId')
      ->innerJoin('s1', 'synonyms', 's2', 's2.synonymId = p2.synonymId')
      ->where('s1.synonym IN (:words)');

    $this->setValue($dbal->fetchAll($qb->getSQL(), array(':words' => implode(',', $this->value))));
    return $this->arrayValues();
  }

  /**
   * Encodes nested arrays
   *
   * @return Set
   */
  public function encodeValues()
  {
    array_walk($this->value,
        function(&$value, $key)
        {
          if (is_array($value)) {
            $value = json_encode($value);
          }
        }
    );
    return $this;
  }

  /**
   * Decodes nested arrays
   *
   * @return Set
   */
  public function decodeValues()
  {
    array_walk($this->value,
        function(&$value, $key)
        {
          if (json_decode($value) !== null) {
            $value = json_decode($value);
          }
        }
    );
    return $this;
  }

  /**
   * Flattens an array using only the values
   *
   * @return Set
   */
  public function flattenValues()
  {
    $array = $this->value;
    $this->value = array();
    array_walk_recursive($array,
        function($value)
        {
          $this->value[] = $value;
        }
    );
    return $this;
  }

  /**
   * Recursively merges arrays
   *
   * @param  array  $array Array to merge
   * @return Set
   */
  public function mergeArrays(array $array)
  {
    $this->setValue(self::recursivelyMergeArrays($this->value, $array));
    return $this;
  }

  /**
   * Recursively merges arrays maintaining keys
   *
   * @param  array|string $array    Array to merge onto. String for last iteration.
   * @param  array|string $arrayTwo Array to merge. Duplicate keys will take the value from this array. String for last iteration.
   * @return array
   */
  public static function recursivelyMergeArrays($array, $arrayTwo)
  {
    if (!is_array($array) || !is_array($arrayTwo)) {
      return $arrayTwo;
    }
    $similarKeys = array_intersect(array_keys($array), array_keys($arrayTwo));
    if (empty($similarKeys)) {
      return $array + $arrayTwo;
    }
    $return = $array + $arrayTwo;
    foreach ($similarKeys as $similarKey) {
      $return[$similarKey] = self::recursivelyMergeArrays($array[$similarKey], $arrayTwo[$similarKey]);
    }
    return $return;
  }

  /**
   * Format an array
   *
   * Usage:
   * <code>
   * $test = array(
   *  array('one', 'two', 'three'),
   *  array('four', 'five', 'six'),
   *  array('seven', 'eight', 'nine'),
   * );
   *
   * echo (new Set($test))->format('%s %s and %s ', array(0, 1, 2))->getValue();
   * // Outputs "one two and three four five and six seven eight and nine "
   * </code>
   *
   * @param string $pattern Pattern to format each value of the array in sprintf() pattern format
   * @param array $keyArray Array of keys to use from sub-arrays in order of usage in $pattern. Use '[key]' for the key of the array
   * @param array $callbacks Functions to perform on each value
   * @return String
   */
  public function format($pattern = '%s', array $keyArray = array(0), array $callbacks = array())
  {
    assert('is_string($pattern)');

    $r  = '';

    if (count($this->value) > 0) {
      if (!is_array($callbacks)) {
        $callbacks = array($callbacks);
      }

      foreach ($this->value as $rowkey => $row) {
        $argsArray = array();
        if (is_string($row)) {
          foreach ($keyArray as $position => $key) {
            if ($key === '[key]') {
              $argsArray[] = $rowkey;
            } else {
              $argsArray[] = $row;
            }
          } // foreach
        } else {
          foreach ($keyArray as $position => $key) {
            if ($key === '[key]') {
              $argsArray[] = $rowkey;
            } else if (isset($row[$key])) {
              $argsArray[] = $row[$key];
            } else {
              $argsArray[] = self::array_at($row, $position);
            }
          } // foreach
        } // if

        if ($callbacks) {
          foreach ($callbacks as $callback) {
            $argsArray = array_map($callback, $argsArray);
          }
        } // if

        $r .= vsprintf($pattern, $argsArray);
      } // foreach
    } // if

    return new String($r);
  }

  /**
   * Builds a url from the current associative array.
   *   Supports arrays returned by parse_url.
   *   Array can have keys of:
   *   <ul>
   *     <li>host</li>
   *     <li>scheme</li>
   *     <li>port</li>
   *     <li>path</li>
   *     <li>query</li>
   *     <li>fragment</li>
   *   </ul>
   *
   * @return String
   */
  public function buildURL()
  {
    $url = '';
    if (isset($this->value['host'])) {
      if (isset($this->value['scheme'])) {
        $url .= sprintf('%s://', $this->value['scheme']);
      }
      $url .= $this->value['host'];
      if (isset($this->value['port'])) {
        $url .= sprintf(':%s', $this->value['port']);
      }
    }

    if (isset($this->value['path'])) {
      $url .= $this->value['path'];
    }
    if (isset($this->value['query'])) {
      $url .= sprintf('?%s', $this->value['query']);
    }
    if (isset($this->value['fragment'])) {
      $url .= sprintf('#%s', $this->value['fragment']);
    }
    return new String($url);
  }
}
