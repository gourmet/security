<?php

App::uses('SecurityAppModel', 'Security.Model');

class SecurityToken extends SecurityAppModel {

/**
 * {@inheritdoc}
 */
	public $actsAs = array(
		'Common.Encodable' => array('fields' => array('foreign_data' => array('encoding' => 'serialize'), 'foreign_flash' => array('encoding' => 'serialize'))),
		'Common.Stateable' => array('values' => array('', 'expired', 'used'))
	);

	public $belongsToForeignModels = array();

/**
 * {@inheritdoc}
 */
	public $displayField = 'token';

/**
 * Token's validity in valid `strtotime()` value.
 *
 * @var string
 */
	public $expires = null;

/**
 * {@inheritdoc}
 */
	// public $validate = array(
	// 	'token' => array(
	// 		'notEmpty' => array(
	// 			'rule' => array('notEmpty'),
	// 			'required' => true,
	// 			'allowEmpty' => false,
	// 			'message' => "Can not be empty."
	// 		),
	// 		'length' => array(
	// 			'rule' => array('minLength', 6),
	// 			'message' => "Too short, minimum 6."
	// 		),
	// 		'unique' => array(
	// 			'rule' => array('isUnique'),
	// 			'message' => "Already exists."
	// 		),
	// 	),
	// 	'expires' => array(
	// 		'notEmpty' => array(
	// 			'rule' => array('notEmpty'),
	// 			'required' => true,
	// 			'allowEmpty' => false,
	// 			'message' => "Can not be empty."
	// 		),
	// 	)
	// );

/**
 * {@inheritdoc}
 */
	public function __construct($id = false, $table = null, $ds = null) {
		$this->friendly = __d('security', "Token");
		$this->findMethods['token'] = true;
		parent::__construct($id, $table, $ds);
	}

/**
 * {@inheritdoc}
 */
	public function beforeValidate($options = array()) {
		if (empty($this->data[$this->alias]['token'])) {
			$this->data[$this->alias]['token'] = $this->generate();
		}

		if (empty($this->data[$this->alias]['expires'])) {
			if (!empty($this->expires)) {
				$this->data[$this->alias]['expires'] = $this->expires;
			} else {
				$this->data[$this->alias]['expires'] = Common::read('Security.expireToken', '+3 days');
			}
		}

		if (!Validation::datetime($this->data[$this->alias]['expires'])) {
			$this->data[$this->alias]['expires'] = date('Y-m-d H:i:s', strtotime($this->data[$this->alias]['expires']));
		}

		return true;
	}

/**
 * Validates and expires a token.
 *
 * @param string $token
 * @param string $condition Optional. Either a valid status or an `strtotime` valid string.
 * @return boolean True if the token is valid, false otherwise.
 */
	public function check($token, $foreignModel = null, $foreignKey = null, $condition = null) {
		$queryData = array('conditions' => array($this->alias . '.token' => $token));

		if (empty($foreignKey) && empty($condition)) {
			$condition = $foreignModel;
		} else {
			$queryData['conditions'][$this->alias . '.foreign_model'] = $foreignModel;
			$queryData['conditions'][$this->alias . '.foreign_key'] = $foreignKey;
		}

		if (!empty($condition)) {
			$queryData['conditions'][$this->alias . '.status'] = $condition;
		} else {
			$queryData['conditions'][$this->alias . '.expires >'] = date('Y-m-d H:i:s');
		}

		if (!$token = $this->find('token', $queryData)) {
			return false;
		}

		if (!empty($token[$this->alias]['foreign_data'])) {
			$ForeignModel = ClassRegistry::init($token[$this->alias]['foreign_model']);
			if ($ForeignModel->Behaviors->loaded('Authorizable')) {
				$ForeignModel->skipAuthorizable(1);
			}
			$ForeignModel->id = $token[$this->alias]['foreign_key'];
			$ForeignModel->save($token[$this->alias]['foreign_data'], array('callbacks' => false, 'validate' => false));
		}

		$this->id = $token[$this->alias][$this->primaryKey];

		if (empty($condition)) {
			$this->delete();
			return $token;
		}

		$this->changeStatus('used');
		return true;
	}

/**
* Generates a random token.
*
* @param int $len Optional. Token's length.
* @param string $char Optional. Valid characters to randomize from.
* @return string Random token.
*/
	public static function generate($len = 20, $char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
		$token = Common::random($len, $char);

		if (ClassRegistry::init('Security.SecurityToken')->find('count', array('conditions' => compact('token'), 'recursive' => -1))) {
			$token = Common::random($len, $char);
		}

		return $token;
	}

/**
 * Custom `Token::find('token')` method.
 *
 * @param [type] $state [description]
 * @param [type] $queryData [description]
 * @param array $results [description]
 * @return [type]
 */
	protected function _findToken($state, $queryData, $results = array()) {
		if ('after' == $state) {
			if (count($results) === 1) {
				return current($results);
			}
			return $results;
		}

		if (!empty($queryData[0])) {
			$queryData = Hash::merge($queryData, array('conditions' => array($this->alias . '.token' => $queryData[0])));
			unset($queryData[0]);
		}

		if (empty($queryData['conditions']['expires']) && empty($queryData['conditions'][$this->alias . '.expires'])) {
			$queryData['conditions'][$this->alias . '.expires >'] = date('Y-m-d H:i:s');
		}

		if (empty($queryData['fields'])) {
			$queryData['fields'] = array(
				'id',
				'token',
				'foreign_model',
				'foreign_key',
				'foreign_data',
				'foreign_flash',
				'foreign_field'
			);
		}

		if (empty($queryData['recursive'])) {
			$queryData['recursive'] = -1;
		}

		return $queryData;
	}
}
