<?php

class AccessLimitComponent extends Component {

	const ALL = 'all';

	const ARO = 'IPv4';

	public $authUserModel = null;

	public $Controller = null;

	public $Model = null;

	public $modelClass = 'Security.AccessLimit';

	protected $_authenticateObjects = array();

	protected static $_user = array();

	public function startup(Controller $Controller) {
		$this->Controller = $Controller;
		$this->Model = ClassRegistry::init($this->modelClass);
		$this->constructLimits();

		$action = strtolower($Controller->request->params['action']);
		$types = array_map('strtolower', array_keys($this->settings));

		foreach ($types as $type) {
			if (!$Controller->request->is($type) || empty($this->{$type}[$action])) {
				continue;
			}

			extract($this->{$type}[$action]);

			if (!$this->limit(compact('limit'))) {
				list($method, $params) = (array) $callback;
				$Controller->dispatchMethod($method, $params);
				return false;
			}

			if ('get' == $type) {
				$this->fail(compact('duration'));
			}
		}

		return true;
	}

	public function constructLimits() {
		$global = array(
			'callback' => array('flash', array('security.access_limit.fail')),
			'duration' => '+1 hour',
			'limit' => 5
		);

		if (!empty($this->settings[AccessLimitComponent::ALL])) {
			$global = array_merge($global, $this->settings[AccessLimitComponent::ALL]);
			unset($this->settings[AccessLimitComponent::ALL]);
		}

		foreach ($this->settings as $requestType => $settings) {
			$this->{$requestType} = array();
			foreach (Hash::normalize($settings) as $action => $limit) {
				if (!empty($limit) && !is_array($limit)) {
					$limit = compact('limit');
				}
				$this->{$requestType}[$action] = array_merge($global, (array) $limit);
			}
		}
	}

	public function fail($aro = null, $aco = null, $aroid = null, $acoid = null, $duration = '+1 day') {
		$args = compact('aro', 'aco', 'aroid', 'acoid', 'duration');
		if (is_array($aro)) {
			$args = $aro;
		}

		extract(array_merge($this->_getDefaults(), array('duration' => '+1 day'), $args));

		return $this->Model->fail($aro, $aco, $aroid, $acoid, $duration);
	}

	public function limit($aro = null, $aco = null, $aroid = null, $acoid = null, $limit = 10) {
		$args = compact('aro', 'aco', 'aroid', 'acoid', 'limit');
		if (is_array($aro)) {
			$args = $aro;
		}

		extract(array_merge($this->_getDefaults(), array('limit' => 10), $args));

		return $this->Model->limit($aro, $aco, $aroid, $acoid, $limit);
	}

	public function reset($aro = null, $aco = null, $aroid = null, $acoid = null) {
		$args = compact('aro', 'aco', 'aroid', 'acoid');
		if (is_array($aro)) {
			$args = $aro;
		}

		extract(array_merge($this->_getDefaults(), $args));

		return $this->Model->reset($aro, $aco, $aroid, $acoid);
	}

	protected function _getAco() {
		$name = $this->Controller->name;
		$action = $this->Controller->request->params['action'];
		return strtolower(implode('.', array('controller', $name, $action)));
	}

	protected function _getAcoid() {
		if (isset($this->Controller->request->params[0])) {
			return $this->Controller->request->params[0];
		}
		return null;
	}

	protected function _getAro() {
		$aro = AccessLimitComponent::ARO;
		if ($this->_getUser()) {
			$aro = $this->authUserModel;
			self::$_user = $this->Controller->Auth->user();
		}
		return $aro;
	}

	protected function _getAroid() {
		if (!empty($this->authUserModel)) {
			return self::$_user[ClassRegistry::init($this->authUserModel)->primaryKey];
		}
		return $this->Controller->request->clientIp();
	}

	protected function _getDefaults() {
		return array(
			'aro' => $this->_getAro(),
			'aco' => $this->_getAco(),
			'aroid' => $this->_getAroid(),
			'acoid' => $this->_getAcoid(),
		);
	}

	protected function _getUser() {
		if (!isset($this->Controller->Auth) || !is_a($this->Controller->Auth, 'AuthComponent')) {
			return false;
		}

		if (empty($this->_authenticateObjects)) {
			$this->_authenticateObjects = $this->Controller->Auth->constructAuthenticate();
		}

		foreach ($this->_authenticateObjects as $auth) {
			if (is_a($auth, 'FormAuthenticate')) {
				$this->authUserModel = $auth->settings['userModel'];
			}
		}

		$user = $this->Controller->Auth->user();
		if ($user) {
			return true;
		}

		foreach ($this->_authenticateObjects as $auth) {
			$result = $auth->getUser($this->Controller->request);
			if (!empty($result) && is_array($result)) {
				$this->authUserModel = $auth->settings['userModel'];
				self::$_user = $result;
				return true;
			}
		}
		return false;
	}

}
