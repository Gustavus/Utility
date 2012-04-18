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
class SetTest extends \Gustavus\Test\Test
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
    $this->set = new \Gustavus\Test\TestObject(new Utility\Set());
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
    $this->assertSame('one', $set->arrayToSentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithTwoWord()
  {
    $array  = array('one', 'two');
    $set = new Utility\Set($array);
    $this->assertSame('one and two', $set->arrayToSentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithThreeWords()
  {
    $array  = array('one', 'two', 'three');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, and three', $set->arrayToSentence());
  }

  /**
   * @test
   */
  public function AliasArrayToSentenceBasic()
  {
    $array  = array('one');
    $set = new Utility\Set($array);
    $this->assertSame('one', $set->arrayToSentence());

    $array  = array('one', 'two');
    $set = new Utility\Set($array);
    $this->assertSame('one and two', $set->arrayToSentence());

    $array  = array('one', 'two', 'three');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, and three', $set->arrayToSentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithCommasInArray()
  {
    $array  = array('one, two, and three', 'four, five, and six', 'seven, eight, and nine');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, and three; four, five, and six; and seven, eight, and nine', $set->arrayToSentence());
  }

  /**
   * @test
   */
  public function AliasArrayToSentenceWithCommasInArray()
  {
    $array  = array('one, two, and three', 'four, five, and six', 'seven, eight, and nine');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, and three; four, five, and six; and seven, eight, and nine', $set->arrayToSentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithNulls()
  {
    $array = array('one', 'two', null, 'three', null, 'four');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, three, and four', $set->arrayToSentence());

    $array = array(null);
    $set = new Utility\Set($array);
    $this->assertSame('', $set->arrayToSentence());

    $array = array('', null, '', null, ' ', null);
    $set = new Utility\Set($array);
    $this->assertSame('', $set->arrayToSentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithPadding()
  {
    $array = array('one', ' two', 'three ', ' four ', '  five  ');
    $set = new Utility\Set($array);
    $this->assertSame('one, two, three, four, and five', $set->arrayToSentence());
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithCallbacks()
  {
    $array = array('ONE', ' two', 'ThReE',);
    $set = new Utility\Set($array);
    $this->assertSame('ONE, TWO, and THREE', $set->arrayToSentence('%s', array(0), array('strtoupper')));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithCallbacksAndNestedArrays()
  {
    $array = array(array('one', 'two'), array('three', 'four'), array('five', 'six'));
    $set = new Utility\Set($array);
    $this->assertSame('TWO, FOUR, and SIX', $set->arrayToSentence('%s', array(1), array('strtoupper')));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithoutEndWordTwoWords()
  {
    $set = new Utility\Set(array('one', 'two'));
    $this->assertSame('one two', $set->arrayToSentence('%s', array(0), array(), 0, ''));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithoutEndWordThreeWords()
  {
    $set = new Utility\Set(array('one', 'two', 'three'));
    $this->assertSame('one, two, three', $set->arrayToSentence('%s', array(0), array(), 0, ''));
  }

  /**
   * @test
   */
  public function ArrayToSentencWithKey()
  {
    $array = array('a' => array('one', 'two'), 'b' => array('three', 'four'), 'c' => array('five', 'six'));
    $set = new Utility\Set($array);
    $this->assertSame('a, b, and c', $set->arrayToSentence('%s', array('[key]')));
    $this->assertSame('a-two, b-four, and c-six', $set->arrayToSentence('%s-%s', array('[key]', 1)));
  }

  /**
   * @test
   */
  public function ArrayToSentenceFancyPattern()
  {
    $array = array('one', 'two', 'three');
    $set = new Utility\Set($array);
    $this->assertSame('-one-, -two-, and -three-', $set->arrayToSentence('-%s-'));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithCommasInTags()
  {
    $array = array('<a href="#" title="This, is a test">one</a>', 'two', 'three');
    $set = new Utility\Set($array);
    $this->assertSame('<a href="#" title="This, is a test">one</a>, two, and three', $set->arrayToSentence());
  }
}
