<?php
/**
 * AccessLimitFixture
 *
 */
class AccessLimitFixture extends CommonTestFixture {

/**
 * {@inheritdoc}
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'aro' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36),
		'aco' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36),
		'aroid' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'acoid' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'expires' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'AROACO' => array('column' => array('aro', 'aco')),
			'AROACOID' => array('column' => array('aro', 'aco', 'aroid', 'acoid'))
		),
	);

/**
 * {@inheritdoc}
 */
	public $records = array(
		array(
			'aro' => 'IPv4',
			'aco' => 'controller.users.login',
			'aroid' => '127.0.0.1',
			'acoid' => 'admin',
			'expires' => '+23 hours'
		),
		array(
			'aro' => 'IPv4',
			'aco' => 'controller.users.login',
			'aroid' => '127.0.0.1',
			'acoid' => 'admin',
			'expires' => '+1 day'
		),
		array(
			'aro' => 'IPv4',
			'aco' => 'controller.users.login',
			'aroid' => '127.0.0.1',
			'acoid' => 'admin',
			'expires' => '+22 hours'
		),
		array(
			'aro' => 'IPv4',
			'aco' => 'controller.comments.post',
			'aroid' => '127.0.0.1',
			'acoid' => '',
			'expires' => '+3 hours'
		),
		array(
			'aro' => 'IPv4',
			'aco' => 'controller.comments.post',
			'aroid' => '127.0.0.1',
			'acoid' => '',
			'expires' => '+2 hours'
		),
		array(
			'aro' => 'IPv4',
			'aco' => 'controller.comments.post',
			'aroid' => '127.0.0.1',
			'acoid' => '',
			'expires' => '+4 hours'
		),
		array(
			'aro' => 'user',
			'aco' => 'model.report.export',
			'aroid' => '1',
			'acoid' => '',
			'expires' => '+1 month'
		),
		array(
			'aro' => 'user',
			'aco' => 'model.report.export',
			'aroid' => '1',
			'acoid' => '',
			'expires' => '+29 days'
		),
		array(
			'aro' => 'user',
			'aco' => 'model.report.export',
			'aroid' => '1',
			'acoid' => '',
			'expires' => '-2 days'
		),
		array(
			'aro' => 'user',
			'aco' => 'model.report.export',
			'aroid' => '1',
			'acoid' => '',
			'expires' => '-1 hour'
		),
		array(
			'aro' => 'user',
			'aco' => 'model.report.export',
			'aroid' => '1',
			'acoid' => '',
			'expires' => '-1 month'
		),
	);

	public function init() {
		foreach ($this->records as $k => $data) {
			$this->records[$k]['id'] = String::uuid();
			$this->records[$k]['expires'] = date('Y-m-d H:i:s', strtotime($data['expires']));
			$this->records[$k]['created'] = date('Y-m-d H:i:s');
		}
		parent::init();
	}
}
