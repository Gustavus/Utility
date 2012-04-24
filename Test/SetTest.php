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
    $this->set = new TestObject(new Utility\Set());
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
    $set = new Utility\Set(array('one', 'two', 'three'));

    $this->expectOutputString('one, two, and three');
    echo $set;
  }

  /**
   * @test
   * @expectedException DomainException
   */
  public function setValue()
  {
    $this->assertInstanceOf('DomainException', new Utility\Set('hello'));
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
    $set = new Utility\Set(array('one', 'two', 'three'));

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
    $set = new Utility\Set($array);

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
  public function toSentenceWithOneWord()
  {
    $array  = array('one');
    $set = new Utility\Set($array);
    $this->assertSame('one', $set->toSentence()->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithTwoWord()
  {
    $array  = array('one', 'two');
    $set = new Utility\Set($array);
    $this->assertSame('one and two', $set->toSentence()->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithThreeWords()
  {
    $array  = array('one', 'two', 'three');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, and three', $set->toSentence()->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithCommasInArray()
  {
    $array  = array('one, two, and three', 'four, five, and six', 'seven, eight, and nine');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, and three; four, five, and six; and seven, eight, and nine', $set->toSentence()->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithNulls()
  {
    $array = array('one', 'two', null, 'three', null, 'four');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, three, and four', $set->toSentence()->getValue());

    $array = array(null);
    $set = new Utility\Set($array);
    $this->assertSame('', $set->toSentence()->getValue());

    $array = array('', null, '', null, ' ', null);
    $set = new Utility\Set($array);
    $this->assertSame('', $set->toSentence()->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithPadding()
  {
    $array = array('one', ' two', 'three ', ' four ', '  five  ');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, three, four, and five', $set->toSentence()->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithCallbacks()
  {
    $array = array('ONE', ' two', 'ThReE',);
    $set = new Utility\Set($array);
    $this->assertSame('ONE, TWO, and THREE', $set->toSentence('%s', array(0), array('strtoupper'))->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithCallbacksArrays()
  {
    $array = array('a' => array('ONE', 'two'), 'b' => array(' two', 'three'), 'c' => 'ThReE',);
    $set = new Utility\Set($array);
    $this->assertSame('ONE, TWO, and THREE', $set->toSentence('%s', array(0), array('strtoupper'))->getValue());
  }


  /**
   * @test
   */
  public function toSentenceWithCallbacksAndNestedArrays()
  {
    $array = array(array('one', 'two'), array('three', 'four'), array('five', 'six'));
    $set = new Utility\Set($array);
    $this->assertSame('TWO, FOUR, and SIX', $set->toSentence('%s', array(1), array('strtoupper'))->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithoutEndWordTwoWords()
  {
    $set = new Utility\Set(array('one', 'two'));
    $this->assertSame('one two', $set->toSentence('%s', array(0), array(), 0, '')->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithoutEndWordThreeWords()
  {
    $set = new Utility\Set(array('one', 'two', 'three'));
    $this->assertSame('one, two, three', $set->toSentence('%s', array(0), array(), 0, '')->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithKey()
  {
    $array = array('a' => array('one', 'two'), 'b' => array('three', 'four'), 'c' => array('five', 'six'));
    $set = new Utility\Set($array);
    $this->assertSame('a, b, and c', $set->toSentence('%s', array('[key]'))->getValue());
    $this->assertSame('a-two, b-four, and c-six', $set->toSentence('%s-%s', array('[key]', 1))->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithKeyNonArray()
  {
    $array = array('a' => 'one', 'b' => 'three', 'c' => 'five');
    $set = new Utility\Set($array);
    $this->assertSame('a-one, b-three, and c-five', $set->toSentence('%s-%s', array('[key]', 1))->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithKeyArrays()
  {
    $array = array('a' => array('one', 'two'), 'b' => array('three', 'four'), 'c' => array('five', 'six'));
    $set = new Utility\Set($array);
    $this->assertSame('a-two, b-four, and c-six', $set->toSentence('%s-%s', array('[key]', 3))->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithKeyNumbers()
  {
    $array = array('a' => 1, 'b' => 3, 'c' => 4);
    $set = new Utility\Set($array);
    $this->assertSame('a-1, b-3, and c-4', $set->toSentence('%s-%s', array('[key]', 3))->getValue());
  }


  /**
   * @test
   */
  public function toSentenceFancyPattern()
  {
    $array = array('one', 'two', 'three');
    $set = new Utility\Set($array);
    $this->assertSame('-one-, -two-, and -three-', $set->toSentence('-%s-')->getValue());
  }

  /**
   * @test
   */
  public function toSentenceWithCommasInTags()
  {
    $array = array('<a href="#" title="This, is a test">one</a>', 'two', 'three');
    $set = new Utility\Set($array);
    $this->assertSame('<a href="#" title="This, is a test">one</a>, two, and three', $set->toSentence()->getValue());
  }

  /**
   * @test
   */
  public function toSentenceNonZeroMax()
  {
    $set = new Utility\Set(array('one', 'two', 'three'));
    $expected = '<span class="">one <small><a href="#" class="doToggle" rel="span.">(more)</a></small></span><span class="nodisplay ">one, two, and three <small><a href="#" class="doToggle" rel="span.">(less)</a></small></span>';
    // filter out random class names
    $actual = $set->toSentence('%s', array(0), array(), 1)->getValue();
    preg_match('`class="([^"]+)"`', $actual, $match);
    $actual = str_replace($match[1], '', $actual);

    $this->assertSame($expected, $actual);
  }

  /**
   * @test
   */
  public function newSentence()
  {
    $set = new Utility\Set(array('one', 'two', 'three'));
    $this->assertSame('one, two, and three', $set->newSentence('{{ value }}')->getValue());
  }

  /**
   * @test
   */
  public function newSentenceToUpper()
  {
    $set = new Utility\Set(array('one', 'two', 'three'));
    $this->assertSame('ONE, TWO, and THREE', $set->newSentence('{{ value|upper }}')->getValue());
  }

  /**
   * @test
   */
  public function newSentenceWithCommas()
  {
    $set = new Utility\Set(array('one, two', 'two', 'three'));
    $this->assertSame('one, two; two; and three', $set->newSentence('{{ value }}')->getValue());
  }

  /**
   * @test
   */
  public function newSentenceWithArrays()
  {
    $set = new Utility\Set(array('a' => array('one', 'two'), 'b' => array('three', 'four'), 'c' => 'five'));
    $this->assertSame('two, and four', $set->newSentence('{{ value.1 }}')->getValue());
  }

  /**
   * @test
   */
  public function newSentenceWithArraysAtFirstPos()
  {
    $set = new Utility\Set(array('a' => array('one', 'two'), 'b' => array('three', 'four'), 'c' => 'five'));
    $this->assertSame('a-one, b-three, and c-', $set->newSentence('{{ key }}-{{ value.0 }}')->getValue());
  }

  /**
   * @test
   */
  public function newSentenceWithCommasInTags()
  {
    $array = array('<a href="#" title="This, is a test">one</a>', 'two', 'three');
    $set = new Utility\Set($array);
    $this->assertSame('<a href="#" title="This, is a test">one</a>, two, and three', $set->newSentence('{{ value }}')->getValue());
  }

  /**
   * @test
   */
  public function newSentenceNonZeroMax()
  {
    $set = new Utility\Set(array('one', 'two', 'three'));
    $expected = 'one<span class=""><small><a href="#" class="doToggle" rel="span.">and more</a></small></span><span class="nodisplay ">, two, and three <small><a href="#" class="doToggle" rel="span.">less</a></small></span>';
    // filter out random class names
    $actual = $set->newSentence('{{ value }}', 'and', 1)->getValue();
    preg_match('`class="([^"]+)"`', $actual, $match);
    $actual = str_replace($match[1], '', $actual);

    $this->assertSame($expected, $actual);
  }

  /**
   * @test
   */
  public function newSentenceNonOneMax()
  {
    $set = new Utility\Set(array('one', 'two', 'three'));
    $expected = 'one<span class=""><small><a href="#" class="doToggle" rel="span.">and more</a></small></span><span class="nodisplay ">, two, and three <small><a href="#" class="doToggle" rel="span.">less</a></small></span>';
    // filter out random class names
    $actual = $set->newSentence('{{ value }}', 'and', 2)->getValue();
    preg_match('`class="([^"]+)"`', $actual, $match);
    $actual = str_replace($match[1], '', $actual);

    $this->assertSame($expected, $actual);
  }

  /**
   * @test
   */
  public function newSentenceNonTwoMax()
  {
    $set = new Utility\Set(array('one', 'two', 'three', 'four'));
    $expected = 'one, two<span class=""><small><a href="#" class="doToggle" rel="span.">, and more</a></small></span><span class="nodisplay ">, three, and four <small><a href="#" class="doToggle" rel="span.">less</a></small></span>';
    // filter out random class names
    $actual = $set->newSentence('{{ value }}', 'and', 3)->getValue();
    preg_match('`class="([^"]+)"`', $actual, $match);
    $actual = str_replace($match[1], '', $actual);

    $this->assertSame($expected, $actual);
  }

  /**
   * @test
   */
  public function newSentenceThreeMaxAndThreeTotal()
  {
    $set = new Utility\Set(array('one', 'two', 'three'));
    $expected = 'one, two, and three';

    $this->assertSame($expected, $set->newSentence('{{ value }}', 'and', 3)->getValue());
  }
}
