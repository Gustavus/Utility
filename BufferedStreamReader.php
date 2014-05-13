<?php
/**
 * BufferedStreamReader.php
 *
 * @package Utility
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility;

use InvalidArgumentException,
    RuntimeException;



/**
 * BufferedStreamReader.php
 *
 * @package Utility
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class BufferedStreamReader implements StreamReader
{
  /**
   * The number of bytes to attempt to read from the backing stream on each read operation.
   *
   * @var integer
   */
  const CHUNK_SIZE = 1024;

  /**
   * The maximum number of bytes to keep buffered before discarding the buffer (and invalidating
   * marks).
   *
   * @var integer
   */
  const MAX_BUFFER_SIZE = 262144; // 256 kiB


  /**
   * The stream from which data will be read.
   *
   * @var resource
   */
  protected $stream;

  /**
   * Our current data buffer. This will be changing fairly often.
   *
   * @var string
   */
  protected $buffer;

  /**
   * Our current offset into the buffer.
   *
   * @var integer
   */
  protected $offset;

  /**
   * The position of our last mark. Will be null if a position has not been marked.
   *
   * @var integer
   */
  protected $mark;

  /**
   * The total number of bytes read from the stream. Has no practical use.
   *
   * @var integer
   */
  protected $read;

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Creates a new StreamReader instance for the specified input stream.
   *
   * @param resource $stream
   *  The stream from which to read data.
   *
   * @throws InvalidArgumentException
   *  if $stream is not a valid input stream or does not appear to be readable.
   */
  public function __construct($stream)
  {
    // Impl note:
    // This also checks if the stream is still open. A closed resource is, apparently, not a
    // resource.
    if (!is_resource($stream)) {
      throw new InvalidArgumentException('$stream is not a valid input stream.');
    }

    // Make sure the stream is readable or we'll have problems later.
    $metadata = @stream_get_meta_data($stream);

    if (empty($metadata['mode']) || strpbrk($metadata['mode'], 'r+') === false) {
      throw new InvalidArgumentException('$stream does not appear to be a readable stream.');
    }

    $this->stream = $stream;
    $this->buffer = null;

    $this->offset = -1;
    $this->mark   = null;
    $this->read   = 0;
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * {@inheritDoc}
   */
  public function close()
  {
    if ($result = is_resource($this->stream)) {
      fclose($this->stream);
    }

    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function available()
  {
    if (!is_resource($this->stream)) {
      throw new RuntimeException('The stream has been closed.');
    }

    return strlen($this->buffer) - $this->offset;
  }

  /**
   * {@inheritDoc}
   */
  public function read($count = 1, &$read = null)
  {
    if (!is_resource($this->stream)) {
      throw new RuntimeException('The stream has been closed.');
    }

    return null;
  }

  /**
   * {@inheritDoc}
   */
  public function peek($count = 1, &$read = null)
  {
    if (!is_resource($this->stream)) {
      throw new RuntimeException('The stream has been closed.');
    }

    return null;
  }

  /**
   * {@inheritDoc}
   */
  public function skip($count = 1, &$read = null)
  {
    if (!is_resource($this->stream)) {
      throw new RuntimeException('The stream has been closed.');
    }

    return null;
  }

  /**
   * {@inheritDoc}
   */
  public function canMark()
  {
    return true;
  }

  /**
   * {@inheritDoc}
   */
  public function canRewind()
  {
    return true;
  }

  /**
   * {@inheritDoc}
   */
  public function mark()
  {
    if (!is_resource($this->stream)) {
      throw new RuntimeException('The stream has been closed.');
    }

    return false;
  }

  /**
   * {@inheritDoc}
   */
  public function rewind()
  {
    if (!is_resource($this->stream)) {
      throw new RuntimeException('The stream has been closed.');
    }

    return false;
  }



}
