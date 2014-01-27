<?php

	$__params = array('plugin' => 'security', 'controller' => 'security_tokens');

	Router::connect('/token/*', $__params + array('action' => 'verify'));
	Router::connect('/verify', $__params + array('action' => 'index'));

	unset($__params);
