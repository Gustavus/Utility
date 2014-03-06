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
   * Array to store extention to mime type mappings
   *
   * @var array
   */
  private static $mimeTypes;

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
  public function filename($location = null, $extension = null)
  {
    assert('is_string($location) || is_null($location)');

    if (preg_match('`^(.*)(\..*)$`', $this->value, $matches)) {
      $filename  = $matches[1];
      $fileExtension = $matches[2];
    } else {
      $filename  = $this->value;
      $fileExtension = '';
    }

    // make sure there aren't any encoded html entities in the filename
    $filename = urldecode($filename);
    $fileExtension = urldecode($fileExtension);

    if (!empty($extension)) {
      if (strpos($extension, '.') !== 0) {
        // make sure the extension includes the "."
        $extension = '.' . $extension;
      }
      // we want to make sure the fileExtension matches the wanted extension
      if ($fileExtension !== $extension) {
        $filename .= $fileExtension;
        $fileExtension = $extension;
      }
    }

    $this->value  = preg_replace('`\s+|\+`', '-', strtolower($filename . $fileExtension));

    if (!empty($location)) {
      $filename = preg_replace('`\s+|\+`', '-', strtolower($filename));
      $i = 1;
      while (file_exists("$location/$this->value")) {
        $this->value = strtolower("{$filename}-{$i}{$fileExtension}");
        ++$i;
      }
    }

    return $this;
  }

  /**
   * Looks for the specified filename up the directory tree from the server's requested file
   *
   * @param mixed  $startDir     Directory to start looking in for the file
   * @param mixed  $defaultValue Default return value if nothing is found
   * @param integer $levels      Maximum number of levels higher to check
   * @return mixed Path of file if it is found. Defaults to false if a file isn't found.
   */
  public function find($startDir = null, $defaultValue = false, $levels = 5)
  {
    assert('is_int($levels)');
    if ($startDir === null) {
      $startDir    = dirname($_SERVER['SCRIPT_FILENAME']);
    }
    $currentDirArr = explode('/', trim($startDir, '/'));

    $i = 0;
    while ($i < $levels && count($currentDirArr) !== 0) {
      $check = sprintf('/%s/%s', implode('/', $currentDirArr), $this->value);
      array_pop($currentDirArr);

      if (file_exists($check)) {
        $this->value = $check;
        return $this;
      }
      ++$i;
    }
    // file not found
    $this->value = $defaultValue;
    return $this;
  }

  /**
   * Serves the current file
   *
   * @return void
   */
  public function serve()
  {
    if (empty(self::$mimeTypes)) {
      self::$mimeTypes = [
        'pdf' => 'application/pdf',
        'zip' => 'application/zip',
        'gzip' => 'application/gzip',
        'doc' => 'application/msword',
        'docx' => 'application/msword',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.ms-powerpoint'
      ];
    }

    $info = pathinfo($this->value);
    $ext = isset($info['extension']) ? strtolower($info['extension']) : '';

    if (is_readable($this->value) && isset(self::$mimeTypes[$ext])) {
      $mime = self::$mimeTypes[$ext];
      $size = filesize($this->value);

      header("Pragma: public"); // required
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Cache-Control: private",false); // required for certain browsers
      header("Content-Type: {$mime}");
      header("Content-Disposition: filename=\"{$info['basename']}\";" );
      header("Content-Transfer-Encoding: binary");
      header("Content-Length: {$size}");

      ob_clean();
      flush();
      readfile($this->value);
    } else {
      PageUtil::renderPageNotFound();
      exit;
    }
  }
}