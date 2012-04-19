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
    $this->dateTime = new TestObject(new Utility\DateTime());
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
   * Takes an array and runs relativeClassName on each item
   * @param  array  $array
   */
  public function testRelativeClassNamesFromArray(array $testArray = array())
  {
    foreach ($testArray as $key => $value) {
      $this->dateTime->setValue($value[0]);
      $now = (isset($value[1])) ? $value[1] : null;
      $this->assertSame($key, $this->dateTime->relativeClassName($now));
    }
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
   * @test
   */
  public function relativeClassNameMinute()
  {
    $testArray = array(
      'minute' => array('-40 seconds', 'now'),
    );
    $this->testRelativeClassNamesFromArray($testArray);
  }

  /**
   * @test
   */
  public function relativeClassNameEmptyInterval()
  {
    $testArray = array(
      'now' => array('now', 'now'),
    );
    $this->testRelativeClassNamesFromArray($testArray);
  }

  /**
   *
   */
  public function relativeClassName()
  {
    $testArray = array(
      'now' => array('-2 seconds', 'now'),
      'minute' => array('-40 seconds', 'now'),
      'minute' => array('-60 seconds', 'now'),
      'minutes' => array('-2 minutes', 'now'),
      'now' => array('now', 'now'),
      'now' => array('-5 seconds', 'now'),
      'minute' => array('-11 seconds', 'now'),
      'minute' => array('-61 seconds', 'now'),
      'minutes' => array('-120 seconds', 'now'),
      'hour' => array('-3600 seconds', 'now'),
      'hours' => array('-7200 seconds', 'now'),
      'day' => array('-86400 seconds', 'now'),
      'days' => array('-172800 seconds', 'now'),
      'week' => array('-604800 seconds', 'now'),
      'weeks' => array('-1209600 seconds', 'now'),
      'month' => array('-1 month', 'now'),
      'months' => array('-61 days', 'now'),
      'year' => array('-366 days', 'now'),
      'years' => array('-2 years', 'now'),
    );
    $this->testRelativeClassNamesFromArray($testArray);
  }

  /**
   * @test
   */
  public function relativeClassNameNowSpecified()
  {
    $testArray = array(
      'now' => array('-2 seconds', '-1 seconds'),
      'now' => array('-70 seconds', '-60 seconds'),
    );
    $this->testRelativeClassNamesFromArray($testArray);
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
   * Takes an array and runs relative on each item
   * @param  array  $array
   * @return void
   */
  public function testRelativeDatesFromArray(array $array = array())
  {
    foreach ($array as $key => $value) {
      $now             = (isset($value[1])) ? $value[1] : null;
      $beSpecific      = (isset($value[2])) ? $value[2] : false;
      $this->dateTime->setValue($value[0]);
      $this->assertSame($key, $this->dateTime->relative($now, $beSpecific));
    }
  }

  /**
   * @test
   */
  public function makeRelativeDate()
  {
    $testArray = array(
      'Last month' => array('-1 months -3 weeks'),

      '1 month, 3 weeks, and 2 days ago' => array('-1 months -3 weeks', null, true),

      '1 year, 1 month, 3 weeks, and 2 days from now' => array('+1 years +1 months +3 weeks +3 days', null, true),
      '1 minute ago' => array('-1 minutes'),

      'Just Now' => array(null, 'now'),
      'Just Now' => array('-5 seconds', 'now'),
      'A few seconds ago' => array('-11 seconds', 'now'),
      '1 minute ago' => array('-61 seconds', 'now'),
      '2 minutes ago' => array('-120 seconds', 'now'),
      '1 hour ago' => array('-3600 seconds', 'now'),
      '2 hours ago' => array('-7200 seconds', 'now'),
      'Yesterday' => array('-86400 seconds', 'now'),
      '2 days ago' => array('-172800 seconds', 'now'),
      'Last week' => array('-604800 seconds', 'now'),
      '2 weeks ago' => array('-1209600 seconds', 'now'),
      'Last month' => array('-1 months', 'now'),
      '2 months ago' => array('-2 months', 'now'),
      'Last year' => array('-12 months', 'now'),
      'Around 2 years ago' => array('-2 years', 'now'),

      '1 minute ago' => array(time()-60, 'now'),
      'Around 2 years ago' => array(time()-(62899200 + 86400 * 3), 'now'),
      'Next year' => array(time()+62899200, 'now'),
    );
    $this->testRelativeDatesFromArray($testArray);
  }

  /**
   * @test
   */
  public function makeRelativeDateNowSpecified()
  {
    $testArray = array(
      'Last week' => array('-1 months -1 weeks', '-1 months'),

      '1 month, 3 weeks, and 3 days ago' => array('-1 year -1 months -3 weeks', '-1 year', true),
    );
    $this->testRelativeDatesFromArray($testArray);
  }

  /**
   * @test
   */
  public function makeRelativeDateSameDates()
  {
    $testArray = array(
      'Just Now' => array('now', 'now'),
    );
    $this->testRelativeDatesFromArray($testArray);
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