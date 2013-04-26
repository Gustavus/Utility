<?php
/**
 * @package Utility
 * @subpackage Test
 */

namespace Gustavus\Utility\Test;

use Gustavus\Utility\PageUtil,
  Gustavus\Test\Test;

class PageUtilRedirectOveride extends PageUtil
{
  /**
   * Overloads redirect from PageUtil to return the path instead of redirecting
   *
   * @param  string $path path to redirect to
   * @return  string
   */
  public static function redirect($path = '/')
  {
    echo $path;
  }
}

/**
 * @package Utility
 * @subpackage Test
 */
class PageUtilTest extends Test
{
  /**
   * sets up the object for each test
   * @return void
   */
  public function setUp()
  {
    $_SERVER['SCRIPT_NAME'] = '/billy/index.php';
  }

  /**
   * destructs the object after each test
   * @return void
   */
  public function tearDown()
  {
  }

  /**
   * @test
   */
  public function buildMessageKey()
  {
    $result = $this->call('\Gustavus\Utility\PageUtil', 'buildMessageKey', array('https://beta.gac.edu/billy/index.php'));
    $this->assertSame(hash('md4', '/billy/index.php'), $result);
  }

  /**
   * @test
   */
  public function buildMessageKeyNoParams()
  {
    $result = $this->call('\Gustavus\Utility\PageUtil', 'buildMessageKey');
    $this->assertSame(hash('md4', '/billy/index.php'), $result);
  }

  /**
   * @test
   */
  public function buildMessageKeyNoFile()
  {
    $result = $this->call('\Gustavus\Utility\PageUtil', 'buildMessageKey', array('https://beta.gac.edu/billy/'));
    $this->assertSame(hash('md4', '/billy/index.php'), $result);
  }

  /**
   * @test
   */
  public function setSessionMessage()
  {
    PageUtil::setSessionMessage('TestMessage');
    $this->assertSame('TestMessage', $_SESSION['messages'][hash('md4', $_SERVER['SCRIPT_NAME'])]);
  }

  /**
   * @test
   */
  public function setSessionErrorMessage()
  {
    PageUtil::setSessionMessage('TestErrorMessage', true);
    $this->assertSame('TestErrorMessage', $_SESSION['errorMessages'][hash('md4', $_SERVER['SCRIPT_NAME'])]);
  }

  /**
   * @test
   */
  public function getSessionMessage()
  {
    $_SERVER['SCRIPT_NAME'] = '/arst/arst.php';
    PageUtil::setSessionMessage('TestMessage', false, '/arst/arst.php');
    $this->assertSame('TestMessage', PageUtil::getSessionMessage());
    $this->assertFalse(isset($_SESSION['messages'][hash('md4', $_SERVER['SCRIPT_NAME'])]));
  }

  /**
   * @test
   */
  public function getSessionErrorMessage()
  {
    $_SERVER['SCRIPT_NAME'] = '/arst/arst.php';
    PageUtil::setSessionMessage('TestErrorMessage', true, '/arst/arst.php');
    $this->assertSame('TestErrorMessage', PageUtil::getSessionErrorMessage());
    $this->assertFalse(isset($_SESSION['errorMessages'][hash('md4', $_SERVER['SCRIPT_NAME'])]));
  }

  /**
   * @test
   */
  public function getSessionMessageNull()
  {
    PageUtil::setSessionMessage('TestMessage', false, '/arst/arst.php');
    $this->assertFalse(isset($_SESSION['messages'][hash('md4', $_SERVER['SCRIPT_NAME'])]));
    $this->assertNull(PageUtil::getSessionMessage());
  }

  /**
   * @test
   */
  public function getSessionErrorMessageNull()
  {
    PageUtil::setSessionMessage('TestErrorMessage', true, '/arst/arst.php');
    $this->assertFalse(isset($_SESSION['errorMessages'][hash('md4', $_SERVER['SCRIPT_NAME'])]));
    $this->assertNull(PageUtil::getSessionErrorMessage());
  }

  /**
   * @test
   */
  public function redirectWithMessage()
  {
    ob_clean();
    PageUtilRedirectOveride::redirectWithMessage('/arst/arst.php', 'Message');
    $location = ob_get_contents();
    ob_clean();
    $this->assertSame('/arst/arst.php', $location);
    $this->assertSame('Message', $_SESSION['messages'][hash('md4', '/arst/arst.php')]);
  }

  /**
   * @test
   */
  public function redirectWithError()
  {
    ob_clean();
    PageUtilRedirectOveride::redirectWithError('/arst/arst.php', 'Error Message');
    $location = ob_get_contents();
    ob_clean();
    $this->assertSame('/arst/arst.php', $location);
    $this->assertSame('Error Message', $_SESSION['errorMessages'][hash('md4', '/arst/arst.php')]);
  }
}