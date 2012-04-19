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

    $this->expectOutputString((string) $now);
    echo $dateTime;
  }

  /**
   * @test
   */
  public function relativeClassNameMinute()
  {
    $testArray = array(
      'minute' => array(new \DateTime('-40 seconds'), new \DateTime('now')),
    );
    $this->testRelativeClassNamesFromArray($testArray);
  }

  /**
   * @test
   */
  public function relativeClassNameEmptyInterval()
  {
    $testArray = array(
      'now' => array(new \DateTime('now'), new \DateTime('now')),
    );
    $this->testRelativeClassNamesFromArray($testArray);
  }

  /**
   * @test
   */
  public function relativeClassName()
  {
    $testArray = array(
      'now' => array(new \DateTime('-2 seconds'), new \DateTime('now')),
      'minute' => array(new \DateTime('-40 seconds'), new \DateTime('now')),
      'minute' => array(new \DateTime('-60 seconds'), new \DateTime('now')),
      'minutes' => array(new \DateTime('-2 minutes'), new \DateTime('now')),
      'now' => array(new \DateTime(), new \DateTime('now')),
      'now' => array(new \DateTime('-5 seconds'), new \DateTime('now')),
      'minute' => array(new \DateTime('-11 seconds'), new \DateTime('now')),
      'minute' => array(new \DateTime('-61 seconds'), new \DateTime('now')),
      'minutes' => array(new \DateTime('-120 seconds'), new \DateTime('now')),
      'hour' => array(new \DateTime('-3600 seconds'), new \DateTime('now')),
      'hours' => array(new \DateTime('-7200 seconds'), new \DateTime('now')),
      'day' => array(new \DateTime('-86400 seconds'), new \DateTime('now')),
      'days' => array(new \DateTime('-172800 seconds'), new \DateTime('now')),
      'week' => array(new \DateTime('-604800 seconds'), new \DateTime('now')),
      'weeks' => array(new \DateTime('-1209600 seconds'), new \DateTime('now')),
      'month' => array(new \DateTime('-1 month'), new \DateTime('now')),
      'months' => array(new \DateTime('-61 days'), new \DateTime('now')),
      'year' => array(new \DateTime('-366 days'), new \DateTime('now')),
      'years' => array(new \DateTime('-2 years'), new \DateTime('now')),
    );
    $this->testRelativeClassNamesFromArray($testArray);
  }

  /**
   * @test
   */
  public function relativeClassNameNowSpecified()
  {
    $testArray = array(
      'now' => array(new \DateTime('-2 seconds'), new \DateTime('-1 seconds')),
      'now' => array(new \DateTime('-70 seconds'), new \DateTime('-60 seconds')),
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
      'Last month' => array(new \DateTime('-1 months -3 weeks')),

      '1 month, 3 weeks, and 2 days ago' => array(new \DateTime('-1 months -3 weeks'), null, true),

      '1 year, 1 month, 3 weeks, and 2 days from now' => array(new \DateTime('+1 years +1 months +3 weeks +3 days'), null, true),
      '1 minute ago' => array(new \DateTime('-1 minutes')),

      'Just Now' => array(new \DateTime(), new \DateTime('now')),
      'Just Now' => array(new \DateTime('-5 seconds'), new \DateTime('now')),
      'A few seconds ago' => array(new \DateTime('-11 seconds'), new \DateTime('now')),
      '1 minute ago' => array(new \DateTime('-61 seconds'), new \DateTime('now')),
      '2 minutes ago' => array(new \DateTime('-120 seconds'), new \DateTime('now')),
      '1 hour ago' => array(new \DateTime('-3600 seconds'), new \DateTime('now')),
      '2 hours ago' => array(new \DateTime('-7200 seconds'), new \DateTime('now')),
      'Yesterday' => array(new \DateTime('-86400 seconds'), new \DateTime('now')),
      '2 days ago' => array(new \DateTime('-172800 seconds'), new \DateTime('now')),
      'Last week' => array(new \DateTime('-604800 seconds'), new \DateTime('now')),
      '2 weeks ago' => array(new \DateTime('-1209600 seconds'), new \DateTime('now')),
      'Last month' => array(new \DateTime('-1 months'), new \DateTime('now')),
      '2 months ago' => array(new \DateTime('-2 months'), new \DateTime('now')),
      'Last year' => array(new \DateTime('-12 months'), new \DateTime('now')),
      'Around 2 years ago' => array(new \DateTime('-2 years'), new \DateTime('now')),

      '1 minute ago' => array(time()-60, new \DateTime('now')),
      'Around 2 years ago' => array(time()-(62899200 + 86400 * 3), new \DateTime('now')),
      'Next year' => array(time()+62899200, new \DateTime('now')),
    );
    $this->testRelativeDatesFromArray($testArray);
  }

  /**
   * @test
   */
  public function makeRelativeDateNowSpecified()
  {
    $testArray = array(
      'Last week' => array(new \DateTime('-1 months -1 weeks'), new \DateTime('-1 months')),

      '1 month, 3 weeks, and 3 days ago' => array(new \DateTime('-1 year -1 months -3 weeks'), new \DateTime('-1 year'), true),
    );
    $this->testRelativeDatesFromArray($testArray);
  }

  /**
   * @test
   */
  public function makeRelativeDateSameDates()
  {
    $testArray = array(
      'Just Now' => array(new \DateTime('now'), new \DateTime('now')),
    );
    $this->testRelativeDatesFromArray($testArray);
  }

  /**
   * @test
   */
  public function makeDateTime()
  {
    $date = new \DateTime('-2 minutes');
    $this->assertSame($date, $this->dateTime->makeDateTime($date));
    $this->assertInstanceOf('\DateTime', $this->dateTime->makeDateTime(time() - 120));
    $this->assertInstanceOf('\DateTime', $this->dateTime->makeDateTime());
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

    $interval    = $date->diff($now);
    $expected = array(
      'second' => 40,
    );
    $this->assertSame($expected, $this->dateTime->makeIntervalArray($interval));
  }
}