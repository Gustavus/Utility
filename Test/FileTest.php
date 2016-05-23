<?php
/**
 * @package Utility
 * @subpackage Test
 */

namespace Gustavus\Utility\Test;

use Gustavus\Utility\File,
  Gustavus\Test\Test,
  Gustavus\Test\TestObject,
  Gustavus\GACCache\GlobalCache;

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
   * @dataProvider filenameData
   */
  public function filename($expected, $filename, $location = null, $extension = null)
  {
    $this->path = $filename;
    $this->init();
    $this->assertSame($expected, $this->file->filename($location, $extension)->getValue());
  }

  /**
   * Filename data provider
   * @return  array
   */
  public function filenameData()
  {
    $extension = '.ext';
    $longname = str_repeat('abc123', 45) . $extension;
    $truncated = substr($longname, 0, 240 - strlen($extension)) . $extension;

    return [
      ['newfile.php', 'newfile.php', '/new/path/'],
      ['newfile', 'newfile', '/new/path/'],
      ['sentence-1.twig', 'sentence.twig', '/cis/lib/Gustavus/Utility/Views/Set/'],
      ['format.class.php', 'format.class.php'],
      ['fetch>uid>.gac>10046.gac>10046', 'fetch>UID>.GAC>10046.GAC%3E10046', '/cis/lib/Gustavus/Utility/Test/Files'],
      ['fetch>uid>.gac>10046.gac>10046.jpg', 'fetch>UID>.GAC>10046.GAC%3E10046', '/cis/lib/Gustavus/Utility/Test/Files', 'jpg'],
      ['fetch>uid>.gac>10046.gac>10046.jpg', 'fetch>UID>.GAC>10046.GAC%3E10046', '/cis/lib/Gustavus/Utility/Test/Files', '.jpg'],
      [$truncated, $longname],
      [$truncated, $longname, '/long/path/'],
    ];
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

  /**
   * @test
   */
  public function ViewUsingVnsprintf()
  {
    $vnsValues = [
      'name'      => 'Santa Claus',
      'adjective' => 'jolly',
      'noun'      => 'man',
      'percent'   => 99,
    ];

    $vnsExpecting = 'Santa Claus is a jolly man 99% of the time.';

    $file = new File(__DIR__ . '/views/vnsprintf.view.html');

    $result = $file->renderAsPView($vnsValues, true);
    $this->assertInstanceOf('\\Gustavus\\Utility\\GACString', $result);
    $this->assertSame($vnsExpecting, $result->getValue());
  }

  /**
   * @test
   */
  public function ViewUsingVnsprintfAndEvaluatingView()
  {
    $vnsValues = [
      'name'      => 'Santa Claus',
      'adjective' => 'jolly',
      'noun'      => 'man',
      'percent'   => 99,
    ];

    $vnsExpecting = 'Santa Claus is a jolly man 99% of the time.';

    $file = new File(__DIR__ . '/views/vnsprintf.view.php');

    $result = $file->renderAsPView($vnsValues, true, true);
    $this->assertInstanceOf('\\Gustavus\\Utility\\GACString', $result);
    $this->assertSame($vnsExpecting, $result->getValue());
  }

  /**
   * @test
   */
  public function ViewUsingVsprintf()
  {
    $vnsValues = [
      'name'      => 'Santa Claus',
      'adjective' => 'jolly',
      'noun'      => 'man',
      'percent'   => 99,
      'dummy'     => 1,
    ];

    $vnsExpecting = 'Santa Claus is a jolly man 99% of the time.';

    $file = new File(__DIR__ . '/views/vsprintf.view.html');

    $result = $file->renderAsPView($vnsValues);
    $this->assertInstanceOf('\\Gustavus\\Utility\\GACString', $result);
    $this->assertSame($vnsExpecting, $result->getValue());
  }

  /**
   * @test
   */
  public function ViewUsingVsprintfAndEvaluatingView()
  {
    $vnsValues = [
      'name'      => 'Santa Claus',
      'adjective' => 'jolly',
      'noun'      => 'man',
      'percent'   => 99,
      'dummy'     => 2,
    ];

    $vnsExpecting = 'Santa Claus is a jolly man 99% of the time.';

    $file = new File(__DIR__ . '/views/vsprintf.view.php');

    $result = $file->renderAsPView($vnsValues, false, true);
    $this->assertInstanceOf('\\Gustavus\\Utility\\GACString', $result);
    $this->assertSame($vnsExpecting, $result->getValue());
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function ViewWithNonexistentView()
  {
    $vnsValues = [
      'name'      => 'Santa Claus',
      'adjective' => 'jolly',
      'noun'      => 'man',
      'percent'   => 99,
      'dummy'     => 3,
    ];

    $vnsExpecting = 'Santa Claus is a jolly man 99% of the time.';

    $file = new File('view/that/does/not/exist.html');

    $file->renderAsPView(array('test'));
  }

  /**
   * @test
   */
  public function ViewWithCachedResult()
  {
    $vnsValues = [
      'name'      => 'Santa Claus',
      'adjective' => 'jolly',
      'noun'      => 'man',
      'percent'   => 99,
      'dummy'     => 4,
    ];

    $vnsExpecting = 'Santa Claus is a jolly man 99% of the time.';

    $datastore = GlobalCache::getGlobalDataStore();
    $datastore->clearAllValues();

    $file = new File(__DIR__ . '/views/vnsprintf.view.html');

    $result = $file->renderAsPView($vnsValues, true);
    $this->assertInstanceOf('\\Gustavus\\Utility\\GACString', $result);
    $this->assertSame($vnsExpecting, $result->getValue());

    $result = $file->renderAsPView($vnsValues, true);
    $this->assertInstanceOf('\\Gustavus\\Utility\\GACString', $result);
    $this->assertSame($vnsExpecting, $result->getValue());
  }
}