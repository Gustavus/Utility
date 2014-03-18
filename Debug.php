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
   * The default initial indentation depth at which to begin indenting.
   *
   * @var integer
   */
  const DUMP_DEFAULT_INITIAL_INDENT = 0;

  /**
   * The default maximum depth into objects an arrays the dump function will traverse in a single
   * call. If negative, no limit will be imposed.
   *
   * @var integer
   */
  const DUMP_DEFAULT_MAX_TRAVERSAL_DEPTH = 5;

  /**
   * The default maximum number of characters to display when printing strings. If negative, no
   * limit will be imposed.
   *
   * @var integer
   */
  const DUMP_DEFAULT_MAX_STRING_LENGTH = 256;

  /**
   * The character set to force upon all displayed strings.
   *
   * @var string
   */
  const DUMP_STRING_ENCODING = 'UTF-8';

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
   * @param array $options
   *  <em>Optional</em>.
   *  An associative array consisting of output options. Valid options are as follows:
   *  <ul>
   *    <li>
   *      <strong>indent</strong> - integer<br/>
   *      The initial depth of the indentation. Can be used to make nested calls while maintaining
   *      proper indentation. Default: 0.
   *    </li>
   *    <li>
   *      <strong>maxdepth</strong> - integer<br/>
   *      The maximum depth into an object or array this function will traverse. If negative, no
   *      limit will be imposed. Default: 5.
   *    </li>
   *    <li>
   *      <strong>maxstrlen</strong> - integer<br/>
   *      The maximum number of characters to display when dumping strings. Characters after the
   *      limit will be trimmed and replaced with an ellipsis. If negative, no limit will be
   *      imposed. Default: 256.
   *    </li>
   *  </ul>
   *
   *  Invalid options will be silently ignored.
   *
   * @return string
   *  The captured debug output if, and only if, $capture was set; null otherwise.
   */
  public static function dump($var, $capture = false, array $options = [])
  {
    // Parse options
    $indent = (isset($options['indent']) && is_numeric($options['indent'])) ? (int) $options['indent'] : static::DUMP_DEFAULT_INITIAL_INDENT;
    $maxdepth = (isset($options['maxdepth']) && is_numeric($options['maxdepth'])) ? (int) $options['maxdepth'] : static::DUMP_DEFAULT_MAX_TRAVERSAL_DEPTH;
    $maxstrlen = (isset($options['maxstrlen']) && is_numeric($options['maxstrlen'])) ? (int) $options['maxstrlen'] : static::DUMP_DEFAULT_MAX_STRING_LENGTH;

    // Used to detect recursion in arrays and objects
    $reckey = '__recursion_key-' . time();
    $reclevel = 0;
    $recmap = [];

    static $calldepth = 0;
    static $insmap = [];

    // Formatter which does all the work of actually formatting data...
    $formatter = function ($indent, $capture, $key, &$value) use (&$formatter, &$maxdepth, &$maxstrlen, &$reckey, &$reclevel, &$recmap, &$insmap) {
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
          $len = mb_strlen($value, static::DUMP_STRING_ENCODING);

          if ($maxstrlen >= 0 && $len > $maxstrlen) {
            $value = mb_substr($value, 0, $maxstrlen, static::DUMP_STRING_ENCODING);
            $value .= '...';
          }

          // @todo:
          // Maybe add an option to not add slashes to "special" characters within the string?
          $original = mb_regex_encoding();
          mb_regex_encoding(static::DUMP_STRING_ENCODING);

          $value = mb_ereg_replace_callback('([\\\\\'"\\n\\x00])', function($matches) {
            switch ($matches[1]) {
              case "\x00":
                  return "\\0";

              case "\n":
                  return "\\n";
            }

            return "\\{$matches[1]}";
          }, $value);

          mb_regex_encoding($original);

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
          $hash = spl_object_hash($value);
          $oid = isset($insmap[$hash]) ? $insmap[$hash] : ($insmap[$hash] = count($insmap));
          $buffer .= "(object): {$class} [{$oid}: {$hash}] {\n";

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

    ++$calldepth;

    $result = $formatter(($indent > 0 ? $indent : 0), $capture, null, $var);

    if (--$calldepth <= 0) {
      $calldepth = 0;
      $insmap = [];
    }

    return $result;
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
