<?php
/**
 * SecurityTokensController
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

App::uses('SecurityAppController', 'Security.Controller');

/**
 * Security Tokens Controller
 *
 * @package       Security.Controller
 */
class SecurityTokensController extends SecurityAppController {

/**
 * Manual token verification.
 *
 * @return void
 */
	public function index() {}

/**
 * Token verification.
 *
 * @param string $token Optional. Token to check.
 * @return void
 */
	public function verify($token = null) {
		if (empty($token) && !$this->request->is('post')) {
			throw new NotFoundException();
		}

		if (empty($token)) {
			$token = $this->request->data[$this->modelClass]['token'];
		}

		$Event = new CakeEvent('Controller.SecurityTokens.beforeCheck', $this, compact('token'));
		list($Event->break, $Event->breakOn) = array(true, false);
		$result = $this->triggerEvent($Event);
		if (false === $result || !$result = $this->{$this->modelClass}->check($token)) {
			return $this->flash('view.fail');
		}

		$this->triggerEvent('Controller.SecurityTokens.afterCheck', $this, $result);

		$this->flash('security_tokens.check.success', (array) $result[$this->{$this->modelClass}->alias]['foreign_flash']);
	}

/**
 * {@inheritdoc}
 */
	protected function _constructCrumbs() {}

}
