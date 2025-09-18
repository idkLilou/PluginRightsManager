<?php

define('RIGHTS_MANAGER_VERSION', '1.0.0');

/**
 * Hook executed during the GLPI boot sequence, before the session is actually loaded
 * and before the initialization of the active plugins.
 */
function plugin_pluginrightsmanager_boot() {
   // Indicates to GLPI that the `/plugins/rightsmanager/api.php` path is stateless and therefore
   // should not use session cookies nor check for a valid session.
   \Glpi\Http\SessionManager::registerPluginStatelessPath('rightsmanager', '#^/api\.php#');
}

/**
 * Init the hooks of the plugins - Needed
 *
 * @return void
 */
function plugin_init_pluginrightsmanager() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   //required!
   $PLUGIN_HOOKS['csrf_compliant']['pluginrightsmanager'] = true;

   Plugin::registerClass('PluginPluginrightsmanagerProfile');
    
   Plugin::registerClass('PluginPluginrightsmanagerRights');
    
   Plugin::registerClass('PluginPluginrightsmanagerConfig');
    
    // Vérifier les droits d'accès (admin ou super-admin uniquement)
   if (Session::haveRight('config', UPDATE)) {
      $PLUGIN_HOOKS['menu_toadd']['pluginrightsmanager'] = [
         'admin' => 'PluginPluginrightsmanagerConfig'
      ];
        
      $PLUGIN_HOOKS['config_page']['pluginrightsmanager'] = 'front/config.php';
   }

}

/**
 * Get the name and the version of the plugin - Needed
 *
 * @return array
 */
function plugin_version_pluginrightsmanager() {
   return [
      'name'           => 'Plugin Rights Manager',
      'version'        => RIGHTS_MANAGER_VERSION,
      'author'         => 'Lilou DUFAU <a href="https://github.com/LilouDUFAU">Foo Bar</a>',
      'license'        => 'GLPv2+',
      'homepage'       => 'https://github.com/LilouDUFAU/PluginRightsManager',
      'requirements'   => [
         'glpi'   => [
            'min' => '10.0.0'
         ]
      ]
   ];
}

/**
 * Optional : check prerequisites before install : may print errors or add to message after redirect
 *
 * @return boolean
 */
function plugin_pluginrightsmanager_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '10.0.0', 'lt')) {
        echo "Ce plugin nécessite GLPI >= 10.0.0";
        return false;
    }
    return true;
}

/**
 * Check configuration process for plugin : need to return true if succeeded
 * Can display a message only if failure and $verbose is true
 *
 * @param boolean $verbose Enable verbosity. Default to false
 *
 * @return boolean
 */
function plugin_pluginrightsmanager_check_config($verbose = false) {
   return true;
}

/**
 * Optional: defines plugin options.
 *
 * @return array
 */
// function plugin_pluginrightsmanager_options() {
//    return [
//       Plugin::OPTION_AUTOINSTALL_DISABLED => true,
//    ];
// }