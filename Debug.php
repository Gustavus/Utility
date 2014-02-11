<?php
/**
 * Debug.php
 *
 * @package Utility
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility;

use ReflectionObject,

    InvalidArgumentException;




/**
 * The Debug class provides common debugging utilities and functionality.
 *
 * @package Utility
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class Debug
{
  /**
   * The number of spaces by which to increase the indentation depth when traversing into arrays or
   * objects while dumping variables. Should be a positive integer.
   *
   * @var integer
   */
  const DUMP_INDENT_INCREMENT = 2;


  /**
   * Recursively dumps the specified variable and any properties or elements it contains. If an
   * object implements the DebugPrinter interface, this function will use that object's
   * generateDebugOutput method rather than the default behavior of recursively dumping its
   * children.
   *
   * @param mixed $var
   *  The variable to dump.
   *
   * @param boolean $capture
   *  <em>Optional</em>.
   *  Whether or not to capture and return the debug output, or to print directly to the standard
   *  output stream. If true, the debug output will be returned as a string. Defaults to false.
   *
   * @param integer $indent
   *  <em>Optional</em>.
   *  The initial depth of the indentation. Can be used to make nested calls while maintaining
   *  proper indentation.
   *
   * @param integer $maxdepth
   *  <em>Optional</em>.
   *  The maximum depth into an object or array this function will traverse. If zero, no limit will
   *  be imposed. Defaults to zero.
   *
   * @throws InvalidArgumentException
   *  if $indent or $maxdepth is a not an integer or contains a negative value.
   *
   * @return string
   *  The captured debug output if, and only if, $capture was set; null otherwise.
   */
  public static function dump($var, $capture = false, $indent = 0, $maxdepth = 6)
  {
    if (!is_int($indent) || $indent < 0) {
      throw new InvalidArgumentException('$indent is not an integer or contains a negative value.');
    }

    if (!is_int($maxdepth) || $maxdepth < 0) {
      throw new InvalidArgumentException('$maxdepth is not an integer or contains a negative value.');
    }


    // Used to detect recursion in arrays and objects
    $reckey = '__recursion_key-' . time();
    $reclevel = 0;
    $recmap = [];

    // Formatter which does all the work of actually formatting data...
    $formatter = function ($indent, $capture, $key, &$value) use (&$formatter, &$maxdepth, &$reckey, &$reclevel, &$recmap) {
      $padding = str_repeat(' ', $indent);
      $buffer = $padding;

      ++$reclevel;
      $type = gettype($value);

      if (isset($key)) {
        $buffer .= "{$key} => ";
      }

      switch ($type) {
        case 'boolean':
          $value = $value ? 'true' : 'false';

        case 'integer':
        case 'double':
          $buffer .= "({$type}): {$value}\n";
            break;

        case 'string':
          $encoding = mb_detect_encoding($value);
          $len = $encoding ? mb_strlen($value, $encoding) : mb_strlen($value);

          // @todo:
          // Perhaps add slashes to the string here...?

          $buffer .= "(string[{$len}]): \"{$value}\"\n";
            break;

        case 'array':
          $count = (isset($value[$reckey]) ? count($value) - 1 : count($value));
          $buffer .= "(array[{$count}]) {\n";

          if ($reclevel <= $maxdepth || $maxdepth < 1) {
            if (!isset($value[$reckey])) {

              $value[$reckey] = $reclevel;

              foreach ($value as $key => &$val) {
                if ($key !== $reckey) {
                  $pv = print_r($val, true);
                  $buffer .= $formatter($indent + static::DUMP_INDENT_INCREMENT, true, $key, $val);
                }
              }

              unset($value[$reckey]);
            } else {
              $diff = $reclevel - $value[$reckey];
              $buffer .= str_repeat(' ', $indent + static::DUMP_INDENT_INCREMENT) . "**RECURSION: {$diff} level(s)**\n";
            }
          } else {
            $buffer .= str_repeat(' ', $indent + static::DUMP_INDENT_INCREMENT) . "...\n";
          }

          $buffer .= "{$padding}}\n";
            break;

        case 'object':
          $class = get_class($value);
          $buffer .= "(object): {$class} {\n";

          if ($reclevel <= $maxdepth || $maxdepth < 1) {
            if ($value instanceof DebugPrinter) {
              // Object generates its own debug output.
              $buffer .= $value->generateDebugOutput($indent + static::DUMP_INDENT_INCREMENT, max($maxdepth - $reclevel, 0)) . "\n";
            } else {
              // We need to generate generic output.
              $hash = spl_object_hash($value);

              if (!isset($recmap[$hash])) {
                $recmap[$hash] = $reclevel;

                // Process object
                $ro = new ReflectionObject($value);
                $properties = $ro->getProperties();

                foreach ($properties as $property) {
                  if (!$property->isStatic()) {
                    $property->setAccessible(true);
                    $key = $property->getName();
                    $val = $property->getValue($value);

                    $buffer .= $formatter($indent + static::DUMP_INDENT_INCREMENT, true, $key, $val);
                  }
                }

                unset($recmap[$hash]);
              } else {
                $diff = $reclevel - $recmap[$hash];
                $buffer .= str_repeat(' ', $indent + static::DUMP_INDENT_INCREMENT) . "**RECURSION: {$diff} level(s)**\n";
              }
            }
          } else {
            $buffer .= str_repeat(' ', $indent + static::DUMP_INDENT_INCREMENT) . "...\n";
          }

          $buffer .= "{$padding}}\n";
            break;

        case 'resource':
        case 'null':
        default:
          $buffer .= '(' . strtolower($type) . ")\n";
            break;
      }

      --$reclevel;

      if (!$capture) {
        print($buffer);
        return null;
      } else {
        return $buffer;
      }
    };

    return $formatter($indent, $capture, null, $var);
  }

  /**
   * Performs a bulk uncaptured dump of all provided variables. This function operates identically
   * to repeated calls to <code>dump($var, false)</code>.
   *
   * @param mixed $var
   *  <em>Variadic</em>.
   *  The variable (or variables) to dump.
   *
   * @return void
   */
  public static function dumpAll($var)
  {
    $stack = debug_backtrace(null, 1);
    $argv = $stack[0]['args'];
    $argc = func_num_args();

    for ($i = 0; $i < $argc; ++$i) {
      static::dump($argv[$i], false);
    }
  }

  /**
   * Performs a bulk captured dump of all provided variables. This function operates identically to
   * repeated calls to <code>dump($var, true)</code>.
   *
   * @param mixed $var
   *  <em>Variadic</em>.
   *  The variable (or variables) to dump.
   *
   * @return string
   *  A string containing the dumped representation of all specified variables.
   */
  public static function dumpAllToString($var)
  {
    $buffer = '';

    $stack = debug_backtrace(null, 1);
    $argv = $stack[0]['args'];
    $argc = func_num_args();

    for ($i = 0; $i < $argc; ++$i) {
      $buffer .= static::dump($argv[$i], true);
    }

    return $buffer;
  }
}
