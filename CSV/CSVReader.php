<?php
/**
 * CSVReader.php
 *
 * @package Utility
 * @subpackage CSV
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\CSV;

use Gustavus\Utility\BufferedStreamReader,

    InvalidArgumentException;



/**
 * The CSVReader class provides stream-like read functionality for CSV data, reading raw CSV data
 * from a backing input stream and allowing it to be processed in logical chunks.
 *
 * This implementation uses the following grammar for parsing CSV data:
 *
 *  <csv>         ::= <row-set>
 *
 *  <row-set>     ::= <row> <row-del> <row-set> | <row>
 *  <row>         ::= <val-set>
 *  <row-del>     ::= "\r\n" | "\n"
 *
 *  <val-set>     ::= <val> <val-del> <val-set> | <val>
 *  <val>         ::= '"' <quoted-text> '"' | <safe-chars>
 *  <val-del>     ::= <whitespace> "," <whitespace>
 *
 *  <quoted-text> ::= '""' <quoted-text> | <safe-chars> | [\r\n,]
 *  <safe-chars>  ::= ~[\r\n,"] <safe-chars> | ""
 *  <whitespace>  ::= " " <whitespace> | "\t" <whitespace> | ""
 *
 * @package Utility
 * @subpackage CSV
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class CSVReader
{
  /**
   * The underlying input stream from which raw CSV data is read, wrapped in a StreamReader
   * instance.
   *
   * @var StreamReader
   */
  protected $reader;

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   *
   * @param resource $stream
   *  The stream from which to read raw CSV data.
   *
   * @throws InvalidArgumentException
   *  if $stream is not a valid input stream, or does not appear to be readable.
   */
  public function __construct($stream)
  {
    if (!is_resource($stream)) {
      throw new InvalidArgumentException('$stream is not a valid input stream.');
    }

    // Make sure the stream is readable or we'll have problems later.
    $metadata = @stream_get_meta_data($stream);

    if (empty($metadata['mode']) || strpbrk($metadata['mode'], 'r+') === false) {
      throw new InvalidArgumentException('$stream does not appear to be a readable stream.');
    }

    $this->reader = $this->getStreamReader($stream);
    $this->lastToken = null;
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Retrieves a StreamReader instance for the specified stream.
   *
   * @param resource $stream
   *  The stream to wrap in a StreamReader.
   *
   * @throws InvalidArgumentException
   *  if $stream is not a valid input stream, or does not appear to be readable.
   */
  protected function getStreamReader($stream)
  {
    return new BufferedStreamReader($stream);
  }

  /**
   * Checks if the stream backing this CSVReader is at end-of-file.
   *
   * @return boolean
   *  True if the stream is at EOF; false otherwise.
   */
  public function isEOF()
  {
    return $this->reader->isEOF();
  }

  /**
   * Reads the next value from the stream.
   *
   * @param boolean &$endofrow
   *  <em>Optional, Out</em>
   *  Whether or not the value read by this method was at the end of its row.
   *
   * @throws RuntimeException
   *  if the backing input stream is closed or data is, otherwise, unavailable.
   *
   * @return string
   *  The next value in the stream, or false if the stream is at EOF.
   */
  public function readValue(&$endofrow = null)
  {
    $buffer = false;
    $whitespace = '';

    $inval    = false;  // Whether or not we're in a value.
    $enclosed = false;  // Whether or not the value is enclosed. Implies inval.
    $endofrow = false;

    // Force a read so we can check if there's any data available.
    $this->reader->peek(1);

    if (!$this->reader->isEOF()) {
      $buffer = '';

      while (true) {
        $chars = $this->reader->peek(2);

        if ($chars !== false) {
          switch ($chars[0]) {
            case ',': // Column/value delimiter
              if (!$enclosed) {
                $this->reader->skip(1);
                break 2;
              }
                break;

            case '"':
              if ($enclosed) {
                $this->reader->skip(1);

                if (empty($chars[1]) || $chars[1] !== $chars[0]) {
                  $enclosed = false;
                  $inval = false;

                  continue 2;
                }
              } else if (!$inval) {
                $this->reader->skip(1);

                $inval = true;
                $enclosed = true;

                continue 2;
              }
                break;

            case "\r":
              if (!$enclosed && !empty($chars[1]) && $chars[1] === "\n") {
                $endofrow = true;
                $this->reader->skip(2);

                break 2;
              }
                break;

            case "\n":
              if (!$enclosed) {
                $endofrow = true;
                $this->reader->skip(1);

                break 2;
              }
                break;

            case " ":
            case "\t":
              if (!$inval) {
                $whitespace .= $chars[0];
                $this->reader->skip(1);
                continue 2;
              }

            default:
              if (!$inval && !empty($buffer)) {
                // This only happens if the data appeared to be quoted when it really wasn't. For
                // example: ""What is this garbage!?" he exclaimed loudly."

                // We can recover by tacking the quote and whitespace to our buffer and letting it
                // process normally.

                $buffer = "\"{$buffer}\"{$whitespace}";
              }
          }

          $buffer .= $chars[0];
          $this->reader->skip(1);

          $inval = true;
          $whitespace = '';
        } else {
          $endofrow = true;
          break;
        }
      }
    }

    return $buffer;
  }

  /**
   * Reads the remaining values in the current row. If the current row is empty, this method returns
   * an empty array.
   *
   * @param boolean &$endoffile
   *  <em>Optional, Out</em>
   *  Whether or not the
   *
   * @throws RuntimeException
   *  if the backing input stream is closed or data is, otherwise, unavailable.
   *
   * @return array
   *  An array containing the values remaining in the current row, or false if the stream is at EOF.
   */
  public function readRow()
  {
    $result = false;
    $endofrow = false;
    $endoffile = true;

    $this->reader->peek(1);

    if (!$this->reader->isEOF()) {
      $result = [];

      while (!$endofrow && ($value = $this->readValue($endofrow)) !== false) {
        $result[] = $value;
      }
    }

    return $result;
  }

}
