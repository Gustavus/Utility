<?php
/**
 * @package Utility
 *
 * @author Nicholas Dobie <ndobie@gustavus.edu>
 * @author Justin Holcomb <jholcom2@gustavus.edu>
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
 *
 * @author Nicholas Dobie <ndobie@gustavus.edu>
 * @author Billy Visto
 * @author Justin Holcomb <jholcom2@gustavus.edu>
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
      static::$curl->setOption(CURLOPT_ENCODING, '');
      static::$curl->setOption(CURLOPT_FOLLOWLOCATION, true);
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
      return $this->localURL($url);
    } else {
      return false;
    }
  }

  /**
   * Trys to grab first image from content.
   *
   * @param string $content
   * @param integer $minimumWidth
   * @param integer $minimumHeight
   *
   * @return string
   *  Local URL or false if there is no image in content
   */
  public function grabFirstImageFromContent($content, $minimumWidth = null, $minimumHeight = null)
  {
    $image = null;
    $position = 0;

    do {
      ++$position;
      $image = $this->grabImageFromContent($content, $position);

      if ($image === false) {
        return false;
      } else if (!$this->imagePassesSizeRestrictions($image, $minimumWidth, $minimumHeight)) {
        $image  = null;
      }
    } while ($image === null);

    return $image;
  }

  /**
   * Trys to grab image from content.
   *
   * @param string $content
   * @param integer $imagePosition
   *
   * @return string
   *  Local URL or false if there is no image in content
   */
  private function grabImageFromContent($content, $imagePosition)
  {
    $image = $this->extractImageFromContent($content, $imagePosition);

    if ($image) {
      return $this->grabFile($image);
    } else {
      return false;
    }
  }

  /**
   * Checks to see if image is within a certain size range.
   *
   * @param string $imagePath
   * @param integer $minimumWidth
   * @param integer $minimumHeight
   *
   * @return boolean
   */
  private function imagePassesSizeRestrictions($imagePath, $minimumWidth, $minimumHeight)
  {
    if ($minimumWidth === null && $minimumHeight === null) {
      return true;
    }

    $fullPath = ltrim($_SERVER['DOCUMENT_ROOT']) . '/' . $imagePath;

    if (!file_exists($fullPath)) {
      $this->processQueue();
    }

    if (!file_exists($fullPath)) {
      return false;
    }

    if (filesize($fullPath) === 0) {
      return false;
    }

    $fhandle  = finfo_open(FILEINFO_MIME);
    $fileType = finfo_file($fhandle, $fullPath);
    if (strpos($fileType, 'image') === false) {
      // need to check if the file is an image otherwise getimagesize will throw a notice
      return false;
    }

    $size = getimagesize($fullPath);

    if ($minimumWidth !== null && $minimumWidth > $size[0]) {
      return false;
    } else if ($minimumHeight !== null && $minimumHeight > $size[1]) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Get urls of images from the content.
   *
   * Get an array of all the images:
   * <code>
   * $images = FileGrabber->extractImageFromContent($content);
   * </code>
   *
   * Get first image
   * <code>
   * $firstImage = FileGrabberextractImageFromContent($content, 1);
   * </code>
   *
   * @param string $content
   * @param integer $imagePosition
   *
   * @return string|array|boolean
   *  False if content does not contain an image in the requested position
   */
  public function extractImageFromContent($content, $imagePosition = null)
  {
    $hasImage = preg_match_all(
        '`<img\b(?>[^>]*?src=)([\'"])(.+?)\1`i',
        $content,
        $image
    );

    if ($imagePosition !== null) {
      if ($hasImage >= $imagePosition) {
        return $image[2][$imagePosition - 1];
      } else {
        return false;
      }
    } else {
      return $image[2];
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
    $grabbed  = $this->isGrabbed($url);
    $mime     = $this->isAllowedMime($url);
    return ($domain && (!$grabbed || ($grabbed && $mime)));
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
   * @throws RuntimeException        If remote file can't be saved.
   * @throws RuntimeException        If URL is invalid.
   * @throws RuntimeException        If remote file isn't an accepted mime type.
   * @return string                  Returns the contents of the file.
   */
  private function fetchFileFromServer($url)
  {
    if (preg_match(Regex::url(), $url) && $this->isAllowedDomain($url)) {
      $attempt = 0;

      do {
        ++$attempt;
        $file = static::$curl->execute($url);
        // Retry if timed out
      } while (static::$curl->getLastErrorNumber() == CURLE_OPERATION_TIMEOUTED && $attempt < static::ATTEMPT_LIMIT);

      $resultCode = static::$curl->getInfo(CURLINFO_HTTP_CODE);

      if ($file === false || empty($file) || $attempt >= static::ATTEMPT_LIMIT || $resultCode != '200') {
        throw new RuntimeException("Unable to fetch \"{$url}\".");
      }
      if (!is_dir(static::FILE_GRABBER_FS_STORAGE)) {
        mkdir(static::FILE_GRABBER_FS_STORAGE, 0775, true);
      }
      if (file_put_contents($this->localPath($url), $file) === false) {
        throw new RuntimeException(sprintf('We were unable to save the file from "%s" to "%s"', $url, $this->localPath($url)));
      }
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