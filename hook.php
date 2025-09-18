<?php
/**
 * Install hook
 *
 * @return boolean
 */
function plugin_pluginrightsmanager_install() {
    global $DB;

    $migration = new Migration(100);

    // Table principale des droits personnalisés
    if (!$DB->tableExists('glpi_plugin_pluginrightsmanager_rights')) {
        $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_pluginrightsmanager_rights` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `plugin_name` varchar(255) NOT NULL,
            `entity_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `user_id` int(11) UNSIGNED DEFAULT NULL,
            `group_id` int(11) UNSIGNED DEFAULT NULL,
            `profile_id` int(11) UNSIGNED DEFAULT NULL,
            `right_type` varchar(255) NOT NULL,
            `right_value` tinyint(1) NOT NULL DEFAULT '0',
            `custom_rights` text,
            `date_creation` TIMESTAMP NULL DEFAULT NULL,
            `date_mod` TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_right` (`plugin_name`,`right_type`,`user_id`,`group_id`,`profile_id`),
            KEY `plugin_name` (`plugin_name`),
            KEY `user_id` (`user_id`),
            KEY `group_id` (`group_id`),
            KEY `profile_id` (`profile_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $DB->queryOrDie($query, $DB->error());
    }

    // Table pour les droits spécifiques par plugin
    if (!$DB->tableExists('glpi_plugin_pluginrightsmanager_custom_rights')) {
        $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_pluginrightsmanager_custom_rights` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `plugin_name` varchar(255) NOT NULL,
            `right_name` varchar(255) NOT NULL,
            `right_label` varchar(255) NOT NULL,
            `date_creation` TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `plugin_right` (`plugin_name`, `right_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $DB->queryOrDie($query, $DB->error());
    }
    
    // Configuration du plugin
    if (!$DB->tableExists('glpi_plugin_pluginrightsmanager_configs')) {
        $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_pluginrightsmanager_configs` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `value` text,
            PRIMARY KEY (`id`),
            UNIQUE KEY `name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $DB->queryOrDie($query, $DB->error());
    }
    
    $migration->executeMigration();
   
    return true;
}

/**
 * Uninstall hook
 *
 * @return boolean
 */
function plugin_pluginrightsmanager_uninstall() {
   global $DB;

   $tables = [
        'pluginrightsmanager_rights',
        'pluginrightsmanager_custom_rights',
        'pluginrightsmanager_configs'
   ];

   foreach ($tables as $table) {
       $tablename = 'glpi_plugin_' . $table;
       if ($DB->tableExists($tablename)) {
           $DB->queryOrDie(
               "DROP TABLE `$tablename`",
               $DB->error()
           );
        }
   }
   return true;
}