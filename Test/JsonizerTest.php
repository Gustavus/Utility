<?php
/**
 * @package Utility
 * @subpackage Test
 * @author Nicholas Dobie <ndobie@gustavus.edu>
 */
namespace Gustavus\Utility\Test;

use Gustavus\Test\Test,
    Gustavus\Utility\Jsonizer;


/**
 *Test for the Jsonizer
 *
 * @package Utility
 * @subpackage Test
 * @author Nicholas Dobie <ndobie@gustavus.edu>
 */
class JsonizerTest extends Test
{

  private $namespace = '\Gustavus\Utility\Jsonizer';

  private $overrides;

  public $headerSent;
  public $lastHeader = null;

  public function setUp()
  {

    $_SERVER['REQUEST_METHOD'] = 'GET';

    $this->overrides = [];

    $test = $this;

    $this->overrides[] = override_function('headers_sent', function () use (&$test) {
      return $test->headerSent;
    });

    $this->overrides[] = override_function('header', function ($header) use (&$test) {
      $test->lastHeader = $header;
    });

    $this->headerSent = true;
  }

  public function tearDown()
  {
    unset($this->overrides);
  }

  /**
   * @test
   */
  public function picksUpOnJsonpRequest()
  {

    $_GET['callback'] = 'jQuery';

    $this->assertTrue($this->call($this->namespace, 'isJSONP'));

  }

  /**
   * @test
   */
  public function picksUpOnJsonRequest()
  {
    $this->assertFalse($this->call($this->namespace, 'isJSONP'));
    $_GET['somethingElse'] = 'jQuery';
    $this->assertFalse($this->call($this->namespace, 'isJSONP'));
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $this->assertFalse($this->call($this->namespace, 'isJSONP'));
  }

  /**
   * @test
   */
  public function refusesNonGustavusDomains()
  {
    $array = array('success' => false);
    $_SERVER['HTTP_REFERER'] = 'http://nickdobie.com/';

    $results = $this->call($this->namespace, 'checkOrigin', array($array));

    $this->assertThat(
        $results,
        $this->logicalNot(
            $this->equalTo($array)
        )
    );
  }

  /**
   * @test
   * @dataProvider AllowsGustavusDomainsProvider
   */
  public function allowsGustavusDomains($domain)
  {

    $array = array('success' => false);
    $_SERVER['HTTP_REFERER'] = $domain;

    $results = $this->call($this->namespace, 'checkOrigin', array($array));

    $this->assertThat(
        $results,
        $this->equalTo($array)
    );
  }

  /**
   * Data provider for AllowsGustavusDomains
   */
  public function allowsGustavusDomainsProvider()
  {
    return [
      ['http://gustavus.edu'],
      ['http://gac.edu/'],
      ['https://beta.gac.edu'],
      ['https://api.gustavus.edu/']
    ];
  }

  /**
   * @test
   * @dataProvider CheckJsonFormatProvider
   */
  public function checkJsonFormat($raw, $compiled)
  {
    $this->assertJsonStringEqualsJsonString(Jsonizer::toJSON($raw), $compiled);
  }

  /**
   * @test
   * @dataProvider CheckJsonFormatProvider
   */
  public function checkJsonpFormat($raw, $compiled)
  {
    $_GET['callback'] = 'jQuery_1234';

    $this->assertThat(Jsonizer::toJSON($raw), $this->equalTo('jQuery_1234(' . $compiled . ')'));

  }

  /**
   * Data provider for CheckJsonFormat and CheckJsonpFormat
   */
  public function checkJsonFormatProvider()
  {
    return [
      [
        ['success' => true],
        '{"success":true}'
      ],
      [
        ['string' => 'this is a string'],
        '{"string":"this is a string"}'
      ],
      [
        ['one'=>1,'two'=>'2'],
        '{"one":1,"two":"2"}'
      ],
      [
        ['nested'=>['items'=>true]],
        '{"nested":{"items":true}}'
      ]
    ];
  }

  /**
   * @test
   */
  public function dontSendHeadersIfAlreadySent()
  {

    $this->call($this->namespace, 'setHeaders');

    $this->assertNull($this->lastHeader);
  }

  /**
   * @test
   */
  public function setJsonpHeaders()
  {

    $this->headerSent = false;

    $_GET['callback'] = 'jQuery';

    $this->call($this->namespace, 'setHeaders');

    $this->assertEquals('Content-Type: application/javascript; charset=utf-8', $this->lastHeader);
  }

  /**
   * @test
   */
  public function setJsonHeaders()
  {

    $this->headerSent = false;


    $this->call($this->namespace, 'setHeaders');

    $this->assertEquals('Content-Type: application/json; charset=utf-8', $this->lastHeader);
  }

}