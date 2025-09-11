<?php

include ('../../../inc/includes.php');

header('Content-Type: application/json');

if (!Session::haveRight('config', READ)) {
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

global $DB;

$query = "SELECT id, name 
          FROM glpi_profiles 
          ORDER BY name";

$result = $DB->query($query);
$profiles = [];

while ($data = $DB->fetchAssoc($result)) {
    $profiles[] = [
        'id' => $data['id'],
        'text' => $data['name']
    ];
}

echo json_encode($profiles);