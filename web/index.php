<?php
require_once dirname(__FILE__).'/inc/config.php';

require_once _cfg('classes').'/system.php';

//Loading whole system
$system = new System(0);
$system->run();