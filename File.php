<?php
/**
 * @package Utility
 * @author  Billy Visto
 */
namespace Gustavus\Utility;

/**
 * Object for working with Files
 *
 * @package Utility
 * @author  Billy Visto
 */
class File extends Base
{
  /**
   * Function to overide abstract function in base to make sure the value is valid
   *
   * @param  mixed $value value passed into setValue()
   * @return boolean
   */
  final protected function valueIsValid($value)
  {
    return is_string($value);
  }

  /**
   * Includes specified file and evaluates its contents as PHP code
   *
   * Usage:
   * <code>
   * echo (new Utility\File('/path/to/file.php'))->loadAndEvaluate()
   * </code>
   *
   * @return string Result of evaluated file
   */
  public function loadAndEvaluate()
  {
    assert('is_string($this->value)');

    if (!empty($this->value) && $this->exists()) {
      ob_start();
      include $this->value;
      return ob_get_clean();
    } else {
      return null;
    }
  }

  /**
   * Expanded file_exists function
   * Searches in include_path
   *
   * @param boolean $returnFullPath If true, returns the full path if the file is found
   * @return boolean|string
   */
  public function exists($returnFullPath = false)
  {
    assert('is_string($this->value)');
    assert('is_bool($returnFullPath)');

    if (substr($this->value, 0, 1) === '/') {
      if ($returnFullPath) {
        if (file_exists($this->value)) {
          return $this->value;
        } else {
          return false;
        }
      } else {
        return file_exists($this->value);
      }
    }

    if (function_exists('get_include_path')) {
      $includePath = get_include_path();
    } else if (false !== ($ip = ini_get('include_path'))) {
      $includePath = $ip;
    } else {
      return false;
    }

    if (false !== strpos($includePath, PATH_SEPARATOR)) {
      if (false !== ($temp = explode(PATH_SEPARATOR, $includePath)) && count($temp) > 0) {
        for ($n = 0; $n < count($temp); $n++) {
          if (false !== file_exists($temp[$n] . '/' . $this->value)) {
            if ($returnFullPath) {
              return "{$temp[$n]}/{$this->value}";
            } else {
              return true;
            }
          }
        }
        return false;
      } else {
        return false;
      }
    } else if (!empty($includePath)) {
      if (false !== file_exists($includePath . '/' . $this->value)) {
        if ($returnFullPath) {
          return "{$includePath}/{$this->value}";
        } else {
          return true;
        }
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  /**
   * Format a filename to be web-safe. If a location exists, check to see if it exists and modify appropriately.
   *
   * @param string $location Location on server (e.g. "/cis/www/campus/files/")
   * @return  File
   */
  public function filename($location = null)
  {
    assert('is_string($location) || is_null($location)');

    if (preg_match('`^(.*)(\..*)$`', $this->value, $matches)) {
      $origFilename    = $matches[1];
      $origExtension   = $matches[2];
    } else {
      $origFilename    = $this->value;
      $origExtension   = '';
    }

    $this->value            = preg_replace('`\s+|\+`', '-', strtolower($this->value));
    $origFilename        = preg_replace('`\s+|\+`', '-', strtolower($origFilename));

    if (!empty($location)) {
      $i = 1;
      while (file_exists("$location/$this->value")) {
        $this->value    = strtolower("$origFilename-$i$origExtension");
        ++$i;
      }
    }

    return $this;
  }
}