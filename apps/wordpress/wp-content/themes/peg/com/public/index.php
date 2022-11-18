<?php
define('LAZER_DATA_PATH', '../data/');

require('../vendor/autoload.php');
require('../ArtalkServer.php');

new ArtalkServer(require('../Config.php'));
