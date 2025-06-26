<?php
require_once "controllers/template.controller.php";

require_once "controllers/users.controller.php";
require_once "controllers/vertical.controller.php";
require_once "controllers/clients.controller.php";
require_once "controllers/projects.controller.php";
require_once "controllers/platforms.controller.php";
require_once "controllers/formats.controller.php";
require_once "controllers/objetives.controller.php";

require_once "models/users.model.php";
require_once "models/vertical.model.php";
require_once "models/clients.model.php";
require_once "models/projects.model.php";
require_once "models/platforms.model.php";
require_once "models/formats.model.php";
require_once "models/objetives.model.php";

$template = new ControllerTemplate();
$template -> ctrTemplate();