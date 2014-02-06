<?php
/**
 * @package Utility
 * @subpackage Test
 * @author Nicholas Dobie <ndobie@gustavus.edu>
 */
namespace Gustavus\Utility\Test;

use Gustavus\Test\Test,
    Gustavus\Test\TestObject,
    Gustavus\Utility\FileGrabber;

/**
 * Tests the file grabber.
 *
 * @package Utility
 * @subpackage Test
 * @author Nicholas Dobie <ndobie@gustavus.edu>
 */
class FileGrabberTest extends Test
{

  /**
   * Stores an instance of FileGrabber
   * @var FileGrabber
   */
  private $fg;

  /**
   * Prepares test
   *
   * @return void
   */
  public function setup()
  {
    $this->fg = new TestObject(new FileGrabber());
  }

  /**
   * Cleans up test
   *
   * @return void
   */
  public function teardown()
  {
    $this->fg->clearQueue();
  }

  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////

  /**
   * @test
   */
  public function constructorAndDestructor()
  {
    $fg = new FileGrabber('https://beta.gac.edu/test.jpg');

    $this->assertEquals(array('https://beta.gac.edu/test.jpg'), $fg->getQueue());

    $fg->clearQueue();

    unset($fg);

    $fg = new FileGrabber(array('https://beta.gac.edu/test.jpg'));

    $this->assertEquals(array('https://beta.gac.edu/test.jpg'), $fg->getQueue());

    $path = $fg->localPath('https://beta.gac.edu/test.jpg');

    @unlink($path);

    unset($fg);

    $this->assertTrue(file_exists($path));

    @unlink($path);

  }

  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////

  /**
   * @test
   */
  public function getFile()
  {
    $url = 'https://beta.gac.edu/test.jpg';

    @unlink($this->fg->localPath($url));

    $remote = $this->fg->getFile($url);

    $this->assertTrue(is_string($remote));

    $this->assertEquals(md5($remote), md5($this->fg->getFile($url)));

    @unlink($this->fg->localPath($url));
  }

  /**
   * @test
   */
  public function grabFile()
  {
    $url = 'https://beta.gac.edu/test.jpg';

    $file = $this->fg->grabFile($url);

    $this->assertEquals($this->fg->localPath($url), $file);

    $this->assertEquals(array($url), $this->fg->getQueue());

    $this->assertFalse($this->fg->grabFile($url . '2'));
  }

  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////

  /**
   * @test
   */
  public function isAllowed()
  {

    $url = 'http://beta.gac.edu/test.jpg';

    @unlink($this->fg->localPath($url));

    $this->assertFalse($this->fg->isAllowed($url . '2'));

    $this->assertTrue($this->fg->isAllowed($url));

    file_put_contents($this->fg->localPath($url), 'This is a test.');

    $this->assertTrue($this->fg->isAllowed($url));

    unlink($this->fg->localPath($url));

    touch($this->fg->localPath($url));

    $this->assertFalse($this->fg->isAllowed($url));

    unlink($this->fg->localPath($url));
  }

  /**
   * @test
   */
  public function getWhitelistDomains()
  {
    $this->assertTrue(is_array($this->fg->getWhitelistDomains()));
  }

  /**
   * @test
   * @dataProvider isAllowedDomainProvider
   */
  public function isAllowedDomain($url, $expected)
  {
    $this->assertSame($expected, $this->fg->isAllowedDomain($url));
  }

  /**
   * Provides data for isAllowedDomain
   */
  public static function isAllowedDomainProvider()
  {
    return array(
      ['https://gustavus.edu/test.jpg', true],
      ['http://distilery10.s3.amazonaws.com/images/456.jpg', true],
      ['https://news.blog.gac.edu/files/image_42843.jpg', true],
      ['http://ip-23-48-54-39.ec2.amazonaws.com/images/456.jpg', false],
      ['http://not.a.valid.site.com/images/123.jpg', false],
    );
  }

  /**
   * @test
   */
  public function isAllowedMime()
  {

    @unlink($this->fg->localPath('test'));

    $this->assertFalse($this->fg->isAllowedMime('test'));

    file_put_contents($this->fg->localPath('test'), 'This is a test.');

    $this->assertTrue($this->fg->isAllowedMime('test'));

    unlink($this->fg->localPath('test'));

    touch($this->fg->localPath('test'));

    $this->assertFalse($this->fg->isAllowedMime('test'));

    unlink($this->fg->localPath('test'));
  }

  /**
   * @test
   */
  public function isGrabbed()
  {

    @unlink($this->fg->localPath('test'));

    $this->assertFalse($this->fg->isGrabbed('test'));

    touch($this->fg->localPath('test'));

    $this->assertTrue($this->fg->isGrabbed('test'));

    unlink($this->fg->localPath('test'));

  }

  /**
   * @test
   */
  public function isReadable()
  {
    $this->assertTrue($this->fg->isReadable('https://gustavus.edu/'));
    $this->assertTrue($this->fg->isReadable('http://gustavus.edu/'));
    $this->assertFalse($this->fg->isReadable('https://gustavus.edu/404'));
    $this->assertFalse($this->fg->isReadable('http://gustavus.edu/404'));
    $this->assertTrue($this->fg->isReadable('http://google.com/'));
    $this->assertFalse($this->fg->isReadable('http://google.com/404'));
  }

  /**
   * @test
   * @expectedException InvalidArgumentException
   */
  public function isReadableException()
  {
    $this->fg->isReadable('malformed_url');
  }

  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////

  /**
   * @test
   */
  public function localPath()
  {
    $this->assertStringStartsWith('/cis/www-etc/lib/Gustavus/Utility/FileGrabber/', $this->fg->localPath('https://gustavus.edu/'));
  }

  /**
   * @test
   */
  public function localURL()
  {
    $this->assertStringStartsWith('/filegrabber/', $this->fg->localURL('https://gustavus.edu/'));
  }

  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider enqueueProvider
   */
  public function enqueue($url)
  {
    $this->fg->enqueue($url);

    $this->assertEquals(array($url), $this->fg->getQueue());
  }

  /**
   * Provides data for enqueue
   */
  public static function enqueueProvider()
  {
    return array(
      ['https://gustavus.edu/image/232.jpg'],
      ['http://pbs.twimg.com/valid/url'],
      ['twitter.com/image/123.jpg']
    );
  }

  /**
   * @test
   */
  public function bulkEnqueue()
  {
    $expected = array(
      'https://gustavus.edu/image/232.jpg',
      'http://pbs.twimg.com/valid/url',
      'twitter.com/image/123.jpg'
    );

    $this->fg->bulkEnqueue($expected);

    $this->assertEquals($expected, $this->fg->getQueue());

    $this->fg->clearQueue();

    $this->assertEquals(array(), $this->fg->getQueue());
  }

  /**
   * @test
   */
  public function processQueue()
  {

    @unlink($this->fg->localPath('https://beta.gac.edu/test.mp4'));
    @unlink($this->fg->localPath('https://beta.gac.edu/test.jpg'));

    $this->fg->bulkEnqueue(array(
      'https://beta.gac.edu/test.jpg',
      'https://beta.gac.edu/test.mp4'
    ));

    $files = $this->fg->processQueue();

    $this->assertTrue(is_array($files));
    $this->assertEquals(1, count($files));
    $this->assertEquals('https://beta.gac.edu/test.mp4', $files[0]['url']);
    $this->assertInstanceOf('Exception', $files[0]['error']);

    $this->assertTrue($this->fg->isGrabbed('https://beta.gac.edu/test.jpg'));

    $this->assertEquals(array(), $this->fg->getQueue());

    $this->fg->enqueue('https://beta.gac.edu/test.jpg');

    $this->assertTrue($this->fg->processQueue());

    @unlink($this->fg->localPath('https://beta.gac.edu/test.mp4'));
    @unlink($this->fg->localPath('https://beta.gac.edu/test.jpg'));
  }

  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////

  /**
   * @test
   */
  public function fetchFileFromFileSystem()
  {
    file_put_contents($this->fg->localPath('test'), 'This is a test');

    $this->assertEquals('This is a test', $this->fg->fetchFileFromFileSystem('test'));

    unlink($this->fg->localPath('test'));
  }

  /**
   * @test
   */
  public function fetchFileFromServer()
  {
    $file = $this->fg->fetchFileFromServer('https://beta.gac.edu/test.jpg');

    $this->assertTrue(is_string($file));

    unlink($this->fg->localPath('https://beta.gac.edu/test.jpg'));
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function fetchFileFromServerThrowsException()
  {
    $this->fg->fetchFileFromServer('https://beta.gac.edu/bad.jpg');
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function fetchFileFromServerThrowsExceptionBadMime()
  {
    $this->fg->fetchFileFromServer('https://beta.gac.edu/test.mp4');
  }
}