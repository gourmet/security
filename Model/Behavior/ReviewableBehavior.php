<?php
/**
 * ReviewableBehavior
 *
 * PHP 5
 *
 * Copyright 2013, Jad Bitar (http://jadb.io)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2013, Jad Bitar (http://jadb.io)
 * @link          http://github.com/gourmet/common
 * @since         0.1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('ModelBehavior', 'Model');

/**
 * Reviewable behavior
 *
 * @package       Security.Model.Behavior
 */
class ReviewableBehavior extends ModelBehavior {

	protected $_defaults = array(
		'assocName' => 'Reviewer',
		'assocKey' => 'id',
		'on' => 'both'
	);

	public function setup(Model $Model, $config = array()) {
		$config = (array) $config;

		if (Hash::numeric($config) && isset($config[0])) {
			$config = array('on' => $config[0]);
		}

		$this->settings[$Model->alias] = array_merge($this->_defaults, $config);
	}

	public function afterSave(Model $Model, $created = true) {
		$config = $this->settings[$Model->alias];

		if (
			empty($Model->data[$config['assocName']])
			|| ($created && !in_array($config['on'], array('both', 'create')))
			|| (!$created && !in_array($config['on'], array('both', 'update')))
		) {
			return true;
		}

		$Review = ClassRegistry::init('Security.SecurityReview');

		foreach ((array) $Model->data[$config['assocName']] as $reviewer) {
			$Review->add($Model->alias, $Model->id, $reviewer[$config['assocKey']]);
		}

		return true;
	}

}
