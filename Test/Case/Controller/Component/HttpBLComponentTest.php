<?php

App::uses('CommonTestCase', 'Common.TestSuite');
App::uses('Controller', 'Controller');
App::uses('HttpBLComponent', 'Security.Controller/Component');

class HttpBLComponentTestController extends Controller {

	public $components = array('Security.HttpBL');

}

class HttpBLComponentTest extends CommonTestCase {

	protected $_testsRequireConnection = array(
		'testIsCommentSpammer',
		'testIsHarvester',
		'testIsOfType',
		'testIsSearchEngine',
		'testIsSuspicious',
		'testStartupWithUncookiedClientThatMeetsThirdCondition',
		'testStartupWithUncookiedValidClient',
		'testStartupWithValidCookiedClient',
	);

	private $__ip = null;

	public function setUp() {
		$this->__ip = $_SERVER['REMOTE_ADDR'];

		$this->Controller = new HttpBLComponentTestController(new CakeRequest, new CakeResponse);
		$this->Controller->constructClasses();

		$this->Controller->HttpBL = $this->getMock('HttpBLComponent', array('_stop'), array($this->Controller->Components));
		$this->Controller->HttpBL->Cookie = $this->getMock('CookieComponent', array('read', 'write'));

		$this->HttpBL = $this->Controller->HttpBL;

		$this->HttpBL->initialize($this->Controller);
	}

	public function tearDown() {
		$_SERVER['REMOTE_ADDR'] = $this->__ip;
		unset($this->Controller, $this->HttpBL);
	}

	public function testSettings() {
		$settings = array(
			'apiKey' => 'some_key',
			'cookie' => array('name' => 'TestHttpBL')
		);
		$HttpBL = new HttpBLComponent(new ComponentCollection(), $settings);
		$this->assertEqual($HttpBL->apiKey, $settings['apiKey']);
		$this->assertEqual($HttpBL->cookie, $settings['cookie']);
	}

	public function testIsCommentSpammer() {
		$_SERVER['REMOTE_ADDR'] = '127.1.1.4';

		$this->assertTrue($this->HttpBL->isCommentSpammer());
		$this->assertTrue($this->HttpBL->isCommentSpammer('127.1.1.5'));
		$this->assertTrue($this->HttpBL->isCommentSpammer('127.1.1.6'));
		$this->assertTrue($this->HttpBL->isCommentSpammer('127.1.1.7'));
		$this->assertFalse($this->HttpBL->isCommentSpammer('127.1.1.0'));
	}

	public function testIsHarvester() {
		$_SERVER['REMOTE_ADDR'] = '127.1.1.6';

		$this->assertTrue($this->HttpBL->isHarvester());
		$this->assertTrue($this->HttpBL->isHarvester('127.1.1.2'));
		$this->assertTrue($this->HttpBL->isHarvester('127.1.1.3'));
		$this->assertTrue($this->HttpBL->isHarvester('127.1.1.7'));
		$this->assertFalse($this->HttpBL->isHarvester('127.1.1.0'));
	}

	public function testIsOfType() {
		$_SERVER['REMOTE_ADDR'] = '127.1.1.0';

		$this->assertFalse($this->HttpBL->isOfType(null, 1));
		$this->assertFalse($this->HttpBL->isOfType(null, array(1, 2)));
		$this->assertTrue($this->HttpBL->isOfType(null, 0));
		$this->assertTrue($this->HttpBL->isOfType(null, array(0, 1, 2)));
		$this->assertEqual($this->HttpBL->isOfType(), (int) 0);
	}

	public function testIsSuspicious() {
		$_SERVER['REMOTE_ADDR'] = '127.1.1.1';

		$this->assertTrue($this->HttpBL->isSuspicious());
		$this->assertTrue($this->HttpBL->isSuspicious('127.1.1.3'));
		$this->assertTrue($this->HttpBL->isSuspicious('127.1.1.5'));
		$this->assertTrue($this->HttpBL->isSuspicious('127.1.1.7'));
		$this->assertFalse($this->HttpBL->isSuspicious('127.1.1.0'));
	}

	public function testIsSearchEngine() {
		$_SERVER['REMOTE_ADDR'] = '127.1.1.0';

		$this->assertTrue($this->HttpBL->isSearchEngine());
		$this->assertTrue($this->HttpBL->isSearchEngine($_SERVER['REMOTE_ADDR']));
		$this->assertFalse($this->HttpBL->isSearchEngine('127.1.1.6'));
	}

	public function testStartupWithUncookiedClientThatMeetsThirdCondition() {
		$this->HttpBL->conditions[] = array('age' => 1, 'level' => 1, 'type' => 2);

		$_SERVER['REMOTE_ADDR'] = '127.1.1.2';

		$this->HttpBL->Cookie->expects($this->once())
			->method('read')
			->with('HttpBL.blacklist')
			->will($this->returnValue(null));

		$this->HttpBL->Cookie->expects($this->at(1))
			->method('write')
			->with('HttpBL.response', array('age' => 1, 'level' => 1, 'type' => 2));

		$this->HttpBL->Cookie->expects($this->at(2))
			->method('write')
			->with('HttpBL.blacklist', 2);

		$this->HttpBL->expects($this->once())->method('_stop')->with();

		$this->HttpBL->startup($this->Controller);
	}

	public function testStartupWithUncookiedValidClient() {
		$_SERVER['REMOTE_ADDR'] = '127.1.1.0';

		$this->HttpBL->Cookie->expects($this->once())
			->method('read')
			->with('HttpBL.blacklist')
			->will($this->returnValue(null));

		$this->HttpBL->Cookie->expects($this->at(1))
			->method('write')
			->with('HttpBL.response', array('age' => 1, 'level' => 1, 'type' => 0));

		$this->HttpBL->Cookie->expects($this->at(2))
			->method('write')
			->with('HttpBL.blacklist', false);

		$this->HttpBL->startup($this->Controller);
	}

	public function testStartupWithValidCookiedClient() {
		$this->HttpBL->Cookie->expects($this->once())
			->method('read')
			->with('HttpBL.blacklist')
			->will($this->returnValue(false));

		$this->HttpBL->Cookie->expects($this->never())->method('write');

		$this->HttpBL->startup($this->Controller);
	}

}
