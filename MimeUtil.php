<?php
/**
 * MimeUtil.php
 *
 * @package Utility
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility;

use InvalidArgumentException;



/**
 * The MimeUtil class provides some basic utility functions for retrieving, processing and
 * validating the MIME type for/from a file.
 *
 * @package Utility
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class MimeUtil
{
  /**
   * The default whitelist to use when validating MIME types if no whitelist is provided.
   *
   * @var string
   */
  const DEFAULT_MIMETYPE_WHITELIST = '/\\/(?:html|g?zip|gif|jpe?g|png|pdf|msword|vnd\\.ms-(?:excel|powerpoint))\\z/i';

  /**
   * The default blacklist to use when validating MIME types if no blacklist is provided.
   *
   * @var string
   */
  const DEFAULT_MIMETYPE_BLACKLIST = '/\\/(?:(?:x-)?(?:httpd-)?php(?:-source)?)\\z/i';

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Retrieves the file's MIME type. If the file does not exist, is not a file or cannot be read,
   * this method returns null.
   *
   * @param string $file
   *  The file to check. May be an absolute path to the file, or a path relative to the current
   *  working directory.
   *
   * @throws InvalidArgumentException
   *  if $file is null, empty or not a string.
   *
   * @return string
   *  The MIME type for the specified file, or null if the file cannot be read.
   */
  public function getMimeType($file)
  {
    if (empty($file) || !is_string($file)) {
      throw new InvalidArgumentException('$file is null, empty or not a string.');
    }

    $mime = null;

    if (is_readable($file) && is_file($file)) {
      if ($finfo = finfo_open(FILEINFO_MIME_TYPE)) {
        $mime = finfo_file($finfo, $file);
        finfo_close($finfo);
      }
    }

    return $mime ? $mime : null;
  }

  /**
   * Retrieves a common extension appropriate for the specified MIME type. If the given MIME type is
   * not mapped to an extension, this method returns null.
   *
   * @param string $mime
   *  The MIME type for which to retrieve a file extension.
   *
   * @throws InvalidArgumentException
   *  if $mime is null, empty or not a string.
   *
   * @return string
   *  A file extension for the specified MIME type, or null if the type is not mapped to a file
   *  extension.
   */
  public function getExtensionForMimeType($mime)
  {
    if (empty($mime) || !is_string($mime)) {
      throw new InvalidArgumentException('$mime is null, empty or not a string.');
    }

    static $extmap = [
      'text' => [
        'plain' => 'txt',
        'html'  => 'html',
      ],

      'image' => [
        'gif' => 'gif',
        'png' => 'png',
        'jpeg' => 'jpg',
      ],

      'application' => [
        'pdf' => 'pdf',
        'zip' => 'zip',
      ]
    ];

    $ext = null;
    $chunklets = explode('/', strtolower($mime), 2);

    if (count($chunklets) === 2) {
      $ext = isset($extmap[$chunklets[0]][$chunklets[1]]) ? $extmap[$chunklets[0]][$chunklets[1]] : null;
    }

    return $ext;
  }

  /**
   * Validates the specified MIME type against the given white and blacklists. The MIME type is
   * considered valid if, and only if, it matches the whitelist and does not match the black list.
   *
   * @param string $mime
   *  The MIME type to validate.
   *
   * @param string $whitelist
   *  <em>Optional</em>.
   *  A regular expression to use as the whitelist for validating the given MIME type. If omitted,
   *  the default MIME type whitelist will be used.
   *
   * @param string $blacklist
   *  <em>Optional</em>.
   *  A regular expression to use as the blacklist for validating the given MIME type. If omitted,
   *  the default MIME type blacklist will be used.

   * @throws InvalidArgumentException
   *  if $mime is provided but is not a string, or a $whitelist or $blacklist are provided, but are
   *  empty, not strings or not valid regular expressions.
   *
   * @return boolean
   *  True if the given MIME type passes the MIME validation; false otherwise.
   */
  public function validateMimeType($mime, $whitelist = null, $blacklist = null)
  {
    if (isset($mime) && !is_string($mime)) {
      throw new InvalidArgumentException('$mime is null, empty or not a string.');
    }

    if (isset($whitelist)) {
      if (empty($whitelist) || !is_string($whitelist) || @preg_match($whitelist, '') === false) {
        throw new InvalidArgumentException('$whitelist is empty, not a string or not a valid regular expression.');
      }
    } else {
      $whitelist = static::DEFAULT_MIMETYPE_WHITELIST;
    }

    if (isset($blacklist)) {
      if (empty($blacklist) || !is_string($blacklist) || @preg_match($blacklist, '') === false) {
        throw new InvalidArgumentException('$blacklist is empty, not a string or not a valid regular expression.');
      }
    } else {
      $blacklist = static::DEFAULT_MIMETYPE_BLACKLIST;
    }

    return !empty($mime) && (preg_match($whitelist, $mime) && preg_match($blacklist, $mime) === 0);
  }

  /**
   * Attempts to determine the MIME type of the specified file and validate it against the given
   * white and blacklists. The MIME type is considered valid if, and only if, it matches the
   * whitelist and does not match the black list.
   *
   * @param string $file
   *  The file to check. May be an absolute path to the file, or a path relative to the current
   *  working directory.
   *
   * @param string $whitelist
   *  <em>Optional</em>.
   *  A regular expression to use as the whitelist for validating the given MIME type. If omitted,
   *  the default MIME type whitelist will be used.
   *
   * @param string $blacklist
   *  <em>Optional</em>.
   *  A regular expression to use as the blacklist for validating the given MIME type. If omitted,
   *  the default MIME type blacklist will be used.

   * @throws InvalidArgumentException
   *  if $name is null, empty or not a string, or a $whitelist or $blacklist are provided, but are
   *  empty, not strings or not valid regular expressions.
   *
   * @return boolean
   *  True if the given MIME type passes the MIME validation; false otherwise.
   */
  public function validateFileMimeType($file, $whitelist = null, $blacklist = null)
  {
    $mime = $this->getMimeType($file);
    return $this->validateMimeType($mime, $whitelist, $blacklist);
  }

}
