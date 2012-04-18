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
  public function Array_at()
  {
    $array  = array('zero', 'one', 'two', 'three', 'four', 'five');

    $this->assertSame('zero', $this->set->array_at($array));
    $this->assertSame('zero', $this->set->array_at($array, 0));
    $this->assertSame('one', $this->set->array_at($array, 1));
    $this->assertSame('two', $this->set->array_at($array, 2));
    $this->assertSame('three', $this->set->array_at($array, 3));
    $this->assertSame('four', $this->set->array_at($array, 4));
    $this->assertSame('five', $this->set->array_at($array, 5));
    $this->assertFalse($this->set->array_at($array, 6));
    $this->assertFalse($this->set->array_at($array, 7));
    $this->assertFalse($this->set->array_at($array, -1));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithOneWord()
  {
    $array  = array('one');
    $this->assertSame('one', $this->set->arrayToSentence($array));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithTwoWord()
  {
    $array  = array('one', 'two');
    $this->assertSame('one and two', $this->set->arrayToSentence($array));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithThreeWords()
  {
    $array  = array('one', 'two', 'three');
    $this->assertSame('one, two, and three', $this->set->arrayToSentence($array));
  }

  /**
   * @test
   */
  public function AliasArrayToSentenceBasic()
  {
    $array  = array('one');
    $this->assertSame('one', $this->set->arrayToSentence($array));

    $array  = array('one', 'two');
    $this->assertSame('one and two', $this->set->arrayToSentence($array));

    $array  = array('one', 'two', 'three');
    $this->assertSame('one, two, and three', $this->set->arrayToSentence($array));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithCommasInArray()
  {
    $array  = array('one, two, and three', 'four, five, and six', 'seven, eight, and nine');
    $this->assertSame('one, two, and three; four, five, and six; and seven, eight, and nine', $this->set->arrayToSentence($array));
  }

  /**
   * @test
   */
  public function AliasArrayToSentenceWithCommasInArray()
  {
    $array  = array('one, two, and three', 'four, five, and six', 'seven, eight, and nine');
    $this->assertSame('one, two, and three; four, five, and six; and seven, eight, and nine', $this->set->arrayToSentence($array));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithNulls()
  {
    $array = array('one', 'two', null, 'three', null, 'four');
    $this->assertSame('one, two, three, and four', $this->set->arrayToSentence($array));

    $array = array(null);
    $this->assertSame('', $this->set->arrayToSentence($array));

    $array = array('', null, '', null, ' ', null);
    $this->assertSame('', $this->set->arrayToSentence($array));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithPadding()
  {
    $array = array('one', ' two', 'three ', ' four ', '  five  ');
    $this->assertSame('one, two, three, four, and five', $this->set->arrayToSentence($array));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithCallbacks()
  {
    $array = array('ONE', ' two', 'ThReE',);
    $this->assertSame('ONE, TWO, and THREE', $this->set->arrayToSentence($array, '%s', array(0), array('strtoupper')));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithCallbacksAndNestedArrays()
  {
    $array = array(array('one', 'two'), array('three', 'four'), array('five', 'six'));
    $this->assertSame('TWO, FOUR, and SIX', $this->set->arrayToSentence($array, '%s', array(1), array('strtoupper')));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithoutEndWordTwoWords()
  {
    $this->assertSame('one two', $this->set->arrayToSentence(array('one', 'two'), '%s', array(0), array(), 0, ''));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithoutEndWordThreeWords()
  {
    $this->assertSame('one, two, three', $this->set->arrayToSentence(array('one', 'two', 'three'), '%s', array(0), array(), 0, ''));
  }

  /**
   * @test
   */
  public function ArrayToSentencWithKey()
  {
    $array = array('a' => array('one', 'two'), 'b' => array('three', 'four'), 'c' => array('five', 'six'));
    $this->assertSame('a, b, and c', $this->set->arrayToSentence($array, '%s', array('[key]')));
    $this->assertSame('a-two, b-four, and c-six', $this->set->arrayToSentence($array, '%s-%s', array('[key]', 1)));
  }

  /**
   * @test
   */
  public function ArrayToSentenceFancyPattern()
  {
    $array = array('one', 'two', 'three');
    $this->assertSame('-one-, -two-, and -three-', $this->set->arrayToSentence($array, '-%s-'));
  }

  /**
   * @test
   */
  public function ArrayToSentenceWithCommasInTags()
  {
    $ar = array('<a href="#" title="This, is a test">one</a>', 'two', 'three');
    $this->assertSame('<a href="#" title="This, is a test">one</a>, two, and three', $this->set->arrayToSentence($ar));
  }
}
