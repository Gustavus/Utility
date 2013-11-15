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
   * Sets up the File object based off of $this->path
   * @return void
   */
  private function init()
  {
    $this->file = new TestObject(new File($this->path));
  }

  /**
   * @test
   */
  public function existsWithIncludePathExistent()
  {
    $this->path = 'Gustavus/Utility/File.php';
    $this->init();
    $this->assertTrue($this->file->exists());
  }

  /**
   * @test
   */
  public function existsWithIncludePathNonexistent()
  {
    $this->path = 'Gustavus/Utility/nonexistent_file.php';
    $this->init();
    $this->assertFalse($this->file->exists());
  }

  /**
   * @test
   */
  public function existsWithAbsoluteExistentPath()
  {
    $this->path = __FILE__;
    $this->init();
    $this->assertTrue($this->file->exists());
    $this->assertSame(__FILE__, $this->file->exists(true));
  }

  /**
   * @test
   */
  public function existsWithAbsoluteNonExistentPath()
  {
    $this->path = '/path/to/a/nonexistent_file.php';
    $this->init();
    $this->assertFalse($this->file->exists());
    $this->assertFalse($this->file->exists(true));
  }

  /**
   * @test
   */
  public function existsWithReturningFullPath()
  {
    $this->path = 'Gustavus/Utility/Test/FileTest.php';
    $this->init();
    $this->assertSame(__FILE__, $this->file->exists(true));
  }

  /**
   * @test
   */
  public function loadAndEvaluate()
  {
    $this->path = 'Gustavus/Utility/Test/views/vnsprintf.view.php';
    $this->init();
    $test = $this->file->loadAndEvaluate();
    $this->assertSame($this->vnsFormat, $test);
  }

  /**
   * @test
   */
  public function loadAndEvaluateNonExistentFile()
  {
    $this->path = 'nonexistent/file.php';
    $this->init();
    $this->assertNULL($this->file->loadAndEvaluate());
  }

  /**
   * @test
   */
  public function FilenameNewFile()
  {
    $this->path = 'newfile.php';
    $this->init();
    $this->assertSame('newfile.php', $this->file->filename('/new/path/')->getValue());
  }

  /**
   * @test
   */
  public function FilenameNewFileNoExtension()
  {
    $this->path = 'newfile';
    $this->init();
    $this->assertSame('newfile', $this->file->filename('/new/path/')->getValue());
  }

  /**
   * @test
   */
  public function FilenameExistingFileWithDots()
  {
    $this->path = 'sentence.twig';
    $this->init();
    $this->assertSame('sentence-1.twig', $this->file->filename('/cis/lib/Gustavus/Utility/Views/Set/')->getValue());
  }

  /**
   * @test
   */
  public function FilenameNoLocation()
  {
    $this->path = 'format.class.php';
    $this->init();
    $this->assertSame('format.class.php', $this->file->filename()->getValue());
  }

  /**
   * @test
   */
  public function find()
  {
    $this->path = 'site_nav.php';
    $this->init();
    $this->assertSame(false, $this->file->find()->getValue());
  }

  /**
   * @test
   */
  public function findFound()
  {
    $this->path = 'site_nav_test.php';
    $this->init();
    // simulate looking for a site nav file from a web directory
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
    $this->assertSame('/cis/lib/Gustavus/Utility/Test/site_nav_test.php', $this->file->find()->getValue());
  }

  /**
   * @test
   */
  public function findFromScriptName()
  {
    $this->path = 'site_nav.php';
    $this->init();
    // simulate looking for a site nav file from a web directory
    $_SERVER['SCRIPT_FILENAME'] = '/cis/www/alumni/class/index.php';
    $expected = '/cis/www/alumni/site_nav.php';
    $this->assertSame($expected, $this->file->find()->getValue());
  }

  /**
   * @test
   */
  public function findFromScriptNameCurrentDir()
  {
    $this->path = 'site_nav_test.php';
    $this->init();
    // simulate looking for a site nav file from a web directory
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
    $expected = __DIR__ . '/site_nav_test.php';
    $this->assertSame($expected, $this->file->find()->getValue());
  }

  /**
   * @test
   */
  public function findFromStartDir()
  {
    $this->path = 'site_nav.php';
    $this->init();
    $expected = '/cis/www/alumni/site_nav.php';
    $this->assertSame($expected, $this->file->find('/cis/www/alumni/class/')->getValue());
  }

  /**
   * @test
   */
  public function findFromStartDirWithDefaultValue()
  {
    $this->path = 'site_nav.php';
    $this->init();
    $expected = 'arst';
    $this->assertSame($expected, $this->file->find('/cis/lib/Gustavus/Utility/Test/', 'arst')->getValue());
  }

  /**
   * @test
   */
  public function findFromStartDirExistingInStartDir()
  {
    $this->path = 'site_nav_test.php';
    $this->init();
    $expected = '/cis/lib/Gustavus/Utility/Test/site_nav_test.php';
    $this->assertSame($expected, $this->file->find('/cis/lib/Gustavus/Utility/Test/', 'arst')->getValue());
  }

  /**
   * @test
   */
  public function findFromStartDirMoreThan5Levels()
  {
    $this->path = 'site_nav_test.php';
    $this->init();
    $this->assertFalse($this->file->find('/cis/lib/Gustavus/Utility/Test/some/random/directory/that/is/above/five/levels/deep/')->getValue());
  }

  /**
   * @test
   */
  public function findFromStartDirSettingLevels()
  {
    $this->path = 'site_nav_test.php';
    $this->init();
    $this->assertFalse($this->file->find('/cis/lib/Gustavus/Utility/Test/some/random/directory/that/is/above/five/levels/deep/', false, 9)->getValue());
    $this->path = 'site_nav_test.php';
    $this->init();
    $expected = '/cis/lib/Gustavus/Utility/Test/site_nav_test.php';
    $this->assertSame($expected, $this->file->find('/cis/lib/Gustavus/Utility/Test/some/random/directory/that/is/above/five/levels/deep/', false, 10)->getValue());
  }
}