<?php

include ('../../../inc/includes.php');

Session::checkRight('config', UPDATE);

Html::header(
    PluginPluginrightsmanagerConfig::getTypeName(),
    $_SERVER['PHP_SELF'],
    'admin',
    'PluginPluginrightsmanagerConfig'
);
    echo '<link rel="stylesheet" type="text/css" href="' . $CFG_GLPI['root_doc'] . '/plugins/pluginrightsmanager/css/pluginrightsmanager.css">';

$config = new PluginPluginrightsmanagerConfig();
$config->showForm(1);

Html::footer();