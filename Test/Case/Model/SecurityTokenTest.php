<?php

App::uses('CommonTestCase', 'Common.TestSuite');

class SecurityTokenTest extends CommonTestCase {

	public $fixtures = array(
		'plugin.security.security_token'
	);

	public function setUp() {
		parent::setUp();
		$this->Token = ClassRegistry::init('Security.SecurityToken');
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function testBeforeValidate() {
		$this->Token->create();
		$this->Token->beforeValidate();
		$result = $this->Token->data;

		$this->assertTrue((boolean) $result);
		$this->assertRegExp('/^[a-z0-9]{20}$/i', $result['SecurityToken']['token']);
		$this->assertTrue(strtotime($result['SecurityToken']['expires']) > strtotime(date('Y-m-d H:i:s', strtotime('+2 days'))));

		$this->Token->create(array('token' => 'anything'));
		$this->Token->beforeValidate();
		$result = $this->Token->data;

		$this->assertTrue((boolean) $result);
		$this->assertEqual('anything', $result['SecurityToken']['token']);
		$this->assertTrue(strtotime($result['SecurityToken']['expires']) > strtotime(date('Y-m-d H:i:s', strtotime('+2 days'))));

		$this->Token->expires = '+1 year';
		$this->Token->create();
		$this->Token->beforeValidate();
		$result = $this->Token->data;

		$this->assertTrue((boolean) $result);
		$this->assertRegExp('/^[a-z0-9]{20}$/i', $result['SecurityToken']['token']);
		$this->assertTrue(strtotime($result['SecurityToken']['expires']) > strtotime(date('Y-m-d H:i:s', strtotime('+364 days'))));

		$this->Token->create(array('expires' => date('Y-m-d H:i:s', strtotime('+1 week'))));
		$this->Token->beforeValidate();
		$result = $this->Token->data;

		$this->assertTrue((boolean) $result);
		$this->assertRegExp('/^[a-z0-9]{20}$/i', $result['SecurityToken']['token']);
		$this->assertTrue(strtotime($result['SecurityToken']['expires']) > strtotime(date('Y-m-d H:i:s', strtotime('+6 days'))));
	}

	public function testCheck() {
		$result = $this->Token->check('foobar', 'new');
		$this->assertTrue($result);
		$this->assertNotEmpty($this->Token->id);
		$this->assertTrue((boolean) $this->Token->find('count', array('conditions' => array('token' => 'foobar', 'status' => 'used'), 'recursive' => -1)));


		$result = $this->Token->check('foobar');
		$this->assertNotEmpty($result);
		$this->assertFalse($this->Token->id);
		$this->assertFalse((boolean) $this->Token->find('count', array('conditions' => array('token' => 'foobar'), 'recursive' => -1)));
	}

	public function testFindToken() {
		$result = $this->Token->find('token', 'foobar');
		$this->assertTrue(is_array($result));
		$this->Token->id = $result['SecurityToken']['id'];
		$this->assertTrue(strtotime($this->Token->field('expires')) > strtotime(date('Y-m-d H:i:s', strtotime('+2 days'))));

		$this->Token->save();
		$result = $this->Token->find('token', array('expires >' => date('Y-m-d H:i:s')));
		$this->assertTrue(is_array($result));
		$this->assertEqual(count($result), 2);
	}
}
