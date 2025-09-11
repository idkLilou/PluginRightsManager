<?php

include ('../../../inc/includes.php');

header('Content-Type: application/json');

if (!Session::haveRight('config', UPDATE)) {
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['error' => 'ID manquant']);
    exit;
}

$rights = new PluginPluginrightsmanagerRights();
if ($rights->delete(['id' => intval($_POST['id'])])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Erreur lors de la suppression']);
}