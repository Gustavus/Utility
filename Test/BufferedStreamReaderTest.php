<?php
/**
 * BufferedStreamReaderTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test;

use Gustavus\Test\Test,
    Gustavus\Test\DelayedExecutionToken,

    Gustavus\Utility\BufferedStreamReader;



/**
 * Test suite for the BufferedStreamReader class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class BufferedStreamReaderTest //extends StreamReaderTestTemplate
{
  /**
   * {@inheritDoc}
   */
  public function buildInstance($stream)
  {
    return new BufferedStreamReader($stream);
  }

}
