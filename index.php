<?php
require_once "controllers/template.controller.php";

require_once "controllers/users.controller.php";
require_once "controllers/verticals.controller.php";
require_once "controllers/clients.controller.php";
require_once "controllers/projects.controller.php";
require_once "controllers/platforms.controller.php";
require_once "controllers/formats.controller.php";
require_once "controllers/objectives.controller.php";
require_once "controllers/campaigns.controller.php";
require_once "controllers/periods.controller.php";
require_once "controllers/urls.controller.php";
require_once "controllers/comments.controller.php";

require_once "models/users.model.php";
require_once "models/clients.model.php";
require_once "models/projects.model.php";
require_once "models/platforms.model.php";
require_once "models/formats.model.php";
require_once "models/objetives.model.php";

$template = new ControllerTemplate();
$template -> ctrTemplate();