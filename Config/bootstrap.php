<?php

Configure::write('Security.expireToken', Common::read('Security.expireToken', '+3 days'));
