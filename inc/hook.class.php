<?php

class PluginPluginrightsmanagerHook {
    
    /**
     * Hook pour vérifier l'accès avant l'affichage d'un plugin
     */
    static function checkPluginAccess($plugin_name) {
        if (!Session::getLoginUserID()) {
            return true; // Pas connecté, laisser GLPI gérer
        }
        
        // Ignorer notre propre plugin pour éviter les boucles infinies
        if ($plugin_name === 'pluginrightsmanager') {
            return true;
        }
        
        $user_id = Session::getLoginUserID();
        
        // Vérifier si l'utilisateur a le droit d'accès à ce plugin
        if (!PluginPluginrightsmanagerRightsValidator::hasPluginAccess($user_id, $plugin_name, 'access')) {
            // Rediriger vers une page d'erreur ou bloquer l'accès
            Html::displayRightError();
            return false;
        }
        
        return true;
    }
    
    /**
     * Hook pour vérifier les droits d'écriture
     */
    static function checkPluginWrite($plugin_name) {
        if (!Session::getLoginUserID()) {
            return true;
        }
        
        if ($plugin_name === 'pluginrightsmanager') {
            return true;
        }
        
        $user_id = Session::getLoginUserID();
        
        return PluginPluginrightsmanagerRightsValidator::hasPluginAccess($user_id, $plugin_name, 'write');
    }
    
    /**
     * Hook pour vérifier les droits de suppression
     */
    static function checkPluginDelete($plugin_name) {
        if (!Session::getLoginUserID()) {
            return true;
        }
        
        if ($plugin_name === 'pluginrightsmanager') {
            return true;
        }
        
        $user_id = Session::getLoginUserID();
        
        return PluginPluginrightsmanagerRightsValidator::hasPluginAccess($user_id, $plugin_name, 'delete');
    }
}