# Plugin Rights Manager pour GLPI

[![GLPI Version](https://img.shields.io/badge/GLPI-v10.0.19+-blue.svg)](https://glpi-project.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv2+-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Status](https://img.shields.io/badge/Status-Stable-brightgreen.svg)]()

## 📋 Description

Le **Plugin Rights Manager** est un plugin avancé pour GLPI qui permet une gestion granulaire et centralisée des droits d'accès à tous les plugins installés sur votre instance. Il offre un contrôle précis des permissions par utilisateur, groupe ou profil avec différents niveaux de droits (accès, lecture, écriture, suppression).

### ✨ Fonctionnalités principales

- **🔍 Détection automatique** de tous les plugins installés et actifs
- **👥 Gestion des droits** par utilisateur, groupe ou profil
- **🔐 Types de droits multiples** : accès, lecture, écriture, suppression
- **⚙️ Droits personnalisés** spécifiques à chaque plugin
- **🛡️ Sécurité renforcée** avec contrôle d'accès strict
- **🌐 Interface multilingue** (Français/Anglais)
- **📱 Interface responsive** et moderne
- **🔎 Barre de recherche** pour filtrer les plugins par nom

## 📦 Installation

### Prérequis

- GLPI 10.0.19 ou supérieur
- PHP 7.4 ou supérieur  
- MySQL 5.7 ou supérieur
- Profil administrateur sur GLPI

### Méthode 1 : Installation depuis GitHub

```bash
cd /var/www/glpi/plugins
git clone https://github.com/LilouDUFAU/glpi-plugin-rights-manager.git pluginrightsmanager
chown -R www-data:www-data pluginrightsmanager
chmod -R 755 pluginrightsmanager
```

### Méthode 2 : Installation manuelle

1. Téléchargez la dernière release
2. Extrayez l'archive dans `/var/www/glpi/plugins/pluginrightsmanager/`
3. Définissez les permissions appropriées

### Activation

1. Connectez-vous à GLPI avec un compte super-administrateur
2. Allez dans **Configuration → Plugins**
3. Trouvez "Plugin Rights Manager" et cliquez sur **Installer**
4. Cliquez sur **Activer**

### ⚠️ Étape supplémentaire après activation

Pour des raisons de sécurité, **vous devez ajouter manuellement un droit dans la table `glpi_plugin_pluginrightsmanager_configs`** via phpMyAdmin ou votre visualiseur de base de données préféré pour accéder à l'affichage du plugin :

- Ouvrez phpMyAdmin (ou équivalent)
- Accédez à la base de données de GLPI
- Rendez-vous dans la table `glpi_plugin_pluginrightsmanager_configs`
- Ajoutez une nouvelle entrée avec :
    - `name` : `access`
    - `value` : `1`

> Sans cette étape, le plugin ne sera pas visible dans le menu même après activation.

## 🚀 Utilisation

### Accès au plugin

Le plugin est accessible via : **Administration → Plugin Rights Manager**

> ⚠️ **Important** : Seuls les utilisateurs avec profil administrateur ou super-administrateur peuvent accéder au plugin.

### Gestion des droits

#### 1. Vue d'ensemble des plugins
- La page principale affiche tous les plugins actifs détectés
- Une barre de recherche permet de filtrer les plugins par nom
- Chaque plugin présente ses informations (nom, version, statut)
- Cliquez sur **"Gérer les droits"** pour configurer les permissions

#### 2. Types de droits disponibles

| Droit | Description |
|-------|-------------|
| **Accès** | Autoriser l'accès global au plugin |
| **Lecture** | Permettre la consultation des données |
| **Écriture** | Autoriser la modification des données |
| **Suppression** | Permettre la suppression des données |

#### 3. Modes d'assignation

- **👤 Utilisateur** : Assigner un droit à un utilisateur spécifique
- **👥 Groupe** : Assigner un droit à tous les membres d'un groupe
- **🏷️ Profil** : Assigner un droit à tous les utilisateurs d'un profil

#### 4. Hiérarchie des droits

1. **Droits utilisateur** (priorité maximale)
2. **Droits de groupe** 
3. **Droits de profil**
4. **Refus par défaut** (si aucun droit défini)

### Droits personnalisés

Pour chaque plugin, vous pouvez créer des droits spécifiques :
- Définir un nom technique pour le droit
- Ajouter un libellé descriptif
- Assigner ces droits selon les mêmes règles que les droits standards

## 🏗️ Architecture

### Structure des fichiers

```
📁 pluginrightsmanager/
├── 📄 setup.php
├── 📄 hook.php
├── 📄 README.md
├── 📁 inc/
│   ├── 📄 config.class.php
│   ├── 📄 rights.class.php
│   ├── 📄 profile.class.php
│   ├── 📄 rightsvalidator.class.php
│   └── 📄 hook.class.php
├── 📁 front/
│   ├── 📄 config.php
│   └── 📄 rights.form.php
├── 📁 ajax/
│   ├── 📄 users.php
│   ├── 📄 groups.php
│   ├── 📄 profiles.php
│   └── 📄 delete_right.php
├── 📁 css/
│   └── 📄 pluginrightsmanager.css
├── 📁 js/
│   └── 📄 pluginrightsmanager.js
└── 📁 locales/
    ├── 📄 fr_FR.php
    └── 📄 en_GB.php
```

### Base de données

Le plugin crée 3 tables :

- `glpi_plugin_pluginrightsmanager_rights` : Droits principaux
- `glpi_plugin_pluginrightsmanager_custom_rights` : Droits personnalisés
- `glpi_plugin_pluginrightsmanager_configs` : Configuration

## 🔧 API pour développeurs

### Vérifier l'accès à un plugin

```php
$hasAccess = PluginPluginrightsmanagerRightsValidator::hasPluginAccess(
    $user_id, 
    'nom_du_plugin', 
    'access'
);

if (!$hasAccess) {
    // Bloquer l'accès
    Html::displayRightError();
    return false;
}
```

### Vérifier un droit personnalisé

```php
$hasCustomRight = PluginPluginrightsmanagerRightsValidator::hasCustomRight(
    $user_id, 
    'nom_du_plugin', 
    'custom_right_name'
);
```

### Hooks pour plugins externes

```php
// Dans le setup.php de votre plugin
function plugin_init_monplugin() {
    global $PLUGIN_HOOKS;
    
    // Vérifier l'accès avant affichage
    $PLUGIN_HOOKS['pre_item_form']['monplugin'] = [
        'PluginPluginrightsmanagerHook', 
        'checkPluginAccess'
    ];
}
```

## 🛠️ Dépannage

### Problèmes courants

**Plugin non visible dans le menu**
- Vérifiez votre profil utilisateur (admin/super-admin requis)
- Confirmez que le plugin est activé
- **Vérifiez que l'entrée `access` existe dans la table `glpi_plugin_pluginrightsmanager_configs`**

**Erreurs JavaScript**
- Vérifiez la console navigateur
- Assurez-vous que jQuery est chargé

**Problèmes de droits**
- Vérifiez la table `glpi_plugin_pluginrightsmanager_rights`
- Testez avec un compte super-admin

### Logs

Les erreurs sont enregistrées dans les logs GLPI :
```
files/_log/php-errors.log
files/_log/sql-errors.log
```

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. **Fork** le projet
2. **Créez** une branche pour votre fonctionnalité (`git checkout -b feature/nouvelle-fonctionnalite`)
3. **Committez** vos changements (`git commit -am 'Ajouter nouvelle fonctionnalité'`)
4. **Push** vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. **Ouvrez** une Pull Request

### Standards de code

- Respecter les conventions de codage GLPI
- Documenter les nouvelles fonctions
- Tester les modifications avant soumission
- Inclure les traductions FR/EN

## 📝 Changelog

### Version 1.0.0
- ✨ Détection automatique des plugins
- ✨ Gestion des droits par utilisateur/groupe/profil
- ✨ Interface d'administration complète
- ✨ API pour développeurs
- ✨ Support multilingue
- ✨ Documentation complète
- ✨ Barre de recherche sur la liste des plugins

## 🐛 Signaler un bug

Si vous rencontrez un problème :

1. Vérifiez que le problème n'est pas déjà signalé dans les [Issues](../../issues)
2. Créez une nouvelle issue en incluant :
   - Version de GLPI
   - Version du plugin
   - Description détaillée du problème
   - Étapes pour reproduire
   - Logs d'erreur si disponibles

## 📄 Licence

Ce projet est sous licence **GPL v2+** - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👨‍💻 Auteur

**Lilou DUFAU** - [Votre GitHub](https://github.com/LilouDUFAU)

## 🙏 Remerciements

- Équipe GLPI pour le framework
- Communauté GLPI pour les retours et suggestions
- Contributeurs du projet

---

⭐ **N'hésitez pas à mettre une étoile si ce plugin vous a été utile !**
