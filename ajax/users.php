<?php

include ('../../../inc/includes.php');

header('Content-Type: application/json');

if (!Session::haveRight('config', READ)) {
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

global $DB;

$query = "SELECT id, name, realname, firstname 
          FROM glpi_users 
          WHERE is_active = 1 
          AND is_deleted = 0 
          ORDER BY name";

$result = $DB->query($query);
$users = [];

while ($data = $DB->fetchAssoc($result)) {
    $display_name = $data['name'];
    if (!empty($data['realname']) || !empty($data['firstname'])) {
        $display_name .= ' (' . trim($data['firstname'] . ' ' . $data['realname']) . ')';
    }
    
    $users[] = [
        'id' => $data['id'],
        'text' => $display_name
    ];
}

echo json_encode($users);