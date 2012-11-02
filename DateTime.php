<?php
/**
 * @package Utility
 * @author  Billy Visto
 * @author  Joe Lencioni
 */
namespace Gustavus\Utility;

use DateTime as PHPDateTime,
  DateInterval;

/**
 * Object for working with DateTimes
 * <p/>
 * <i><b>WARNING:</b> This class makes use of the PHP DateInterval object. When working with
 * negative intervals (such as 61 days into the past), months are not mapped to calendar months, but
 * instead use a constant 31-day month. As a result, when specifying a durations, the number of
 * actual months may not match up with the number of months in the interval.
 *
 * @package Utility
 * @author  Billy Visto
 * @author  Joe Lencioni
 */
class DateTime extends Base
{
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
   * @return  void
   */
  public function setValue($value)
  {
    parent::setValue($this->makeDateTime($value));
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
   * Make DateTime object
   *
   * @param  integer|string $date
   * @return DateTime
   */
  protected function makeDateTime($date = null)
  {
    if (is_numeric($date)) {
      // $date is a timestamp. We want it as a DateTime object
      $date = new PHPDateTime('@'.$date);
    } else if ($date === null) {
      // set date to be now
      $date = new PHPDateTime('now');
    } else {
      $date = new PHPDateTime($date);
    }
    return $date;
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
      foreach ($intervalArr as $key => $value) {
        $numberUtil = new Number($value);
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
        ($interval->format('%r') === "-") ? 'ago' : 'from now'
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
  public function adjustYearsIfNeeded(&$firstDate, &$endDate)
  {
    $firstDateTime = $firstDate->format('U');
    $endDateTime   = $endDate->format('U');
    if ($firstDateTime > $endDateTime) {
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
   * @param  integer|string $firstDate
   * @param  integer|string $endDate
   * @return boolean
   */
  public function inDateRange($firstDate, $endDate)
  {
    $firstDate = $this->makeDateTime($firstDate);
    $endDate   = $this->makeDateTime($endDate);

    // make sure firstDate is before endDate. If not, we may need to adjust years
    $this->adjustYearsIfNeeded($firstDate, $endDate);
    $firstDate = $firstDate->format('U');
    $endDate   = $endDate->format('U');
    $date      = $this->value->format('U');
    return ($firstDate <= $date && $date <= $endDate);
  }
}
