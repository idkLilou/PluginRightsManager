<?php

class PluginPluginrightsmanagerConfig extends CommonDBTM {
    
    static $rightname = 'config';
    
    static function getTypeName($nb = 0) {
        return _n('Plugin Rights Manager', 'Plugin Rights Manager', $nb, 'pluginrightsmanager');
    }
    
    static function getMenuContent() {
        $menu = [];
        if (static::canView()) {
            $menu['title'] = self::getTypeName();
            $menu['page'] = '/plugins/pluginrightsmanager/front/config.php';
            $menu['icon'] = 'fas fa-shield-alt';
        }
        return $menu;
    }
    
    static function canView() {
        return Session::haveRight('config', READ);
    }
    
    static function canCreate() {
        return Session::haveRight('config', UPDATE);
    }
    
    function showForm($ID, $options = []) {
        $this->initForm($ID, $options);
        $this->showFormHeader($options);
        
        echo "<tr>";
        echo "<td colspan='4' style='background:none; border:none;'>";
        echo "<div style='padding:0; margin:0;'>";
        echo "<h2 style='margin-bottom:8px;'>Gestion des droits des plugins</h2>";
        echo "<p style='margin:0;'>Ce plugin permet de gérer finement les droits d'accès à tous les plugins installés sur GLPI.</p>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
        
        $plugins = $this->getInstalledPlugins();
        if (empty($plugins)) {
            echo "<tr>";
            echo "<td colspan='4' style='background:none; border:none; text-align:center;'>Aucun plugin détecté.</td>";
            echo "</tr>";
        } else {
            echo "<tr>";
            echo "<td colspan='4' style='background:none; border:none;'>";
            echo "<h3 style='margin-top:20px;'>Plugins détectés (" . count($plugins) . ")</h3>";
            // Barre de recherche
            echo "<input type='text' id='prm-plugin-search' placeholder='Rechercher un plugin...' style='margin-bottom:16px; width:300px; padding:8px; border-radius:6px; border:1px solid #ccc;'>";
            echo "<div class='prm-plugin-list' id='prm-plugin-list' style='padding:0; margin:0;'>";
            foreach ($plugins as $plugin) {
                echo $this->displayPluginCard($plugin);
            }
            echo "</div>";
            // JS de filtrage
            echo <<<JS
<script>
document.getElementById('prm-plugin-search').addEventListener('input', function() {
    var search = this.value.toLowerCase();
    var cards = document.querySelectorAll('#prm-plugin-list .prm-plugin-card');
    cards.forEach(function(card) {
        var name = card.querySelector('h4') ? card.querySelector('h4').textContent.toLowerCase() : '';
        if (name.indexOf(search) !== -1) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>
JS;
            echo "</td>";
            echo "</tr>";
        }
        
        $this->showFormButtons($options);
        
        return true;
    }
    
    function getInstalledPlugins() {
        global $CFG_GLPI;
        $plugins = [];
        
        $plugin_directories = scandir(GLPI_ROOT . '/plugins');
        
        foreach ($plugin_directories as $dir) {
            if ($dir === '.' || $dir === '..' || !is_dir(GLPI_ROOT . '/plugins/' . $dir)) {
                continue;
            }
            
            $setup_file = GLPI_ROOT . '/plugins/' . $dir . '/setup.php';
            if (file_exists($setup_file)) {
                // Vérifier si le plugin est actif
                $plugin_obj = new Plugin();
                if ($plugin_obj->isActivated($dir)) {
                    $plugins[] = [
                        'directory' => $dir,
                        'name' => $this->getPluginName($dir),
                        'version' => $this->getPluginVersion($dir),
                        'active' => true
                    ];
                }
            }
        }
        
        return $plugins;
    }
    
    function getPluginName($directory) {
        $function_name = 'plugin_version_' . $directory;

        if (!function_exists($function_name)) {
            $setup_file = GLPI_ROOT . '/plugins/' . $directory . '/setup.php';
            if (file_exists($setup_file)) {
                include_once($setup_file);
            }
        }

        if (function_exists($function_name)) {
            $info = $function_name();
            return isset($info['name']) ? $info['name'] : ucfirst($directory);
        }

        return ucfirst($directory);
    }

    
    function getPluginVersion($directory) {
        $function_name = 'plugin_version_' . $directory;

        if (!function_exists($function_name)) {
            $setup_file = GLPI_ROOT . '/plugins/' . $directory . '/setup.php';
            if (file_exists($setup_file)) {
                include_once($setup_file);
            }
        }

        if (function_exists($function_name)) {
            $info = $function_name();
            return isset($info['version']) ? $info['version'] : 'N/A';
        }

        return 'N/A';
    }   

    
    function displayPluginCard($plugin) {
        global $CFG_GLPI;

        $card = "<div class='prm-plugin-card' style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        $card .= "<h4>" . Html::cleanInputText($plugin['name']) . "</h4>";
        $card .= "<p><strong>Répertoire:</strong> " . Html::cleanInputText($plugin['directory']) . "</p>";
        $card .= "<p><strong>Version:</strong> " . Html::cleanInputText($plugin['version']) . "</p>";
        $card .= "<p><strong>Status:</strong> " . ($plugin['active'] ? 'Actif' : 'Inactif') . "</p>";
        
        $rights_url = $CFG_GLPI['root_doc'] . '/plugins/pluginrightsmanager/front/rights.form.php?plugin=' . urlencode($plugin['directory']);
        $card .= "<div style='margin-top:12px;'>";
        $card .= "<a href='$rights_url' class='prm-btn prm-btn-primary'>Gérer les droits</a>";
        $card .= "</div>";
        
        $card .= "</div>";
        
        return $card;
    }
}