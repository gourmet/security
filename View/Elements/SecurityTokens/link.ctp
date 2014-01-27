<?php

/**
 * Re-usable token verification link (i.e. in emails).
 *
 * @param $token
 * @package Security.View.Elements.SecurityTokens
 */

echo $this->Email->para(
	null,
	$this->Email->link(Router::url(array_merge(array(
		'plugin' => 'security',
		'controller' => 'security_tokens',
		'action' => 'verify',
		$token['token']
	), !empty($this->request->prefix) ? array('prefix' => false, $this->request->prefix => false) : array()), true))
);

echo $this->Email->para(
	null,
	__d(
		'users',
		"If the above link is broken for you, go to:"
	)
);

echo $this->Email->para(
	null,
	Router::url(array(
		'plugin' => 'security',
		'controller' => 'security_tokens'
	), true)
);

echo $this->Email->para(
	null,
	String::insert(__d(
		'users',
		"and manually enter your token: :token"
	), $token)
);
