<?php

App::uses('CommonTestCase', 'Common.TestSuite');

class AccessLimitTest extends CommonTestCase {

	public $fixtures = array(
		'plugin.security.access_limit'
	);

	public function setUp() {
		parent::setUp();
		$this->AccessLimit = ClassRegistry::init('Security.AccessLimit');
	}

	public function tearDown() {
		parent::tearDown();
		ClassRegistry::flush();
		unset($this->AccessLimit);
	}

	public function testCount() {
		$result = $this->AccessLimit->count('IPv4', 'controller.users.login', '127.0.0.1');
		$expected = 3;
		$this->assertEqual($result, $expected);

		$result = $this->AccessLimit->count('IPv4', null, '127.0.0.1');
		$expected = 6;
		$this->assertEqual($result, $expected);
	}

	public function testLimit() {
		$this->assertFalse($this->AccessLimit->limit('IPv4', 'controller.users.login', '127.0.0.1', null, 2));
		$this->assertTrue($this->AccessLimit->limit('IPv4', 'controller.users.login', '127.0.0.1', null, 4));
	}

	public function testFail() {
		$result = $this->AccessLimit->fail('IPv4', 'controller.users.login', '127.0.0.1', 'admin', '+1 day');
		$this->assertEquals($result['AccessLimit']['aro'], 'IPv4');
		$this->assertEquals($result['AccessLimit']['aco'], 'controller.users.login');
		$this->assertEquals($result['AccessLimit']['aroid'], '127.0.0.1');
		$this->assertEquals($result['AccessLimit']['acoid'], 'admin');
		$this->assertEquals($result['AccessLimit']['expires'], date('Y-m-d H:i:s', strtotime('+1 day')));

		$result = $this->AccessLimit->fail('IPv4', 'controller.users.login', '127.0.0.1', null, '+10 days');
		$this->assertEquals($result['AccessLimit']['aro'], 'IPv4');
		$this->assertEquals($result['AccessLimit']['aco'], 'controller.users.login');
		$this->assertEquals($result['AccessLimit']['aroid'], '127.0.0.1');
		$this->assertEquals($result['AccessLimit']['expires'], date('Y-m-d H:i:s', strtotime('+10 days')));
		$this->assertFalse(array_key_exists('acoid', $result['AccessLimit']));
	}

	public function testReset() {
		$this->AccessLimit->reset('IPv4', 'controller.users.login', '127.0.0.1');
		$this->assertEqual($this->AccessLimit->find('count', array('conditions' => array('aco' => 'controller.users.login'))), 0);
	}

	public function testCleanup() {
		$this->AccessLimit->cleanup();
		$this->assertEqual($this->AccessLimit->find('count', array('conditions' => array('aco' => 'model.report.export'))), 2);
	}

}
