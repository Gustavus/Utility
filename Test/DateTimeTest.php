<?php
/**
 * @package Utility
 * @subpackage Test
 */

namespace Gustavus\Utility\Test;
use Gustavus\Utility,
  Gustavus\Test\Test,
  Gustavus\Test\TestObject;

/**
 * @package Utility
 * @subpackage Test
 */
class DateTimeTest extends Test
{
  /**
   * @var Utility\DateTime
   */
  private $dateTime;

  /**
   * sets up the object for each test
   * @return void
   */
  public function setUp()
  {
    $this->dateTime = new TestObject(new Utility\DateTime('now'));
  }

  /**
   * destructs the object after each test
   * @return void
   */
  public function tearDown()
  {
    unset($this->dateTime);
  }

  /**
   * @test
   * @dataProvider relativeClassNameData
   */
  public function relativeClassName($expected, $date, $now = null)
  {
    $date = new Utility\DateTime($date);
    $this->assertSame($expected, $date->relativeClassName($now));
  }

  /**
   * @test
   */
  public function toString()
  {
    $now = time();
    $dateTime = new Utility\DateTime($now);

    $date = new \DateTime('@'.$now);
    $this->expectOutputString($date->format('c'));
    echo $dateTime;
  }

  /**
   * @return array
   */
  public function relativeClassNameData()
  {
    return array(
      array('minute', '-40 seconds', 'now'),
      array('now', 'now', 'now'),
      array('now', '-2 seconds', '-1 seconds'),
      array('now', '-70 seconds', '-60 seconds'),
      array('now', '-2 seconds', 'now'),

      array('minute', '-40 seconds', 'now'),
      array('minute', '-60 seconds', 'now'),
      array('minutes', '-2 minutes', 'now'),
      array('now', 'now', 'now'),
      array('now', '-5 seconds', 'now'),
      array('minute', '-11 seconds', 'now'),
      array('minute', '-61 seconds', 'now'),
      array('minutes', '-120 seconds', 'now'),
      array('hour', '-3600 seconds', 'now'),
      array('hours', '-7200 seconds', 'now'),
      array('day', '-86400 seconds', 'now'),
      array('days', '-172800 seconds', 'now'),
      array('week', '-604800 seconds', 'now'),
      array('weeks', '-1209600 seconds', 'now'),
      array('month', '-1 month', 'now'),
      array('months', '-61 days', 'now'),
      array('year', '-366 days', 'now'),
      array('years', '-2 years', 'now'),
    );
  }

  /**
   * @test
   */
  public function makeNonSpecificRelativeDate()
  {
    $this->assertSame('Just Now', $this->dateTime->makeNonSpecificRelativeDate(array('second' => 4)));
    $this->assertSame(array(), $this->dateTime->makeNonSpecificRelativeDate(array()));
    $this->assertSame('A few seconds ago', $this->dateTime->makeNonSpecificRelativeDate(array('second' => 11)));
    $this->assertSame(array('relative' => '1 minute '), $this->dateTime->makeNonSpecificRelativeDate(array('minute' => 1)));
    $this->assertSame(array('relative' => '10 minutes '), $this->dateTime->makeNonSpecificRelativeDate(array('minute' => 10)));
    $this->assertSame(array('relative' => '1 hour '), $this->dateTime->makeNonSpecificRelativeDate(array('hour' => 1)));
    $this->assertSame(array('relative' => '2 hours '), $this->dateTime->makeNonSpecificRelativeDate(array('hour' => 2)));
    $this->assertSame('Yesterday', $this->dateTime->makeNonSpecificRelativeDate(array('day' => 1), 1));
    $this->assertSame('Tomorrow', $this->dateTime->makeNonSpecificRelativeDate(array('day' => 1), -1));
    $this->assertSame(array('relative' => '2 days '), $this->dateTime->makeNonSpecificRelativeDate(array('day' => 2)));
    $this->assertSame('Last week', $this->dateTime->makeNonSpecificRelativeDate(array('week' => 1)));
    $this->assertSame(array('relative' => '2 weeks '), $this->dateTime->makeNonSpecificRelativeDate(array('week' => 2)));
    $this->assertSame('Last month', $this->dateTime->makeNonSpecificRelativeDate(array('month' => 1)));
    $this->assertSame(array('relative' => '2 months '), $this->dateTime->makeNonSpecificRelativeDate(array('month' => 2)));
    $this->assertSame('Last year', $this->dateTime->makeNonSpecificRelativeDate(array('year' => 1)));
    $this->assertSame(array('startText' => 'Around ', 'relative' => '2 years '), $this->dateTime->makeNonSpecificRelativeDate(array('year' => 2)));
  }

  /**
   * @test
   * @dataProvider relativeDateData
   */
  public function relativeDatesFromArray($expected, $date, $now = null, $beSpecific = false)
  {
    $date = new Utility\DateTime($date);
    $this->assertSame($expected, $date->relative($now, $beSpecific));
  }

  /**
   * @return array
   */
  public function relativeDateData()
  {
    return array(
      array('Last month', '-1 months -3 weeks'),

      array('1 month, 3 weeks, and 2 days ago', '-1 months -3 weeks', null, true),

      array('1 year, 1 month, 3 weeks, and 2 days from now', '+1 years +1 months +3 weeks +3 days', null, true),
      array('1 minute ago', '-1 minutes'),

      array('Just Now', '', 'now'),
      array('Just Now', '-5 seconds', 'now'),
      array('A few seconds ago', '-11 seconds', 'now'),
      array('1 minute ago', '-61 seconds', 'now'),
      array('2 minutes ago', '-120 seconds', 'now'),
      array('1 hour ago', '-3600 seconds', 'now'),
      array('2 hours ago', '-7200 seconds', 'now'),
      array('Yesterday', '-86400 seconds', 'now'),
      array('2 days ago', '-172800 seconds', 'now'),
      array('Last week', '-604800 seconds', 'now'),
      array('2 weeks ago', '-1209600 seconds', 'now'),
      array('Last month', '-1 months', 'now'),
      array('2 months ago', '-2 months', 'now'),
      array('Last year', '-12 months', 'now'),
      array('Around 2 years ago', '-2 years', 'now'),

      array('1 minute ago', time()-60, 'now'),
      array('Around 2 years ago', time()-(62899200 + 86400 * 3), 'now'),
      array('Next year', time()+62899200, 'now'),

      array('Last week', '-1 months -1 weeks', '-1 months'),

      array('1 month, 3 weeks, and 3 days ago', '-1 year -1 months -3 weeks', '-1 year', true),

      array('Just Now', 'now', 'now'),
    );
  }

  /**
   * @test
   */
  public function makeDateTime()
  {
    $date = '-2 minutes';
    $this->assertInstanceOf('\DateTime', $this->dateTime->makeDateTime($date));
    $this->assertInstanceOf('\DateTime', $this->dateTime->makeDateTime(time() - 120));
    $this->assertInstanceOf('\DateTime', $this->dateTime->makeDateTime());
  }

  /**
   * @test
   */
  public function makeDateTimeException()
  {
    try {
      $date = $this->dateTime->makeDateTime('abs');
    } catch (\Exception $e) {
      return;
    }
    $this->fail('An expected exception has not been raised');
  }

  /**
   * @test
   */
  public function makeIntervalArray()
  {
    $date        = new \DateTime('-2 hours -4 minutes');
    $now         = new \DateTime('now');

    $interval    = $date->diff($now);
    $expected = array(
      'hour' => 2,
      'minute' => 4,
    );
    $this->assertSame($expected, $this->dateTime->makeIntervalArray($interval));
  }

  /**
   * @test
   */
  public function makeIntervalArrayDaysAgo()
  {
    $date        = new \DateTime('-2 months -4 days');
    $now         = new \DateTime('now');

    $interval    = $date->diff($now);
    $expected = array(
      'month' => 2,
      'day' => 4,
    );
    $this->assertSame($expected, $this->dateTime->makeIntervalArray($interval));
  }

  /**
   * @test
   */
  public function makeIntervalArraySeconds()
  {
    $date        = new \DateTime('-40 seconds');
    $now         = new \DateTime('now');
    //var_dump(gettype($now));

    $interval    = $date->diff($now);
    $expected = array(
      'second' => 40,
    );
    $this->assertSame($expected, $this->dateTime->makeIntervalArray($interval));
  }
}