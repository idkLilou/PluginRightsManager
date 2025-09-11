<?php
/**
 * Install hook
 *
 * @return boolean
 */
function plugin_rightsmanager_install() {
    global $DB;

    $migration = new Migration(100);

    // Table principale des droits personnalisés
    if (!$DB->tableExists('glpi_plugin_rightsmanager_rights')) {
        $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_rightsmanager_rights` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `plugin_name` varchar(255) NOT NULL,
            `entity_id` int(11) NOT NULL DEFAULT '0',
            `user_id` int(11) DEFAULT NULL,
            `group_id` int(11) DEFAULT NULL,
            `profile_id` int(11) DEFAULT NULL,
            `right_type` enum('read','write','delete','access') NOT NULL,
            `right_value` tinyint(1) NOT NULL DEFAULT '0',
            `custom_rights` text,
            `date_creation` datetime DEFAULT NULL,
            `date_mod` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `plugin_name` (`plugin_name`),
            KEY `user_id` (`user_id`),
            KEY `group_id` (`group_id`),
            KEY `profile_id` (`profile_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $migration->addQuery($query);
    }

    // Table pour les droits spécifiques par plugin
    if (!$DB->tableExists('glpi_plugin_rightsmanager_custom_rights')) {
        $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_rightsmanager_custom_rights` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `plugin_name` varchar(255) NOT NULL,
            `right_name` varchar(255) NOT NULL,
            `right_label` varchar(255) NOT NULL,
            `date_creation` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `plugin_right` (`plugin_name`, `right_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $migration->addQuery($query);
    }
    
    // Configuration du plugin
    if (!$DB->tableExists('glpi_plugin_rightsmanager_configs')) {
        $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_rightsmanager_configs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `value` text,
            PRIMARY KEY (`id`),
            UNIQUE KEY `name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $migration->addQuery($query);
    }
    
    $migration->executeMigration();
   
    return true;
}

/**
 * Uninstall hook
 *
 * @return boolean
 */
function plugin_rightsmanager_uninstall() {
   global $DB;

   $tables = [
        'glpi_plugin_rightsmanager_rights',
        'glpi_plugin_rightsmanager_custom_rights',
        'glpi_plugin_rightsmanager_configs'
   ];

   foreach ($tables as $table) {
        $tablename = 'glpi_plugin_' . $table;
        if ($DB->tableExists($tablename)) {
            $DB->queryOrDie(
                "DROP TABLE '$tablename'",
                $DB->error()
            );
        }
   }
   return true;
}