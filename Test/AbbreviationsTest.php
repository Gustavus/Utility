<?php
/**
 * AbbreviationsTest.php
 *
 * @package Gustavus\Utility
 * @subpackage Test
 */
namespace Gustavus\Utility\Test;

use Gustavus\Utility,
    Gustavus\Utility\Abbreviations,

    Gustavus\Test\Test,
    Gustavus\Test\TestObject;

/**
 * @package Gustavus\Utility
 * @subpackage Test
 */
class AbbreviationsTest extends Test
{
  private static $testClasses = [
    'meals' => ['breakfast' => 'b', 'lunch' => 'l', 'dinner' => 'd'],
    'fruit' => ['apple' => 'a', 'banana' => 'b', 'cantaloupe' => 'c'],
    'veggies' => ['artichoke' => 'a', 'broccoli' => 'b', 'cauliflower' => 'c'],
    'things' => ['apple' => '1', 'broccoli' => '2', 'dinner' => '3']
  ];

  public static function setUpBeforeClass()
  {
    $rc = new \ReflectionClass('\Gustavus\Utility\Abbreviations');

    $rp = $rc->getProperty('data');
    $rp->setAccessible(true);

    $val = $rp->getValue();

    // $val should be an array here...
    assert('is_array($val)');

    foreach (AbbreviationsTest::$testClasses as $class => $abbr) {
      $val[$class] = $abbr;
    }

    $rp->setValue(null, $val);
  }







  /**
   * @test
   * @dataProvider abbreviateTestData
   */
  public function testAbbreviate($string, $classes, $ignoreCase, $expected)
  {
    try {
      $result = Abbreviations::abbreviate($string, $classes, $ignoreCase);
      $this->assertEquals($expected, $result);
    } catch (\Exception $e) {
      if ($expected !== false) {
        throw $e;
      }
    }
  }

  public function abbreviateTestData()
  {
    return [
      ['Minnesota', [Abbreviations::US_STATE], true, 'MN'],
      ['Boulevard', [Abbreviations::US_STREET], true, 'BLVD'],
      ['Apartment', [Abbreviations::US_BUILDING], true, 'APT'],
      ['Northeast', [Abbreviations::DIRECTIONALS], true, 'NE'],
      ['Minnesota', [Abbreviations::US_STREET], true, 'Minnesota'],
      ['Boulevard', [Abbreviations::US_BUILDING], true, 'Boulevard'],
      ['Apartment', [Abbreviations::DIRECTIONALS], true, 'Apartment'],
      ['Northeast', [Abbreviations::US_STATE], true, 'Northeast'],

      ['MINNESOTA', [Abbreviations::US_STATE], false, 'MN'],
      ['BOULEVARD', [Abbreviations::US_STREET], false, 'BLVD'],
      ['APARTMENT', [Abbreviations::US_BUILDING], false, 'APT'],
      ['NORTHEAST', [Abbreviations::DIRECTIONALS], false, 'NE'],
      ['Minnesota', [Abbreviations::US_STATE], false, 'Minnesota'],
      ['Boulevard', [Abbreviations::US_STREET], false, 'Boulevard'],
      ['Apartment', [Abbreviations::US_BUILDING], false, 'Apartment'],
      ['Northeast', [Abbreviations::DIRECTIONALS], false, 'Northeast'],

      ['Minnesota', ['banana'], true, 'Minnesota'],
      ['Minnesota', [true], true, 'Minnesota'],
      ['Minnesota', [123], true, 'Minnesota'],
      ['Minnesota', [123.45], true, 'Minnesota'],
      ['Minnesota', ['banana'], false, 'Minnesota'],
      ['Minnesota', [true], false, 'Minnesota'],
      ['Minnesota', [123], false, 'Minnesota'],
      ['Minnesota', [123.45], false, 'Minnesota'],

      [null, [Abbreviations::US_STATE], true, false],
      [true, [Abbreviations::US_STATE], true, false],
      [123, [Abbreviations::US_STATE], true, false],
      [123.45, [Abbreviations::US_STATE], true, false],
      [['a'], [Abbreviations::US_STATE], true, false],
      ['string', [], true, false]
    ];
  }

  /**
   * @test
   * @dataProvider abbreviateAllTestData
   */
  public function testAbbreviateAll($string, $classes, $ignoreCase, $expected)
  {
    try {
      $result = Abbreviations::abbreviateAll($string, $classes, $ignoreCase);
      $this->assertEquals($expected, $result);
    } catch (\Exception $e) {
      if ($expected !== false) {
        throw $e;
      }
    }
  }

  public function abbreviateAllTestData()
  {
    return [
      ['Minnesota', [Abbreviations::US_STATE], true, 'MN'],
      ['Boulevard', [Abbreviations::US_STREET], true, 'BLVD'],
      ['Apartment', [Abbreviations::US_BUILDING], true, 'APT'],
      ['Northeast', [Abbreviations::DIRECTIONALS], true, 'NE'],
      ['Minnesota', [Abbreviations::US_STREET], true, 'Minnesota'],
      ['Boulevard', [Abbreviations::US_BUILDING], true, 'Boulevard'],
      ['Apartment', [Abbreviations::DIRECTIONALS], true, 'Apartment'],
      ['Northeast', [Abbreviations::US_STATE], true, 'Northeast'],

      ['Minnesota Boulevard Northeast, Apartment #305', [Abbreviations::US_STATE, Abbreviations::US_STREET, Abbreviations::US_BUILDING, Abbreviations::DIRECTIONALS], true, 'MN BLVD NE, APT #305'],
      ['Minnesota Boulevard Northeast, Apartment #305', [Abbreviations::US_STATE, Abbreviations::US_STREET, Abbreviations::US_BUILDING], true, 'MN BLVD Northeast, APT #305'],
      ['Minnesota Boulevard Northeast, Apartment #305', [Abbreviations::US_STATE, Abbreviations::US_STREET], true, 'MN BLVD Northeast, Apartment #305'],
      ['Minnesota Boulevard Northeast, Apartment #305', [Abbreviations::US_STATE], true, 'MN Boulevard Northeast, Apartment #305'],

      ['MINNESOTA BOULEVARD NORTHEAST, APARTMENT #305', [Abbreviations::US_STATE, Abbreviations::US_STREET, Abbreviations::US_BUILDING, Abbreviations::DIRECTIONALS], false, 'MN BLVD NE, APT #305'],
      ['MINNESOTA BOULEVARD NORTHEAST, APARTMENT #305', [Abbreviations::US_STATE, Abbreviations::US_STREET, Abbreviations::US_BUILDING], false, 'MN BLVD NORTHEAST, APT #305'],
      ['MINNESOTA BOULEVARD NORTHEAST, APARTMENT #305', [Abbreviations::US_STATE, Abbreviations::US_STREET], false, 'MN BLVD NORTHEAST, APARTMENT #305'],
      ['MINNESOTA BOULEVARD NORTHEAST, APARTMENT #305', [Abbreviations::US_STATE], false, 'MN BOULEVARD NORTHEAST, APARTMENT #305'],
      ['Minnesota Boulevard Northeast, Apartment #305', [Abbreviations::US_STATE, Abbreviations::US_STREET, Abbreviations::US_BUILDING, Abbreviations::DIRECTIONALS], false, 'Minnesota Boulevard Northeast, Apartment #305'],
      ['Minnesota Boulevard Northeast, Apartment #305', [Abbreviations::US_STATE, Abbreviations::US_STREET, Abbreviations::US_BUILDING], false, 'Minnesota Boulevard Northeast, Apartment #305'],
      ['Minnesota Boulevard Northeast, Apartment #305', [Abbreviations::US_STATE, Abbreviations::US_STREET], false, 'Minnesota Boulevard Northeast, Apartment #305'],
      ['Minnesota Boulevard Northeast, Apartment #305', [Abbreviations::US_STATE], false, 'Minnesota Boulevard Northeast, Apartment #305'],

      ['MINNESOTA', [Abbreviations::US_STATE], false, 'MN'],
      ['BOULEVARD', [Abbreviations::US_STREET], false, 'BLVD'],
      ['APARTMENT', [Abbreviations::US_BUILDING], false, 'APT'],
      ['NORTHEAST', [Abbreviations::DIRECTIONALS], false, 'NE'],
      ['Minnesota', [Abbreviations::US_STATE], false, 'Minnesota'],
      ['Boulevard', [Abbreviations::US_STREET], false, 'Boulevard'],
      ['Apartment', [Abbreviations::US_BUILDING], false, 'Apartment'],
      ['Northeast', [Abbreviations::DIRECTIONALS], false, 'Northeast'],

      ['Minnesota', ['banana'], true, 'Minnesota'],
      ['Minnesota', [true], true, 'Minnesota'],
      ['Minnesota', [123], true, 'Minnesota'],
      ['Minnesota', [123.45], true, 'Minnesota'],
      ['Minnesota', ['banana'], false, 'Minnesota'],
      ['Minnesota', [true], false, 'Minnesota'],
      ['Minnesota', [123], false, 'Minnesota'],
      ['Minnesota', [123.45], false, 'Minnesota'],

      [null, [Abbreviations::US_STATE], true, false],
      [true, [Abbreviations::US_STATE], true, false],
      [123, [Abbreviations::US_STATE], true, false],
      [123.45, [Abbreviations::US_STATE], true, false],
      [['a'], [Abbreviations::US_STATE], true, false],
      ['string', [], true, false]
    ];
  }


  /**
   * @test
   * @dataProvider complexAbbreviationTableData
   */
  public function testBuildComplexAbbreviationTable($classes, $expected)
  {
    // Check what we actually get...
    $actual = Abbreviations::getAbbreviationTable($classes);

    $this->assertEquals($expected, $actual);
  }

  public function complexAbbreviationTableData()
  {
    return [
      [['meals'], AbbreviationsTest::$testClasses['meals']],
      [['meals', 'fruit'], array_merge(AbbreviationsTest::$testClasses['meals'], AbbreviationsTest::$testClasses['fruit'])],
      [['meals', 'things'], ['breakfast' => 'b', 'lunch' => 'l', 'apple' => '1', 'broccoli' => '2', 'dinner' => '3']]
    ];
  }
}
