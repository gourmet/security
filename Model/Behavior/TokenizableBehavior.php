<?php
/**
 * TokenizableBehavior
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
 * Tokenizable behavior
 *
 * @package       Security.Model.Behavior
 */
class TokenizableBehavior extends ModelBehavior {

/**
 * Defaults.
 *
 * @var array
 */
	public $_defaults = array(
		'fields' => array('email' => null, 'password' => null),
		'on' => true
	);

/**
 * {@inheritdoc}
 */
	public function setup(Model $Model, $config = array()) {
		if (!empty($config) && (!array_key_exists('fields', (array) $config) && Hash::numeric((array) array_keys($config)))) {
			$config = array('fields' => Hash::normalize($config));
		}

		$this->settings[$Model->alias] = array_merge($this->_defaults, $config);

		$this->bindSecurityToken($Model);
	}

/**
 * Binds `SecurityToken` as a `hasMany` association to current model if not already bound.
 *
 * @param Model $Model
 * @return void
 */
	public function bindSecurityToken(Model $Model) {
		if (!is_object($Model->SecurityToken)) {
			$Model->bindModel(array('hasMany' => array('SecurityToken' => array(
				'className' => 'Security.SecurityToken',
				'foreignKey' => 'foreign_key',
				'conditions' => array('SecurityToken.foreign_model' => $Model->name, 'SecurityToken.expires >' => date('Y-m-d H:i:s'))
			))));
		}
	}

/**
 * Tokenizes a save transaction by checking if it contains any tokenizable fields.
 *
 * @param Model $Model
 * @param array $data Data to save.
 * @param boolean|array $validate Either a boolean, or an array.
 *   If a boolean, indicates whether or not to validate before saving.
 *   If an array, can have following keys:
 *
 *   - flash: An array of `CommonAppController::flash()` options to use on token verification.
 *   - validate: Set to true/false to enable or disable validation.
 *   - fieldList: An array of fields you want to allow for saving.
 *   - callbacks: Set to false to disable callbacks. Using 'before' or 'after'
 *      will enable only those callbacks.
 *
 * @param array $fieldList List of fields to allow to be saved
 * @return mixed On success Model::$data if its not empty or true, false on failure
 */
	public function tokenize(Model $Model, $data = null, $validate = true, $fieldList = array()) {
		$config = $this->settings[$Model->alias];

		$flash = array();
		if (is_array($validate)) {
			$validate = array_merge(array('validate' => true), $validate);
			if (array_key_exists('flash', (array) $validate)) {
				$flash = $validate['flash'];
				unset($validate['flash']);
			}
		}

		if ($Model->getID()) {
			$initialState = $Model->find('first', array('conditions' => array($Model->primaryKey => $Model->id), 'recursive' => -1));
		} else {
			$Model->create();
		}

		$result = $Model->save($data, $validate, $fieldList);

		if (!$result) {
			return false;
		} else if (!isset($initialState) && !in_array($config['on'], array(true, 'create'))) {
			return $result;
		}

		$tokenizable = 0;

		foreach (array_keys($config['fields']) as $key) {
			if (array_key_exists($key, $data[$Model->alias])) {
				$tokenizable++;
				$field = $key;
			}
		}

		if (!$tokenizable) {
			return $result;
		}

		if (!empty($flash['redirect'])) {
			if (is_array($flash['redirect'])) {
				foreach ($flash['redirect'] as $k => $v) {
					$flash['redirect'][$k] = str_replace('{$__cakeID__$}', $Model->id, $v);
				}
			} else {
				$flash['redirect'] = str_replace('{$__cakeID__$}', $Model->id, $flash['redirect']);
			}
		}

		$this->bindSecurityToken($Model);

		$token = array($Model->SecurityToken->alias => array(
			'foreign_model' => (!empty($Model->plugin) ? $Model->plugin . '.' : '') . $Model->name,
			'foreign_key' => $Model->id,
			'foreign_data' => isset($initialState) ? $result : null,
			'foreign_field' => null,
			'foreign_flash' => $flash,
			'expires' => '+3 days'
		));

		if (1 === $tokenizable) {
			$token[$Model->SecurityToken->alias]['foreign_field'] = $field;
			if (!empty($config['fields'][$field])) {
				$token[$Model->SecurityToken->alias]['foreign_flash'] = (array) $config['fields'][$field];
			}
		} else if ($tokenizable && !empty($config['foreignData'])) {
			$token[$Model->SecurityToken->alias]['foreign_data'] = $config['foreignData'];
		}

		if (isset($initialState)) {
			$Model->set($Model->save($initialState, false));
		} else {
			$Model->set($result);
		}

		$Model->SecurityToken->create($token);
		$Model->SecurityToken->set($Model->SecurityToken->save());

		$Model->triggerEvent(
			'Model.' . $Model->alias . '.afterTokenize',
			$Model,
			array('token' => $Model->SecurityToken->data[$Model->SecurityToken->alias])
		);

		if (!empty($Model->SecurityToken->data)) {
			return $result;
		}

		return false;
	}

}
