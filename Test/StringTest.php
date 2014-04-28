<?php
/**
 * @package Utility
 * @subpackage Test
 */

namespace Gustavus\Utility\Test;

use Gustavus\Utility,
  Gustavus\Test\Test,
  Gustavus\Test\TestObject,
  Gustavus\Utility\String;

/**
 * @package Utility
 * @subpackage Test
 */
class StringTest extends Test
{
  /**
   * @var string
   */
  private $longText   = 'Phasellus aliquam imperdiet leo. Suspendisse accumsan enim et ipsum. Nullam vitae augue non ipsum aliquam sagittis. Nullam sed velit. Nunc magna est, lacinia eget, tristique sit amet, pretium sed, turpis. Nulla faucibus aliquet libero. Mauris metus risus, auctor ut, gravida hendrerit, pharetra amet.';

  /**
   * @var string
   */
  private $textShortened  = 'Phasellus aliquam imperdiet leo. Suspendisse accumsan';

  /**
   * @var string
   */
  private $shortText      = 'Phasellus aliquam imperdiet leo.';

  /**
   * @var Utility\String
   */
  private $string;

  /**
   * sets up the object for each test
   * @return void
   */
  public function setUp()
  {
    $this->string = new TestObject(new Utility\String('set up'));
  }

  /**
   * destructs the object after each test
   * @return void
   */
  public function tearDown()
  {
    unset($this->string);
  }

  /**
   * @test
   */
  public function toString()
  {
    $set = new Utility\String('one, two, and three');

    $this->expectOutputString('one, two, and three');
    echo $set;
  }

  /**
   * @test
   * @expectedException DomainException
   */
  public function setValue()
  {
    $this->assertInstanceOf('DomainException', new Utility\String(1));
  }

  /**
   * @test
   */
  public function valueIsValid()
  {
    $string = new TestObject($this->string);
    $this->assertTrue($string->valueIsValid('1'));
    $this->assertTrue($string->valueIsValid('hello'));
    $this->assertFalse($string->valueIsValid(1));
    $this->assertFalse($string->valueIsValid(array(1)));
  }

  /**
   * @test
   */
  public function arrayAccess()
  {
    $string = new Utility\String('test');

    $this->assertSame('t', $string[0]);
    $this->assertSame('e', $string[1]);
    $this->assertSame('s', $string[2]);
    $this->assertSame('t', $string[3]);

    $this->assertTrue(isset($string[0]));
    $this->assertTrue(isset($string[1]));
    $this->assertTrue(isset($string[2]));
    $this->assertTrue(isset($string[3]));
    $this->assertFalse(isset($string[4]));

    $string[0] = 'b';

    $this->assertSame('best', $string->getValue());

    $string[1] = 'l';
    $string[2] = 'a';
    $string[3] = 'r';

    $this->assertSame('blar', $string->getValue());

    $string[4] = 'f';

    $this->assertSame('blarf', $string->getValue());

    // skip one
    $string[6] = '!';

    $this->assertSame('blarf !', $string->getValue());

    unset($string[1]);

    $this->assertSame('barf !', $string->getValue());
  }

  /**
   * @return array
   */
  public static function caseData()
  {
    return array(
      array('testing case', 'Testing Case', 'testing case', 'TESTING CASE'),
      array('TESTING CASE', 'Testing Case', 'testing case', 'TESTING CASE'),
      array('a test of case', 'A Test of Case', 'a test of case', 'A TEST OF CASE'),
      array('king henry The viii', 'King Henry the VIII', 'king henry the viii', 'KING HENRY THE VIII'),
    );
  }

  /**
   * @test
   * @dataProvider caseData
   */
  public function titleCase($value, $title, $lower, $upper)
  {
    $this->string->setValue($value);
    $this->assertSame($title, $this->string->titleCase()->getValue());
  }

  /**
   * @test
   * @dataProvider caseData
   */
  public function titleCaseWithExceptions($value, $title, $lower, $upper)
  {
    $this->string->setValue($value);
    $this->assertSame($title, $this->string->titleCase(array_keys($this->string->titleCaseExceptions))->getValue());
  }

  /**
   * @test
   * @dataProvider caseData
   */
  public function lowerCase($value, $title, $lower, $upper)
  {
    $this->string->setValue($value);
    $this->assertSame($lower, $this->string->lowerCase()->getValue());
  }

  /**
   * @test
   * @dataProvider caseData
   */
  public function upperCase($value, $title, $lower, $upper)
  {
    $this->string->setValue($value);
    $this->assertSame($upper, $this->string->upperCase()->getValue());
  }

  /**
   * @test
   * @dataProvider unCamelCaseData
   */
  public function unCamelCase($expected, $value)
  {
    $this->string->setValue($value);
    $this->assertSame($expected, $this->string->unCamelCase()->getValue());
  }

  /**
   * Data for unCamelCase
   * @return array
   */
  public function unCamelCaseData()
  {
    return [
      ['some un camel case string', 'someUnCamelCaseString'],
      ['randomstring', 'randomstring'],
      ['title string', 'TitleString'],
    ];
  }

  /**
   * @test
   * @dataProvider urlData
   */
  public function url($expected, $url)
  {
    $this->string->setValue($url);
    $this->assertSame($expected, $this->string->url()->getValue());
  }

  /**
   * @return array
   */
  public static function urlData()
  {
    return array(
      array('http://google.com', 'google.com'),
      array('http://google.com', ' google.com '),
      array('http://google.com', 'http://google.com'),
      array('https://google.com', 'https://google.com'),
      array('ftp://google.com', 'ftp://google.com'),

      array('http://google.com/test', 'google.com/test'),
      array('http://google.com/test', ' google.com/test '),
      array('http://google.com/test', 'http://google.com/test'),
      array('https://google.com/test', 'https://google.com/test'),
      array('ftp://google.com/test', 'ftp://google.com/test'),

      // Too short
      array('', 'ab'),

      // Gustavus URLs
      array('ftp://gustavus.edu', 'ftp:\\gustavus.edu'),
      array('ftp://gustavus.edu:1058', 'ftp://gustavus.edu:1058'),
      array('http://bvisto@gustavus.edu', 'http://bvisto@gustavus.edu'),
      array('http://bvisto:pass@gustavus.edu', 'http://bvisto:pass@gustavus.edu'),
      array('http://gustavus.edu', 'gac.edu'),
      array('http://gustavus.edu', ' gac.edu '),
      array('http://gustavus.edu', 'http://gac.edu'),
      array('https://gustavus.edu', 'https://gac.edu'),
      array('ftp://gustavus.edu', 'ftp://gac.edu'),
      array('http://gustavus.edu/test', 'gac.edu/test'),
      array('http://gustavus.edu/test', 'http://gac.edu/test'),
      array('https://gustavus.edu/test', 'https://gac.edu/test'),
      array('ftp://gustavus.edu/test', 'ftp://gac.edu/test'),

      array('http://gustavus.edu', 'gustavus.edu'),
      array('http://gustavus.edu', ' gustavus.edu '),
      array('http://gustavus.edu', 'http://gustavus.edu'),
      array('https://gustavus.edu', 'https://gustavus.edu'),
      array('ftp://gustavus.edu', 'ftp://gustavus.edu'),
      array('http://gustavus.edu/test', 'gustavus.edu/test'),
      array('http://gustavus.edu/test', 'http://gustavus.edu/test'),
      array('https://gustavus.edu/test', 'https://gustavus.edu/test'),
      array('ftp://gustavus.edu/test', 'ftp://gustavus.edu/test'),

      // Homepages
      array('http://homepages.gac.edu/~user/', 'http://homepages.gac.edu/~user/'),
    );
  }

  /**
   * @test
   * @dataProvider buildUrlData
   */
  public function buildUrl($expected, $url, $host, $scriptName = '/test/test.php', $fromMainWebServers = false)
  {
    $_SERVER['HTTP_HOST']   = $host;
    $_SERVER['SCRIPT_NAME'] = $scriptName;
    $this->string->setValue($url);
    $this->assertSame($expected, $this->string->buildUrl($fromMainWebServers)->getValue());
  }

  /**
   * @return array
   */
  public static function buildUrlData()
  {
    return array(
      array('https://gustavus.edu/', '/', 'gustavus.edu'),
      array('https://gustavus.edu/admission', '/admission', 'gustavus.edu'),
      array('https://gustavus.edu/admission?arst=arst', '/admission?arst=arst', 'gustavus.edu'),
      array('https://gustavus.edu/admission?arst=arst#fragment', '/admission?arst=arst#fragment', 'gustavus.edu'),
      array('https://beta.gac.edu/profiles/arst/', '/profiles/arst/', 'beta.gac.edu'),
      array('https://gustavus.edu/admission/apply/', 'apply/', 'gustavus.edu', '/admission/index.php'),
      array('https://gustavus.edu/admission/apply/?arst=arst', 'apply/?arst=arst', 'gustavus.edu', '/admission/index.php'),
      // host is null, so we will fallback to HOSTNAME in this case
      array('https://bart.gac.edu/admission/apply/?arst=arst', 'apply/?arst=arst', null, '/admission/index.php'),

      array('https://blog-beta.gac.edu/admission/apply/?arst=arst', 'apply/?arst=arst', 'blog-beta.gac.edu', '/admission/index.php'),
      array('https://blog-beta.gac.edu/admission/apply/?arst=arst', 'apply/?arst=arst', 'blog-beta.gac.edu', '/admission/index.php'),
      array('https://beta.gac.edu/admission/apply/?arst=arst', 'apply/?arst=arst', 'blog-beta.gac.edu', '/admission/index.php', true),
    );
  }

  /**
   * @test
   * @dataProvider addQueryStringData
   */
  public function addQueryString($expected, $url, $queryParams = [])
  {
    $this->string->setValue($url);
    $this->assertSame($expected, $this->string->addQueryString($queryParams)->getValue());
  }

  /**
   * Data for addQueryString
   * @return array
   */
  public function addQueryStringData()
  {
    return [
      ['/arst/','/arst/'],
      ['/arst/?barebones=1', '/arst/', ['barebones' => true]],
      ['/arst/?barebones=1', '/arst/?barebones=1'],
      ['/arst/?barebones=1', '/arst/?barebones=1', ['barebones' => true]],
      ['/arst/?barebones=1&action=test', '/arst/?barebones=1', ['action' => 'test']],
      ['/arst/?barebones=1&action=test', '/arst/?barebones=1&action=test', ['action' => 'test']],
      ['/arst/?barebones=1&action=test&more=1', '/arst/?barebones=1&action=test', ['action' => 'test', 'more' => true]],
      ['gustavus.edu/arst/?barebones=1&action=test&more=1', 'gustavus.edu/arst/?barebones=1&action=test', ['action' => 'test', 'more' => true]],
      ['www.youtube.com/embed/dl29EnvXCSo?list=PLFCOQw8uiAWOx2iZ7UdX2ldm6gWr28sRV&index=1&rel=0&theme=light', '//www.youtube.com/embed/dl29EnvXCSo', ['list' => 'PLFCOQw8uiAWOx2iZ7UdX2ldm6gWr28sRV', 'index' => 1, 'rel' => 0, 'theme' => 'light']],
    ];
  }

  /**
   * @test
   * @dataProvider emailData
   */
  public function email($expected, $email)
  {
    $this->string->setValue($email);
    $this->assertSame($expected, $this->string->email()->getValue());
  }

  /**
   * @return array
   */
  public function emailData()
  {
    return array(
      array('joe@google.com', 'joe@google.com'),
      array('joe@google.com', ' joe@google.com'),
      array('joe@google.com', 'joe@google.com '),
      array('joe@google.com', ' joe@google.com '),

      array('joe+test@google.com', 'joe+test@google.com'),
      array('joe+test@google.com', ' joe+test@google.com'),
      array('joe+test@google.com', 'joe+test@google.com '),
      array('joe+test@google.com', ' joe+test@google.com '),

      array('', ''),

      array('joe@gustavus.edu', 'joe'),
      array('joe@gustavus.edu', ' joe'),
      array('joe@gustavus.edu', 'joe '),
      array('joe@gustavus.edu', ' joe '),

      array('joe@gustavus.edu', 'joe@gac.edu'),
      array('joe@gustavus.edu', ' joe@gac.edu'),
      array('joe@gustavus.edu', 'joe@gac.edu '),
      array('joe@gustavus.edu', ' joe@gac.edu '),

      array('joe+test@gustavus.edu', 'joe+test@gac.edu'),
      array('joe+test@gustavus.edu', ' joe+test@gac.edu'),
      array('joe+test@gustavus.edu', 'joe+test@gac.edu '),
      array('joe+test@gustavus.edu', ' joe+test@gac.edu '),

      array('joe@gustavus.edu', 'joe@gustavus.edu'),
      array('joe@gustavus.edu', ' joe@gustavus.edu'),
      array('joe@gustavus.edu', 'joe@gustavus.edu '),
      array('joe@gustavus.edu', ' joe@gustavus.edu '),

      array('joe+test@gustavus.edu', 'joe+test@gustavus.edu'),
      array('joe+test@gustavus.edu', ' joe+test@gustavus.edu'),
      array('joe+test@gustavus.edu', 'joe+test@gustavus.edu '),
      array('joe+test@gustavus.edu', ' joe+test@gustavus.edu '),
    );
  }

  /**
   * @test
   * @dataProvider widontData
   */
  public function widont($expected, $value, $lastWordMaxLength = null)
  {
    $this->string->setValue($value);
    $this->assertSame($expected, $this->string->widont($lastWordMaxLength)->getValue());
  }

  /**
   * @return array
   */
  public static function widontData()
  {
    return array(
      array('test ', 'test '),
      array(' test', ' test'),
      array(' test ', ' test '),

      array('test&nbsp;test', 'test test'),
      array('test&nbsp;test', 'test test '),
      array(' test&nbsp;test', ' test test'),
      array(' test&nbsp;test', ' test test '),

      array('test&nbsp;test', 'test  test'),
      array('test&nbsp;test', 'test  test '),
      array(' test&nbsp;test', ' test  test'),
      array(' test&nbsp;test', ' test  test '),

      array('test test&nbsp;test', 'test test test'),
      array('test test&nbsp;test', 'test test test '),
      array(' test test&nbsp;test', ' test test test'),
      array(' test test&nbsp;test', ' test test test '),

      array('test test&nbsp;test', 'test test  test'),
      array('test test&nbsp;test', 'test test  test '),
      array(' test test&nbsp;test', ' test test  test'),
      array(' test test&nbsp;test', ' test test  test '),

      // With max length
      array('test&nbsp;test', 'test test', 5),
      array('test&nbsp;test', 'test test', 4),
      array('test test', 'test test', 3),

      // With max length and ending tags
      array('<strong>test&nbsp;test</strong>', '<strong>test test</strong>', 5),
      array('<strong>test&nbsp;test</strong>', '<strong>test test</strong>', 4),
      array('<strong>test test</strong>', '<strong>test test</strong>', 3),
    );
  }

  /**
   * @test
   * @dataProvider possessiveData
   */
  public function possessive($expected, $value)
  {
    $this->string->setValue($value);
    $this->assertSame($expected, $this->string->possessive()->getValue());
  }

  /**
   * @return array
   */
  public static function possessiveData()
  {
    return array(
      array("Gustavus'", 'Gustavus'),
      array("Jesus'", 'Jesus'),
      array("Nick's", 'Nick'),
      array('my', 'I'),
      array("RYAN'S", 'RYAN'),
      array("the hero's", 'the hero'),
      array('my', 'my'),
      array('HIS', 'HIS'),
      array('His', 'He'),
      array('your', 'you'),
      array('Our', 'We'),
      array('my', 'i'),
      array('her', 'she'),
      array('her', 'her'),
      array('its', 'it'),
      array('their', 'they'),
      array('Joe\'s', 'Joe\'s'),
      array('', ''),
    );
  }

  /**
   * @test
   * @dataProvider stateData
   */
  public function abbreviateState($expected, $value)
  {
    $this->string->setValue($value);
    $this->assertSame($expected, $this->string->abbreviateState()->getValue());
  }

  /**
   * @return array
   */
  public static function stateData()
  {
    return array(
      array('OK', 'Oklahoma'),
      array('MN', 'Minnesota'),
      array('AK', 'Alaska'),
      array('AK', 'alaska'),
      array('MI', 'Michigan'),
      array('ON', 'ONTARIO'),
    );
  }

  /**
   * @test
   * @dataProvider queryStringData
   */
  public function splitQueryString($expected, $value)
  {
    $this->string->setValue($value);
    $this->assertSame($expected, $this->string->splitQueryString()->getValue());
  }

  /**
   * @return array
   */
  public static function queryStringData()
  {
    return [
      [['revisionNumber' => '1', 'oldestRevision' => '0'], '&revisionNumber=1&oldestRevision=0'],
      [['revisionNumber' => '1', 'oldestRevision' => '0'], '?revisionNumber=1&oldestRevision=0'],
      [['revisionNumber' => '1'], '?revisionNumber=1&oldestRevision='],
      [['revisionNumber' => '1'], '?revisionNumber=1&oldestRevision'],
      [['revisionNumber' => '1', 'oldestRevision' => '0'], 'revisionNumber=1&oldestRevision=0'],
      [['revisionNumbers' => ['1', '2']], '?revisionNumbers[]=1&revisionNumbers[]=2'],
    ];
  }



  /**
   * @test
   * @dataProvider findNearestInstanceData
   */
  public function testFindNearestInstance($string, $search, $offset, $expected)
  {
    $this->string->setValue($string);
    $result = $this->string->findNearestInstance($search, $offset);

    $this->assertSame($expected, $result);
  }

  public function findNearestInstanceData()
  {
    return [
      ['_========================', '/\|/', 0,  false],
      ['============_============', '/\|/', 12, false],
      ['========================_', '/\|/', 25, false],
      ['_=====|==================', '/\|/', 0,  6],
      ['======|=====_============', '/\|/', 12, 6],
      ['======|=================_', '/\|/', 25, 6],
      ['_=====|=========|========', '/\|/', 0,  6],
      ['======|=====_===|========', '/\|/', 12, 16],
      ['======|=========|=======_', '/\|/', 25, 16],
      ['_=====|=========|====|===', '/\|/', 0,  6],
      ['======|=====_===|====|===', '/\|/', 12, 16],
      ['======|=========|====|==_', '/\|/', 25, 21],

      ['_========================', '/\||\A|\z/', -25,  0],
      ['============_============', '/\||\A|\z/', -13, 0],
      ['========================_', '/\||\A|\z/', 25, 25],
      ['_=====|==================', '/\||\A|\z/', -25,  0],
      ['======|=====_============', '/\||\A|\z/', -13, 6],
      ['======|=================_', '/\||\A|\z/', 25, 25],
      ['_=====|=========|========', '/\||\A|\z/', -25,  0],
      ['======|=====_===|========', '/\||\A|\z/', -13, 16],
      ['======|=========|=======_', '/\||\A|\z/', 25, 25],
      ['_=====|=========|====|===', '/\||\A|\z/', -25,  0],
      ['======|=====_===|====|===', '/\||\A|\z/', -13, 16],
      ['======|=========|====|==_', '/\||\A|\z/', 25, 25]
    ];
  }

  /**
   * @test
   */
  public function testFindNearestInputFailures()
  {
    $failInputs = [
      ['_========================', '', 0,  false],
      ['_========================', null, 0,  false],
      ['_========================', 123432, 0,  false],
      ['_========================', ['multi-input', 'not supported. :('], 0,  false],
      ['_========================', '/\|/', 45.5,  false],
      ['_========================', '/\|/', '50',  false],
      ['_========================', '/\|/', null,  false],
      ['_========================', '/\|/', true,  false],
    ];

    foreach ($failInputs as $input) {
      try {
        $this->testFindNearestInstance($input[0], $input[1], $input[2], $input[3]);
        $this->assertTrue(false);
      } catch (\InvalidArgumentException $e) {
        $this->assertTrue(true);
      }
    }

  }


  /**
   * @test
   * @dataProvider excerptData
   */
  public function textExcerpt($string, $length, $offset, $expected)
  {
    $this->string->setValue($string);
    $this->string->excerpt($length, $offset);

    $this->assertSame($expected, $this->string->getValue());
  }

  public function excerptData()
  {
    return [
      // Unchanging stuff (no spaces to cleanly break on)...
      ['01234567890123456789012345678901234567890123456789', 10,  0, '01234567890123456789012345678901234567890123456789'],
      ['01234567890123456789012345678901234567890123456789', 10, 10, '01234567890123456789012345678901234567890123456789'],
      ['01234567890123456789012345678901234567890123456789', 20, 20, '01234567890123456789012345678901234567890123456789'],

      // Truncate tail...
      ['0123456789 012345678901234567890123456789 0123456789', 25, 0, '0123456789...'],
      ['0123456789 012345678901234567890123456789 0123456789', 40, 0, '0123456789 012345678901234567890123456789...'],
      ['0123456789 012345678901234567890123456789 0123456789', 47, 0, '0123456789 012345678901234567890123456789 0123456789'],
      ['0123456789 012345678901234567890123456789 0123456789', -10, 0, '0123456789 012345678901234567890123456789...'],

      // Truncate head...
      ['0123456789 012345678901234567890123456789 0123456789', 20, 25, '...012345678901234567890123456789...'],
      ['0123456789 012345678901234567890123456789 0123456789', 30, 40, '...0123456789'],
      ['0123456789 012345678901234567890123456789 0123456789', 25, -35, '...012345678901234567890123456789...'],
      ['0123456789 012345678901234567890123456789 0123456789', -20, -35, '...012345678901234567890123456789...'],

      // Goofy inputs...
      ['01234 56789', 10, 400, '...56789'],
      ['0123456789 012345678901234567890123456789 0123456789', 600, 400, '0123456789 012345678901234567890123456789 0123456789'],
    ];
  }

  /**
   * @test
   */
  public function testExcerptInputFailures()
  {
    $failInputs = [
      ['867-5309', 'twelve', 0, 'not expecting anything because this should fail. Horribly.'],
      ['spaghettios', 0, 0, 'uh oh.'],
      ['asdf', 42, 'this isn\'t an offset at all!', 'fdsa'],
    ];

    foreach ($failInputs as $input) {
      try {
        $this->textExcerpt($input[0], $input[1], $input[2], $input[3]);
      } catch (\InvalidArgumentException $e) {
        continue;
      }

      $this->fail('An expected exception has not been raised.');
    }
  }




  /**
   * @test
   */
  public function testPrepend()
  {
    $this->string->setValue('123');
    $this->string->prepend('abc');

    $this->assertSame('abc123', $this->string->getValue());

    try {
      $this->string->setValue('123');
      $this->string->prepend(array('NEIN!'));
    } catch (\InvalidArgumentException $e) {
      return;
    }

    $this->fail('An expected exception has not been raised.');
  }

  /**
   * @test
   */
  public function testAppend()
  {
    $this->string->setValue('abc');
    $this->string->append('123');

    $this->assertSame('abc123', $this->string->getValue());

    try {
      $this->string->setValue('abc');
      $this->string->append(array('iie!'));
      $this->assertTrue(false);
    } catch (\InvalidArgumentException $e) {
      return;
    }

    $this->fail('An expected exception has not been raised.');
  }

  /**
   * @test
   */
  public function testEncaseInTag()
  {
    $this->string->setValue('abc');
    $this->string->encaseInTag('html');

    $this->assertSame('<html>abc</html>', $this->string->getValue());

    $failInputs = [
      null,
      747,
      true,
      ['a', 'b', 'c'],
      'uhoh-',
      '789badjoke',
      ':(',
      'no spaces or',
      'punctuation!'
    ];

    foreach ($failInputs as $input) {
      try {
        $this->string->setValue('abc');
        $this->string->encaseInTag($input);
        $this->assertTrue(false);
      } catch (\InvalidArgumentException $e) {
        continue;
      }

      $this->fail('An expected exception has not been raised.');
    }
  }















  /**
   * @test
   */
  public function PhoneWithNumbersThatAreTooShort()
  {
    $this->assertSame('1', (new String('1'))->phone()->getValue());
    $this->assertSame('12', (new String('12'))->phone()->getValue());
    $this->assertSame('123', (new String('123'))->phone()->getValue());
  }

  /**
   * @test
   */
  public function PhoneWithNumbersThatAreTooLong()
  {
    $this->assertSame('11111111112222222222', (new String('11111111112222222222'))->phone()->getValue());
  }

  /**
   * @test
   */
  public function PhoneWithOnCampusNumbers()
  {
    // $this->assertSame('507-933-1234', (new String('1234'))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String(' 1234'))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String('1234 '))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String(' 1234 '))->phone()->getValue());

    // $this->assertSame('507-933-1234', (new String('x1234'))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String(' x1234'))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String('x1234 '))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String(' x1234 '))->phone()->getValue());

    // $this->assertSame('507-933-1234', (new String('9331234'))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String(' 9331234'))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String('9331234 '))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String(' 9331234 '))->phone()->getValue());

    // $this->assertSame('507-933-1234', (new String('933-1234'))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String(' 933-1234'))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String('933-1234 '))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String(' 933-1234 '))->phone()->getValue());

    // $this->assertSame('507-933-1234', (new String('5079331234'))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String(' 5079331234'))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String('5079331234 '))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String(' 5079331234 '))->phone()->getValue());

    // $this->assertSame('507-933-1234', (new String('507-933-1234'))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String(' 507-933-1234'))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String('507-933-1234 '))->phone()->getValue());
    // $this->assertSame('507-933-1234', (new String(' 507-933-1234 '))->phone()->getValue());

    // $this->assertSame('507-933-1234', (new String('1234'))->phone('short')->getValue());
    $this->assertSame('<span class="nodisplay">507-</span>933-1234', (new String('1234'))->phone('medium')->getValue());
    $this->assertSame('507-933-1234', (new String('1234'))->phone('long')->getValue());

    $this->assertSame('<a href="tel:1-507-933-1234">507-933-1234</a>', (new String('1234'))->phone('mobile')->getValue());
    $this->assertSame('<a href="tel:1-507-933-1234">507-933-1234</a>', (new String(' 1234'))->phone('mobile')->getValue());
    $this->assertSame('<a href="tel:1-507-933-1234">507-933-1234</a>', (new String('1234 '))->phone('mobile')->getValue());
    $this->assertSame('<a href="tel:1-507-933-1234">507-933-1234</a>', (new String(' 1234 '))->phone('mobile')->getValue());

    $this->assertSame('507-933-1234', (new String('1234'))->phone('short', true)->getValue());
    $this->assertSame('507-933-1234', (new String(' 1234'))->phone('short', true)->getValue());
    $this->assertSame('507-933-1234', (new String('1234 '))->phone('short', true)->getValue());
    $this->assertSame('507-933-1234', (new String(' 1234 '))->phone('short', true)->getValue());

    $this->assertSame('933-1234', (new String('1234'))->phone('medium', true)->getValue());
    $this->assertSame('507-933-1234', (new String('1234'))->phone('long', true)->getValue());

    $this->assertSame('507-933-1234', (new String('9331234'))->phone('short', true)->getValue());
    $this->assertSame('933-1234', (new String('9331234'))->phone('medium', true)->getValue());
    $this->assertSame('507-933-1234', (new String('9331234'))->phone('long', true)->getValue());

    $this->assertSame('507-933-1234', (new String('5079331234'))->phone('short', true)->getValue());
    $this->assertSame('933-1234', (new String('5079331234'))->phone('medium', true)->getValue());
    $this->assertSame('507-933-1234', (new String('5079331234'))->phone('long', true)->getValue());
  }

  /**
   * @test
   */
  public function PhoneWithOffCampusNumbers()
  {
    $this->assertSame('<span class="nodisplay">507-</span>931-1234', (new String('9311234'))->phone()->getValue());
    $this->assertSame('<span class="nodisplay">507-</span>931-1234', (new String(' 9311234'))->phone()->getValue());
    $this->assertSame('<span class="nodisplay">507-</span>931-1234', (new String('9311234 '))->phone()->getValue());
    $this->assertSame('<span class="nodisplay">507-</span>931-1234', (new String(' 9311234 '))->phone()->getValue());

    $this->assertSame('<span class="nodisplay">507-</span>931-1234', (new String('931-1234'))->phone()->getValue());
    $this->assertSame('<span class="nodisplay">507-</span>931-1234', (new String(' 931-1234'))->phone()->getValue());
    $this->assertSame('<span class="nodisplay">507-</span>931-1234', (new String('931-1234 '))->phone()->getValue());
    $this->assertSame('<span class="nodisplay">507-</span>931-1234', (new String(' 931-1234 '))->phone()->getValue());

    $this->assertSame('555-555-1234', (new String('5555551234'))->phone()->getValue());
    $this->assertSame('555-555-1234', (new String(' 5555551234'))->phone()->getValue());
    $this->assertSame('555-555-1234', (new String('5555551234 '))->phone()->getValue());
    $this->assertSame('555-555-1234', (new String(' 5555551234 '))->phone()->getValue());

    $this->assertSame('555-555-1234', (new String('5555551234'))->phone('short')->getValue());
    $this->assertSame('555-555-1234', (new String('5555551234'))->phone('medium')->getValue());
    $this->assertSame('555-555-1234', (new String('5555551234'))->phone('long')->getValue());

    $this->assertSame('<span class="nodisplay">507-</span>555-1234', (new String('5551234'))->phone('short')->getValue());
    $this->assertSame('<span class="nodisplay">507-</span>555-1234', (new String('5551234'))->phone('medium')->getValue());
    $this->assertSame('507-555-1234', (new String('5551234'))->phone('long')->getValue());

    $this->assertSame('931-1234', (new String('9311234'))->phone('short', true)->getValue());
    $this->assertSame('931-1234', (new String(' 9311234'))->phone('short', true)->getValue());
    $this->assertSame('931-1234', (new String('9311234 '))->phone('short', true)->getValue());
    $this->assertSame('931-1234', (new String(' 9311234 '))->phone('short', true)->getValue());

    $this->assertSame('931-1234', (new String('9311234'))->phone('medium', true)->getValue());
    $this->assertSame('507-931-1234', (new String('9311234'))->phone('long', true)->getValue());

    $this->assertSame('931-1234', (new String('9311234'))->phone('short', true)->getValue());
    $this->assertSame('931-1234', (new String('9311234'))->phone('medium', true)->getValue());
    $this->assertSame('507-931-1234', (new String('9311234'))->phone('long', true)->getValue());

    $this->assertSame('555-931-1234', (new String('5559311234'))->phone('short', true)->getValue());
    $this->assertSame('555-931-1234', (new String('5559311234'))->phone('medium', true)->getValue());
    $this->assertSame('555-931-1234', (new String('5559311234'))->phone('long', true)->getValue());
  }

  /**
   * @test
   */
  public function PhoneWithDifferentDefaults()
  {
    $this->assertSame('555-931-1234', (new String('1234'))->phone('short', false, '555', '931')->getValue());
    $this->assertSame('555-931-1234', (new String('9311234'))->phone('short', false, '555', '931')->getValue());
    $this->assertSame('555-931-1234', (new String('5559311234'))->phone('short', false, '555', '931')->getValue());

    $this->assertSame('<span class="nodisplay">555-</span>931-1234', (new String('1234'))->phone('medium', false, '555', '931')->getValue());
    $this->assertSame('<span class="nodisplay">555-</span>931-1234', (new String('9311234'))->phone('medium', false, '555', '931')->getValue());
    $this->assertSame('<span class="nodisplay">555-</span>931-1234', (new String('5559311234'))->phone('medium', false, '555', '931')->getValue());

    $this->assertSame('555-931-1234', (new String('1234'))->phone('long', false, '555', '931')->getValue());
    $this->assertSame('555-931-1234', (new String('9311234'))->phone('long', false, '555', '931')->getValue());
    $this->assertSame('555-931-1234', (new String('5559311234'))->phone('long', false, '555', '931')->getValue());

    $this->assertSame('555-931-1234', (new String('1234'))->phone('short', true, '555', '931')->getValue());
    $this->assertSame('555-931-1234', (new String('9311234'))->phone('short', true, '555', '931')->getValue());
    $this->assertSame('555-931-1234', (new String('5559311234'))->phone('short', true, '555', '931')->getValue());

    $this->assertSame('931-1234', (new String('1234'))->phone('medium', true, '555', '931')->getValue());
    $this->assertSame('931-1234', (new String('9311234'))->phone('medium', true, '555', '931')->getValue());
    $this->assertSame('931-1234', (new String('5559311234'))->phone('medium', true, '555', '931')->getValue());

    $this->assertSame('555-931-1234', (new String('1234'))->phone('long', true, '555', '931')->getValue());
    $this->assertSame('555-931-1234', (new String('9311234'))->phone('long', true, '555', '931')->getValue());
    $this->assertSame('555-931-1234', (new String('5559311234'))->phone('long', true, '555', '931')->getValue());
  }

  /**
   * @test
   */
  public function PhoneWithWeirdFormat()
  {
    $this->assertSame('507-933-1234', (new String('1234'))->phone('arstieanrstien')->getValue());
  }

  /**
   * @test
   */
  public function PhoneWithZero()
  {
    $this->assertSame('', (new String('0'))->phone()->getValue());
  }

  /**
   * @test
   */
  public function PhoneTooLongFromTickeTrak()
  {
    $this->assertSame('', (new String('123456789123456789'))->phone('tickeTrak')->getValue());
  }

  /**
   * @test
   */
  public function IsOncampusPhoneNumber()
  {
    $this->assertTrue($this->call(new String('7072'), 'isOncampusPhoneNumber'));
    $this->assertTrue($this->call(new String('8888'), 'isOncampusPhoneNumber'));
    $this->assertTrue($this->call(new String('9337072'), 'isOncampusPhoneNumber'));
    $this->assertTrue($this->call(new String('5079337072'), 'isOncampusPhoneNumber'));
    $this->assertTrue($this->call(new String('507-933-7072'), 'isOncampusPhoneNumber'));

    $this->assertFalse($this->call(new String('763-389-1787'), 'isOncampusPhoneNumber'));
    $this->assertFalse($this->call(new String('389-1787'), 'isOncampusPhoneNumber'));
    $this->assertFalse($this->call(new String('787'), 'isOncampusPhoneNumber'));
  }

  /**
   * @test
   * @dataProvider ByteStringToBytesProvider
   */
  public function ByteStringToBytes($stringVal, $expected)
  {
    $string = new String($stringVal);
    $this->assertInstanceOf('Gustavus\Utility\Number', $string->toBytes());
    $this->assertEquals($expected, $string->toBytes()->getValue());
  }

  /**
   * Data provider for ByteStringToBytes
   */
  public function ByteStringToBytesProvider()
  {
    return [
      ['32G',  34359738368],
      ['32gb', 34359738368],
      ['20m',  20971520],
      ['2Mb',  2097152],
      ['546k', 559104],
      ['54',   54]
    ];
  }

  /**
   * @test
   * @expectedException        \DomainException
   * @expectedExceptionMessage Must be a byte string.
   * @dataProvider ByteStringExceptionOnInvalidFormatProvider
   */
  public function ByteStringExceptionOnInvalidFormat($string)
  {
    (new String($string))->toBytes();
  }

  /**
   * Data provider for ByteStringExceptionOnInvalidFormat
   */
  public function ByteStringExceptionOnInvalidFormatProvider()
  {
    return [
      ['bad string'],
      ['-32m'],
      ['43q'],
      ['43qb'],
      ['2 Mb', 2097152]
    ];
  }

  /**
   * @test
   * @dataProvider summaryData
   */
  public function summary($expected, $value, $baselength = 200, $wrapperElement = '', $append = '', $plainText = false, $newline = ' ')
  {
    $this->string->setValue($value);
    $actual = $this->string->summary($baselength, $wrapperElement, $append, $plainText, $newline)->getValue();
    $this->assertSame($expected, $actual);
  }

  /**
   * Summary dataProvider
   * @return  array
   */
  public function summaryData()
  {
    return [
      ['arst arst arst arst', ' arst arst  arst    arst '],
      [$this->textShortened . '&#8230;', $this->longText, 50],
      ['<p>' . $this->textShortened . '&#8230;</p>', $this->longText, 50, 'p'],
      [$this->shortText, $this->shortText, 50],
      ['<p>' . $this->shortText . '</p>', $this->shortText, 50, 'p'],
      ['', $this->shortText, 0],
      ['', $this->shortText, -10],
      ['', '', 100],
      ['Test string', 'Test string', 6],
    ];
  }

  /**
   * @test
   * @dataProvider nameData
   */
  public function name($expected, $first, $middle, $last, $preferred = '', $method = 'short', $lastNameFirst = false, $lastNameInitialOnly = false, $graduationYear = null, $maiden = '', $beforeMaiden = '(', $afterMaiden = ')')
  {
    $this->string->setValue($first);
    $this->assertSame($expected, $this->string->name($middle, $last, $preferred, $method, $lastNameFirst, $lastNameInitialOnly, $graduationYear, $maiden, $beforeMaiden, $afterMaiden)->getValue());
  }

  /**
   * name dataProvider
   * @return array
   */
  public function nameData()
  {
    return [
      ['Joseph', 'Joseph', null, null],
      ['Joseph Lencioni', 'Joseph', null, 'Lencioni'],
      ['Joseph Lencioni', 'Joseph', 'Daniel', 'Lencioni'],
      ['Joe Lencioni', 'Joseph', 'Daniel', 'Lencioni', 'Joe'],
      ['Lencioni, Joseph', 'Joseph', 'Daniel', 'Lencioni', null, null, true],
      ['Lencioni, Joe', 'Joseph', 'Daniel', 'Lencioni', 'Joe', null, true],
      ['Joseph L', 'Joseph', 'Daniel', 'Lencioni', null, null, null, true],
      ['Joe L', 'Joseph', 'Daniel', 'Lencioni', 'Joe', null, null, true],
      ['L, Joseph', 'Joseph', 'Daniel', 'Lencioni', null, null, true, true],
      ['L, Joe', 'Joseph', 'Daniel', 'Lencioni', 'Joe', null, true, true],
      ['Joe Lencioni ’05', 'Joseph', 'Daniel', 'Lencioni', 'Joe', null, null, null, '2005'],

      ['Joe Lencioni', 'Joseph', 'Daniel', 'Lencioni', 'Joe', null, null, null, '0'],
      ['Joe Lencioni', 'Joseph', 'Daniel', 'Lencioni', 'Joe', null, null, null, '1'],
      ['Joe Lencioni', 'Joseph', 'Daniel', 'Lencioni', 'Joe', null, null, null, '2'],
      ['Joe Lencioni 1913', 'Joseph', 'Daniel', 'Lencioni', 'Joe', null, null, null, 1913],

      ['Joseph', 'Joseph', null, null, null, 'full'],
      ['Joseph Lencioni', 'Joseph', null, 'Lencioni', null, 'full'],
      ['Joseph Daniel Lencioni', 'Joseph', 'Daniel', 'Lencioni', null, 'full'],
      ['Joseph Daniel Lencioni', 'Joseph', 'Daniel', 'Lencioni', 'Joe', 'full'],

      ['Joseph', 'Joseph', null, null, null, 'verbose'],
      ['Joseph Lencioni', 'Joseph', null, 'Lencioni', null, 'verbose'],
      ['Joseph Daniel Lencioni', 'Joseph', 'Daniel', 'Lencioni', null, 'verbose'],
      ['Joseph (Joe) Daniel Lencioni', 'Joseph', 'Daniel', 'Lencioni', 'Joe', 'verbose'],
      ['Joe Daniel Lencioni', '', 'Daniel', 'Lencioni', 'Joe', 'verbose'],

      ['Samantha (Sam) Arlene Lencioni ’07 (Samantha (Sam) Arlene Matthes)', 'Samantha', 'Arlene', 'Lencioni', 'Sam', 'verbose', false, false, 2007, 'Matthes'],
    ];
  }

  /**
   * @test
   * @dataProvider linkifyProvider
   */
  public function linkify($raw, $expected, $attribs)
  {
    $this->string->setValue($raw);

    $this->string->linkify($attribs, true, true, true);

    $this->assertSame($expected, $this->string->getValue());
  }

  /**
   * Provides data for linkify
   */
  public static function linkifyProvider()
  {
    return array(
      [
        'This is a http://gac.edu',
        'This is a <a href="http://gac.edu">http://gac.edu</a>',
        array()
      ],
      [
        'This is a http://gac.edu/test and twitter.com/5492',
        'This is a <a href="http://gac.edu/test">http://gac.edu/test</a> and <a href="http://twitter.com/5492">twitter.com/5492</a>',
        array()
      ],
      [
        'This is a https://gac.edu/test and twitter.com/5492',
        'This is a <a href="https://gac.edu/test">https://gac.edu/test</a> and <a href="http://twitter.com/5492">twitter.com/5492</a>',
        array()
      ],
      [
        'https://gac.edu/test and twitter.com/5492',
        '<a href="https://gac.edu/test">https://gac.edu/test</a> and <a href="http://twitter.com/5492">twitter.com/5492</a>',
        array()
      ],
      [
        'This is a test@gac.edu',
        'This is a <a href="mailto:test@gac.edu">test@gac.edu</a>',
        array()
      ],
      [
        'This is a test of a phone number 933-7072 the program will find valid phone numbers 3432543 but not invalid ones 345642. It supports international numbers as well, +31 42 123 4567, cool huh? Also add extensions 543-302-2935 x23',
        'This is a test of a phone number <a href="tel:9337072p">933-7072</a> the program will find valid phone numbers <a href="tel:3432543p">3432543</a> but not invalid ones 345642. It supports international numbers as well, <a href="tel:31421234567p">+31 42 123 4567</a>, cool huh? Also add extensions <a href="tel:5433022935p23">543-302-2935 x23</a>',
        array()
      ],
      [
        'This one.co/ has some@attributes.com so 555-3210',
        'This <a href="http://one.co/" class="vanilla" rel="something">one.co/</a> has <a href="mailto:some@attributes.com" class="vanilla" rel="something">some@attributes.com</a> so <a href="tel:5553210p" class="vanilla" rel="something">555-3210</a>',
        [
          'class' => 'vanilla',
          'rel' => 'something'
        ]
      ]
    );
  }

  /**
   * @test
   * @dataProvider linkifyDisableProvider
   */
  public function linkifyDisable($raw, $expected, $url, $email, $phone)
  {
    $this->string->setValue($raw);

    $this->string->linkify(array(), $url, $email, $phone);

    $this->assertSame($expected, $this->string->getValue());
  }

  /**
   * Provides data for linkifyDisable
   */
  public static function linkifyDisableProvider()
  {
    return array(
      [
        'This one.co/ has some@attributes.com so 555-3210',
        'This one.co/ has some@attributes.com so 555-3210',
        false, false, false
      ],
      [
        'This one.co/ has some@attributes.com so 555-3210',
        'This one.co/ has <a href="mailto:some@attributes.com">some@attributes.com</a> so <a href="tel:5553210p">555-3210</a>',
        false, true, true
      ],
      [
        'This one.co/ has some@attributes.com so 555-3210',
        'This <a href="http://one.co/">one.co/</a> has some@attributes.com so <a href="tel:5553210p">555-3210</a>',
        true, false, true
      ],
      [
        'This one.co/ has some@attributes.com so 555-3210',
        'This <a href="http://one.co/">one.co/</a> has <a href="mailto:some@attributes.com">some@attributes.com</a> so 555-3210',
        true, true, false
      ]
    );
  }

  /**
   * @test
   */
  public function extractImagesAndExtractFirstImage()
  {

    $html = '<p><img src="http://blog.gustavus.edu/files/image_1342-200x200.jpg"><img src="http://blog.gustavus.edu/files/image_1342-200x200.jpg">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fuga, sit libero. Maiores, dolores, sint sed fuga eum est delectus odio similique incidunt consectetur impedit voluptatem tempore eligendi minus hic natus! <img src="https://beta.gac.edu/gimli/w234/test/image.jpg"></p><img src="http://feeds.feedburner.com/~r/gustavus/news/~4/qVkAZSjO2tY" height="1" width="1"/>';

    $this->string->setValue($html);

    $this->assertEquals(array(
      'http://blog.gustavus.edu/files/image_1342-200x200.jpg',
      'https://beta.gac.edu/test/image.jpg'
    ), $this->string->extractImages());

    $this->assertEquals(array(
      'http://blog.gustavus.edu/files/image_1342-200x200.jpg',
      'http://blog.gustavus.edu/files/image_1342-200x200.jpg',
      'https://beta.gac.edu/test/image.jpg'
    ), $this->string->extractImages(false));

    $this->assertEquals('http://blog.gustavus.edu/files/image_1342-200x200.jpg', $this->string->extractFirstImage());

    $this->string->setValue('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Necessitatibus, culpa nemo iure perspiciatis illo excepturi ipsam modi rerum tempora quae error mollitia alias commodi suscipit eius hic aperiam nostrum maxime?');

    $this->assertFalse($this->string->extractImages());
    $this->assertFalse($this->string->extractFirstImage());

  }
}