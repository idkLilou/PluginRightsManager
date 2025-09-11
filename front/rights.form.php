<?php

include ('../../../inc/includes.php');

Session::checkRight('config', UPDATE);

$rights = new PluginPluginrightsmanagerRights();

if (isset($_POST['add_right'])) {
    // Traitement de l'ajout de droit
    $input = [
        'plugin_name' => $_POST['plugin_name'],
        'right_type' => $_POST['right_type'],
        'right_value' => 1, // Par défaut autorisé
        'date_creation' => $_SESSION['glpi_currenttime']
    ];
    
    // Déterminer le type d'assignation
    if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
        $input['user_id'] = $_POST['user_id'];
    } elseif (isset($_POST['group_id']) && !empty($_POST['group_id'])) {
        $input['group_id'] = $_POST['group_id'];
    } elseif (isset($_POST['profile_id']) && !empty($_POST['profile_id'])) {
        $input['profile_id'] = $_POST['profile_id'];
    }
    
    if ($rights->add($input)) {
        Session::addMessageAfterRedirect('Droit ajouté avec succès', false, INFO);
    } else {
        Session::addMessageAfterRedirect('Erreur lors de l\'ajout du droit', false, ERROR);
    }
    
    Html::back();
}

if (isset($_POST['add_custom_right'])) {
    // Traitement de l'ajout de droit personnalisé
    global $DB;
    
    $query = "INSERT INTO glpi_plugin_pluginrightsmanager_custom_rights 
              (plugin_name, right_name, right_label, date_creation) 
              VALUES ('" . $DB->escape($_POST['plugin_name']) . "',
                      '" . $DB->escape($_POST['custom_right_name']) . "',
                      '" . $DB->escape($_POST['custom_right_label']) . "',
                      '" . $_SESSION['glpi_currenttime'] . "')";
    
    if ($DB->query($query)) {
        Session::addMessageAfterRedirect('Droit spécifique ajouté avec succès', false, INFO);
    } else {
        Session::addMessageAfterRedirect('Erreur lors de l\'ajout du droit spécifique', false, ERROR);
    }
    
    Html::back();
}

// Affichage de la page
$plugin_name = $_GET['plugin'] ?? '';

if (empty($plugin_name)) {
    Html::displayErrorAndDie('Plugin non spécifié');
}

Html::header(
    'Gestion des droits - ' . $plugin_name,
    $_SERVER['PHP_SELF'],
    'admin',
    'PluginPluginrightsmanagerConfig'
);

$rights->showRightsForPlugin($plugin_name);

Html::footer();