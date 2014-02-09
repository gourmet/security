<?php
/**
 * Security
 *
 * PHP 5
 *
 * Copyright 2013, Jad Bitar (http://jadb.io)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2013, Jad Bitar (http://jadb.io)
 * @link          http://github.com/gourmet/affiliates
 * @since         0.1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class InitSecurity extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 * @access public
 */
	public $description = "Init security's plugin tables";

/**
 * Actions to be performed
 *
 * @var array $migration
 * @access public
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'access_limits' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
					'aro' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36),
					'aco' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36),
					'aroid' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36),
					'acoid' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36),
					'expires' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
					'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'AROACO' => array('column' => array('aro', 'aco')),
						'AROACOID' => array('column' => array('aro', 'aco', 'aroid', 'acoid'))
					),
				),
				'security_tokens' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
					'token' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 64),
					'foreign_model' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
					'foreign_key' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
					'foreign_data' => array('type' => 'text', 'null' => true, 'default' => NULL),
					'foreign_flash' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 255),
					'foreign_field' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
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
				),

			)
		),
		'down' => array(
			'drop_table' => array(
				'access_limits',
				'security_tokens',
			)
		)
	);

/**
 * Before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function after($direction) {
		return true;
	}

}
