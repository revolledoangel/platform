<?php
require_once "controllers/template.controller.php";

require_once "controllers/users.controller.php";
require_once "controllers/verticals.controller.php";
require_once "controllers/clients.controller.php";
require_once "controllers/projects.controller.php";
require_once "controllers/platforms.controller.php";
require_once "controllers/formats.controller.php";
require_once "controllers/objectives.controller.php";
require_once "controllers/campaignTypes.controller.php";
require_once "controllers/channels.controller.php";
require_once "controllers/campaigns.controller.php";
require_once "controllers/periods.controller.php";
require_once "controllers/urls.controller.php";
require_once "controllers/comments.controller.php";
require_once "controllers/mediaMixRealEstate.controller.php";
require_once "controllers/mediaMixRealEstateDetails.controller.php";
require_once "controllers/mediaMixEcommerce.controller.php";
require_once "controllers/mediaMixOthers.controller.php";

require_once "models/users.model.php";
require_once "models/clients.model.php";
require_once "models/projects.model.php";
require_once "models/platforms.model.php";
require_once "models/formats.model.php";
require_once "models/objetives.model.php";
require_once "models/campaignTypes.model.php";
require_once "models/channels.model.php";
require_once "models/objetives.model.php";
require_once "models/mediaMixRealEstate.model.php";
require_once "models/mediaMixEcommerce.model.php";
require_once "models/mediaMixOthers.model.php";

$template = new ControllerTemplate();
$template -> ctrTemplate();
