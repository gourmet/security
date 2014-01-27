<?php

App::uses('CommonTestCase', 'Common.TestSuite');
App::uses('Controller', 'Controller');
App::uses('AuthComponent', 'Controller/Component');
App::uses('AccessLimitComponent', 'Security.Controller/Component');

class AccessLimitTestController extends Controller {

	public $components = array(
		'Session',
		'Security.AccessLimit' => array(
			'all' => array(
				'duration' => '+1 day'
			),
			'post' => array(
				'login' => 3,
				'update' => array('limit' => 2, 'duration' => '+1 hour')
			),
			'get' => array(
				'foo',
				'view' => array('limit' => 4, 'duration' => '+1 month')
			)
		)
	);

}

class AccessLimitComponentTest extends CommonTestCase {

	public $fixtures = array(
		'plugin.security.access_limit'
	);

	public function setUp() {
		$this->Controller = $this->getMock(
			'AccessLimitTestController',
			array('dispatchMethod'),
			array(
				$this->getMock('CakeRequest', array('is'), array(null, false)),
				$this->getMock('CakeResponse')
			)
		);

		$this->Model = $this->getMockForModel('Security.AccessLimit');
		ClassRegistry::addObject('AccessLimit', $this->Model);

		$collection = new ComponentCollection();
		$collection->init($this->Controller);

		$this->Controller->Components->init($this->Controller);
		$this->AccessLimit = $this->Controller->AccessLimit;
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->AccessLimit, $this->Controller, $this->Model);
	}

	public function testConstructLimits() {
		$this->AccessLimit->constructLimits();

		$this->assertFalse(isset($this->AccessLimit->settings[AccessLimitComponent::ALL]));

		$this->assertEqual($this->AccessLimit->post['login']['duration'], '+1 day');
		$this->assertEqual($this->AccessLimit->post['login']['limit'], 3);
		$this->assertEqual($this->AccessLimit->post['login']['callback'], array('flash', array('security.access_limit.fail')));

		$this->assertEqual($this->AccessLimit->get['view']['duration'], '+1 month');
		$this->assertEqual($this->AccessLimit->get['view']['limit'], 4);
		$this->assertEqual($this->AccessLimit->get['view']['callback'], array('flash', array('security.access_limit.fail')));

		$this->assertEqual($this->AccessLimit->get['foo']['duration'], '+1 day');
		$this->assertEqual($this->AccessLimit->get['foo']['limit'], 5);
		$this->assertEqual($this->AccessLimit->get['foo']['callback'], array('flash', array('security.access_limit.fail')));
	}

	public function testStartupLimitLogin() {
		$this->Controller->request->expects($this->once())
			->method('is')
			->with('post')
			->will($this->returnValue(true));

		$this->Model->expects($this->once())
			->method('limit')
			->with('IPv4', 'controller.users.login', '127.0.0.1', null, 3)
			->will($this->returnValue(false));

		$this->Controller->expects($this->once())
			->method('dispatchMethod')
			->with('flash', array('security.access_limit.fail'));

		$this->Controller->name = 'Users';
		$this->Controller->request['action'] = 'login';
		$this->Controller->startupProcess();
	}

	public function testStartupLimitUpdateByBasicAuthenticatedUser() {
		$this->markTestIncomplete();
	}

	public function testStartupLimitUpdateByDigestAuthenticatedUser() {
		$this->markTestIncomplete();
	}

	public function testStartupLimitUpdateByFormAuthenticatedUser() {
		$this->Controller->Auth = $this->getMock('AuthComponent', array('user'), array($this->Controller->Components, array()));

		$this->Controller->request->expects($this->once())
			->method('is')
			->with('post')
			->will($this->returnValue(true));

		$this->Controller->Auth->staticExpects($this->any())
			->method('user')
			->with()
			->will($this->returnValue(array('id' => 1, 'name' => 'John Doe')));

		$this->Model->expects($this->once())
			->method('limit')
			->with('User', 'controller.users.update', 1, null, 2)
			->will($this->returnValue(false));

		$this->Controller->expects($this->once())
			->method('dispatchMethod')
			->with('flash', array('security.access_limit.fail'));

		$this->Controller->name = 'Users';
		$this->Controller->request['action'] = 'update';
		$this->Controller->startupProcess();
	}

	public function testStartupNoLimit() {
		$this->Controller->request->expects($this->at(0))
			->method('is')
			->with('post')
			->will($this->returnValue(false));

		$this->Controller->request->expects($this->at(1))
			->method('is')
			->with('get')
			->will($this->returnValue(true));

		$this->Model->expects($this->once())
			->method('limit')
			->with('IPv4', 'controller.posts.view', '127.0.0.1', null, 4)
			->will($this->returnValue(true));

		$this->Model->expects($this->once())
			->method('fail')
			->with('IPv4', 'controller.posts.view', '127.0.0.1', null, '+1 month');


		$this->Controller->expects($this->never())
			->method('dispatchMethod');

		$this->Controller->name  = 'posts';
		$this->Controller->request['action'] = 'view';
		$this->Controller->startupProcess();
	}

	public function testReset() {
		$this->Model->expects($this->at(0))
			->method('reset')
			->with(null, 'controller.posts.view', null, null);

		$this->Model->expects($this->at(1))
			->method('reset')
			->with('IPv4', 'controller.users.login', '127.0.0.1', null);

		$this->Controller->name  = 'posts';
		$this->Controller->request['action'] = 'admin_reset';
		$this->Controller->startupProcess();

		$this->AccessLimit->reset(null, 'controller.posts.view', null, null);
		$this->AccessLimit->reset(array('aro' => 'IPv4', 'aco' => 'controller.users.login'));
	}
}
