<fieldset>

	<legend><?php echo __d('security', "Token Verification"); ?></legend>

	<?php
	echo $this->Form->create($modelClass, array(
		'class' => 'well form-horizontal',
		'url' => array('plugin' => 'security', 'controller' => 'security_tokens', 'action' => 'verify')
	));

	echo $this->Form->input('token', array('label' => __d('security', "Token")));

	echo $this->Form->submit(__d('security', "Verify"), array('class' => 'btn btn-primary'));
	echo $this->Form->end();
	?>

</fieldset>
