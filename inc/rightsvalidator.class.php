<?php

class PluginPluginrightsmanagerRightsValidator {
    
    /**
     * Vérifie si un utilisateur a accès à un plugin donné
     */
    static function hasPluginAccess($user_id, $plugin_name, $right_type = 'access') {
        global $DB;
        
        // Vérifier d'abord les droits directs utilisateur
        $query = "SELECT right_value FROM glpi_plugin_pluginrightsmanager_rights 
                  WHERE plugin_name = '" . $DB->escape($plugin_name) . "' 
                  AND user_id = " . intval($user_id) . " 
                  AND right_type = '" . $DB->escape($right_type) . "'";
        
        $result = $DB->query($query);
        if ($DB->numrows($result) > 0) {
            $data = $DB->fetchAssoc($result);
            return (bool)$data['right_value'];
        }
        
        // Vérifier les droits par groupe
        $user = new User();
        $user->getFromDB($user_id);
        $groups = Group_User::getUserGroups($user_id);
        
        foreach ($groups as $group) {
            $query = "SELECT right_value FROM glpi_plugin_pluginrightsmanager_rights 
                      WHERE plugin_name = '" . $DB->escape($plugin_name) . "' 
                      AND group_id = " . intval($group['id']) . " 
                      AND right_type = '" . $DB->escape($right_type) . "'";
            
            $result = $DB->query($query);
            if ($DB->numrows($result) > 0) {
                $data = $DB->fetchAssoc($result);
                return (bool)$data['right_value'];
            }
        }
        
        // Vérifier les droits par profil
        $profile_user = new Profile_User();
        $profiles = $profile_user->getProfiles($user_id);
        
        foreach ($profiles as $profile) {
            $query = "SELECT right_value FROM glpi_plugin_pluginrightsmanager_rights 
                      WHERE plugin_name = '" . $DB->escape($plugin_name) . "' 
                      AND profile_id = " . intval($profile['id']) . " 
                      AND right_type = '" . $DB->escape($right_type) . "'";
            
            $result = $DB->query($query);
            if ($DB->numrows($result) > 0) {
                $data = $DB->fetchAssoc($result);
                return (bool)$data['right_value'];
            }
        }
        
        // Par défaut, pas d'accès si aucun droit défini
        return false;
    }
    
    /**
     * Vérifie les droits spécifiques d'un plugin
     */
    static function hasCustomRight($user_id, $plugin_name, $custom_right) {
        global $DB;
        
        // Vérifier si le droit personnalisé existe pour ce plugin
        $query = "SELECT id FROM glpi_plugin_pluginrightsmanager_custom_rights 
                  WHERE plugin_name = '" . $DB->escape($plugin_name) . "' 
                  AND right_name = '" . $DB->escape($custom_right) . "'";
        
        $result = $DB->query($query);
        if ($DB->numrows($result) == 0) {
            return false; // Le droit personnalisé n'existe pas
        }
        
        // Utiliser la même logique que hasPluginAccess mais pour les droits personnalisés
        return self::hasPluginAccess($user_id, $plugin_name, $custom_right);
    }
}