<?php
/**
 * @package Utility
 * @subpackage Test
 * @author  Billy Visto
 */

namespace Gustavus\Utility\Test;

use Gustavus\Utility\Set,
  Gustavus\Test\Test,
  Gustavus\Test\TestObject,
  Gustavus\Utility\Test\TestObject as SetTestObject;

/**
 * @package Utility
 * @subpackage Test
 * @author  Billy Visto
 */
class SetTest extends Test
{
  /**
   * @var Utility\Number
   */
  private $set;

  /**
   * sets up the object for each test
   * @return void
   */
  public function setUp()
  {
    $this->set = new TestObject(new Set());
  }

  /**
   * destructs the object after each test
   * @return void
   */
  public function tearDown()
  {
    unset($this->set);
  }

  /**
   * @test
   */
  public function toString()
  {
    $set = new Set(array('one', 'two', 'three'));

    $this->expectOutputString('one, two, and three');
    echo $set;
  }

  /**
   * @test
   * @expectedException DomainException
   */
  public function setValue()
  {
    $this->assertInstanceOf('DomainException', new Set('hello'));
  }

  /**
   * @test
   */
  public function valueIsValid()
  {
    $this->assertTrue($this->set->valueIsValid(array(1)));
    $this->assertTrue($this->set->valueIsValid(array(array())));
    $this->assertFalse($this->set->valueIsValid('1'));
    $this->assertFalse($this->set->valueIsValid(1));
  }

  /**
   * @test
   */
  public function ArrayAccess()
  {
    $set = new Set(array('one', 'two', 'three'));

    $this->assertSame('one', $set[0]);
    $this->assertSame('two', $set[1]);
    $this->assertSame('three', $set[2]);

    $this->assertTrue(isset($set[0]));
    $this->assertTrue(isset($set[1]));
    $this->assertTrue(isset($set[2]));
    $this->assertFalse(isset($set[3]));

    $set[1] = '2';

    $this->assertSame('2', $set[1]);

    $set['test'] = 'testing';

    $this->assertSame('testing', $set['test']);

    $this->assertTrue(isset($set[2]));

    unset($set[2]);

    $this->assertFalse(isset($set[2]));

    $this->assertSame(array('one', '2', 'test' => 'testing'), $set->getValue());
  }

  /**
   * @test
   */
  public function mapRecursive()
  {
    $array = array('zero', 'one', array('two', 'three', array('four')));
    $this->set->setValue($array);
    $this->set->mapRecursive('strtoupper');
    $this->assertSame(array('ZERO', 'ONE', array('TWO', 'THREE', array('FOUR'))), $this->set->value);
  }

  /**
   * @test
   */
  public function titleCase()
  {
    $array = array('testing this thing', 'ANOTHER test');
    $this->set->setValue($array);
    $this->set->titleCase();
    $this->assertSame(array('Testing This Thing', 'Another Test'), $this->set->value);
  }

  /**
   * @test
   */
  public function titleCaseWithExceptions()
  {
    $array = array('testing this thing', 'ANOTHER test');
    $this->set->setValue($array);
    $this->set->titleCase(array('test', 'THIS'));
    $this->assertSame(array('Testing THIS Thing', 'Another test'), $this->set->value);
  }

  /**
   * @test
   */
  public function arrayAt()
  {
    $array  = array('zero', 'one', 'two', 'three', 'four', 'five');
    $set = new Set($array);

    $this->assertSame('zero', $set->at());
    $this->assertSame('zero', $set->at(0));
    $this->assertSame('one', $set->at(1));
    $this->assertSame('two', $set->at(2));
    $this->assertSame('three', $set->at(3));
    $this->assertSame('four', $set->at(4));
    $this->assertSame('five', $set->at(5));
    $this->assertFalse($set->at(6));
    $this->assertFalse($set->at(7));
    $this->assertFalse($set->at(-1));
  }

  /**
   * @test
   */
  public function toSentence()
  {
    $set = new Set(array('one', 'two', 'three'));
    $this->assertSame('one, two, and three', $set->toSentence('{{ value }}')->getValue());
  }

  /**
   * @test
   */
  public function toSentenceEmptyArray()
  {
    $set = new Set(array());
    $this->assertSame('', $set->toSentence()->getValue());
  }

  /**
   * @test
   */
  public function toSentenceToUpper()
  {
    $set = new Set(array('one', 'two', 'three'));
    $this->assertSame('ONE, TWO, and THREE', $set->toSentence('{{ value|upper }}')->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithCommas()
  {
    $set = new Set(array('one, two', 'two', 'three'));
    $this->assertSame('one, two; two; and three', $set->toSentence('{{ value }}')->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithArrays()
  {
    $set = new Set(array('a' => array('one', 'two'), 'b' => array('three', 'four'), 'c' => 'five'));
    $this->assertSame('two, and four', $set->toSentence('{{ value.1 }}')->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithArraysAtFirstPos()
  {
    $set = new Set(array('a' => array('one', 'two'), 'b' => array('three', 'four'), 'c' => 'five'));
    $this->assertSame('a-one, b-three, and c-', $set->toSentence('{{ key }}-{{ value.0 }}')->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithArraysLogicInTwig()
  {
    $set = new Set(array('a' => array('one', 'two'), 'b' => array('three', 'four'), 'c' => 'five'));
    $this->assertSame('a-one, b-three, and c-five', $set->toSentence('{% if value|keys|length > 0 %}{{ key }}-{{ value.0 }}{% else %}{{ key }}-{{ value }}{% endif %}')->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithCommasInTags()
  {
    $array = array('<a href="#" title="This, is a test">one</a>', 'two', 'three');
    $set = new Set($array);
    $this->assertSame('<a href="#" title="This, is a test">one</a>, two, and three', $set->toSentence('{{ value }}')->getValue());
  }

  /**
   * @test
   */
  public function toSentenceNonZeroMax()
  {
    $set = new Set(array('one', 'two', 'three'));
    $expected = 'one<span class=""><small><a href="#" class="doToggle" rel="span.">and more</a></small></span><span class="nodisplay ">, two, and three <small><a href="#" class="doToggle" rel="span.">less</a></small></span>';
    // filter out random class names
    $actual = $set->toSentence('{{ value }}', 'and', 1)->getValue();
    preg_match('`class="([^"]+)"`', $actual, $match);
    $actual = str_replace($match[1], '', $actual);

    $this->assertSame($expected, $actual);
  }

  /**
   * @test
   */
  public function toSentenceNonOneMax()
  {
    $set = new Set(array('one', 'two', 'three'));
    $expected = 'one<span class=""><small><a href="#" class="doToggle" rel="span.">and more</a></small></span><span class="nodisplay ">, two, and three <small><a href="#" class="doToggle" rel="span.">less</a></small></span>';
    // filter out random class names
    $actual = $set->toSentence('{{ value }}', 'and', 2)->getValue();
    preg_match('`class="([^"]+)"`', $actual, $match);
    $actual = str_replace($match[1], '', $actual);

    $this->assertSame($expected, $actual);
  }

  /**
   * @test
   */
  public function toSentenceNonTwoMax()
  {
    $set = new Set(array('one', 'two', 'three', 'four'));
    $expected = 'one, two<span class=""><small><a href="#" class="doToggle" rel="span.">, and more</a></small></span><span class="nodisplay ">, three, and four <small><a href="#" class="doToggle" rel="span.">less</a></small></span>';
    // filter out random class names
    $actual = $set->toSentence('{{ value }}', 'and', 3)->getValue();
    preg_match('`class="([^"]+)"`', $actual, $match);
    $actual = str_replace($match[1], '', $actual);

    $this->assertSame($expected, $actual);
  }

  /**
   * @test
   */
  public function toSentenceThreeMaxAndThreeTotal()
  {
    $set = new Set(array('one', 'two', 'three'));
    $expected = 'one, two, and three';

    $this->assertSame($expected, $set->toSentence('{{ value }}', 'and', 3)->getValue());
  }

  /**
   * @test
   */
  public function toSentenceNonCommaSeparator()
  {
    $set = new Set(array('one', 'two', 'three'));
    $this->assertSame('one| two| three', $set->toSentence('{{ value }}', '', 0, '|')->getValue());
  }

  /**
   * @test
   */
  public function toSentenceArrayOfObjects()
  {
    $array = [
      new SetTestObject('billy'),
      new SetTestObject('jerry'),
      new SetTestObject('chris'),
    ];
    $set = new Set($array);

    $this->assertSame('billy, jerry, and chris', $set->toSentence('{{ value.getName() }}')->getValue());
  }

  /**
   * @test
   */
  public function toSentenceArrayOfObject()
  {
    $array = [
      new SetTestObject('billy', new \DateTime),
      new SetTestObject('jerry', new \DateTime),
      new SetTestObject('chris', new \DateTime),
    ];
    $set = new Set($array);

    $this->assertSame('billy, jerry, and chris', $set->toSentence('{{ value.getName() }}')->getValue());
  }

  /**
   * @test
   */
  public function getSynonyms()
  {
    $set = new Set(array('billy'));
    $expected = array('bill', 'william', 'billy', 'will', 'willy', 'willie');

    $this->assertSame($expected, $set->getSynonyms()->getValue());
  }

  /**
   * @test
   */
  public function arrayValues()
  {
    $arrayOfAssoc = array(
      array('test' => 'value'),
      array('another array' => 'value1'),
      'value2',
    );
    $expected = array('value', 'value1', 'value2');
    $set = new Set($arrayOfAssoc);
    $this->assertSame($expected, $set->arrayValues()->getValue());
  }

  /**
   * @test
   */
  public function arrayValues2()
  {
    $arrayOfAssoc = array(
      'test' => 'value',
      array('another array' => 'value1'),
      'value2',
    );
    $expected = array('value', 'value1', 'value2');
    $set = new Set($arrayOfAssoc);
    $this->assertSame($expected, $set->arrayValues()->getValue());
  }

  /**
   * @test
   */
  public function arrayValues3()
  {
    $arrayOfAssoc = array(
      'test' => 'value',
      'another array' => 'value1',
      'value2',
    );
    $expected = array('value', 'value1', 'value2');
    $set = new Set($arrayOfAssoc);
    $this->assertSame($expected, $set->arrayValues()->getValue());
  }

  /**
   * @test
   */
  public function encodeValues()
  {
    $array = ['all', 'anon',['callbacks']];
    $expected = ['all', 'anon', '["callbacks"]'];
    $this->assertSame($expected, (new Set($array))->encodeValues()->getValue());
  }

  /**
   * @test
   */
  public function encodeValuesTwo()
  {
    $array = ['all', 'anon',['callbacks',['test']]];
    $expected = ['all', 'anon', '["callbacks",["test"]]'];
    $this->assertSame($expected, (new Set($array))->encodeValues()->getValue());
  }

  /**
   * @test
   */
  public function decodeValues()
  {
    $expected = ['all', 'anon',['callbacks']];
    $array = ['all', 'anon', '["callbacks"]'];
    $this->assertSame($expected, (new Set($array))->decodeValues()->getValue());
  }

  /**
   * @test
   */
  public function decodeValuesTwo()
  {
    $expected = ['all', 'anon',['callbacks',['test']]];
    $array = ['all', 'anon', '["callbacks",["test"]]'];
    $this->assertSame($expected, (new Set($array))->decodeValues()->getValue());
  }

  /**
   * @test
   */
  public function flatten()
  {
    $expected = [1,2,3,4];
    $array = [['id' => 1], 2, 'id' => 3, [['id' => 4]]];
    $this->assertSame($expected, (new Set($array))->flattenValues()->getValue());
  }

  /**
   * @test
   */
  public function format()
  {
    $test = array(
      array('one', 'two', 'three'),
      array('four', 'five', 'six'),
      array('seven', 'eight', 'nine'),
    );

    $this->assertSame(
        'one two and three four five and six seven eight and nine ',
        (new Set($test))->format('%s %s and %s ', array(0, 1, 2))->getValue()
    );
  }

  /**
   * @test
   * @dataProvider buildURLData
   */
  public function buildURL($url)
  {
    $urlData = parse_url($url);
    $result = (new Set($urlData))->buildURL()->getValue();
    $this->assertSame($url, $result);
  }

  /**
   * DataProvider for buildURL
   * @return array
   */
  public function buildURLData()
  {
    return [
      ['https://gustavus.edu:80/arst?hello=test#here'],
      ['https://gustavus.edu/arst?hello=test#here'],
      ['https://gustavus.edu:80/arst?hello=test'],
      ['https://gustavus.edu:80/arst'],
      ['https://gustavus.edu:80/'],
      ['https://gustavus.edu/'],
      ['gustavus.edu/arst'],
      ['arst?hello=test#here'],
      ['arst'],
    ];
  }

  /**
   * @test
   * @dataProvider recursivelyMergeArraysData
   */
  public function recursivelyMergeArrays($expected, $array, $arrayTwo)
  {
    $actual = $this->call('\Gustavus\Utility\Set', 'recursivelyMergeArrays', [$array, $arrayTwo]);
    $this->assertSame($expected, $actual);

    $set = new Set($array);
    $this->assertSame($expected, $set->mergeArrays($arrayTwo)->getValue());
  }

  /**
   * Data provider for recursivelyMergeArrays
   * @return array
   */
  public static function recursivelyMergeArraysData()
  {
    return [
      [['1' => 'arst', '2' => 'test'], ['1' => 'arst'], ['2' => 'test']],
      [['1' => ['nest' => 'arst', 'test' => 'test']], ['1' => ['nest' => 'arst']], ['1' => ['test' => 'test']]],
      [['1' => ['nest' => 'arst', 'test' => 'test'], '2' => 'two'], ['1' => ['nest' => 'arst']], ['1' => ['test' => 'test'], '2' => 'two']],
      [['1' => ['nest' => 'testing']], ['1' => ['nest' => 'arst']], ['1' => ['nest' => 'testing']]],
    ];
  }
}
