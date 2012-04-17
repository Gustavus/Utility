<?php
/**
 * @package Utility
 * @subpackage Test
 */

namespace Gustavus\Utility\Test;
use Gustavus\Utility;

/**
 * @package Utility
 * @subpackage Test
 */
class DateTimeTest extends \Gustavus\Test\Test
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
    $this->dateTime = new \Gustavus\Test\TestObject(new Utility\DateTime());
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
   */
  public function getReturnClassName()
  {
    $this->assertSame('now', $this->dateTime->getReturnClassName(array('second' => 4)));
    $this->assertSame('now', $this->dateTime->getReturnClassName(array()));
    $this->assertSame('minute', $this->dateTime->getReturnClassName(array('second' => 11)));
    $this->assertSame('minute', $this->dateTime->getReturnClassName(array('minute' => 1)));
    $this->assertSame('minutes', $this->dateTime->getReturnClassName(array('minute' => 10)));
    $this->assertSame('hour', $this->dateTime->getReturnClassName(array('hour' => 1)));
    $this->assertSame('hours', $this->dateTime->getReturnClassName(array('hour' => 2)));
    $this->assertSame('day', $this->dateTime->getReturnClassName(array('day' => 1)));
    $this->assertSame('days', $this->dateTime->getReturnClassName(array('day' => 2)));
    $this->assertSame('week', $this->dateTime->getReturnClassName(array('week' => 1)));
    $this->assertSame('weeks', $this->dateTime->getReturnClassName(array('week' => 2)));
    $this->assertSame('month', $this->dateTime->getReturnClassName(array('month' => 1)));
    $this->assertSame('months', $this->dateTime->getReturnClassName(array('month' => 2)));
    $this->assertSame('year', $this->dateTime->getReturnClassName(array('year' => 1)));
    $this->assertSame('years', $this->dateTime->getReturnClassName(array('year' => 2)));
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

  public function testRelativeDatesFromArray(array $array = array())
  {
    foreach ($array as $key => $value) {
      $returnClassName = (isset($value[1])) ? $value[1] : false;
      $beSpecific      = (isset($value[2])) ? $value[2] : false;
      $this->dateTime->setConstructorParam($value[0]);
      $this->assertSame($key, $this->dateTime->relative($returnClassName, $beSpecific));
    }
  }

  /**
   * @test
   */
  public function makeRelativeDate()
  {
    $testArray = array(
      'Last month' => array(new \DateTime('-1 months -3 weeks')),

      '1 month, 3 weeks, and 2 days ago' => array(new \DateTime('-1 months -3 weeks'), false, true),

      '1 year, 1 month, 3 weeks, and 2 days from now' => array(new \DateTime('+1 years +1 months +3 weeks +3 days'), false, true),
      '1 minute ago' => array(new \DateTime('-1 minutes')),

      'now' => array(new \DateTime('-2 seconds'), true, false),
      'minute' => array(new \DateTime('-59 seconds'), true, false),
      'minute' => array(new \DateTime('-60 seconds'), true, false),
      'minutes' => array(new \DateTime('-2 minutes'), true, false),

      'Just Now' => array(new \DateTime()),
      'Just Now' => array(new \DateTime('-5 seconds')),
      'A few seconds ago' => array(new \DateTime('-11 seconds')),
      '1 minute ago' => array(new \DateTime('-61 seconds')),
      '2 minutes ago' => array(new \DateTime('-120 seconds')),
      '1 hour ago' => array(new \DateTime('-3600 seconds')),
      '2 hours ago' => array(new \DateTime('-7200 seconds')),
      'Yesterday' => array(new \DateTime('-86400 seconds')),
      '2 days ago' => array(new \DateTime('-172800 seconds')),
      'Last week' => array(new \DateTime('-604800 seconds')),
      '2 weeks ago' => array(new \DateTime('-1209600 seconds')),
      'Last month' => array(new \DateTime('-1 months')),
      '2 months ago' => array(new \DateTime('-2 months')),
      'Last year' => array(new \DateTime('-12 months')),
      'Around 2 years ago' => array(new \DateTime('-2 years')),

      'now' => array(new \DateTime(), true, false),
      'now' => array(new \DateTime('-5 seconds'), true, false),
      'minute' => array(new \DateTime('-11 seconds'), true, false),
      'minute' => array(new \DateTime('-61 seconds'), true, false),
      'minutes' => array(new \DateTime('-120 seconds'), true, false),
      'hour' => array(new \DateTime('-3600 seconds'), true, false),
      'hours' => array(new \DateTime('-7200 seconds'), true, false),
      'day' => array(new \DateTime('-86400 seconds'), true, false),
      'days' => array(new \DateTime('-172800 seconds'), true, false),
      'week' => array(new \DateTime('-604800 seconds'), true, false),
      'weeks' => array(new \DateTime('-1209600 seconds'), true, false),
      'month' => array(new \DateTime('-1 month'), true, false),
      'months' => array(new \DateTime('-61 days'), true, false),
      'year' => array(new \DateTime('-366 days'), true, false),
      'years' => array(new \DateTime('-2 years'), true, false),

      '1 minute ago' => array(time()-60),
      'Around 2 years ago' => array(time()-(62899200 + 86400 * 3)),
      'Next year' => array(time()+62899200),
    );
  }
}