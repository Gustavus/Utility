<?php
/**
 * StreamReader.php
 *
 * @package Utility
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility;



/**
 * The StreamReader provides a standardized interface for performing read operations on a stream;
 * loosely based on Java's Reader interface.
 *
 * @package Utility
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
interface StreamReader
{
  /**
   * Closes this StreamReader, the input stream backing it and releases any associated resources.
   * Once the stream has closed, calls to methods which perform operations on the stream will throw
   * an exception. Repeated invocations will have no effect on this instance.
   *
   * @return boolean
   *  True if this reader was closed successfully; false otherwise.
   */
  public function close();

  /**
   * Checks if this StreamReader has been closed.
   *
   * @return boolean
   *  True if this reader is closed; false otherwise.
   */
  public function isClosed();

  /**
   * Checks if this StreamReader is at the end-of-file/stream. Note that for a stream to reach EOF,
   * it must first fail a read operation.
   *
   * @throws RuntimeException
   *  if the stream is closed or an error occurs during reading.
   *
   * @return boolean
   *  True if the stream is at EOF; false otherwise.
   */
  public function isEOF();

  /**
   * Returns the estimated number of characters that can be read, peeked or skipped without
   * blocking. The number returned by this method does not necessarily provide any indication of the
   * amount of characters remaining in the stream.
   *
   * @return integer
   *  The estimated number of characters that can be read, peeked or skipped without blocking.
   */
  public function available();

  /**
   * Reads a number of characters from the backing input stream. This method will block until the
   * specified number of characters are read, end-of-stream is reached or an error occurs.
   *
   * @param integer $count
   *  <em>Optional</em>
   *  The number of characters to read from the stream. If omitted, a single character will be read.
   *
   * @param integer &$read
   *  <em>Optional, Out</em>
   *  The number of characters actually read during this operation.
   *
   * @throws RuntimeException
   *  if the stream is closed or an error occurs during reading.
   *
   * @throws InvalidArgumentException
   *  if $count is not an integer value.
   *
   * @return string
   *  The characters read from the stream as a string.
   */
  public function read($count = 1, &$read = null);

  /**
   * Reads a number of characters from the backing input stream without consuming the data. This
   * method will block until the specified number of characters are read, end-of-stream is reached
   * or an error occurs.
   *
   * As the data is not consumed, repeated calls to this method will return the same character
   * sequence.
   *
   * @param integer $count
   *  <em>Optional</em>
   *  The number of characters to read from the stream. If omitted, a single character will be read.
   *
   * @param integer &$read
   *  <em>Optional, Out</em>
   *  The number of characters actually read during this operation.
   *
   * @throws RuntimeException
   *  if the stream is closed or an error occurs during reading.
   *
   * @throws InvalidArgumentException
   *  if $count is not an integer value.
   *
   * @return string
   *  The characters read from the stream as a string.
   */
  public function peek($count = 1, &$read = null);

  /**
   * Skips a number of characters from the backing input stream. This method will block until the
   * specified number of characters are skipped, end-of-stream is reached or an error occurs.
   *
   * @param integer $count
   *  <em>Optional</em>
   *  The number of characters to skip in the stream. If omitted, a single character will be
   *  skipped.
   *
   * @param integer &$read
   *  <em>Optional, Out</em>
   *  The number of characters actually read during this operation.
   *
   * @throws RuntimeException
   *  if the stream is closed or an error occurs during reading.
   *
   * @throws InvalidArgumentException
   *  if $count is not an integer value.
   *
   * @return void
   */
  public function skip($count = 1, &$read = null);

  /**
   * Checks whether or not this reader can mark the current position for rewinding.
   *
   * @return boolean
   *  True if this reader supports the mark operation; false otherwise.
   */
  public function canMark();

  /**
   * Checks whether or not this reader can rewind to a previous point in the stream.
   *
   * @return boolean
   *  True if this reader supports the rewind operation; false otherwise.
   */
  public function canRewind();

  /**
   * Marks the current position in the stream. Subsequent calls to <tt>rewind()</tt> will attempt to
   * reposition the stream to this point.
   *
   * <strong>Note:</strong>
   *  Not all StreamReader implementations will support this operation. Check the result of the
   *  <tt>canMark()</tt> method if this functionality is required.
   *
   * @throws RuntimeException
   *  if the stream is closed.
   *
   * @return boolean
   *  True if the current position was marked successfully; false otherwise.
   */
  public function mark();

  /**
   * Clears the marked position, if any, in the stream.
   *
   * @throws RuntimeException
   *  if the stream is closed.
   *
   * @return boolean
   *  True if a marked position was cleared; false otherwise.
   */
  public function clearMark();

  /**
   * Checks if this stream has a previous position in the stream marked and the mark has not been
   * invalidated.
   *
   * @throws RuntimeException
   *  if the stream is closed.
   *
   * @return boolean
   *  True if this stream has a valid, marked position; false otherwise.
   */
  public function isMarked();

  /**
   * Attempts to reposition the stream to the previously marked position. If a position has not been
   * marked, the stream will be attempt to be repositioned to the beginning. If the rewind operation
   * cannot complete successfully, the stream pointer will not be repositioned at all.
   *
   * As marked positions may be invalidated by the reader, calling applications should first check
   * the result of the <tt>isMarked()</tt> method to determine which behavior will be triggered by
   * this method.
   *
   * <strong>Note:</strong>
   *  Not all StreamReader implementations will support this operation. Check the result of the
   *  <tt>canRewind()</tt> method if this functionality is required.
   *
   * @throws RuntimeException
   *  if the stream is closed.
   *
   * @return boolean
   *  True if the stream was repositioned successfully; false otherwise.
   */
  public function rewind();

}
