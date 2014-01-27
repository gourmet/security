<?php
/**
 * HoneyPotComponent
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

App::uses('Component', 'Controller');
App::uses('HttpBL', 'Security.Lib');

/**
 * HoneyPot component
 *
 * @package       Security.Controller.Component
 */
class HttpBLComponent extends Component {

/**
 * HttpBL API Key.
 *
 * @var string
 */
	public $apiKey = null;

/**
 * Components.
 *
 * @var array
 */
	public $components = array('Cookie');

/**
 * Conditions against which to check.
 *
 * @var array
 */
	public $conditions = array(
		array('age' => 14, 'level' => 25, 'type' => array(3, 4, 5, 6, 7)),
		array('age' => 14, 'level' => 35, 'type' => 2),
	);

/**
 * Cookie settings.
 *
 * @var array
 */
	public $cookie = array();

/**
 * HoneyPot (or QuickLink) URL.
 *
 * @var string
 * @see https://www.projecthoneypot.org/manage_honey_pots.php
 * @see https://www.projecthoneypot.org/manage_quicklink.php
 */
	public $honeyPot = null;

/**
 * HttpBL instance.
 *
 * @var HttpBL
 */
	public $HttpBL = null;

/**
 * {@inheritdoc}
 */
	public function initialize(Controller $Controller) {
		$this->HttpBL = new HttpBL(Common::read('Security.HttpBL.apiKey', $this->apiKey));
	}

/**
 * {@inheritdoc}
 */
	public function startup(Controller $Controller) {
		foreach ($this->cookie as $property => $value) {
			$this->Cookie->{$property} = $value;
		}

		$conditionKey = $this->Cookie->read('HttpBL.blacklist');

		if (false === $conditionKey) {
			return;
		}

		if (is_null($conditionKey)) {
			$conditionKey = $this->check();
		}

		$this->Cookie->write('HttpBL.response', $this->HttpBL->lastResponse);
		$this->Cookie->write('HttpBL.blacklist', $conditionKey);

		if (false !== $conditionKey) {
			$this->blackHole($Controller, $this->conditions[$conditionKey]);
		}
	}

/**
 * Blackholes clients that meet one of the `HttpBLComponent::$conditions`.
 *
 * @param Controller $Controller Instance of the current Controller.
 * @param array $condition Optional. Matching condition.
 * @return void
 */
	public function blackHole(Controller $Controller, $condition = null) {
		if (method_exists($Controller, 'blackHoleHttpBL')) {
			return $Controller->blackHoleHttpBL($condition);
		}

		if ($honeyPot = Common::read('Security.HttpBL.honeyPot') || $honeyPot = $this->honeyPot) {
			return $Controller->redirect($honeyPot, 301);
		}

		$this->_stop();
	}

/**
 * Checks if a given IP address meets any of the `HttpBLComponent::$conditions`.
 *
 * @param string $client Optional. IP address to check. Defaults to client's IP.
 * @param string $apiKey Optional. HttpBL API Key to use. Defaults to
 *   `HttpBLComponent::$apiKey` or the 'Security.HttpBL.apiKey' configuration key.
 * @return mixed The type's identifier or FALSE if does not meet any condition.
 */
	public function check($client = null, $apiKey = null) {
		try {
			extract($this->HttpBL->query($client, $apiKey));
		} catch (Exception $e) {
			return false;
		}

		foreach ($this->conditions as $k => $condition) {
			if (
				(int) $age <= (int) $condition['age']
				&& (int) $level >= (int) $condition['level']
				&& in_array($type, (array) $condition['type'])
			) {
				return $k;
			}
		}

		return false;
	}

/**
 * Checks if the given IP address is a 'Comment Spammer'.
 *
 * @param string $client Optional. IP address to check. Defaults to client's IP.
 * @return boolean
 */
	public function isCommentSpammer($client = null) {
		return $this->isOfType($client, array(4, 5, 6, 7));
	}

/**
 * Checks if the given IP address is flagged as 'Harvester'.
 *
 * @param string $client Optional. IP address to check. Defaults to client's IP.
 * @return boolean
 */
	public function isHarvester($client = null) {
		return $this->isOfType($client, array(2, 3, 6, 7));
	}

/**
 * Checks the type of visitor for the given IP address if it's part of a types' group.
 *
 * @param string $client Optional. IP address to check. Defaults to client's IP.
 * @param array $types Optional. List of valid type identifiers.
 * @return mixed The type identifier if no `$types` are passed. Otherwise result of the check.
 */
	public function isOfType($client = null, $types = null) {
		extract($this->HttpBL->query($client));

		if (!is_null($types)) {
			return in_array($type, (array) $types);
		}

		return $type;
	}

/**
 * Checks if the given IP address is flagged as 'Search Engine'.
 *
 * @param string $client Optional. IP address to check. Defaults to client's IP.
 * @return boolean
 */
	public function isSearchEngine($client = null) {
		return $this->isOfType($client, 0);
	}

/**
 * Checks if the given IP address is flagged as 'Suspicious'.
 *
 * @param string $client Optional. IP address to check. Defaults to client's IP.
 * @return boolean
 */
	public function isSuspicious($client = null) {
		return $this->isOfType($client, array(1, 3, 5, 7));
	}

}
