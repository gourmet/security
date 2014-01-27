<?php
/**
 * HttpBL
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

App::uses('CakeRequest', 'Network');

/**
 * Http:BL class
 *
 * @package       Security.Lib
 * @see http://www.projecthoneypot.org/httpbl_api.php
 */
class HttpBL extends Object {

/**
 * String returned when no record is found in http:BL database.
 *
 * @var string
 */
	const NO_RECORD = 'NXDOMAIN';

/**
 * Http:BL API Key.
 *
 * @var string
 * @see http://www.projecthoneypot.org/httpbl_configure.php
 */
	public $apiKey = null;

/**
 * Wether or not to cache responses by client.
 *
 * @var boolean
 */
	public $cacheResponses = true;

/**
 * IP address to lookup.
 *
 * @var string
 */
	public $client = null;

/**
 * The parsed last response.
 *
 * @var array
 */
	public $lastResponse = null;

/**
 * Http:BL host to query.
 *
 * @var string
 */
	public $host = 'dnsbl.httpbl.org';

/**
 * The raw response returned by http:BL.
 *
 * @var string
 */
	public $rawResponse = null;

/**
 * List of cached responses by client.
 *
 * @var array
 */
	public $responsesCache = array();

/**
 * Search engines defined by http:BL.
 *
 * @var array
 */
	private $__searchEngines = array(
		0 => 'Undocumented',
		1 => 'AltaVista',
		2 => 'Ask',
		3 => 'Baidu',
		4 => 'Excite',
		5 => 'Google',
		6 => 'Looksmart',
		7 => 'Lycos',
		8 => 'MSN',
		9 => 'Yahoo',
		10 => 'Cuil',
		11 => 'InfoSeek',
		12 => 'Miscellaneous'
	);

/**
 * Type of visitors defined by http:BL.
 *
 * @var array
 */
	private $__visitorTypes = array(
		0 => 'Search Engine',
		1 => 'Suspicious',
		2 => 'Harvester',
		4 => 'Comment Spammer'
	);

/**
 * Constructor.
 *
 * @param array $config The configuration to merge with this class properties.
 */
	public function __construct($config = array()) {
		if (is_string($config)) {
			$config = array('apiKey' => $config);
		}

		$this->_set($config);
	}

/**
 * Gets the number of days since the queried client was last seen by any Honey Pot.
 *
 * @param string $client IP address to query for.
 * @return integer Number of days.
 */
	public function lastSeen($client = null) {
		extract($this->query($client));

		if (0 === (int) $type) {
			return 0;
		}

		return $age;
	}

/**
 * Queries the http:BL service.
 *
 * @param string $client IP address to query for.
 * @param string $apiKey Security service quey.
 * @return array
 * @throws Exception If any of the `$client` or `$apiKey` is empty.
 * @throws Exception If no record found in the http:BL database.
 * @throws Exception If request query malformed.
 */
	public function query($client = null, $apiKey = null) {
		if (empty($client) && empty($this->client)) {
			$request = new CakeRequest;
			$client = $request->clientIp(true);
		}

		foreach (array('apiKey', 'client') as $var) {
			if (!is_null(${$var})) {
				$this->{$var} = ${$var};
			}
			if (empty($this->{$var})) {
				throw new Exception(__d('security', "Missing the '%s' for the http:BL database to be queried.", $var));
			}
		}

		if ($this->cacheResponses && isset($this->responsesCache[$this->client])) {
			return $this->responsesCache[$this->client];
		}

		$query = $this->_buildQuery();
		$this->rawResponse = gethostbyname($query);

		if (in_array($this->rawResponse, array($query, self::NO_RECORD))) {
			throw new Exception(__d('security', "No record found in the http:BL database for '%s'.", $this->client));
		}

		list($result, $age, $level, $type) = explode('.', $this->rawResponse);

		if (127 != $result) {
			throw new Exception(__d('security', "Malformed http:BL query (%s) for '%s'.", $query, $this->client));
		}

		$this->lastResponse = compact('age', 'level', 'type');
		$this->responsesCache[$client] = $this->lastResponse;
		return $this->lastResponse;
	}

/**
 * Get the threat score of the current client address.
 *
 * @param string $client IP address to query for.
 * @return integer Threat score.
 */
	public function threatScore($client = null) {
		extract($this->query($client));

		if (0 === (int) $type) {
			return 0;
		}

		return $level;
	}

/**
 * Gets the type of the address.
 *
 * @param string $client IP address to query for.
 * @return string The type of IP address.
 */
	public function typeOf($client = null) {
		extract($this->query($client));

		if (isset($this->__visitorTypes[$type])) {
			return $this->__visitorTypes[$type];
		}

		foreach (array_reverse(array_keys($this->__visitorTypes)) as $k) {
			if ($type && $k <= $type) {
				$type = $type - $k;
				$result[] = $this->__visitorTypes[$k];
			}
		}

		return implode(' & ', $result);
	}

/**
 * Builds a query
 *
 * @return string The query content.
 */
	protected function _buildQuery() {
		return implode('.', array(
			$this->apiKey,
			implode('.', array_reverse(explode('.', $this->client))),
			$this->host
		));
	}

}
