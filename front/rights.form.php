<?php
include ('../../../inc/includes.php');

// Suppression d'un droit spécifique
if (isset($_GET['delete_custom_right']) && ctype_digit($_GET['delete_custom_right'])) {
    global $DB;
    $id = (int)$_GET['delete_custom_right'];
    // Récupérer le nom du droit spécifique avant suppression
    $res = $DB->query("SELECT right_name, plugin_name FROM glpi_plugin_pluginrightsmanager_custom_rights WHERE id = $id");
    if ($row = $DB->fetchAssoc($res)) {
        $right_name = $DB->escape($row['right_name']);
        $plugin_name = $DB->escape($row['plugin_name']);
        // Supprimer toutes les assignations liées à ce droit spécifique
        $DB->query("DELETE FROM glpi_plugin_pluginrightsmanager_rights WHERE right_type = '$right_name' AND plugin_name = '$plugin_name'");
    }
    $DB->query("DELETE FROM glpi_plugin_pluginrightsmanager_custom_rights WHERE id = $id");
    Session::addMessageAfterRedirect('Droit spécifique supprimé et assignations supprimées.', false, INFO);
    Html::redirect($_SERVER['PHP_SELF'] . '?plugin=' . urlencode($_GET['plugin']));
}

// Edition d'un droit spécifique
if (isset($_GET['edit_custom_right']) && ctype_digit($_GET['edit_custom_right']) && isset($_GET['new_name']) && isset($_GET['new_label'])) {
    global $DB;
    $id = (int)$_GET['edit_custom_right'];
    $new_name = $DB->escape($_GET['new_name']);
    $new_label = $DB->escape($_GET['new_label']);
    // Récupérer l'ancien nom du droit et le plugin
    $res = $DB->query("SELECT right_name, plugin_name FROM glpi_plugin_pluginrightsmanager_custom_rights WHERE id = $id");
    if ($row = $DB->fetchAssoc($res)) {
        $old_name = $DB->escape($row['right_name']);
        $plugin_name = $DB->escape($row['plugin_name']);
        // Mettre à jour toutes les assignations avec le nouveau nom
        $DB->query("UPDATE glpi_plugin_pluginrightsmanager_rights SET right_type = '$new_name' WHERE right_type = '$old_name' AND plugin_name = '$plugin_name'");
    }
    $sql = "UPDATE glpi_plugin_pluginrightsmanager_custom_rights SET right_name = '$new_name', right_label = '$new_label' WHERE id = $id";
    if (!$DB->query($sql)) {
        echo '<pre style="color:red;">Erreur SQL : ' . htmlspecialchars($DB->error()) . "\nRequête : " . htmlspecialchars($sql) . '</pre>';
        exit;
    }
    Session::addMessageAfterRedirect('Droit spécifique modifié et assignations mises à jour.', false, INFO);
    Html::redirect($_SERVER['PHP_SELF'] . '?plugin=' . urlencode($_GET['plugin']));
}
Session::checkRight('config', UPDATE);

$rights = new PluginPluginrightsmanagerRights();

if (isset($_POST['add_custom_right']) && !isset($_POST['add_right'])) {
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

if (isset($_POST['add_right']) && !isset($_POST['add_custom_right']) && (isset($_POST['right_types']) || isset($_POST['custom_right_types']))) {
    file_put_contents(__DIR__ . '/../log_pluginrightsmanager.txt', "[DEBUG] Entrée dans add_right POST: " . print_r($_POST, true) . "\n", FILE_APPEND);
    // Log juste avant le traitement des droits spécifiques
    file_put_contents(__DIR__ . '/../log_pluginrightsmanager.txt', "[DEBUG] custom_right_types isset: " . (isset($_POST['custom_right_types']) ? 'OUI' : 'NON') . ", is_array: " . (is_array($_POST['custom_right_types'] ?? null) ? 'OUI' : 'NON') . "\n", FILE_APPEND);
    if (empty($_POST['user_id']) && empty($_POST['group_id']) && empty($_POST['profile_id'])) {
        Session::addMessageAfterRedirect("Veuillez sélectionner un utilisateur, un groupe ou un profil avant d'ajouter un droit.", false, ERROR);
        Html::back();
    }
    $rights = new PluginPluginrightsmanagerRights();
    $added = [];
    $ignored = [];


    foreach ($_POST['right_types'] as $type) {
        $criteria = [
            'plugin_name' => $_POST['plugin_name'],
            'right_type'  => $type,
            'right_value' => 1
        ];

        if (!empty($_POST['user_id'])) {
            $criteria['user_id'] = $_POST['user_id'];
        } elseif (!empty($_POST['group_id'])) {
            $criteria['group_id'] = $_POST['group_id'];
        } elseif (!empty($_POST['profile_id'])) {
            $criteria['profile_id'] = $_POST['profile_id'];
        }

        if (!$rights->find($criteria)) {
            $input = $criteria;
            $input['date_creation'] = $_SESSION['glpi_currenttime'];
            $rights->add($input);
            $added[] = $type;
        } else {
            $ignored[] = $type;
        }
    }

    // Traitement droits spécifiques
    if (isset($_POST['custom_right_types']) && is_array($_POST['custom_right_types'])) {
    file_put_contents(__DIR__ . '/../log_pluginrightsmanager.txt', "[DEBUG] custom_right_types: " . print_r($_POST['custom_right_types'], true) . "\n", FILE_APPEND);
        foreach ($_POST['custom_right_types'] as $custom_type) {
            file_put_contents(__DIR__ . '/../log_pluginrightsmanager.txt', "[DEBUG] custom_type: $custom_type\n", FILE_APPEND);
            if (!empty($custom_type)) {
                file_put_contents(__DIR__ . '/../log_pluginrightsmanager.txt', "[DEBUG] custom_type not empty: $custom_type\n", FILE_APPEND);
                    file_put_contents(__DIR__ . '/../log_pluginrightsmanager.txt', "[DEBUG] Ajout droit spécifique: " . print_r($criteria, true) . "\n", FILE_APPEND);
                    file_put_contents(__DIR__ . '/../log_pluginrightsmanager.txt', "[DEBUG] Droit spécifique déjà attribué: " . print_r($criteria, true) . "\n", FILE_APPEND);
                $criteria = [
                    'plugin_name' => $_POST['plugin_name'],
                    'right_type'  => $custom_type,
                    'right_value' => 1
                ];
                if (!empty($_POST['user_id'])) {
                    $criteria['user_id'] = $_POST['user_id'];
                } elseif (!empty($_POST['group_id'])) {
                    $criteria['group_id'] = $_POST['group_id'];
                } elseif (!empty($_POST['profile_id'])) {
                    $criteria['profile_id'] = $_POST['profile_id'];
                }
                if (!$rights->find($criteria)) {
                    $criteria['date_creation'] = $_SESSION['glpi_currenttime'];
                    $rights->add($criteria);
                    $added[] = $custom_type;
                } else {
                    $ignored[] = $custom_type;
                }
            }
        }
    }

    if ($added) {
        Session::addMessageAfterRedirect("Droits ajoutés : " . implode(', ', $added), false, INFO);
    }
    if ($ignored) {
        Session::addMessageAfterRedirect("Droits ignorés (déjà attribués) : " . implode(', ', $ignored), false, WARNING);
    }

    Html::back();
}

// Affichage de la page

// Récupère le nom du plugin depuis POST (après soumission) ou GET (affichage initial)
$plugin_name = $_POST['plugin_name'] ?? $_GET['plugin'] ?? '';

if (empty($plugin_name)) {
    Html::displayErrorAndDie('Plugin non spécifié');
}

Html::header(
    'Gestion des droits - ' . $plugin_name,
    $_SERVER['PHP_SELF'],
    'admin',
    'PluginPluginrightsmanagerConfig'
);

    echo '<link rel="stylesheet" type="text/css" href="' . $CFG_GLPI['root_doc'] . '/plugins/pluginrightsmanager/css/pluginrightsmanager.css">';


$rights->showRightsForPlugin($plugin_name);

echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">';
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
echo '<script src="' . $CFG_GLPI['root_doc'] . '/plugins/pluginrightsmanager/js/pluginrightsmanager.js"></script>';

Html::footer();