<?php

App::uses('CommonTestCase', 'Common.TestSuite');
App::uses('HttpBL', 'Security.Lib');

class HttpBLTest extends CommonTestCase {

	protected $_testsRequireConnection = array(
		'testLastSeen',
		'testQueryNonResult',
		'testQueryOfCachedClient',
		'testQueryOfSelfClient',
		'testQueryWithMissingApiKey',
		'testThreatScore',
		'testTypeOf',
	);

	public function setUp() {
		$this->apiKey = (string) Configure::read('Security.HttpBL.apiKey');
		$this->HttpBL = new HttpBL($this->apiKey);
	}

	public function tearDown() {
		unset($this->HttpBL);
	}

	public function data() {

	}

	public function testConstructor() {
		// if (empty($this->apiKey)) {
		// 	$this->markTestSkipped();
		// }

		$this->HttpBL = new HttpBL('some_test_key');

		$result = $this->HttpBL->apiKey;
		$expected = 'some_test_key';
		$this->assertEqual($result, $expected);
	}

	public function testLastSeen() {
		$result = $this->HttpBL->lastSeen('127.10.1.1');
		$expected = 10;
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->lastSeen('127.20.1.1');
		$expected = 20;
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->lastSeen('127.40.1.1');
		$expected = 40;
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->lastSeen('127.80.1.1');
		$expected = 80;
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->lastSeen('127.1.1.0');
		$expected = 0;
		$this->assertEqual($result, $expected);
	}

	public function testQueryNonResult() {
		$this->expectException('Exception', __d('security', "No record found in the http:BL database for '127.0.0.1'."));
		$result = $this->HttpBL->query('127.0.0.1');
	}

	public function testQueryOfCachedClient() {
		$result = $this->HttpBL->query('127.1.1.0');
		$expected = $this->HttpBL->query('127.1.1.0');
		$this->assertEqual($result, $expected);

		$this->assertTrue(isset($this->HttpBL->responsesCache['127.1.1.0']));
		$this->assertEqual($this->HttpBL->responsesCache['127.1.1.0'], $expected);
	}

	public function testQueryOfSelfClient() {
		$_SERVER['REMOTE_ADDR'] = '127.1.1.0';

		$result = $this->HttpBL->query();
		$expected = array('age' => 1, 'level' => 1, 'type' => 0);
		$this->assertEqual($result, $expected);
	}

	public function testQueryWithMissingApiKey() {
		$this->HttpBL->apiKey = null;
		$this->expectException('Exception', __d('security', "Missing the 'apiKey' for the http:BL database to be queried."));
		$this->HttpBL->query();
	}

	public function testThreatScore() {
		$result = $this->HttpBL->threatScore('127.1.10.1');
		$expected = 10;
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->threatScore('127.1.20.1');
		$expected = 20;
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->threatScore('127.1.40.1');
		$expected = 40;
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->threatScore('127.1.80.1');
		$expected = 80;
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->threatScore('127.1.1.0');
		$expected = 0;
		$this->assertEqual($result, $expected);
	}

	public function testTypeOf() {
		$result = $this->HttpBL->typeOf('127.1.1.0');
		$expected = 'Search Engine';
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->typeOf('127.1.1.1');
		$expected = 'Suspicious';
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->typeOf('127.1.1.2');
		$expected = 'Harvester';
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->typeOf('127.1.1.3');
		$expected = 'Harvester & Suspicious';
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->typeOf('127.1.1.4');
		$expected = 'Comment Spammer';
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->typeOf('127.1.1.5');
		$expected = 'Comment Spammer & Suspicious';
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->typeOf('127.1.1.6');
		$expected = 'Comment Spammer & Harvester';
		$this->assertEqual($result, $expected);

		$result = $this->HttpBL->typeOf('127.1.1.7');
		$expected = 'Comment Spammer & Harvester & Suspicious';
		$this->assertEqual($result, $expected);
	}

}
