<?php
/**
 * CSVBuilder.php
 *
 * @package Utility
 * @subpackage CSV
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\CSV;

use InvalidArgumentException;



/**
 * The CSVBuilder class provides functionality for building and processing CSV data programatically.
 *
 * <strong>Note:</strong>
 *  This class stores its data in memory as it is being built. As such, it is subject to PHP's
 *  memory limits. When compiling or processing large amounts of CSV data, it may make more sense
 *  to use the CSVReader or CSVWriter classes directly and process chunks of data at a time.
 *
 * @package Utility
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class CSVBuilder
{
  /**
   * CSV data, stored as an array of arrays.
   *
   * @var array
   */
  protected $data;

  /**
   * Whether or not the UTF-8 Byte Order Mark (BOM) should be prepended to any data we export. This
   * is a hack and should only be enabled when exporting a file for certain programs that can't
   * process UTF-8 data without it (see: select versions of Excel).
   *
   * @var boolean
   */
  protected $addbom;

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Creates a new CSVBuilder instance, optionally populating it with the specified data.
   *
   * @param mixed $data
   *  <em>Optional</em>.
   *  The data with which to populate this builder. May be specified in any of the following
   *  formats:
   *  <ul>
   *    <li>A string representing a file from which to import data</li>
   *    <li>A string representing raw CSV data</li>
   *    <li>An array representing a single row of data</li>
   *    <li>An array representing a collection of rows</li>
   *    <li>A Traversable object representing a single row of data</li>
   *    <li>A Traversable object representing a collection of rows</li>
   *  </ul>
   *
   *  As some of these formats may be indistinguishable from one another, the order in which they're
   *  listed is the order they will be attempted. For example, a string that represents a single
   *  column of data will be used as a file name before being processed as raw CSV data.
   *
   * @throws InvalidArgumentException
   *  if $data is provided in an unsupported format.
   */
  public function __construct($data = null)
  {

  }

  /**
   * Copy constructor (of sorts).
   */
  public function __clone()
  {
    // @todo:
    // Make a deep-copy of the data here
  }

////////////////////////////////////////////////////////////////////////////////////////////////////





////////////////////////////////////////////////////////////////////////////////////////////////////



  /**
   * Imports the specified data as raw CSV data, appending its data to the data currently stored by
   * this builder.
   *
   * @param string $data
   *  The raw CSV data to import.
   *
   * @throws InvalidArgumentException
   *  if $data is not a string.
   *
   * @return integer
   *  The number of rows added as a result of this operation.
   */
  public function import($data)
  {

  }

  /**
   * Imports the data from the specified file, appending its data to the data currently stored by
   * this builder.
   *
   * @param string $file
   *  The name of the file from which to import data.
   *
   * @throws InvalidArgumentException
   *  if $file is null, empty or not a string.
   *
   * @return integer
   *  The number of rows added as a result of this operation.
   */
  public function importFile($file)
  {

  }

  public function export()
  {

  }

  public function exportToFile($file)
  {

  }

  // add row (index, row)
  // get row (index)
  // set row (index, row)
  // remove row (index)

  // add col
  // get col
  // set col
  // remove col

  // get value (x, y)
  // set value (x, y, value)

  // clear
}
