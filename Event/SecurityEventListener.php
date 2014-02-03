<?php
/**
 * SecurityEventListener
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

App::uses('CakeEventListener', 'Event');

/**
 * Security event listener
 *
 * Collection of Security's events to load.
 *
 * @package       Common.Event
 */
class SecurityEventListener implements CakeEventListener {

/**
 * {@inheritdoc}
 */
	public function implementedEvents() {
		return array(
			'Controller.constructClasses' => array('callable' => 'controllerConstructClasses'),
		);
	}

/**
 * Defines the `Security` flash message(s).
 *
 * @param CakeEvent $Event
 * @return void
 */
	public function controllerConstructClasses(CakeEvent $Event) {
		$Event->result = Hash::merge((array) $Event->result, array('alertMessages' => array(
			'security_tokens.check.success' => array(
				'message' => __d('security', "Token approved."),
				'redirect' => true,
				'dismiss' => true
			),
		)));
	}

}
