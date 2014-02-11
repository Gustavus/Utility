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
   * The maximum number of characters that will be displayed in a string. Strings longer than this
   * value will be trimmed down and have an ellipsis appended. Should be a positive integer.
   *
   * @var integer
   */
  const DUMP_STRING_DISPLAY_LENGTH = 256;


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
   *  The maximum depth into an object or array this function will traverse. If negative, no limit
   *  will be imposed. Defaults to five.
   *
   * @throws InvalidArgumentException
   *  if $indent is a not an integer or contains a negative value, or $maxdepth is not an integer.
   *
   * @return string
   *  The captured debug output if, and only if, $capture was set; null otherwise.
   */
  public static function dump($var, $capture = false, $indent = 0, $maxdepth = 5)
  {
    if (!is_int($indent) || $indent < 0) {
      throw new InvalidArgumentException('$indent is not an integer or contains a negative value.');
    }

    if (!is_int($maxdepth)) {
      throw new InvalidArgumentException('$maxdepth is not an integer.');
    }


    // Used to detect recursion in arrays and objects
    $reckey = '__recursion_key-' . time();
    $reclevel = 0;
    $recmap = [];

    // Formatter which does all the work of actually formatting data...
    $formatter = function ($indent, $capture, $key, &$value) use (&$formatter, &$maxdepth, &$reckey, &$reclevel, &$recmap) {
      $padding = str_repeat(' ', $indent);
      $buffer = $padding;

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

          if ($len > static::DUMP_STRING_DISPLAY_LENGTH) {
            $value = ($encoding ? mb_substr($value, 0, static::DUMP_STRING_DISPLAY_LENGTH, $encoding) : mb_substr($value, 0, static::DUMP_STRING_DISPLAY_LENGTH));
            $value .= mb_convert_encoding('...', ($encoding ? $encoding : 'UTF-8'));
          }

          if ($encoding) {
            mb_regex_encoding($encoding);
          }

          $value = mb_ereg_replace_callback('([\\\\\'"\\n\\x00])', function($matches) {
            switch ($matches[1]) {
              case "\x00":
                  return "\\0";

              case "\n":
                  return "\\n";
            }

            return "\\{$matches[1]}";
          }, $value);

          mb_regex_encoding();
          $buffer .= "(string[{$len}]): \"{$value}\"\n";
            break;

        case 'array':
          $count = (isset($value[$reckey]) ? count($value) - 1 : count($value));
          $buffer .= "(array[{$count}]) {\n";

          if (++$reclevel <= $maxdepth || $maxdepth < 0) {
            if (!isset($value[$reckey])) {

              $value[$reckey] = $reclevel;

              foreach ($value as $key => &$val) {
                if ($key !== $reckey) {
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

          --$reclevel;
          $buffer .= "{$padding}}\n";
            break;

        case 'object':
          $class = get_class($value);
          $buffer .= "(object): {$class} {\n";

          if (++$reclevel <= $maxdepth || $maxdepth < 0) {
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

          --$reclevel;
          $buffer .= "{$padding}}\n";
            break;

        case 'resource':
        case 'null':
        default:
          $buffer .= '(' . strtolower($type) . ")\n";
            break;
      }

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
