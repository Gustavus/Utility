<?php
/**
 * @package Utility
 * @author Nicholas Dobie <ndobie@gustavus.edu>
 */
namespace Gustavus\Utility;

use Gustavus\Regex\Regex,

    InvalidArgumentException,
    RuntimeException,
    Exception;

/**
 * Retrieves files from external servers and stores a local copy of the file.
 *
 * @package Utility
 * @author Nicholas Dobie <ndobie@gustavus.edu>
 */
class FileGrabber
{

  /**
   * Storage point for cached files
   */
  const FILE_GRABBER_FS_STORAGE = '/cis/www-etc/lib/Gustavus/Utility/FileGrabber/';

  /**
   * Web access point for cached files
   */
  const FILE_GRABBER_WEB_ACCESS = '/filegrabber/';

  /**
   * How many tries to make before giving up.
   */
  const ATTEMPT_LIMIT = 5;

  /**
   * CURL request class
   * @var CURLRequest
   */
  private static $curl;

  /**
   * Domains FileGrabber can pull from. Will include any subdomains
   * @var array
   */
  private $whitelist = array(

    // Gustavus
    'gustavus.edu',
    'gac.edu',

    // Flickr
    'flickr.com',
    'staticflickr.com',

    // Google
    'google.com',
    'youtube.com',
    'ggpht.com',
    'ytimg.com',
    'blogspot.com',

    // Twitter
    'twitter.com',
    'twimg.com',

    // Facebook
    'facebook.com',
    'fbcdn.net',
    'akamaihd.net',

    // Instagram
    'instagram.com',
    's3.amazonaws.com',

  );

  /**
   * List of allowed Mime Types
   * @var array
   */
  private $mimeTypes = array(
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/gif',
    'text/calendar',
    'text/plain'
  );

  /**
   * Stores URL for grabbing later.
   *
   * @var array
   */
  private $queue = array();

  /**
   * Sets up FileGrabber
   *
   * @param string|array $url Enqueues an URL or multiple URLs.
   */
  public function __construct($url = null)
  {

    if (!isset(static::$curl)) {
      static::$curl = new CURLRequest();
    }

    if (!empty($url)) {
      if (is_array($url)) {
        $this->bulkEnqueue($url);
      } else {
        $this->enqueue($url);
      }
    }
  }

  /**
   * Processes queue on destruction of FileGrabber instance.
   */
  public function __destruct()
  {
    $this->processQueue();
  }

  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////

  /**
   * Gets the file from either the cache or directly from the remote server.
   *
   * @param  string  $url       URL to retrieve
   * @param  boolean $useCache  If true will attempt to pull file from cache, otherwise it will get a copy from the server.
   * @return string             Returns the file contents. Returns null if unable to get the file.
   */
  public function getFile($url, $useCache = true)
  {
    if ($useCache && $this->isGrabbed($url)) {
      return $this->fetchFileFromFileSystem($url);
    } else {
      return $this->fetchFileFromServer($url);
    }
  }

  /**
   * Delays grabbing a file from the remote server.
   *
   * @param  string         $url URL to retrieve.
   * @return string|boolean      Returns the future local URL of the file or false if can't queue file.
   */
  public function grabFile($url)
  {
    if ($this->isAllowed($url)) {
      if (!$this->isGrabbed($url)) {
        $this->enqueue($url);
      }
      return $this->localPath($url);
    } else {
      return false;
    }
  }

  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////

  /**
   * Checks if file is from an allowed domain and is an allowed Mime type.
   *
   * **Mime type only works on already cached files, check will be skipped if not already cached.**
   *
   * @param  string  $url URL to check.
   * @return boolean      Returns true if file passes check.
   */
  public function isAllowed($url)
  {
    $domain   = $this->isAllowedDomain($url);
    $readable = $this->isReadable($url);
    $grabbed  = $this->isGrabbed($url);
    $mime     = $this->isAllowedMime($url);
    return ($domain && $readable && (!$grabbed || ($grabbed && $mime)));
  }

  /**
   * Returns a list of domains FileGrabber can pull from
   *
   * @return array
   */
  public function getWhitelistDomains()
  {
    return $this->whitelist;
  }

  /**
   * Checks that file is coming from an allow domain.
   *
   * @param  string  $url URL to check.
   * @return boolean      Returns true if file passes check.
   */
  public function isAllowedDomain($url)
  {
    $domain = preg_replace('`^(?:https?://|)([^/]+)/?.*$`i', '$1', $url);
    foreach ($this->whitelist as $whitelistDomain) {
      if (substr($domain, strlen($whitelistDomain)*-1) == $whitelistDomain) {
        return true;
      }
    }
    return false;
  }

  /**
   * Check that file is an allowed mime type.
   *
   * **Requires that the file already be cached.**
   *
   * @param  string           $url  URL to check.
   * @throws RuntimeException       If FileInfo can't load.
   * @return boolean                Returns true if file passes check. Will return false if unable to preform check.
   */
  public function isAllowedMime($url)
  {
    if ($this->isGrabbed($url)) {
      $finfo = new \finfo(FILEINFO_MIME_TYPE);
      if (!$finfo) {
        throw new RuntimeException('Opening fileinfo database failed');
      }
      $mime = $finfo->file($this->localPath($url));
      return in_array($mime, $this->mimeTypes);
    } else {
      return false;
    }
  }

  /**
   * Checks that the file is already downloaded.
   *
   * @param  string  $url URL to check.
   * @return boolean      Returns true if in cache.
   */
  public function isGrabbed($url)
  {
    return file_exists($this->localPath($url));
  }

  /**
   * Checks that the file on the external server is readable
   *
   * _Note: Several sites don't set the 404 status code on 404 pages._
   *
   * @param  string                    $url URL to check if is external
   * @throws InvalidArgumentException       If URL is not valid.
   * @return boolean                        Returns true if readable.
   */
  public function isReadable($url)
  {
    if (preg_match(Regex::url(), $url)) {
      static::$curl->setOption(CURLOPT_HEADER, true);
      static::$curl->setOption(CURLOPT_NOBODY, true);

      $content = static::$curl->execute($url);

      static::$curl->setOption(CURLOPT_HEADER, false);
      static::$curl->setOption(CURLOPT_NOBODY, false);

      if (preg_match('/Location: ([^\r\n]*)/i', $content, $match)) {
        // Follow redirects
        return $this->isReadable($match[1]);
      } else {
        return substr($content, 9, 3) === '200';
      }

    } else {
      throw new InvalidArgumentException("\"{$url}\" is not a valid url.");
    }
  }

  /**
   * Gets the local path of the file.
   *
   * @param  string $url URL to convert to local path.
   * @return string      Returns local path.
   */
  public function localPath($url)
  {
    return static::FILE_GRABBER_FS_STORAGE . md5($url);
  }

  /**
   * Gets the local URL. Use this with SLIR or GIMLI.
   *
   * @param  string $url URL to convert to local URL.
   * @return string      Returns local URL.
   */
  public function localURL($url)
  {
    if (!is_link(rtrim(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . static::FILE_GRABBER_WEB_ACCESS, '/'))) {
      symlink(static::FILE_GRABBER_FS_STORAGE, $_SERVER['DOCUMENT_ROOT'] . static::FILE_GRABBER_WEB_ACCESS);
    }
    return static::FILE_GRABBER_WEB_ACCESS . md5($url);
  }

  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////

  /**
   * Adds an items to the download queue.
   *
   * @param  string                     $url  URL to download.
   * @throws InvalidArgumentException         If URL is not a valid URL.
   * @return FileGrabber                      Returns $this.
   */
  public function enqueue($url)
  {
    $this->queue[] = $url;
    return $this;
  }

  /**
   * Adds an array of items to the download queue.
   *
   * @param  array        $urls Array of URLs to download.
   * @return FileGrabber        Return $this.
   */
  public function bulkEnqueue(array $urls)
  {
    $this->queue = array_merge($this->queue, $urls);
    return $this;
  }

  /**
   * Returns the list of files that will be downloaded.
   *
   * @return array
   */
  public function getQueue()
  {
    return $this->queue;
  }

  /**
   * Clears all files added to the queue.
   *
   * @return FileGrabber Return $this.
   */
  public function clearQueue()
  {
    $this->queue = array();
    return $this;
  }

  /**
   * Downloads all of the files in the queue.
   *
   * @return boolean|array Returns true if successful, otherwise returns an array of failed URLs and errors.
   */
  public function processQueue()
  {
    $errors = array();
    foreach ($this->queue as $url) {
      try {
        $this->fetchFileFromServer($url);
      } catch (Exception $e) {
        $errors[] = array(
          'url' => $url,
          'error' => $e
        );
      }
    }

    $this->clearQueue();

    if (empty($errors)) {
      return true;
    } else {
      return $errors;
    }
  }

  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////

  /**
   * Fetches a file from the file system.
   *
   * @param  string $url URL to find in storage.
   * @return string      Returns the contents of the file.
   */
  private function fetchFileFromFileSystem($url)
  {
    return file_get_contents($this->localPath($url));
  }

  /**
   * Fetches the files from a remote server
   * @param  string             $url URL to pull file from.
   * @throws RuntimeException        If URL can't be retrieved.
   * @throws RuntimeException        If URL is invalid.
   * @throws RuntimeException        If remote file isn't an accepted mime type.
   * @return string                  Returns the contents of the file.
   */
  private function fetchFileFromServer($url)
  {
    if (preg_match(Regex::url(), $url) && $this->isAllowedDomain($url) && $this->isReadable($url)) {
      $attempt = 0;

      do {
        ++$attempt;
        $file = static::$curl->execute($url);
      } while (static::$curl->getLastErrorNumber() == CURLE_OPERATION_TIMEOUTED && $attempt < static::ATTEMPT_LIMIT); // Retry if timed out

      if ($file === false || empty($file) || $attempt >= static::ATTEMPT_LIMIT) {
        throw new RuntimeException("Unable to fetch \"{$url}\".");
      }
      if (!is_dir(static::FILE_GRABBER_FS_STORAGE)) {
        mkdir(static::FILE_GRABBER_FS_STORAGE, 0775, true);
      }
      file_put_contents($this->localPath($url), $file);
      if ($this->isAllowedMime($url)) {
        return $file;
      } else {
        unlink($this->localPath($url));
        throw new RuntimeException("\"{$url}\" is not a valid mime type.");
      }
    } else {
      throw new RuntimeException("\"{$url}\" is not from a valid domain.");
    }
  }


}