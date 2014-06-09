<?php
/**
 * @package Utility
 * @author  Billy Visto
 * @author  Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility;

use InvalidArgumentException,
    Gustavus\GACCache\GlobalCache;


/**
 * Object for working with Files
 *
 * @package Utility
 * @author  Billy Visto
 * @author  Chris Rog <crog@gustavus.edu>
 */
class File extends Base
{
  /**
   * Number of seconds to cache a view
   * @var integer
   */
  const VIEW_TTL    = 86400; // 60 * 60 * 24

  /**
   * Retrieves the MIME util instance to use for performing MIME type checks and operations.
   *
   * @return MimeUtil
   *  A MimeUtil instance.
   */
  protected function getMimeUtil()
  {
    return new MimeUtil();
  }


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

    // Make sure our filename isn't too long. Names longer than 250 characters tend to break certain
    // applications (see: Office).
    // We use 240 characters instead of 250 here to give us 10 extra characters to play with for
    // adding extra digits in the case of filename collisions (see below).
    $blen = strlen($filename);
    $elen = strlen($fileExtension);

    if ($blen + $elen > 240) {
      $filename = substr($filename, 0, 240 - $elen);
    }

    $this->value  = preg_replace('`\s+|\+`', '-', strtolower($filename . $fileExtension));

    if (!empty($location)) {
      $filename = preg_replace('`\s+|\+`', '-', strtolower($filename));
      $i = 1;
      while (file_exists("{$location}/{$this->value}")) {
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
   * Serves the file represented by this File object. If the file is not a file, cannot be read or
   * does not match the MIME type restrictions, a File Not Found error page will be served instead.
   *
   * Note:
   *  This method sends headers and ends the request via "exit." The calling application will not
   *  continue after a call to this method.
   *
   * @param string $name
   *  <em>Optional</em>.
   *  The name to use when serving the file. If omitted, the basename of the file will be used.
   *
   * @param string $mime
   *  <em>Optional</em>.
   *  The MIME type to use for this file. If omitted, the MIME will be detected from the contents of
   *  the file.
   *
   * @param string $whitelist
   *  <em>Optional</em>.
   *  A regular expression specifying an expression the file's MIME type must match to be served. If
   *  omitted, the default MIME type whitelist will be used.
   *
   * @param string $blacklist
   *  <em>Optional</em>.
   *  A regular expression specifying an expression the file's MIME type must not match to be
   *  served. If omitted, the default MIME type blacklist will be used.
   *
   * @throws InvalidArgumentException
   *  if $name is provided, but is empty or not a string, $mime is provided, but is empty or not a
   *  string, or either $whitelist or $blacklist are provided, but are empty, not strings or not
   *  valid regular expressions.
   *
   * @return void
   */
  public function serve($name = null, $mime = null, $whitelist = null, $blacklist = null)
  {
    if (isset($name) && (empty($name) || !is_string($name))) {
      throw new InvalidArgumentException('$name is provided, but is empty or not a string.');
    }

    if (isset($mime) && (empty($mime) || !is_string($mime))) {
      throw new InvalidArgumentException('$mime is provided, but is empty or not a string.');
    }


    $mu = $this->getMimeUtil();

    if (!isset($mime)) {
      $mime = $mu->getMimeType($this->value);
    }

    $serve = $mu->validateMimeType($mime, $whitelist, $blacklist);

    if ($serve && $mime) {
      $size = filesize($this->value);

      if (!isset($name)) {
        $name = basename($this->value);
      }

      header("Pragma: public"); // required
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Cache-Control: private", false); // required for certain browsers
      header("Content-Type: {$mime}");
      header("Content-Disposition: filename=\"{$name}\";" ); // Will this require urlencoding...?
      header("Content-Transfer-Encoding: binary");
      header("Content-Length: {$size}");

      readfile($this->value);
    } else {
      PageUtil::renderPageNotFound();
    }

    exit;
  }

  /**
   * Renders the contents of this file as a p-view (extended printf-formatted view).
   *
   * @param array $data
   *  Data to use in view
   *
   * @param boolean $useVnsprintf
   *  If true, will use String::vnsprintf() for formatting, otherwise will use vsprintf() for
   *  formatting.
   *
   * @param boolean $evaluateView
   *  If true, will evaluate any PHP in the view
   *
   * @return String
   *  A String instance containing the rendered view.
   */
  public function renderAsPView(array $data, $useVnsprintf = false, $evaluateView = false)
  {
    $fullPath = $this->exists(true);

    if ($fullPath === false) {
      throw new \RuntimeException("Requested view cannot be found: {$this->value}");
    }

    $viewModifiedOn = filemtime($fullPath);
    $key = 'pview_' . hash('md4', "{$useVnsprintf}-{$fullPath}-{$viewModifiedOn}-" . json_encode($data));

    $datastore = GlobalCache::getGlobalDataStore();
    $r = $datastore->getValue($key, $incache);

    if (!$incache) {
      $view = $evaluateView ? $this->loadAndEvaluate() : file_get_contents($fullPath);

      if ($useVnsprintf) {
        $r = String::vnsprintf($view, $data);
      } else {
        $r = new String(vsprintf($view, $data));
      }

      $datastore->setValue($key, $r->getValue(), static::VIEW_TTL);
    } else {
      $r = new String($r);
    }

    return $r;
  }
}