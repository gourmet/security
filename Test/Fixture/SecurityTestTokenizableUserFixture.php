<?php
/**
 * SecurityTestTokenizableUserFixture
 *
 */
class SecurityTestTokenizableUserFixture extends CommonTestFixture {

/**
 * {@inheritdoc}
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'email' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
		),
	);

	public $records = array(
		array(
			'email' => 'foo@bar.com'
		)
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
		}
		parent::init();
	}

}
