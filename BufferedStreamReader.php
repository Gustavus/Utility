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
  const CHUNK_SIZE = 8192; // 8 kiB

  /**
   * The maximum number of bytes to keep buffered before discarding the buffer (and invalidating
   * marks). Note that due to how PHP works with strings, we will need 2-4x more memory than this
   * value to safely complete most operations.
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
   * The length of the data buffer. Used to avoid unnecessary, repeated calls to strlen. Will be
   * changing as often as $buffer.
   *
   * @var integer
   */
  protected $length;

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
   * The total number of bytes read from the stream. Has no practical use aside from bookkeeping.
   *
   * @var integer
   */
  protected $read;

  /**
   * Whether or not we're at our simulated EOF. Should only be set after a read, peek or skip has
   * been attempted and has either failed or read into EOF.
   *
   * @var boolean
   */
  protected $eof;

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
    $this->mark   = null;

    $this->resetBuffer();
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Resets the back buffer and all related statistics.
   *
   * @return StreamReader
   *  This StreamReader instance.
   */
  protected function resetBuffer()
  {
    $this->buffer = null;
    $this->length = 0;
    $this->offset = 0;
    $this->read   = 0;
    $this->eof    = false;

    return $this;
  }

  /**
   * Attempts to fill the back buffer with more data. If the stream does not have enough data to
   * complete the request or fill an entire chunk, this method returns false.
   *
   * @param integer $request
   *  The number of characters needed to fulfill the current request.
   *
   * @throws RuntimeException
   *  if the backing input stream has been closed.
   *
   * @throws InvalidArgumentException
   *  if $request is not a positive integer.
   *
   * @return integer
   *  The number of characters made available as a result of this operation.
   */
  protected function fillBuffer($request)
  {
    if ($this->isClosed()) {
      throw new RuntimeException('This stream has been closed.');
    }

    if (!is_int($request) || $request < 1) {
      throw new InvalidArgumentException('$request is not a positive integer.');
    }


    $available = $this->length - $this->offset;
    $read = 0;

    if ($request > $available) {
      $space = static::MAX_BUFFER_SIZE - $this->length;

      // Check if we can fulfill the request without exceeding our buffer limits.
      if ($space < $request) {
        if (isset($this->mark)) {
          if ($space + $this->mark >= $request) {
            // We can fulfill the request if we discard some consumed bytes first.
            $discard = $this->mark;
            $this->mark = 0;
          } else {
            // We can only fulfill the request if we invalidate the mark.
            $discard = $this->offset;
            $this->mark = null;
          }
        } else {
          $discard = $this->offset;
        }

        // Check if we should discard some consumed bytes.
        if ($discard > 0) {
          $this->buffer = substr($this->buffer, $discard);
          $this->length -= $discard;
          $this->offset -= $discard;
          $space        += $discard;
        }
      }

      $remain = min(static::CHUNK_SIZE, $space);

      // Read enough data to fill our buffer or fulfill the request.
      while ($available < $request && $remain > 0) {
        if ($chunk = fread($this->stream, $remain)) {
          $clen   = strlen($chunk);
          $read   += $clen;
          $remain -= $clen;

          $this->buffer .= $chunk;
          $this->length += $clen;
          $available    += $clen;
        } else {
          break; // EOF or error
        }
      }
    }

    $this->read += $read;
    return $read;
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
  public function isClosed()
  {
    return !is_resource($this->stream);
  }

  /**
   * {@inheritDoc}
   */
  public function isEOF()
  {
    if ($this->isClosed()) {
      throw new RuntimeException('This stream has been closed.');
    }

    return feof($this->stream) && !($this->length - $this->offset) && $this->eof;
  }

  /**
   * {@inheritDoc}
   */
  public function available()
  {
    if ($this->isClosed()) {
      throw new RuntimeException('This stream has been closed.');
    }

    return $this->length - $this->offset;
  }

  /**
   * {@inheritDoc}
   */
  public function read($count = 1, &$read = null)
  {
    if ($this->isClosed()) {
      throw new RuntimeException('This stream has been closed.');
    }

    if (!is_int($count)) {
      throw new InvalidArgumentException('$count is not an integer value.');
    }


    $result = false;
    $remain = $count;

    while ($remain > 0) {
      $available = $this->length - $this->offset;

      if ($available < 1) {
        // Try to fill the buffer, breaking if we fail to do so.
        if (!$this->fillBuffer($remain)) {
          $this->eof = true;
          break; // Uh oh.
        }

        $available = $this->length - $this->offset;
      }

      // Copy as much of our data buffer as we can to the output buffer
      $copied = min($remain, $available);
      $result .= substr($this->buffer, $this->offset, $copied);
      $this->offset += $copied;
      $remain -= $copied;
    }

    $read = ($count - $remain);

    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function peek($count = 1, &$read = null)
  {
    if ($this->isClosed()) {
      throw new RuntimeException('This stream has been closed.');
    }

    if (!is_int($count)) {
      throw new InvalidArgumentException('$count is not an integer value.');
    }


    $result = false;
    $remain = $count;
    $offset = $this->offset;
    $index  = $this->offset;

    while ($remain > 0) {
      $available = $this->length - $index;

      if ($available < 1) {
        // Try to fill the buffer, breaking if we fail to do so.
        if (!$this->fillBuffer($count)) {
          $this->eof = true;
          break; // Uh oh.
        }

        // Check if we discarded some consumed data to fulfill this request
        if ($this->offset != $offset) {
          $index -= ($offset - $this->offset);
        }

        $available = $this->length - $index;
      }

      // Copy as much of our data buffer as we can to the output buffer
      $copied = min($remain, $available);
      $result .= substr($this->buffer, $index, $copied);
      $remain -= $copied;
      $index  += $copied;
    }

    $read = ($count - $remain);
    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function skip($count = 1, &$read = null)
  {
    if ($this->isClosed()) {
      throw new RuntimeException('This stream has been closed.');
    }

    if (!is_int($count)) {
      throw new InvalidArgumentException('$count is not an integer value.');
    }


    $remain = $count;

    while ($remain > 0) {
      $available = $this->length - $this->offset;

      if ($available < 1) {
        // Try to fill the buffer, breaking if we fail to do so.
        if (!$this->fillBuffer($remain)) {
          $this->eof = true;
          break; // Uh oh.
        }

        $available = $this->length - $this->offset;
      }

      // Copy as much of our data buffer as we can to the output buffer
      $copied = min($remain, $available);
      $this->offset += $copied;
      $remain -= $copied;
    }

    $read = ($count - $remain);

    return !$remain;
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
    if ($this->isClosed()) {
      throw new RuntimeException('This stream has been closed.');
    }

    $this->mark = $this->offset;
    return true;
  }

  /**
   * {@inheritDoc}
   */
  public function clearMark()
  {
    if ($this->isClosed()) {
      throw new RuntimeException('This stream has been closed.');
    }

    $result = isset($this->mark);
    $this->mark = null;

    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function isMarked()
  {
    if ($this->isClosed()) {
      throw new RuntimeException('This stream has been closed.');
    }

    return isset($this->mark);
  }

  /**
   * {@inheritDoc}
   */
  public function rewind()
  {
    if ($this->isClosed()) {
      throw new RuntimeException('This stream has been closed.');
    }

    $result = false;

    if (isset($this->mark)) {
      $diff = $this->offset - $this->mark;

      $this->read -= $diff;
      $this->offset = $this->mark;
      $this->eof = false;

      $result = true;
    } else {
      if ($result = rewind($this->stream)) {
        $this->resetBuffer();
      }
    }

    return $result;
  }



}
