<?php

include ('../../../inc/includes.php');

Session::checkRight('config', UPDATE);

Html::header(
    PluginPluginrightsmanagerConfig::getTypeName(),
    $_SERVER['PHP_SELF'],
    'admin',
    'PluginPluginrightsmanagerConfig'
);

$config = new PluginPluginrightsmanagerConfig();
$config->showForm(1);

Html::footer();