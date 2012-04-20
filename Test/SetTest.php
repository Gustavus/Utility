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
  public function ArrayToSentenceWithOneWord()
  {
    $array  = array('one');
    $set = new Utility\Set($array);
    $this->assertSame('one', $set->sentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithTwoWord()
  {
    $array  = array('one', 'two');
    $set = new Utility\Set($array);
    $this->assertSame('one and two', $set->sentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithThreeWords()
  {
    $array  = array('one', 'two', 'three');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, and three', $set->sentence());
  }

  /**
   * @test
   */
  public function AliasArrayToSentenceBasic()
  {
    $array  = array('one');
    $set = new Utility\Set($array);
    $this->assertSame('one', $set->sentence());

    $array  = array('one', 'two');
    $set = new Utility\Set($array);
    $this->assertSame('one and two', $set->sentence());

    $array  = array('one', 'two', 'three');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, and three', $set->sentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithCommasInArray()
  {
    $array  = array('one, two, and three', 'four, five, and six', 'seven, eight, and nine');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, and three; four, five, and six; and seven, eight, and nine', $set->sentence());
  }

  /**
   * @test
   */
  public function AliasArrayToSentenceWithCommasInArray()
  {
    $array  = array('one, two, and three', 'four, five, and six', 'seven, eight, and nine');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, and three; four, five, and six; and seven, eight, and nine', $set->sentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithNulls()
  {
    $array = array('one', 'two', null, 'three', null, 'four');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, three, and four', $set->sentence());

    $array = array(null);
    $set = new Utility\Set($array);
    $this->assertSame('', $set->sentence());

    $array = array('', null, '', null, ' ', null);
    $set = new Utility\Set($array);
    $this->assertSame('', $set->sentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithPadding()
  {
    $array = array('one', ' two', 'three ', ' four ', '  five  ');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, three, four, and five', $set->sentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithCallbacks()
  {
    $array = array('ONE', ' two', 'ThReE',);
    $set = new Utility\Set($array);
    $this->assertSame('ONE, TWO, and THREE', $set->sentence('%s', array(0), array('strtoupper')));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithCallbacksArrays()
  {
    $array = array('a' => array('ONE', 'two'), 'b' => array(' two', 'three'), 'c' => 'ThReE',);
    $set = new Utility\Set($array);
    $this->assertSame('ONE, TWO, and THREE', $set->sentence('%s', array(0), array('strtoupper')));
  }


  /**
   * @test
   */
  public function ArrayToSentenceWithCallbacksAndNestedArrays()
  {
    $array = array(array('one', 'two'), array('three', 'four'), array('five', 'six'));
    $set = new Utility\Set($array);
    $this->assertSame('TWO, FOUR, and SIX', $set->sentence('%s', array(1), array('strtoupper')));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithoutEndWordTwoWords()
  {
    $set = new Utility\Set(array('one', 'two'));
    $this->assertSame('one two', $set->sentence('%s', array(0), array(), 0, ''));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithoutEndWordThreeWords()
  {
    $set = new Utility\Set(array('one', 'two', 'three'));
    $this->assertSame('one, two, three', $set->sentence('%s', array(0), array(), 0, ''));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithKey()
  {
    $array = array('a' => array('one', 'two'), 'b' => array('three', 'four'), 'c' => array('five', 'six'));
    $set = new Utility\Set($array);
    $this->assertSame('a, b, and c', $set->sentence('%s', array('[key]')));
    $this->assertSame('a-two, b-four, and c-six', $set->sentence('%s-%s', array('[key]', 1)));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithKeyNonArray()
  {
    $array = array('a' => 'one', 'b' => 'three', 'c' => 'five');
    $set = new Utility\Set($array);
    $this->assertSame('a-one, b-three, and c-five', $set->sentence('%s-%s', array('[key]', 1)));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithKeyArrays()
  {
    $array = array('a' => array('one', 'two'), 'b' => array('three', 'four'), 'c' => array('five', 'six'));
    $set = new Utility\Set($array);
    $this->assertSame('a-two, b-four, and c-six', $set->sentence('%s-%s', array('[key]', 3)));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithKeyNumbers()
  {
    $array = array('a' => 1, 'b' => 3, 'c' => 4);
    $set = new Utility\Set($array);
    $this->assertSame('a-1, b-3, and c-4', $set->sentence('%s-%s', array('[key]', 3)));
  }


  /**
   * @test
   */
  public function ArrayToSentenceFancyPattern()
  {
    $array = array('one', 'two', 'three');
    $set = new Utility\Set($array);
    $this->assertSame('-one-, -two-, and -three-', $set->sentence('-%s-'));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithCommasInTags()
  {
    $array = array('<a href="#" title="This, is a test">one</a>', 'two', 'three');
    $set = new Utility\Set($array);
    $this->assertSame('<a href="#" title="This, is a test">one</a>, two, and three', $set->sentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceNonZeroMax()
  {
    $set = new Utility\Set(array('one', 'two', 'three'));
    $expected = '<span class="">one <small><a href="#" class="doToggle" rel="span.">(more)</a></small></span><span class="nodisplay ">one, two, and three <small><a href="#" class="doToggle" rel="span.">(less)</a></small></span>';
    // filter out random class names
    $actual = $set->sentence('%s', array(0), array(), 1);
    preg_match('`class="([^"]+)"`', $actual, $match);
    $actual = str_replace($match[1], '', $actual);

    $this->assertSame($expected, $actual);
  }
}
