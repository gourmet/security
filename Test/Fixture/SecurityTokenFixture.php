<?php
/**
 * SecurityTokenFixture
 *
 */
class SecurityTokenFixture extends CommonTestFixture {

/**
 * {@inheritdoc}
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'token' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 64),
		'foreign_model' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'foreign_key' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36),
		'foreign_data' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'foreign_flash' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 255),
		'foreign_field' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 255),
		'status' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 16),
		'expires' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => true),
			'TOKEN' => array('column' => array('token'), 'unique' => false),
			'MODEL' => array('column' => array('foreign_model', 'foreign_key'), 'unique' => false),
			'STATUS' => array('column' => array('status'), 'unique' => false)
		),
	);

/**
 * {@inheritdoc}
 */
	public $records = array(
		array(
			'token' => 'foobar',
			'status' => 'new',
			'expires' => '+3 days'
		),
		array(
			'token' => 'bar',
			'status' => 'new',
			'expires' => '+3 days'
		),
	);

/**
 * {@inheritdoc}
 */
	public function init() {
		foreach ($this->records as $k => $data) {
			$record = array(
				'id' => String::uuid(),
				'created' => date('Y-m-d H:i:s')
			);
			$this->records[$k] = array_merge($record, $data);
			$this->records[$k]['expires'] = date('Y-m-d H:i:s', strtotime($data['expires']));
		}
		parent::init();
	}

}
