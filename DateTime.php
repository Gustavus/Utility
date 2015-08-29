<?php
/**
 * @package Utility
 * @author  Billy Visto
 * @author  Joe Lencioni
 * @author  Justin Holcomb
 */
namespace Gustavus\Utility;

use DateTime as PHPDateTime,
  DateInterval,
  DateTimeZone;

/**
 * Object for working with DateTimes
 * <p/>
 * <i><strong>Warning:</strong>
 *  This class makes use of the PHP DateInterval object. When working with negative intervals (such
 *  as 61 days into the past), months are not mapped to calendar months, but instead use a constant
 *  31-day month. As a result, when specifying a durations, the number of actual months may not
 *  match up with the number of months in the interval.
 *
 * @package Utility
 * @author  Billy Visto
 * @author  Joe Lencioni
 * @author  Justin Holcomb
 */
class DateTime extends Base
{
  // Unicode characters to use in spans (instead of HTML entities)
  const NDASH_CHARACTER = '&ndash;';
  const NON_BREAKING_SPACE_CHARACTER = '&nbsp;';

  /**
   * Function to overide abstract function in base to make sure the value is valid
   *
   * @param  mixed $value value passed into setValue()
   * @return boolean
   */
  final protected function valueIsValid($value)
  {
    // DateTime will throw an exception if the value isn't supported. No need to do our own checks.
    return true;
  }

  /**
   * Calls parent setValue with value converted to a DateTime object
   *
   * @param string|integer $value
   * @return DateTime
   */
  public function setValue($value)
  {
    return parent::setValue($this->makeDateTime($value));
  }

  /**
   * Magical function to return the constructor param if the object is echoed
   *
   * @return mixed
   */
  public function __toString()
  {
    return $this->value->format('c');
  }

  /**
   * Make DateTime object
   *
   * @param  integer|string|DateTime $date
   * @return DateTime
   */
  protected function makeDateTime($date = null)
  {
    if ($date instanceof PHPDateTime) {
      return $date;
    } else if (is_numeric($date)) {
      // $date is a timestamp. We want it as a DateTime object
      $date = new PHPDateTime('@'.$date);
      // timestamps are in the UTC timezone. We want them to be converted to our timezone.
      $date->setTimezone(new DateTimeZone(ini_get('date.timezone')));
    } else if ($date === null) {
      // set date to be now
      $date = new PHPDateTime('now');
    } else {
      $date = new PHPDateTime($date);
    }
    return $date;
  }

  /**
   * @return boolean
   */
  public function isMorning()
  {
    $hour = (integer) $this->value->format('G');
    // 4-11 am
    return $hour >= 4 && $hour <= 11;
  }

  /**
   * @return boolean
   */
  public function isAfternoon()
  {
    $hour = (integer) $this->value->format('G');
    // 12-5 pm
    return $hour >= 12 && $hour <= 17;
  }

  /**
   * @return boolean
   */
  public function isEvening()
  {
    $hour = (integer) $this->value->format('G');
    // 6-9 pm
    return $hour >= 18 && $hour <= 21;
  }

  /**
   * @return boolean
   */
  public function isNight()
  {
    $hour = (integer) $this->value->format('G');
    // 10 pm to 3 am
    return $hour >= 22 || $hour <= 3;
  }

  /**
   * Determines if $this->value and $endTime are intended to represent the notion of "all day"
   *
   * @param mixed $endTime End time
   * @return boolean
   */
  public function isAllDay($endTime)
  {
    return ($this->value->format('H:i') == '00:00' && $this->makeDateTime($endTime)->format('H:i') == '00:00');
  }

  /**
   * Determines if $this->value and the $endTime spans multiple days
   *
   * @param mixed $endTime End time
   * @return boolean
   */
  public function isMultipleDays($endTime)
  {
    return ($this->value->format('F j, Y') != $this->makeDateTime($endTime)->format('F j, Y'));
  }

  /**
   * Figures out the class name based off of the DateInterval
   *
   * @param integer|string $now time to get relative time against
   * @return String
   */
  public function toRelativeClassName($now = null)
  {
    $date        = $this->value;
    $now         = $this->makeDateTime($now);
    $interval    = $date->diff($now);
    $intervalArr = $this->makeIntervalArray($interval);

    // first key will be the greatest time measurement that isn't empty
    $firstKey = key($intervalArr);
    // return a single class name
    if (!empty($firstKey)) {
      if ($firstKey === 'second') {
        if ($intervalArr['second'] > 10) {
          return new String('minute');
        } else {
          return new String('now');
        }
      } else if ($intervalArr[$firstKey] > 1) {
        return new String("{$firstKey}s");
      } else {
        return new String($firstKey);
      }
    } else {
      return new String('now');
    }
  }

  /**
   * Make non specific relative date. Either makes a string or an array of data
   *
   * @param  array  $array  Typically output from makeIntervalArray()
   * @param  integer $totalDays
   * @return mixed either a string, or an array
   */
  private function makeNonSpecificRelativeDate(array $array, $totalDays = 0)
  {
    // first key will be the greatest time measurement that isn't empty
    $firstKey = key($array);
    $return = array();

    if (!empty($firstKey)) {
      switch ($firstKey) {
        case 'day':
          if ($array['day'] === 1) {
            return ($totalDays < 0) ? 'Tomorrow': 'Yesterday';
          }
            break;
        case 'second':
          if ($array['second'] > 10) {
            return 'A few seconds ago';
          } else {
            return 'Just Now';
          }
            break;
        case 'year':
          if ($array['year'] > 1) {
            $return['startText'] = 'Around ';
          }
            break;
      }
      if ($array[$firstKey] === 1 && !in_array($firstKey, array('hour', 'minute', 'second'))) {
        return ($totalDays < 0) ? 'Next ' . $firstKey : 'Last ' . $firstKey;
      }
      $numberUtil = new Number($array[$firstKey]);
      $return['relative'] = $numberUtil->toQuantity("%s $firstKey ", "%s {$firstKey}s ")->getValue();
    }
    return $return;
  }

  /**
   * Makes an array out of the DateInterval with the singular label of the measurement as the key, and the amount for that measurement as the value
   *
   * @param  DateInterval $interval
   * @param  integer  $totalDays total number of days in the DateInterval
   * @return array
   */
  private function makeIntervalArray(DateInterval $interval, $totalDays = null)
  {
    $days = $interval->d;
    if ($totalDays === null) {
      // set up total days if not specified
      $totalDays   = ($interval->invert === 0) ? $interval->days : 0 - $interval->days;
    }
    if ($totalDays > 1 || $totalDays < -1) {
      $intervalArr = array_filter(array(
          'year'  => $interval->y,
          'month' => $interval->m,
          'week'  => (int) floor($days / 7),
          'day'   => $days % 7,
          )
      );
    } else {
      $intervalArr = array_filter(array(
          'day'    => $days,
          'hour'   => $interval->h,
          'minute' => $interval->i,
          'second' => $interval->s,
          )
      );
    }
    return $intervalArr;
  }

  /**
   * Outputs a sentence of how long ago this revision was made.
   * ie. 2 years ago, 3 months ago, 5 days ago, 1 day ago, 3 hours ago, 4 minutes ago, and 23 seconds ago.
   *
   * @param integer|string $now Time to get relative time against
   * @param boolean $beSpecific whether to output the greatest time measurement, or to be as specific as possible
   * @return String
   */
  public function toRelative($now = null, $beSpecific = false)
  {
    $date        = $this->value;
    $now         = $this->makeDateTime($now);

    $interval    = $now->diff($date);
    $totalDays   = ($interval->invert === 1) ? $interval->days : 0 - $interval->days;
    $relative    = array();
    $startText   = '';
    $intervalArr = $this->makeIntervalArray($interval, $totalDays);

    if (!$beSpecific) {
      $nonSpecificDate = $this->makeNonSpecificRelativeDate($intervalArr, $totalDays);
      if (is_array($nonSpecificDate)) {
        if (!empty($nonSpecificDate['startText'])) {
          $startText = $nonSpecificDate['startText'];
        }
        if (!empty($nonSpecificDate['relative'])) {
          $relative[] = $nonSpecificDate['relative'];
        }
      } else {
        return new String($nonSpecificDate);
      }
    } else {
      // make specific date array
      $numberUtil = new Number(0);
      foreach ($intervalArr as $key => $value) {
        $numberUtil->setValue($value);
        $relative[] = $numberUtil->toQuantity("%s $key", "%s {$key}s")->getValue();
      }
    }

    if (empty($relative)) {
      // modified less than a second ago, output just now
      return new String('Just Now');
    }

    $setUtil = new Set($relative);
    return new String(sprintf(
        '%s%s %s',
        $startText,
        $setUtil->toSentence(),
        ($interval->format('%r') === '-') ? 'ago' : 'from now'
    ));
  }

  /**
   * Adjust years if endDate is before the firstDate.
   * We either need to add a year to the endDate, or subtract a year from the firstDate.
   *
   * @param  \DateTime &$firstDate
   * @param  \DateTime &$endDate
   * @return void
   */
  public function adjustYearsIfNeeded(\DateTime &$firstDate, \DateTime &$endDate)
  {
    $endDateTime = $endDate->format('U');
    if ($firstDate->format('U') > $endDateTime) {
      // endDateTime should be after first date time
      // we need to adjust years
      $now  = $this->makeDateTime('now')->format('U');

      if ($now > $endDateTime) {
        // add a year to the endDate
        $endDate->modify('+1 year');
      } else {
        // subtract a year from the firstDate
        $firstDate->modify('-1 year');
      }
    }
  }

  /**
   * Checks to see if $this->value is in the date range of firstDate and endDate
   * Requires firstDate to be before endDate
   *
   * @param  integer|string|\DateTime $firstDate
   * @param  integer|string|\DateTime $endDate
   * @return boolean
   */
  public function inDateRange($firstDate, $endDate)
  {
    if (!($firstDate instanceOf \DateTime)) {
      $firstDate = $this->makeDateTime($firstDate);
    }
    if (!($endDate instanceOf \DateTime)) {
      $endDate   = $this->makeDateTime($endDate);
    }

    // make sure firstDate is before endDate. If not, we may need to adjust years
    $this->adjustYearsIfNeeded($firstDate, $endDate);
    $firstDate = $firstDate->format('U');
    $endDate   = $endDate->format('U');
    $date      = $this->value->format('U');
    return ($firstDate <= $date && $date <= $endDate);
  }


  /**
   * Chooses a value from the four input values based on the relative time-of-day represented by
   * this DateTime object.
   *
   * @param string $morningValue
   *  The value to return if this DateTime represents a time in the morning (4-12am).
   *
   * @param string $afternoonValue
   *  The value to return if this DateTime represents a time in the afternoon (12-6pm).
   *
   * @param string $eveningValue
   *  The value to return if this DateTime represents a time in the evening (6-10pm).
   *
   * @param string $nightValue
   *  The value to return if this DateTime represents a time in the night (10pm-4am).
   *
   * @return String
   *  The value chosen based on the relative time-of-day.
   */
  public function chooseByTimeOfDay($morningValue = null, $afternoonValue = null, $eveningValue = null, $nightValue = null)
  {
    $result = null;

    if ($this->isMorning()) {
      $result = $morningValue;
    } else if ($this->isAfternoon()) {
      $result = $afternoonValue;
    } else if ($this->isEvening()) {
      $result = $eveningValue;
    } else {
      $result = $nightValue;
    }

    return new String($result);
  }

  /**
   * Chooses a greeting based on the relative time-of-day represented by this DateTime instance.
   *
   * @param string $morningGreeting
   *  <em>Optional</em>
   *  The value to return if this DateTime represents a time in the morning (4-12am). Defaults to
   *  "Good morning".
   *
   * @param string $afternoonGreeting
   *  <em>Optional</em>
   *  The value to return if this DateTime represents a time in the afternoon (12-6pm). Defaults to
   *  "Good afternoon".
   *
   * @param string $eveningGreeting
   *  <em>Optional</em>
   *  The value to return if this DateTime represents a time in the evening (6-10pm). Defaults to
   *  "Good evening".
   *
   * @param string $nightGreeting
   *  <em>Optional</em>
   *  The value to return if this DateTime represents a time in the night (10pm-4am). Defaults to
   *  "Good night".
   *
   * @return String
   *  The greeting chosen based on the relative time-of-day.
   */
  public function getGreeting($morningGreeting = 'Good morning', $afternoonGreeting = 'Good afternoon', $eveningGreeting = 'Good evening', $nightGreeting = 'Good night')
  {
    return $this->chooseByTimeOfDay($morningGreeting, $afternoonGreeting, $eveningGreeting, $nightGreeting);
  }

  /**
   * Formats a span of datetimes
   *
   * @param integer|string|\DateTime $e End time
   * @param boolean $long
   * @param boolean $relative
   * @param string $firstDateLink
   * @param string $secondDateLink
   * @return String Formatted span of dates and times (e.g. "May 4 at 3-5 pm")
   */
  public function dateTimeSpan($e, $long = false, $relative = true, $firstDateLink = '', $secondDateLink = '')
  {
    assert('is_int($e) || is_string($e) || $e instanceOf \DateTime');
    assert('is_bool($long)');
    assert('is_bool($relative)');
    assert('is_string($firstDateLink)');
    assert('is_string($secondDateLink)');

    $end = $this->makeDateTime($e);

    $isMultiple = $this->isMultipleDays($end);
    $isAllDay = $this->isAllDay($end);

    $r  = '';
    if (($isMultiple && $isAllDay) || !$isMultiple) {
      $r .= $this->dateSpan($e, $long, $relative, $firstDateLink, $secondDateLink)->getValue();
    }

    if (!$isMultiple && !$isAllDay) {
      $r .= ' at ';
    }

    $r .= $this->timeSpan($e, $long, $relative, $firstDateLink, $secondDateLink)->getValue();

    return new String($r);
  }

  /**
   * Formats a span of dates
   *
   * @param integer|string|\DateTime $e End time
   * @param boolean $long Month length (if true, display full month, otherwise abbreviation)
   * @param boolean $relative
   * @param string $firstDateLink
   * @param string $secondDateLink
   * @return String Formatted span of dates (e.g. "May 4-5")
   */
  public function dateSpan($e, $long = false, $relative = true, $firstDateLink = '', $secondDateLink = '')
  {
    assert('is_int($e) || is_string($e) || $e instanceOf \DateTime');
    assert('is_bool($long) || is_null($long)');
    assert('is_bool($relative)');
    assert('is_string($firstDateLink)');
    assert('is_string($secondDateLink)');

    $s = $this->value;
    $end = $this->makeDateTime($e);

    if ($s > $end) {
      $end = $s;
    }

    $monthForm  = ($long) ? 'F' : 'M';
    $tests      = $s->format('M j, Y');
    $teste      = $end->format('M j, Y');
    $today      = date('M j, Y');
    $tomorrow   = date('M j, Y', strtotime('tomorrow'));
    $yesterday  = date('M j, Y', strtotime('yesterday'));
    $thismonth  = date($monthForm);
    $thisyear   = date('Y');

    $result   = '';

    $wrapper  = array('', '', '', '');
    if (!empty($firstDateLink)) {
      $wrapper[0] = "<a href=\"$firstDateLink\">";
      $wrapper[1] = '</a>';
    }

    if (!empty($secondDateLink)) {
      $wrapper[2] = "<a href=\"$secondDateLink\">";
      $wrapper[3] = '</a>';
    }

    if ($relative) {
      if ($tests === $today) {
        $result = 'Today';
      } else if ($tests === $tomorrow) {
        $result = 'Tomorrow';
      } else if ($tests === $yesterday) {
        $result = 'Yesterday';
      }
    }

    // if the start and end dates are the same
    // return the date that the event happens on
    if (!$this->isMultipleDays($end)) {
      if (!empty($result)) {
        return new String($wrapper[0] . $result . $wrapper[1]);
      } else if ($s->format('Y') == $thisyear) {
        return new String($wrapper[0] . $s->format($monthForm . ' j') . $wrapper[1]); // This year, so we leave out the year
      } else {
        return new String($wrapper[0] . $s->format($monthForm . ' j, Y') . $wrapper[1]);
      }
    }

    // the start and end dates are not the same
    // so we need to figure out how to format the date span

    if (empty($result)) {
      // start by putting the month and date in the string
      $result = $s->format($monthForm) . self::NON_BREAKING_SPACE_CHARACTER . $s->format('j');

      // if the start and end dates are not in the same year
      // add the year of the start date to the string
      if ($s->format('Y') != $end->format('Y')) {
        $result .= $s->format(', Y');
      }
    }

    $result = $wrapper[0] . $result . $wrapper[1];

    // end of start date formatting, begin end date formatting

    // if the start and end dates are not in the same month and year
    // add the month to the string
    if ($s->format($monthForm . ' Y') !== $end->format($monthForm . ' Y')) {
      $result .= ' to ';
      /* This was doubling up the month
      if ($tests != $today && $tests != $tomorrow && $tests != $yesterday)
        $result .= date($monthForm, $e) . '&nbsp;';
      */
    } else {
      $result .= self::NDASH_CHARACTER . '<wbr />';
    }

    // finally, add the date and year of the end date to the string

    $result .= $wrapper[2];

    if ($relative && $teste === $today) {
      $result .= 'Today';
    } else if ($relative && $teste === $tomorrow) {
      $result .= 'Tomorrow';
    } else if ($relative && $teste === $yesterday) {
      $result .= 'Yesterday';
    } else if ($relative && ($tests === $today || $tests === $tomorrow || $tests === $yesterday)) {
      if ($end->format('Y') === $thisyear) {
        $result .= $end->format($monthForm) . self::NON_BREAKING_SPACE_CHARACTER . $end->format('j');
      } else {
        $result .= $end->format($monthForm . ' j, Y');
      }
    } else {
      if ($end->format($monthForm) === $thismonth && $s->format($monthForm) === $end->format($monthForm) && $end-format('Y') === $thisyear) {
        $result .= $end->format('j');
      } else if ($end->format('Y') === $thisyear) {
        if ($end->format($monthForm) !== $s->format($monthForm)) {
          $result .= $end->format($monthForm) . self::NON_BREAKING_SPACE_CHARACTER;
        }
        $result .= $end->format('j');
      } else {
        if ($end->format($monthForm) !== $s->format($monthForm)) {
          $result .= $end->format($monthForm) . ' ';
        }
        $result .= $end->format('j, Y');
      }
    } // if

    $result .= $wrapper[3];

    return new String($result);
  }

  /**
   * Formats a span of times
   *
   * @param integer|string|\DateTime $e End time
   * @param boolean $hCalendar If true will output in hCalendar format
   * @param boolean $relative
   * @param string $firstDateLink
   * @param string $secondDateLink
   * @return String If $s and $e are on the same day, will return formatted span of times (e.g. "6-8 pm")
   */
  public function timeSpan($e, $hCalendar = false, $relative = true, $firstDateLink = '', $secondDateLink = '')
  {
    assert('is_int($e) || is_string($e) || $e instanceOf \DateTime');
    assert('is_bool($hCalendar)');
    assert('is_bool($relative)');
    assert('is_string($firstDateLink)');
    assert('is_string($secondDateLink)');

    $s = $this->value;
    $end = $this->makeDateTime($e);

    if ($this->isAllDay($end)) {
      if ($hCalendar) {
        return new String(' <abbr style="display:none;" class="dtstart" title="' . $s->format('Y-m-d') . '">All day</abbr>');
      } else {
        return new String('');
      }
    }

    $isMultipleDays = $this->isMultipleDays($end);

    $r = '';

    if ($hCalendar) {
      $r .= '<abbr class="dtstart" title="' . $s->format('Y-m-d\TH:i:s') . $s->format('P') . '">';
    }

    if ($isMultipleDays) {
      $r .= $this->dateSpan($s, false, $relative, $firstDateLink)->getValue() . ' at ';
    }

    if ($s->format('H:i:s') === '00:00:00') {
      $r .= 'midnight ';
    } else if ($s->format('H:i:s') === '12:00:00') {
      $r .= 'noon ';
    } else {
      $r .= $s->format('g');

      if ($s->format('i') !== '00') {
        $r .= $s->format(':i');
      }

      if ($isMultipleDays || $s->format('a') !== $end->format('a') || $end->format('H:i:s') === '00:00:00' || $end->format('H:i:s') === '12:00:00' || $end <= $s) {
        $r .= self::NON_BREAKING_SPACE_CHARACTER . $s->format('a ');
      }
    }

    if ($hCalendar) {
      $r .= '</abbr>';
    }

    if ($end > $s && $end->format('H:i:s') !== '00:00:00') {
      if ($isMultipleDays || $s->format('a') !== $end->format('a') || $s->format('H:i:s') === '00:00:00' || $s->format('H:i:s') === '00:00:00' || $s->format('H:i:s') === '12:00:00' || $end->format('H:i:s') === '00:00:00' || $end->format('H:i:s') === '12:00:00') {
        $r .= 'to ';
      } else {
        $r .= self::NDASH_CHARACTER;
      }

      if ($hCalendar) {
        $r .= '<abbr class="dtend" title="' . $end->format('Y-m-d\TH:i:s') . $end->format('P') . '">';
      }

      if ($isMultipleDays) {
        $this->setValue($end);
        $r .= $this->dateSpan($end, false, $relative, $secondDateLink)->getValue() . ' at ';
        $this->setValue($s);
      }

      if ($end->format('H:i:s') === '11:59:59') {
        $r .= 'midnight';
      } else if ($end->format('H:i:s') === '12:00:00') {
        $r .= 'noon';
      } else {
        $r .= $end->format('g');

        if ($end->format('i') !== '00' || ($end->format('i') !== '00') && $s->format('a') === $end->format('a')) {
          $r .= $end->format(':i');
        }

        $r .= self::NON_BREAKING_SPACE_CHARACTER . $end->format('a');
      }

      if ($hCalendar) {
        $r .= '</abbr>';
      }
    }

    //replace am or pm with a.m. and p.m. then return
    return new String(str_replace(array('am','pm'), array('a.m.','p.m.'), $r));
  }
}
