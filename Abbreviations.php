<?php
/**
 * Abbreviations.php
 *
 * @package Gustavus\Utility
 *
 * @author Chris Rog
 */
namespace Gustavus\Utility;



/**
 * The Abbreviations class provides utility functions for obtaining common abbreviations and
 * performing certain trivial replacement tasks.
 *
 * @package Gustavus\Utility
 *
 * @author Chris Rog
 */
class Abbreviations
{
  const DIRECTIONALS      = 0x01;

  const US_STATE          = 0x02;
  const US_STREET         = 0x03;
  const US_BUILDING       = 0x04;

  const CA_PROVINCES      = 0x05;

  /**
   * Contains our abbreviations (eventually).
   *
   * @var array
   */
  private static $data = [];

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Populates the specified portion(s) of the abbreviation table.
   * <p/>
   * This function trades memory space for processing time. Considering that most pages are cached,
   * processing time is already reduced elsewhere through more advanced methods, and the amount of
   * abbreviation data far exceeds the amount of extra processor time required to lazily load it.
   * This is especially important as PHP puts an upper-bound on the amount of memory a file/request
   * can allocate.
   *
   * @param scalar &$class
   *  The abbreviation class to prepare.
   *
   * @return boolean
   *  True if the table is populated upon completion of a call to this function.
   */
  private static function populateAbbreviationTable(&$class)
  {
    assert('!empty($class) && is_scalar($class)');

    // Check that we haven't already set this value...
    if (isset(Abbreviations::$data[$class])) {
      return true;
    }

    // @todo:
    // Maybe make these a bit more friendly/not uppercase so we can use them in grammatically/
    // syntactically correct sentences?
    switch ($class) {
      case Abbreviations::DIRECTIONALS:
        Abbreviations::$data[$class] = [
          'NORTH'     => 'N',   'EAST'      => 'E',   'SOUTH'     => 'S',   'WEST'      => 'W',
          'NORTHEAST' => 'NE',  'SOUTHEAST' => 'SE',  'NORTHWEST' => 'NW',  'SOUTHWEST' => 'SW'
        ];
          return true;

      case Abbreviations::US_STATE:
        Abbreviations::$data[$class] = [
          'ALABAMA'         => 'AL',    'ALASKA'          => 'AK',    'ARIZONA'         => 'AZ',
          'ARKANSAS'        => 'AR',    'CALIFORNIA'      => 'CA',    'COLORADO'        => 'CO',
          'CONNECTICUT'     => 'CT',    'DELAWARE'        => 'DE',    'FLORIDA'         => 'FL',
          'GEORGIA'         => 'GA',    'HAWAII'          => 'HI',    'IDAHO'           => 'ID',
          'ILLINOIS'        => 'IL',    'INDIANA'         => 'IN',    'IOWA'            => 'IA',
          'KANSAS'          => 'KS',    'KENTUCKY'        => 'KY',    'LOUISIANA'       => 'LA',
          'MAINE'           => 'ME',    'MARYLAND'        => 'MD',    'MASSACHUSETTS'   => 'MA',
          'MICHIGAN'        => 'MI',    'MINNESOTA'       => 'MN',    'MISSISSIPPI'     => 'MS',
          'MISSOURI'        => 'MO',    'MONTANA'         => 'MT',    'NEBRASKA'        => 'NE',
          'NEVADA'          => 'NV',    'NEW HAMPSHIRE'   => 'NH',    'NEW JERSEY'      => 'NJ',
          'NEW MEXICO'      => 'NM',    'NEW YORK'        => 'NY',    'NORTH CAROLINA'  => 'NC',
          'NORTH DAKOTA'    => 'ND',    'OHIO'            => 'OH',    'OKLAHOMA'        => 'OK',
          'OREGON'          => 'OR',    'PENNSYLVANIA'    => 'PA',    'RHODE ISLAND'    => 'RI',
          'SOUTH CAROLINA'  => 'SC',    'SOUTH DAKOTA'    => 'SD',    'TENNESSEE'       => 'TN',
          'TEXAS'           => 'TX',    'UTAH'            => 'UT',    'VERMONT'         => 'VT',
          'VIRGINIA'        => 'VA',    'WASHINGTON'      => 'WA',    'WEST VIRGINIA'   => 'WV',
          'WISCONSIN'       => 'WI',    'WYOMING'         => 'WY',


          'AMERICAN SAMOA'                  => 'AS',
          'DISTRICT OF COLUMBIA'            => 'DC',
          'FEDERATED STATES OF MICRONESIA'  => 'FM',
          'GUAM'                            => 'GU',
          'MARSHALL ISLANDS'                => 'MH',
          'NORTHERN MARIANA ISLANDS'        => 'MP',
          'PALAU'                           => 'PW',
          'PUERTO RICO'                     => 'PR',
          'VIRGIN ISLANDS'                  => 'VI',
          'ARMED FORCES AFRICA'             => 'AE',
          'ARMED FORCES AMERICAS'           => 'AA',
          'ARMED FORCES CANADA'             => 'AE',
          'ARMED FORCES EUROPE'             => 'AE',
          'ARMED FORCES MIDDLE EAST'        => 'AE',
          'ARMED FORCES PACIFIC'            => 'AP'
        ];
          return true;

      case Abbreviations::US_STREET:
        Abbreviations::$data[$class] = [
          'ALLEY'       => 'ALY',        'ANNEX'       => 'ANX',        'ARCADE'      => 'ARC',
          'AVENUE'      => 'AVE',        'BAYOO'       => 'BYU',        'BEACH'       => 'BCH',
          'BEND'        => 'BND',        'BLUFF'       => 'BLF',        'BLUFFS'      => 'BLFS',
          'BOTTOM'      => 'BTM',        'BOULEVARD'   => 'BLVD',       'BRANCH'      => 'BR',
          'BRIDGE'      => 'BRG',        'BROOK'       => 'BRK',        'BURG'        => 'BG',
          'BURGS'       => 'BGS',        'BYPASS'      => 'BYP',        'CAMP'        => 'CP',
          'CANYON'      => 'CYN',        'CAPE'        => 'CPE',        'CAUSEWAY'    => 'CSWY',
          'CENTER'      => 'CTR',        'CENTERS'     => 'CTRS',       'CIRCLE'      => 'CIR',
          'CIRCLES'     => 'CIRS',       'CLIFF'       => 'CLF',        'CLIFFS'      => 'CLFS',
          'CLUB'        => 'CLB',        'COMMON'      => 'CMN',        'CORNER'      => 'COR',
          'CORNERS'     => 'CORS',       'COURSE'      => 'CRSE',       'COURT'       => 'CT',
          'COURTS'      => 'CTS',        'COVE'        => 'CV',         'COVES'       => 'CVS',
          'CREEK'       => 'CRK',        'CRESCENT'    => 'CRES',       'CREST'       => 'CRST',
          'CROSSING'    => 'XING',       'CROSSROAD'   => 'XRD',        'CURVE'       => 'CURV',
          'DALE'        => 'DL',         'DAM'         => 'DM',         'DIVIDE'      => 'DV',
          'DRIVE'       => 'DR',         'DRIVES'      => 'DRS',        'ESTATE'      => 'EST',
          'ESTATES'     => 'ESTS',       'EXPRESSWAY'  => 'EXPY',       'EXTENSION'   => 'EXT',
          'EXTENSIONS'  => 'EXTS',       'FALL'        => 'FALL',       'FALLS'       => 'FLS',
          'FERRY'       => 'FRY',        'FIELD'       => 'FLD',        'FIELDS'      => 'FLDS',
          'FLAT'        => 'FLT',        'FLATS'       => 'FLTS',       'FORD'        => 'FRD',
          'FORDS'       => 'FRDS',       'FOREST'      => 'FRST',       'FORGE'       => 'FRG',
          'FORGES'      => 'FRGS',       'FORK'        => 'FRK',        'FORKS'       => 'FRKS',
          'FORT'        => 'FT',         'FREEWAY'     => 'FWY',        'GARDEN'      => 'GDN',
          'GARDENS'     => 'GDNS',       'GATEWAY'     => 'GTWY',       'GLEN'        => 'GLN',
          'GLENS'       => 'GLNS',       'GREEN'       => 'GRN',        'GREENS'      => 'GRNS',
          'GROVE'       => 'GRV',        'GROVES'      => 'GRVS',       'HARBOR'      => 'HBR',
          'HARBORS'     => 'HBRS',       'HAVEN'       => 'HVN',        'HEIGHTS'     => 'HTS',
          'HIGHWAY'     => 'HWY',        'HILL'        => 'HL',         'HILLS'       => 'HLS',
          'HOLLOW'      => 'HOLW',       'INLET'       => 'INLT',       'ISLAND'      => 'IS',
          'ISLANDS'     => 'ISS',        'ISLE'        => 'ISLE',       'JUNCTION'    => 'JCT',
          'JUNCTIONS'   => 'JCTS',       'KEY'         => 'KY',         'KEYS'        => 'KYS',
          'KNOLL'       => 'KNL',        'KNOLLS'      => 'KNLS',       'LAKE'        => 'LK',
          'LAKES'       => 'LKS',        'LAND'        => 'LAND',       'LANDING'     => 'LNDG',
          'LANE'        => 'LN',         'LIGHT'       => 'LGT',        'LIGHTS'      => 'LGTS',
          'LOAF'        => 'LF',         'LOCK'        => 'LCK',        'LOCKS'       => 'LCKS',
          'LODGE'       => 'LDG',        'LOOP'        => 'LOOP',       'MALL'        => 'MALL',
          'MANOR'       => 'MNR',        'MANOR'       => 'MNR',        'MANORS'      => 'MNRS',
          'MANORS'      => 'MNRS',       'MEADOW'      => 'MDW',        'MEADOWS'     => 'MDWS',
          'MEWS'        => 'MEWS',       'MILL'        => 'ML',         'MILLS'       => 'MLS',
          'MISSION'     => 'MSN',        'MOTORWAY'    => 'MTWY',       'MOUNT'       => 'MT',
          'MOUNTAIN'    => 'MTN',        'MOUNTAINS'   => 'MTNS',       'ORCHARD'     => 'ORCH',
          'OVAL'        => 'OVAL',       'OVERPASS'    => 'OPAS',       'PARK'        => 'PARK',
          'PARKS'       => 'PARK',       'PARKWAY'     => 'PKWY',       'PARKWAYS'    => 'PKWY',
          'PASS'        => 'PASS',       'PASSAGE'     => 'PSGE',       'PATH'        => 'PATH',
          'PIKE'        => 'PIKE',       'PINE'        => 'PNE',        'PINES'       => 'PNES',
          'PLACE'       => 'PL',         'PLAIN'       => 'PLN',        'PLAINS'      => 'PLNS',
          'PLAZA'       => 'PLZ',        'POINT'       => 'PT',         'POINTS'      => 'PTS',
          'PORT'        => 'PRT',        'PORTS'       => 'PRTS',       'PRAIRIE'     => 'PR',
          'RADIAL'      => 'RADL',       'RAMP'        => 'RAMP',       'RANCH'       => 'RNCH',
          'RAPID'       => 'RPD',        'RAPIDS'      => 'RPDS',       'REST'        => 'RST',
          'RIDGE'       => 'RDG',        'RIDGES'      => 'RDGS',       'RIVER'       => 'RIV',
          'ROAD'        => 'RD',         'ROADS'       => 'RDS',        'ROUTE'       => 'RTE',
          'ROW'         => 'ROW',        'RUE'         => 'RUE',        'RUN'         => 'RUN',
          'SHOALS'      => 'SHLS',       'SHORE'       => 'SHR',        'SHORES'      => 'SHRS',
          'SKYWAY'      => 'SKWY',       'SPRING'      => 'SPG',        'SPRINGS'     => 'SPGS',
          'SPUR'        => 'SPUR',       'SPURS'       => 'SPUR',       'SQUARE'      => 'SQ',
          'SQUARES'     => 'SQS',        'STATION'     => 'STA',        'STRAVENUE'   => 'STRA',
          'STREAM'      => 'STRM',       'STREET'      => 'ST',         'STREETS'     => 'STS',
          'SUMMIT'      => 'SMT',        'TERRACE'     => 'TER',        'THROUGHWAY'  => 'TRWY',
          'TRACE'       => 'TRCE',       'TRACK'       => 'TRAK',       'TRAFFICWAY'  => 'TRFY',
          'TRAIL'       => 'TRL',        'TUNNEL'      => 'TUNL',       'TURNPIKE'    => 'TPKE',
          'UNDERPASS'   => 'UPAS',       'UNION'       => 'UN',         'UNIONS'      => 'UNS',
          'VALLEY'      => 'VLY',        'VALLEYS'     => 'VLYS',       'VIADUCT'     => 'VIA',
          'VIEW'        => 'VW',         'VIEWS'       => 'VWS',        'VILLAGE'     => 'VLG',
          'VILLAGES'    => 'VLGS',       'VILLE'       => 'VL',         'VISTA'       => 'VIS',
          'WALK'        => 'WALK',       'WALKS'       => 'WALK',       'WALL'        => 'WALL',
          'WAY'         => 'WAY',        'WAYS'        => 'WAYS',       'WELL'        => 'WL',
          'WELLS'       => 'WLS'
        ];
          return true;

      case Abbreviations::US_BUILDING:
        Abbreviations::$data[$class] = [
          'APARTMENT'   => 'APT',        'BASEMENT'    => 'BSMT',       'BUILDING'    => 'BLDG',
          'DEPARTMENT'  => 'DEPT',       'FLOOR'       => 'FL',         'FRONT'       => 'FRNT',
          'HANGAR'      => 'HNGR',       'LOBBY'       => 'LBBY',       'LOT'         => 'LOT',
          'LOWER'       => 'LOWR',       'OFFICE'      => 'OFC',        'PENTHOUSE'   => 'PH',
          'PIER'        => 'PIER',       'REAR'        => 'REAR',       'ROOM'        => 'RM',
          'SIDE'        => 'SIDE',       'SLIP'        => 'SLIP',       'SPACE'       => 'SPC',
          'STOP'        => 'STOP',       'SUITE'       => 'STE',        'TRAILER'     => 'TRLR',
          'UNIT'        => 'UNIT',       'UPPER'       => 'UPPR'
        ];
          return true;

      case Abbreviations::CA_PROVINCES:
        Abbreviations::$data[$class] = [
          'ALBERTA'                  => 'AB',
          'BRITISH COLUMBIA'         => 'BC',
          'MANITOBA'                 => 'MB',
          'NEW BRUNSWICK'            => 'NB',
          'LABRADOR'                 => 'NL',
          'NEWFOUNDLAND'             => 'NL',
          'NORTHWEST TERRITORIES'    => 'NT',
          'NOVA SCOTIA'              => 'NS',
          'NUNAVUT'                  => 'NU',
          'ONTARIO'                  => 'ON',
          'PRINCE EDWARD ISLAND'     => 'PE',
          'QUEBEC'                   => 'QC',
          'SASKATCHEWAN'             => 'SK',
          'YUKON'                    => 'YT'
        ];
          return true;
    }

    return false;
  }


  /**
   * Obtains a mapping of the specified abbreviation classes to their respective values.
   * <p/>
   * Note: When combining abbreviation classes, if two or more classes contain an abbreviation for
   * the same long word, the last class specified will take precedence.
   * <p/>
   * <b><i>WARNING:</i></b> This is a <i>very</i> expensive function and should be used sparingly.
   * When requesting multiple abbreviation tables, this function can easily exceed 10k of memory
   * just with internal operations, nevermind the resulting table. If the abbreviation table is
   * absolutely necessary, try to avoid combining several abbreviation classes into one table.
   *
   * @param array $classes
   *  A collection of abbreviation classes. Use the constants provided by the Abbreviations class.
   *
   * @return array
   *  A table consisting of full words to their abbreviated equivalent.
   */
  public static function getAbbreviationTable(array $classes)
  {
    $table = [];



    foreach ($classes as $class) {
      if (isset(Abbreviations::$data[$class])) {
        $table = array_merge($table, Abbreviations::$data[$class]);
      }
    }

    return $table;
  }

  /**
   * Abbreviates the specified string if, and only if, the string is equal to at least one mapping
   * in the specified abbreviation classes.
   * <p/>
   * Note: Abbreviation classes are applied in serial. As such, the number of abbreviations that can
   * occur as a result of a call to this function is less than or equal to the number of specified
   * abbreviation classes.
   *
   * @param string $string
   *  The string to abbreviate
   *
   * @param array $classes
   *  A collection of abbreviation classes. Use the constants provided by the Abbreviations class.
   *
   * @param boolean $ignoreCase
   *  Whether or not this function should ignore case when abbreviating. Default: true.
   *
   * @throws \InvalidArgumentException
   *  If $string is null or not a string, or $classes is null, empty, not an array or contains
   *  non-scalar values.
   *
   * @return string
   *  The abbreviated string. If no abbreviations occurred, this function returns the unmodified
   *  input string.
   */
  public static function abbreviate($string, array $classes, $ignoreCase = true)
  {
    if ($string === null || !is_string($string)) {
      throw new \InvalidArgumentException('$string is null or not a string.');
    }

    if (empty($classes)) {
      throw new \InvalidArgumentException('$classes is null, empty or not an array.');
    }

    foreach ($classes as $class) {
      if (!is_scalar($class)) {
        throw new \InvalidArgumentException('$classes contains non-scalar values');
      }

      $search = $ignoreCase ? strtoupper($string) : $string;

      if (Abbreviations::populateAbbreviationTable($class) && isset(Abbreviations::$data[$class][$search])) {
        $string = Abbreviations::$data[$class][$search];
      }
    }

    return $string;
  }

  /**
   * Abbreviates all known long-words in the specified string and returns the resulting string.
   * <p/>
   * Note: Abbreviation classes are applied in serial. As such, the number of abbreviations that can
   * occur as a result of a call to this function is less than or equal to the number of specified
   * abbreviation classes.
   *
   * @param string $string
   *  The string to abbreviate
   *
   * @param array $classes
   *  A collection of abbreviation classes. Use the constants provided by the Abbreviations class.
   *
   * @param boolean $ignoreCase
   *  Whether or not this function should ignore case when abbreviating. Default: true.
   *
   * @throws \InvalidArgumentException
   *  If $string is null or not a string, or $classes is null, empty, not an array or contains
   *  non-scalar values.
   *
   * @return string
   *  The abbreviated string. If no abbreviations occurred, this function returns the unmodified
   *  input string.
   */
  public static function abbreviateAll($string, array $classes, $ignoreCase = true)
  {
    if ($string === null || !is_string($string)) {
      throw new \InvalidArgumentException('$string is null or not a string.');
    }

    if (empty($classes)) {
      throw new \InvalidArgumentException('$classes is null, empty or not an array.');
    }

    foreach ($classes as $class) {
      if (!is_scalar($class)) {
        throw new \InvalidArgumentException('$classes contains non-scalar values');
      }

      if (Abbreviations::populateAbbreviationTable($class)) {
        $search = array_keys(Abbreviations::$data[$class]);
        $values = array_values(Abbreviations::$data[$class]);

        $string = $ignoreCase ? str_ireplace($search, $values, $string) : str_replace($search, $values, $string);
      }
    }

    return $string;
  }

}
