<?php
/**
 * @package Utility
 * @subpackage Test
 * @author  Billy Visto
 * @author  Joe Lencioni
 */

namespace Gustavus\Utility\Test;
use Gustavus\Utility,
  Gustavus\Test\Test,
  Gustavus\Test\TestObject;

/**
 * @package Utility
 * @subpackage Test
 * @author  Billy Visto
 * @author  Joe Lencioni
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
    $this->assertSame($expected, $date->toRelativeClassName($now)->getValue());
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
   * @dataProvider timeOfDayData
   */
  public function isMorning($isMorning, $isAfternoon, $isEvening, $isNight, $value)
  {
    $this->dateTime->setValue($value);
    $this->assertSame($isMorning, $this->dateTime->isMorning());
  }

  /**
   * @test
   * @dataProvider timeOfDayData
   */
  public function isAfternoon($isMorning, $isAfternoon, $isEvening, $isNight, $value)
  {
    $this->dateTime->setValue($value);
    $this->assertSame($isAfternoon, $this->dateTime->isAfternoon());
  }

  /**
   * @test
   * @dataProvider timeOfDayData
   */
  public function isEvening($isMorning, $isAfternoon, $isEvening, $isNight, $value)
  {
    $this->dateTime->setValue($value);
    $this->assertSame($isEvening, $this->dateTime->isEvening());
  }

  /**
   * @test
   * @dataProvider timeOfDayData
   */
  public function isNight($isMorning, $isAfternoon, $isEvening, $isNight, $value)
  {
    $this->dateTime->setValue($value);
    $this->assertSame($isNight, $this->dateTime->isNight());
  }

  /**
   * @return array
   */
  public static function timeOfDayData()
  {
    return array(
      array(false, false, false, true, '12 am'),
      array(false, false, false, true, '1 am'),
      array(false, false, false, true, '2 am'),
      array(false, false, false, true, '3 am'),
      array(true, false, false, false, '4 am'),
      array(true, false, false, false, '5 am'),
      array(true, false, false, false, '6 am'),
      array(true, false, false, false, '7 am'),
      array(true, false, false, false, '8 am'),
      array(true, false, false, false, '9 am'),
      array(true, false, false, false, '10 am'),
      array(true, false, false, false, '11 am'),
      array(false, true, false, false, '12 pm'),
      array(false, true, false, false, '1 pm'),
      array(false, true, false, false, '2 pm'),
      array(false, true, false, false, '3 pm'),
      array(false, true, false, false, '4 pm'),
      array(false, true, false, false, '5 pm'),
      array(false, false, true, false, '6 pm'),
      array(false, false, true, false, '7 pm'),
      array(false, false, true, false, '8 pm'),
      array(false, false, true, false, '9 pm'),
      array(false, false, false, true, '10 pm'),
      array(false, false, false, true, '11 pm'),
    );
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
      //array('month', '-1 month', 'now'), // See note below.
      array('month', '-32 days', 'now'),
      array('months', '-65 days', 'now'),
      array('year', '-366 days', 'now'),
      array('years', '-2 years', 'now'),
    );

    // Relative months in PHP's date interval stuff only works properly when the previous month
    // has at least as many days as the current month (July, August, September).
    // However, when the previous month has fewer days, subtracting one month is not a full month
    // from the current month. For instance, September only has 30 days, while October has 31, which
    // causes a bug on October 31: Subtracting 1 month from October 31 only subtracts 30 days!
    // As a result, a test using negative months may fail on certain days of the year, but not
    // others. To avoid this, use -32 days instead of -1 month.
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
    $this->assertSame($expected, $date->toRelative($now, $beSpecific)->getValue());
  }

  /**
   * @return array
   */
  public function relativeDateData()
  {
    return array(
      array('Last month', '-1 months -3 weeks'),

      array('1 month, 3 weeks, and 2 days ago', '-1 months -3 weeks -2 days', '', true),

      array('1 year, 1 month, 3 weeks, and 3 days from now', '+1 years +1 months +3 weeks +3 days', null, true),
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
      array('Last month', '-32 days', 'now'),
      array('2 months ago', '-2 months', 'now'),
      array('Last year', '-12 months', 'now'),
      array('Around 2 years ago', '-2 years', 'now'),

      array('1 minute ago', time()-60, 'now'),
      array('Around 2 years ago', time()-(62899200 + 86400 * 3), 'now'),
      array('Next year', time()+62899200, 'now'),

      array('Last week', '-1 months -1 weeks', '-1 months'),

      array('1 month, 3 weeks, and 3 days ago', '-1 year -1 months -3 weeks -3 days', '-1 year', true),

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
    $this->assertInstanceOf('\DateTime', $this->dateTime->makeDateTime('1301213595'));
    $this->assertInstanceOf('\DateTime', $this->dateTime->makeDateTime(time()));
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
    $date     = new \DateTime('-2 hours -4 minutes');
    $now      = new \DateTime('now');

    $interval = $now->diff($date);
    $expected = array(
      'hour'   => 2,
      'minute' => 4,
    );
    $this->assertSame($expected, $this->dateTime->makeIntervalArray($interval));
  }

  /**
   * @test
   */
  public function makeIntervalArrayDaysAgo()
  {
    $date     = new \DateTime('-2 months -4 days');
    $now      = new \DateTime('now');

    $interval = $now->diff($date);
    $expected = array(
      'month' => 2,
      'day'   => 4,
    );
    $this->assertSame($expected, $this->dateTime->makeIntervalArray($interval));
  }

  /**
   * @test
   */
  public function makeIntervalArraySeconds()
  {
    $date     = new \DateTime('-40 seconds');
    $now      = new \DateTime('now');

    $interval = $now->diff($date);
    $expected = array(
      'second' => 40,
    );
    $this->assertSame($expected, $this->dateTime->makeIntervalArray($interval));
  }

  /**
   * @test
   * @dataProvider inDateRangeData
   */
  public function inDateRange($expected, $start, $end, $date)
  {
    $date = new Utility\DateTime($date);
    $this->assertSame($expected, $date->inDateRange($start, $end));
  }

  /**
   * @return array
   */
  public function inDateRangeData()
  {
    return array(
      array(false, 'June 1', 'August 15', 'May 31'),
      array(false, 'June 1', 'August 15', 'August 16'),
      array(true, 'June 1', 'August 15', 'June 1'),
      array(true, 'June 1', 'August 15', 'July 29'),
      array(true, 'June 1', 'August 15', 'August 15'),
      array(false, 'August 15', 'June 15', 'August 14'),
      array(true, 'August 15', 'June 15', 'August 16 -1 year'),
      array(false, 'October 22', 'February 1', 'September 22'),
      array(false, 'October 22 2012', 'February 1 2013', 'September 22 2012'),
      array(true, 'September 1 2012', 'February 1 2013', 'September 16 2012'),
      array(false, 'October 22', 'February 1', 'September 22'),
      array(false, 'January 1', 'February 1', 'September 22'),
      array(true, 'February 4 2012', 'February 1 2013', 'September 22 2012'),
      array(true, 'November 14 00:00:00', 'November 14 23:59:59', 'November 14'),
      array(true, new \DateTime('September 1 2012'), new \DateTime('February 1 2013'), 'November 14 2012'),
    );
  }

  /**
   * @test
   * @dataProvider adjustYearsIfNeededData
   */
  public function adjustYearsIfNeeded($expectedStart, $expectedEnd, $start, $end, $dateToTestAround)
  {
    // set up mock so whenever DateTime calls makeDateTime, we return the date we want to test around, instead of using the current date
    $dateMock = $this->getMock('\Gustavus\Utility\DateTime', array('makeDateTime'), [$dateToTestAround]);

    $dateMock->expects($this->any())
      ->method('makeDateTime')
      ->will($this->returnValue(new \DateTime($dateToTestAround)));

    $expectedStart  = new \DateTime($expectedStart);
    $expectedEnd    = new \DateTime($expectedEnd);
    $start          = new \DateTime($start);
    $end            = new \DateTime($end);

    $dateMock->adjustYearsIfNeeded($start, $end);

    $this->assertEquals($expectedStart, $start);
    $this->assertEquals($expectedEnd, $end);
  }

  /**
   * @return array
   */
  public function adjustYearsIfNeededData()
  {
    return array(
      array('September 1', 'February 1 +1 year', 'September 1', 'February 1', 'November 1'),
      array('August 15', 'June 15 +1 year', 'August 15', 'June 15', 'August 16'),
      //array('September 1', 'February 1 +1 year', 'September 1', 'February 1', 'November 1'),
      array('September 1 2012', 'February 1 2013', 'September 1 2012', 'February 1 2013', 'November 1 2012'),
      array('December 1 -1 year', 'November 1', 'December 1', 'November 1', 'October 22'),
    );
  }

  /**
   * @test
   */
  public function inDateRangeReference()
  {

    $firstDate = new \DateTime('September 1 2023');
    $expectedFirstDate = (new \DateTime('September 1 2022'))->format('U');
    $endDate = new \DateTime('February 1 2023');

    (new Utility\DateTime('January 1 2023'))->inDateRange($firstDate, $endDate);

    $this->assertSame($expectedFirstDate, $firstDate->format('U'));
  }
}
