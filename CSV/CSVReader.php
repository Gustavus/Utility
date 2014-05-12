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

use InvalidArgumentException;



/**
 * The CSVReader class provides stream-like read functionality for CSV data, reading raw CSV data
 * from a backing input stream and allowing it to be processed in logical chunks.
 *
 * @package Utility
 * @subpackage CSV
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class CSVReader
{
  /**
   * Token identifier for the end of the stream.
   *
   * @var integer
   */
  const TOKEN_END_OF_STREAM   = 0x00;

  /**
   * Token identifier for a column (value) delimiter.
   *
   * @var integer
   */
  const TOKEN_COL_DELIMITER   = 0x01;

  /**
   * Token identifier for a row delimiter.
   *
   * @var integer
   */
  const TOKEN_ROW_DELIMITER   = 0x02;

  /**
   * Token identifier for a value enclosure.
   *
   * @var integer
   */
  const TOKEN_VALUE_ENCLOSURE = 0x03;





  /**
   * The underlying input stream from which raw CSV data is read.
   *
   * @var resource
   */
  protected $stream;

  /**
   * Token buffer to be used with the peekToken method.
   *
   * @var mixed
   */
  protected $tbuffer;

////////////////////////////////////////////////////////////////////////////////////////////////////

  public function __construct($stream)
  {
    if (!is_resource($stream)) {
      throw new InvalidArgumentException('$stream is not a valid output stream.');
    }

    // Make sure the stream is readable or we'll have problems later.
    $metadata = @stream_get_meta_data($stream);

    if (empty($metadata['mode']) || strpbrk($metadata['mode'], 'r+') === false) {
      throw new InvalidArgumentException('$stream does not appear to be a readable stream.');
    }

    $this->stream = $stream;
    $this->tbuffer = null;
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Reads the next token from the backing input stream. The return type of this method varies
   * depending on the token. For generic character data, the value returned will be a string. For
   * special control tokens, the value will be an integer.
   *
   * @throws RuntimeException
   *  if the backing input stream is closed or data is, otherwise, unavailable.
   *
   * @return string|integer
   *  The next token in the stream.
   */
  protected function readToken()
  {
    if (!isset($this->tbuffer)) {
      $this->peekToken();
    }

    $token = $this->tbuffer;
    $this->tbuffer = null;

    return $token;
  }

  /**
   * Reads the next token from the backing input stream without consuming the token. The return type
   * of this method varies depending on the token returned. For generic character data, the value
   * returned will be a string. For special control tokens, the value will be an integer.
   *
   * @throws RuntimeException
   *  if the backing input stream is closed or data is, otherwise, unavailable.
   *
   * @return string|integer
   *  The next token in the stream.
   */
  protected function peekToken()
  {
    if (!isset($this->tbuffer)) {
      $char = fgetc($this->stream);

      if ($char !== false) {
        switch ($char) {
          case '"':
            // Peek next character
            // Check if the next character is also a quote.


        }
      } else {
        $this->tbuffer = static::TOKEN_END_OF_STREAM;
      }
    }

    // We need to make sure this is set by the time we get here.
    return $this->tbuffer;
  }


  /**
   * Reads the next value from the stream.
   *
   * @throws RuntimeException
   *  if the backing input stream is closed or data is, otherwise, unavailable.
   *
   * @return string
   *  The next value in the stream.
   */
  public function readValue()
  {
    $buffer = '';
    $enclosed = false;

    while (true) {
      $token = $this->readToken();

      if (is_string($token)) {
        // Generic data

      } else {
        // Control token
        switch ($token) {


        }
      }
    }



    // Loop: Read a token
    //  If the token is character data, add it to the buffer
    //
    //  If the token is an enclosure
    //    If we've not found an enclosure, check if our buffer has (non-whitespace) data
    //      If it does, the value seems to be malformed. Throw an exception
    //      Else discard the whitespace and set the "enclosed" flag
    //
    //    If we've found an enclosure
    //      Deserialize the value
    //      if the next token is one of: column delimiter, row delimiter or EOF
    //        deserialize value
    //        return value
    //
    //      Else
    //        Data is malformed. Throw an exception.
    //
    //  If the token is a column or row delimiter, or stream EOF:
    //    If we've found an enclosure, the data is malformed. Throw an exception
    //
    //    If we've not found an enclosure
    //      deserialize the value
    //      return value
    //
    //  If the token is anything else, throw an exception.
  }

  /**
   * Reads the remaining values in the current row. If the current row is empty, this method returns
   * an empty array.
   *
   * @throws RuntimeException
   *  if the backing input stream is closed or data is, otherwise, unavailable.
   *
   * @return array
   *  An array containing the values remaining in the current row.
   */
  public function readRow()
  {
    // Loop: Read values until EOR or EOF

    // - Determining the last token consumed to read a value is troublesome. We could buffer it
    // internally somewhere, but that seems pretty janky and not very friendly for extending.
  }

}
