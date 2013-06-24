<?php
/**
 * @package Utility
 * @subpackage Test
 * @author Nicholas Dobie <ndobie@gustavus.edu>
 */
namespace Gustavus\Utility;

/**
 * The last header set
 *
 * @var mixed
 */
$lastHeader = null;

/**
 * Determines if the headers are sent
 *
 * @var boolean
 */
$headersSent;

/**
 * Returns the last header set
 * @return mixed
 */
function getLastHeader()
{
  global $lastHeader;
  return $lastHeader;
}

/**
 * Overrides the built in header function.
 *
 * @param  string $header header to set
 * @return void
 */
function header($header)
{
  global $lastHeader;
  $lastHeader = $header;
}

/**
 * Change the status of the headers
 *
 * @param boolean $sent true if headers are sent
 * @return void
 */
function setHeadersSent($sent)
{
  global $headersSent;
  $headersSent = $sent;
}

/**
 * Overrides the built in header_sent function.
 *
 * @return boolean
 */
function headers_sent() {
  global $headersSent;
  return $headersSent;
}