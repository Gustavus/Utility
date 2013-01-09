<?php
/**
 * @package Utility
 * @subpackage Test
 */

namespace Gustavus\Utility\Test;

use Gustavus\Utility\File,
  Gustavus\Test\Test,
  Gustavus\Test\TestObject;

/**
 * @package Utility
 * @subpackage Test
 */
class FileTest extends Test
{
  /**
   * @var string
   */
  private $path = '/cis/lib/Gustavus/Utility/Test/FileTest.php';

  /**
   * @var Utility\File
   */
  private $file;


  private $vnsFormat    = '%name$s is a %adjective$s %noun$s %percent$d%% of the time.';

  /**
   * sets up the object for each test
   * @return void
   */
  public function setUp()
  {
    $this->file = new TestObject(new File($this->path));
  }

  /**
   * destructs the object after each test
   * @return void
   */
  public function tearDown()
  {
    unset($this->file);
  }

  /**
   * @test
   */
  public function existsWithIncludePathExistent()
  {
    $this->path = 'Gustavus/Utility/File.php';
    $this->setUp();
    $this->assertTrue($this->file->exists());
  }

  /**
   * @test
   */
  public function existsWithIncludePathNonexistent()
  {
    $this->path = 'Gustavus/Utility/nonexistent_file.php';
    $this->setUp();
    $this->assertFalse($this->file->exists());
  }

  /**
   * @test
   */
  public function existsWithAbsoluteExistentPath()
  {
    $this->path = __FILE__;
    $this->setUp();
    $this->assertTrue($this->file->exists());
    $this->assertSame(__FILE__, $this->file->exists(true));
  }

  /**
   * @test
   */
  public function existsWithAbsoluteNonExistentPath()
  {
    $this->path = '/path/to/a/nonexistent_file.php';
    $this->setUp();
    $this->assertFalse($this->file->exists());
    $this->assertFalse($this->file->exists(true));
  }

  /**
   * @test
   */
  public function existsWithReturningFullPath()
  {
    $this->path = 'Gustavus/Utility/Test/FileTest.php';
    $this->setUp();
    $this->assertSame(__FILE__, $this->file->exists(true));
  }

  /**
   * @test
   */
  public function loadAndEvaluate()
  {
    $this->path = 'Gustavus/Utility/Test/views/vnsprintf.view.php';
    $this->setUp();
    $test = $this->file->loadAndEvaluate();
    $this->assertSame($this->vnsFormat, $test);
  }

  /**
   * @test
   */
  public function loadAndEvaluateNonExistentFile()
  {
    $this->path = 'nonexistent/file.php';
    $this->setUp();
    $this->assertNULL($this->file->loadAndEvaluate());
  }

}