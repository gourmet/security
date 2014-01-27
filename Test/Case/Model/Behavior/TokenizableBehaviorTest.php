<?php

App::uses('AppModel', 'Model');
App::uses('CommonTestCase', 'Common.TestSuite');
App::uses('TokenizableBehavior', 'Common.Model/Behavior');

class SecurityTestTokenizableUser extends AppModel {

	public $actsAs = array('Security.Tokenizable' => array('email'));

	public $validate = array(
		'email' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				'required' => true,
				'allowEmpty' => false,
				'message' => "Can not be empty.",
			),
			'email' => array(
				'rule' => array('email'),
				'message' => "Invalid email address.",
			),
			'unique' => array(
				'rule' => array('isUnique', 'email'),
				'message' => "Already exists.",
			)
		),
	);

}

class TokenizableBehaviorTest extends CommonTestCase {

	public $fixtures = array(
		'plugin.security.security_test_tokenizable_user',
		'plugin.security.security_token'
	);

	public function setUp() {
		parent::setUp();
		$this->User = ClassRegistry::init('SecurityTestTokenizableUser');
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->User);
	}

	public function testSetup() {
		$result = $this->User->Behaviors->Tokenizable->settings[$this->User->alias];
		$expected = array('fields' => array('email' => null), 'on' => true);
		$this->assertEqual($result, $expected);

	}

	public function testTokenize() {
		$this->User->id = Hash::extract($this->User->findByEmail('foo@bar.com'), $this->User->alias . '.id');
		$result = $this->User->tokenize(array($this->User->alias => array('email' => 'bar@foo.com')));

		$this->assertEqual($result[$this->User->alias]['email'], 'bar@foo.com');
		$this->assertEqual($this->User->find('count', array('conditions' => array('email' => 'foo@bar.com'))), 1);
		$this->assertNotEmpty($this->User->SecurityToken->data);

		$result = $this->User->SecurityToken->check($this->User->SecurityToken->data[$this->User->SecurityToken->alias]['token']);

		$this->assertNotEmpty($result);
		$this->assertEmpty($this->User->findByEmail('foo@bar.com'));
	}

}
