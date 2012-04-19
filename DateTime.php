<?php
/**
 * @package Utility
 */
namespace Gustavus\Utility;

/**
 * Object for working with DateTimes
 *
 * @package Utility
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
   * Figures out the class name based off of the DateInterval
   *
   * @param mixed $now time to get relative time against
   * @return string
   */
  public function relativeClassName($now = null)
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
          return 'minute';
        } else {
          return 'now';
        }
      } else if ($intervalArr[$firstKey] > 1) {
        return $firstKey . 's';
      } else {
        return $firstKey;
      }
    } else {
      return 'now';
    }
  }

  /**
   * Make non specific relative date. Either makes a string or an array of data
   *
   * @param  array  $array
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
      $return['relative'] = $numberUtil->quantity("%s $firstKey ", "%s {$firstKey}s ");
    }
    return $return;
  }

  /**
   * Make DateTime object
   *
   * @param  mixed $date
   * @return DateTime
   */
  private function makeDateTime($date = null)
  {
    if (is_int($date)) {
      // $date is a timestamp. We want it as a DateTime object
      $date = new \DateTime('@'.$date);
    } else if ($date === null) {
      // set date to be now
      $date = new \DateTime('now');
    } else {
      $date = new \DateTime($date);
    }
    return $date;
  }

  /**
   * Make Array with the singular label as the key, and an integer as the value
   *
   * @param  DateInterval $interval
   * @param  integer  $totalDays
   * @return array
   */
  private function makeIntervalArray(\DateInterval $interval, $totalDays = null)
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
   * @param mixed $now Time to get relative time against
   * @param boolean $beSpecific whether to output the greatest time measurement, or to be as specific as possible
   * @return string
   */
  public function relative($now = null, $beSpecific = false)
  {
    $date        = $this->value;
    $now         = $this->makeDateTime($now);

    $interval    = $date->diff($now);
    $totalDays   = ($interval->invert === 0) ? $interval->days : 0 - $interval->days;
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
        return $nonSpecificDate;
      }
    } else {
      // make specific date array
      foreach ($intervalArr as $key => $value) {
        $numberUtil = new Number($value);
        $relative[] = $numberUtil->quantity("%s $key ", "%s {$key}s ");
      }
    }

    if (empty($relative)) {
      // modified less than a second ago, output just now
      return 'Just Now';
    }

    $setUtil = new Set($relative);
    return sprintf(
        '%s%s %s',
        $startText,
        $setUtil->toSentence(),
        ($interval->format('%r') === "") ? 'ago' : 'from now'
    );
  }

}