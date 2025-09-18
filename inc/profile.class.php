<?php

class PluginPluginrightsmanagerProfile extends Profile {
    
    static function createFirstAccess($ID) {
        $profile = new self();
        $profile->getFromDB($ID);
        
        if ($profile->fields['name'] == 'super-admin') {
            $rights = [
                'pluginrightsmanager:read' => 1,
                'pluginrightsmanager:write' => 1,
                'pluginrightsmanager:delete' => 1,
                'pluginrightsmanager:access' => 1
            ];
            
            foreach ($rights as $right => $value) {
                ProfileRight::updateProfileRights($ID, [$right => $value]);
            }
        }
    }
    
    static function addDefaultProfileInfos($profiles_id, $rights) {
        $profileRight = new ProfileRight();
        
        foreach ($rights as $right => $value) {
            $profileRight->updateProfileRights($profiles_id, [$right => $value]);
        }
    }
    
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        return self::createTabEntry(__('Plugin Rights Manager', 'pluginrightsmanager'));
    }
    
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        $profile = new self();
        $profile->showForm($item->getID());
        return true;
    }
    
    function showForm($profiles_id = 0, $openform = true, $closeform = true) {
        echo "<div class='spaced'>";
        
        if ($openform && ($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))) {
            echo "<form method='post' action='".$this->getFormURL()."'>";
        }
        
        $profile = new Profile();
        $profile->getFromDB($profiles_id);
        
        $rights = $this->getAllRights();
        $profile->displayRightsChoiceMatrix($rights, ['canedit' => $canedit,
                                                     'default_class' => 'tab_bg_2',
                                                     'title' => __('General')]);
        
        if ($canedit && $closeform) {
            echo "<div class='center'>";
            echo Html::hidden('id', ['value' => $profiles_id]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
            echo "</div>\n";
            Html::closeForm();
        }
        
        echo "</div>";
        
        $this->showLegend();
    }
    
    static function getAllRights() {
        $rights = [
            [
                'itemtype' => 'PluginPluginrightsmanagerConfig',
                'label' => __('Plugin Rights Manager', 'pluginrightsmanager'),
                'field' => 'pluginrightsmanager:access'
            ]
        ];
        
        return $rights;
    }
}
// Fonctions utilitaires supplémentaires