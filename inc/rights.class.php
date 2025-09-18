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

        echo "<div class='prm-container'>";
        // Bloc ajout droit simple
        echo "<div class='prm-card prm-card-simple'>";
        echo "<h2><i class='fas fa-user-shield'></i> Ajouter un droit standard</h2>";
        echo "<form method='post' class='add-right-form' action='" . $CFG_GLPI['root_doc'] . "/plugins/pluginrightsmanager/front/rights.form.php'>";
        echo Html::hidden('plugin_name', ['value' => $plugin_name]);
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        echo "<div class='prm-form-row'>";
        echo "<label>Assigner à :</label>";
        $assign_types = [
            'user' => 'Utilisateur',
            'group' => 'Groupe',
            'profile' => 'Profil'
        ];
        Dropdown::showFromArray(
            'assign_type',
            ['' => '-- Sélectionner un type --'] + $assign_types,
            ['width' => '200px']
        );
        echo "<span id='assign_selector' class='prm-assign-selector'>Sélectionnez d'abord un type</span>";
        echo "</div>";
        echo "<div class='prm-form-row'>";
        echo "<label>Droits :</label>";
        $rights_types = [
            'access' => 'Accès',
            'read' => 'Lecture',
            'write' => 'Écriture',
            'delete' => 'Suppression'
        ];
        foreach ($rights_types as $key => $label) {
            echo "<label class='prm-checkbox'>";
            echo "<input type='checkbox' name='right_types[]' value='$key'> $label";
            echo "</label>";
        }
        echo "</div>";
        global $DB;
        $query = "SELECT id, right_name, right_label FROM glpi_plugin_pluginrightsmanager_custom_rights WHERE plugin_name = '" . $DB->escape($plugin_name) . "'";
        $result = $DB->query($query);
        echo "<div class='prm-form-row'>";
        echo "<label>Droits spécifiques :</label>";
        if ($DB->numrows($result) > 0) {
            while ($data = $DB->fetchAssoc($result)) {
                echo "<label class='prm-checkbox'>";
                echo "<input type='checkbox' name='custom_right_types[]' value='" . $data['right_name'] . "'> " . Html::cleanInputText($data['right_label']);
                echo "</label>";
            }
        } else {
            echo "<em>Aucun droit spécifique défini</em>";
        }
        echo "</div>";
        echo "<div class='prm-form-row prm-form-actions'>";
        echo Html::submit('Ajouter', ['name' => 'add_right', 'class' => 'btn btn-primary']);
        echo "</div>";
        echo "</form>";
        echo "</div>";
        // Bloc droits spéciaux
        echo "<div class='prm-card prm-card-special'>";
        echo "<h2><i class='fas fa-star'></i> Création de droits spéciaux</h2>";
        $this->showCustomRights($plugin_name);
        echo "</div>";
        // Bloc liste droits existants
        echo "<div class='prm-card prm-card-list'>";
        echo "<h2><i class='fas fa-list'></i> Droits existants</h2>";
        $this->showExistingRights($plugin_name);
        echo "</div>";
        echo "</div>";
        echo "<script type='text/javascript'>
            $(document).ready(function() {
                $('select[name=\"assign_type\"]').change(function() {
                    var type = $(this).val();
                    var selector = $('#assign_selector');
                    var endpoints = {
                        user: 'users.php',
                        group: 'groups.php',
                        profile: 'profiles.php'
                    };
                    if (endpoints[type]) {
                        var selectName = type + '_id';
                        selector.html('<select name=\"' + selectName + '\" id=\"' + selectName + '\" style=\"width:200px;\"><option value=\"\">-- Sélectionner --</option></select>');
                        $.ajax({
                            url: '" . $CFG_GLPI['root_doc'] . "/plugins/pluginrightsmanager/ajax/' + endpoints[type],
                            dataType: 'json',
                            success: function(data) {
                                var select = $('#' + selectName);
                                $.each(data, function(index, item) {
                                    select.append($('<option>', {
                                        value: item.id,
                                        text: item.text
                                    }));
                                });
                            }
                        });
                    } else {
                        selector.html('Sélectionnez d\\'abord un type');
                    }
                });
            });
        </script>";
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

        if (!$result) {
            echo "<div class='center b'>Erreur SQL : " . $DB->error() . "</div>";
            return;
        }

        if ($DB->numrows($result) > 0) {
            echo "<h3>Droits existants</h3>";
            echo "<table class='prm-tab_cadre_fixehov'>";
            echo "<tr class='tab_bg_1'>";
            echo "<th>Assigné à</th>";
            echo "<th>Type</th>";
            echo "<th>Droit</th>";
            echo "<th>Valeur</th>";
            echo "<th>Actions</th>";
            echo "</tr>";

            while ($data = $DB->fetchAssoc($result)) {
                echo "<tr class='tab_bg_1'>";

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
                echo "<a href='#' class='prm-btn prm-btn-sm prm-btn-danger' onclick='deleteRight(" . $data['id'] . ")'>Supprimer</a>";
                echo "</td>";
                echo "</tr>";
            }

            echo "</table>";
        }
    }

    function showCustomRights($plugin_name) {
        echo "<h3>Droits spécifiques du plugin</h3>";
        echo "<p>Ici, vous pourrez définir des droits spécifiques à ce plugin.</p>";

        global $CFG_GLPI, $DB;
        echo "<form method='post' class='add-custom-right-form' action='" . $CFG_GLPI['root_doc'] . "/plugins/pluginrightsmanager/front/rights.form.php'>";
        echo Html::hidden('plugin_name', ['value' => $plugin_name]);
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);

        echo "<table class='prm-tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'><th colspan='4'>Ajouter un droit spécifique</th></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>Nom du droit:</td>";
        echo "<td><input type='text' name='custom_right_name' required></td>";
        echo "<td>Libellé:</td>";
        echo "<td><input type='text' name='custom_right_label' required></td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='4' class='center'>";
        echo Html::submit('Ajouter droit spécifique', ['name' => 'add_custom_right', 'class' => 'prm-btn prm-btn-secondary']);
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</form>";

        // Liste des droits spécifiques existants
        $query = "SELECT id, right_name, right_label FROM glpi_plugin_pluginrightsmanager_custom_rights WHERE plugin_name = '" . $DB->escape($plugin_name) . "' ORDER BY id DESC";
        $result = $DB->query($query);
        echo "<h4>Liste des droits spécifiques</h4>";
        echo "<table class='prm-tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'><th>Nom</th><th>Libellé</th><th>Actions</th></tr>";
        while ($data = $DB->fetchAssoc($result)) {
            echo "<tr class='tab_bg_1' id='customright-row-{$data['id']}'>";
            echo "<td><span class='customright-name' data-id='{$data['id']}'>{$data['right_name']}</span></td>";
            echo "<td><span class='customright-label' data-id='{$data['id']}'>{$data['right_label']}</span></td>";
            echo "<td>";
            // Bouton Modifier
            echo "<button type='button' class='prm-btn prm-btn-primary prm-btn-sm' onclick='editCustomRight({$data['id']})'>Modifier</button> ";
            // Bouton Supprimer
            echo "<button type='button' class='prm-btn prm-btn-danger prm-btn-sm' onclick='confirmDeleteCustomRight({$data['id']})'>Supprimer</button>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // JS pour confirmation et édition inlines
        echo <<<JS
            <script>
                function confirmDeleteCustomRight(id) {
                    Swal.fire({
                        title: 'Supprimer ce droit spécifique ?',
                        text: 'Cette action est irréversible.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74c3c',
                        cancelButtonColor: '#95a5a6',
                        confirmButtonText: 'Supprimer',
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '?delete_custom_right=' + id + '&plugin=' + encodeURIComponent('{$plugin_name}');
                        }
                    });
                }

                function editCustomRight(id) {
                    var row = document.getElementById('customright-row-' + id);
                    var nameCell = row.querySelector('.customright-name');
                    var labelCell = row.querySelector('.customright-label');
                    var oldName = nameCell.textContent;
                    var oldLabel = labelCell.textContent;

                    nameCell.innerHTML = '<input type="text" id="edit-name-' + id + '" value="' + oldName + '" style="width:120px">';
                    labelCell.innerHTML = '<input type="text" id="edit-label-' + id + '" value="' + oldLabel + '" style="width:120px">';

                    // Hide all action buttons in the row (Modifier & Supprimer)
                    var actionTd = row.querySelector('td:last-child');
                    var buttons = actionTd.querySelectorAll('button');
                    buttons.forEach(function(btn) {
                        btn.style.display = 'none';
                    });

                    var saveBtn = document.createElement('button');
                    saveBtn.className = 'prm-btn btn-success prm-btn-sm';
                    saveBtn.textContent = 'Enregistrer';
                    saveBtn.onclick = function() {
                        var newName = document.getElementById('edit-name-' + id).value;
                        var newLabel = document.getElementById('edit-label-' + id).value;
                        window.location.href = '?edit_custom_right=' + id + '&new_name=' + encodeURIComponent(newName) + '&new_label=' + encodeURIComponent(newLabel) + '&plugin=' + encodeURIComponent('{$plugin_name}');
                    };

                    actionTd.appendChild(saveBtn);
                }
            </script>
        JS;

    }
}