<?php
/**
 * CSVWriter.php
 *
 * @package Utility
 * @subpackage CSV
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\CSV;

use InvalidArgumentException;



/**
 * The CSVWriter class provides stream-like writing functionality for CSV data, writing raw CSV data
 * to a backing output stream.
 *
 * @package Utility
 * @subpackage CSV
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class CSVWriter
{
  /**
   * The maximum number of consecutive write attempts to make on the underlying output stream before
   * assuming the stream is broken and aborting.
   *
   * @var integer
   */
  const MAX_WRITE_ATTEMPTS = 3;

  /**
   * The underlying output stream to which raw CSV data is written.
   *
   * @var resource
   */
  protected $stream;

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Creates a new CSVWriter instance with the specified configuration.
   *
   * @param resource $stream
   *  The stream to receive raw CSV data.
   *
   * @throws InvalidArgumentException
   *  if $stream is not a valid output stream, or does not appear to be writeable.
   */
  public function __construct($stream)
  {
    if (!is_resource($stream)) {
      throw new InvalidArgumentException('$stream is not a valid output stream.');
    }

    // Make sure the stream is writeable or we'll have problems later.
    $metadata = @stream_get_meta_data($stream);

    if (empty($metadata['mode']) || strpbrk($metadata['mode'], 'waxc+') === false) {
      throw new InvalidArgumentException('$stream does not appear to be a writeable stream.');
    }

    $this->stream = $stream;
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Writes the specified data to the output stream.
   *
   * @param scalar $data
   *  The data to write to the stream.
   *
   * @return boolean
   *  True if the data was written in its entirety, false otherwise.
   */
  protected function writeToStream($data)
  {
    $result = false;

    if (is_scalar($data) || $data === null) {
      $target   = strlen($data);
      $attempts = 0;

      for ($written = 0; $written < $target && $attempts < static::MAX_WRITE_ATTEMPTS; $written += $output) {
        if (($output = fwrite($this->stream, substr($data, $written))) === false) {
          break;
        }

        $attempts = ($output === 0 ? $attempts + 1 : 0);
      }

      $result = ($written === $target);
    }

    return $result;
  }


  /**
   * Serializes a value such that it can be written directly to the output stream safely. The value
   * will be returned with any control characters escaped, and enclosed in quotes if necessary.
   *
   * @param scalar $value
   *  The value to encode.
   *
   * @throws InvalidArgumentException
   *  if $value is not null, nor a scalar value.
   *
   * @return string
   *  The encoded, stringified value.
   */
  protected function serializeValue($value)
  {
    if (!is_scalar($value) && $value !== null) {
      throw new InvalidArgumentException('$value is not null, nor a scalar value.');
    }

    switch (gettype($value)) {
      case 'boolean':
        $value = $value ? '1' : '0';

      default:
        $value = (string) $value;

      case 'string':
        $value = str_replace('"', '""', $value);

        if (strpbrk($value, ",\r\n") !== false) {
          $value = "\"{$value}\"";
        }
    }

    return $value;
  }

  /**
   * Writes a column/value delimiter to the underlying stream.
   *
   * @return boolean
   *  True if the delimiter was written successfully; false otherwise.
   */
  public function writeColumnDelimiter()
  {
    return $this->writeToStream(',');
  }

  /**
   * Writes a row delimiter to the underlying stream.
   *
   * @return boolean
   *  True if the delimiter was written successfully; false otherwise.
   */
  public function writeRowDelimiter()
  {
    return $this->writeToStream("\r\n");
  }

  /**
   * Writes the specified value to the underlying stream.
   *
   * <strong>Warning:</strong>
   *  If this method fails to write the entire value, the stream and CSV data are left in an
   *  undefined state.
   *
   * @param scalar $value
   *  The value to write.
   *
   * @return boolean
   *  True if the value was written successfully; false otherwise.
   */
  public function writeValue($value)
  {
    $value = $this->serializeValue($value);
    return $this->writeToStream($value);
  }

  /**
   * Writes the specified values to the underlying stream, followed by a row delimiter.
   *
   * <strong>Warning:</strong>
   *  If this method fails to write the entire row, the stream and CSV data are left in an undefined
   *  state.
   *
   * @param mixed $values
   *  The values to write to the stream. May be provided as a scalar representing a single value or
   *  an array or Traversable object representing multiple values in the row.
   *
   * @param integer $count
   *  <em>Optional</em>
   *  The number of values to write from the offset. If the count is larger than the size of the
   *  collection, empty values will be written for each absent value.
   *
   * @param integer $offset
   *  <em>Optional</em>
   *  The offset into the collection at which to begin writing values. If the offset is larger than
   *  the size of the collection, empty values will be written for each absent value.
   *
   * @throws InvalidArgumentException
   *  if $count is provided but is not a positive integer; or if $offset is provided, but is not a
   *  non-negative integer.
   *
   * @return boolean
   *  True if the values were written successfully; false otherwise.
   */
  public function writeRow($values, $count = null, $offset = null)
  {
    if (isset($offset) && (!is_int($offset) || $offset < 0)) {
      throw new InvalidArgumentException('$offset is provided, but is not a non-negative integer.');
    }

    if (isset($count) && (!is_int($count) || $count < 0)) {
      throw new InvalidArgumentException('$count is provided, but is not a positive integer.');
    }

    if (!(is_array($values) || $values instanceof Traversable)) {
      $values = [$values];
    }

    $written = 0;
    $pos = 0;

    if (isset($count)) {
      foreach ($values as $value) {
        if ($pos >= $offset) {
          if ($written && !$this->writeColumnDelimiter()) {
            break; // Uh oh.
          }

          if (!$this->writeValue($value)) {
            break; // Uh oh.
          }

          if (++$written >= $count) {
            break;
          }
        }

        ++$pos;
      }

      // Write some delimiters (which create empty value regions) to get up to our count.
      while ($written < $count) {
        if ($written++ && !$this->writeColumnDelimiter()) {
          break; // Uh oh.
        }
      }
    } else {
      $count = max(count($values) - (int) $offset, 0);

      // Writer without count checks.
      foreach ($values as $value) {
        if ($pos >= $offset) {
          if ($written && !$this->writeColumnDelimiter()) {
            break; // Uh oh.
          }

          if (!$this->writeValue($value)) {
            break; // Uh oh.
          }

          ++$written;
        }

        ++$pos;
      }
    }

    return ($written === $count) && $this->writeRowDelimiter();
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

}
