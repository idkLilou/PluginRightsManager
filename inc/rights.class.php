<?php

class PluginPluginrightsmanagerRights extends CommonDBTM {
    
    static $rightname = 'config';
    
    static function getTypeName($nb = 0) {
        return _n('Droit plugin', 'Droits plugins', $nb, 'pluginrightsmanager');
    }
    
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        return self::createTabEntry(self::getTypeName());
    }
    
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        $rights = new self();
        $rights->showRightsForPlugin($_GET['plugin']);
        return true;
    }
    
    function showRightsForPlugin($plugin_name) {
        global $CFG_GLPI;
        
        if (!Session::haveRight('config', UPDATE)) {
            return false;
        }
        
        echo "<div class='center'>";
        echo "<h2>Gestion des droits pour le plugin: " . Html::cleanInputText($plugin_name) . "</h2>";
        
        // Formulaire d'ajout de droits
        echo "<form method='post' action='" . $CFG_GLPI['root_doc'] . "/plugins/pluginrightsmanager/front/rights.form.php'>";
        echo Html::hidden('plugin_name', ['value' => $plugin_name]);
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'><th colspan='6'>Ajouter un droit</th></tr>";
        
        // Type d'assignation
        echo "<tr class='tab_bg_1'>";
        echo "<td>Assigner à:</td>";
        echo "<td>";
        $assign_types = [
            'user' => 'Utilisateur',
            'group' => 'Groupe',
            'profile' => 'Profil'
        ];
        Dropdown::showFromArray('assign_type', $assign_types, ['width' => '200px']);
        echo "</td>";
        
        // Sélecteur dynamique (sera mis à jour via JavaScript)
        echo "<td id='assign_selector'>Sélectionnez d'abord un type</td>";
        
        // Type de droit
        echo "<td>Droit:</td>";
        echo "<td>";
        $rights_types = [
            'access' => 'Accès',
            'read' => 'Lecture',
            'write' => 'Écriture', 
            'delete' => 'Suppression'
        ];
        Dropdown::showFromArray('right_type', $rights_types, ['width' => '150px']);
        echo "</td>";
        
        echo "<td>";
        echo Html::submit('Ajouter', ['name' => 'add_right', 'class' => 'btn btn-primary']);
        echo "</td>";
        echo "</tr>";
        
        echo "</table>";
        echo "</form>";
        
        // Affichage des droits existants
        $this->showExistingRights($plugin_name);
        
        // Gestion des droits personnalisés
        $this->showCustomRights($plugin_name);
        
        echo "</div>";
        
        // JavaScript pour la sélection dynamique
        echo "<script type='text/javascript'>";
        echo "$(document).ready(function() {
            $('select[name=\"assign_type\"]').change(function() {
                var type = $(this).val();
                var selector = $('#assign_selector');
                
                if (type === 'user') {
                    selector.html('<select name=\"user_id\"><option value=\"\">-- Sélectionner un utilisateur --</option></select>');
                    // Charger les utilisateurs via AJAX
                } else if (type === 'group') {
                    selector.html('<select name=\"group_id\"><option value=\"\">-- Sélectionner un groupe --</option></select>');
                    // Charger les groupes via AJAX  
                } else if (type === 'profile') {
                    selector.html('<select name=\"profile_id\"><option value=\"\">-- Sélectionner un profil --</option></select>');
                    // Charger les profils via AJAX
                }
            });
        });";
        echo "</script>";
        
        return true;
    }
    
    function showExistingRights($plugin_name) {
        global $DB;
        
        $query = "SELECT pr.*, u.name as username, g.name as groupname, p.name as profilename 
                  FROM glpi_plugin_pluginrightsmanager_rights pr
                  LEFT JOIN glpi_users u ON pr.user_id = u.id
                  LEFT JOIN glpi_groups g ON pr.group_id = g.id  
                  LEFT JOIN glpi_profiles p ON pr.profile_id = p.id
                  WHERE pr.plugin_name = '" . $DB->escape($plugin_name) . "'
                  ORDER BY pr.right_type, pr.id";
        
        $result = $DB->query($query);
        
        if ($DB->numrows($result) > 0) {
            echo "<h3>Droits existants</h3>";
            echo "<table class='tab_cadre_fixehov'>";
            echo "<tr class='tab_bg_1'>";
            echo "<th>Assigné à</th>";
            echo "<th>Type</th>";
            echo "<th>Droit</th>";
            echo "<th>Valeur</th>";
            echo "<th>Actions</th>";
            echo "</tr>";
            
            while ($data = $DB->fetchAssoc($result)) {
                echo "<tr class='tab_bg_1'>";
                
                // Assigné à
                if ($data['user_id']) {
                    echo "<td>Utilisateur: " . Html::cleanInputText($data['username']) . "</td>";
                } elseif ($data['group_id']) {
                    echo "<td>Groupe: " . Html::cleanInputText($data['groupname']) . "</td>";
                } elseif ($data['profile_id']) {
                    echo "<td>Profil: " . Html::cleanInputText($data['profilename']) . "</td>";
                }
                
                echo "<td>" . Html::cleanInputText($data['right_type']) . "</td>";
                echo "<td>" . Html::cleanInputText($data['right_type']) . "</td>";
                echo "<td>" . ($data['right_value'] ? 'Autorisé' : 'Refusé') . "</td>";
                echo "<td>";
                echo "<a href='#' class='btn btn-sm btn-danger' onclick='deleteRight(" . $data['id'] . ")'>Supprimer</a>";
                echo "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    }
    
    function showCustomRights($plugin_name) {
        echo "<h3>Droits spécifiques du plugin</h3>";
        echo "<p>Ici, vous pourrez définir des droits spécifiques à ce plugin.</p>";
        
        // Formulaire pour ajouter des droits personnalisés
        echo "<form method='post'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'><th colspan='4'>Ajouter un droit spécifique</th></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>Nom du droit:</td>";
        echo "<td><input type='text' name='custom_right_name' required></td>";
        echo "<td>Libellé:</td>";
        echo "<td><input type='text' name='custom_right_label' required></td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='4' class='center'>";
        echo Html::submit('Ajouter droit spécifique', ['name' => 'add_custom_right']);
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</form>";
    }
}