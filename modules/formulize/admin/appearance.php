<?php
###############################################################################
##     Formulize - ad hoc form creation and reporting module for XOOPS       ##
##                    Copyright (c) Formulize Project                        ##
###############################################################################
##  This program is free software; you can redistribute it and/or modify     ##
##  it under the terms of the GNU General Public License as published by     ##
##  the Free Software Foundation; either version 2 of the License, or        ##
##  (at your option) any later version.                                      ##
##                                                                           ##
##  You may not change or alter any portion of this comment or credits       ##
##  of supporting developers from this source code or any supporting         ##
##  source code which is considered copyrighted (c) material of the          ##
##  original comment or credit authors.                                      ##
##                                                                           ##
##  This program is distributed in the hope that it will be useful,          ##
##  but WITHOUT ANY WARRANTY; without even the implied warranty of           ##
##  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            ##
##  GNU General Public License for more details.                             ##
##                                                                           ##
##  You should have received a copy of the GNU General Public License        ##
##  along with this program; if not, write to the Free Software              ##
##  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA ##
###############################################################################
##  Project: Formulize                                                       ##
###############################################################################

// Appearance admin page: colours, font, and logo for the end-user UI, edited
// one theme at a time. The theme picker works the same way as the one in the
// Theme Editor (admin/themeeditor.php): it lists the installed themes and
// starts on the site's active theme, and switching it reloads this page with
// ?theme= so the form always shows the selected theme's own settings.

if(!defined('_FORMULIZE_UI_PHP_INCLUDED')) { exit(); }

include_once XOOPS_ROOT_PATH . "/modules/formulize/include/appearance.php";

$config_handler = xoops_gethandler('config');
$formulizeModId = getFormulizeModId();

// gather the appearance config item objects, keyed by name
$criteria = new CriteriaCompo(new Criteria('conf_modid', $formulizeModId));
$configItems = array();
foreach($config_handler->getConfigs($criteria) as $configItem) {
    if(strstr($configItem->getVar('conf_name'), 'appearance_')) {
        $configItems[$configItem->getVar('conf_name')] = $configItem;
    }
}

$colourMap = formulize_appearanceColourMap();
$fontMap = formulize_appearanceFontMap();
$saved = false;
$errors = array();

// Which theme's settings are being edited: whatever was picked in the theme select
// (the form posts it back so a save stays on the same theme), falling back to the
// site's active theme. Anything not actually installed resolves back to that too.
$themes = formulize_getAppearanceThemes();
$requestedTheme = isset($_POST['appearance_theme']) ? $_POST['appearance_theme'] : (isset($_GET['theme']) ? $_GET['theme'] : '');
$selectedTheme = formulize_resolveAppearanceTheme($requestedTheme);

// helper to write one appearance config value for the theme being edited, if the
// config item exists (items only exist once the module has been updated in the
// system admin). Only the selected theme's value in the item is touched; the other
// themes' values are carried through untouched.
function formulize_saveAppearanceConfig($name, $value, $theme, &$configItems, &$errors) {
    global $config_handler;
    if(!isset($configItems[$name])) {
        $errors[] = "Could not save setting '$name'. The Formulize module may need to be updated in the system admin.";
        return;
    }
    $stored = formulize_setAppearanceSettingValue($configItems[$name]->getConfValueForOutput(), $theme, $value);
    $configItems[$name]->setConfValueForInput($stored);
    if(!$config_handler->insertConfig($configItems[$name])) {
        $errors[] = "Could not save setting '$name' to the database.";
    }
}

// the value of one appearance setting for the theme being edited
function formulize_currentAppearanceValue($name, $theme, &$configItems) {
    if(!isset($configItems[$name])) {
        return '';
    }
    $values = formulize_parseAppearanceSettingValue($configItems[$name]->getConfValueForOutput());
    return isset($values[$theme]) ? trim($values[$theme]) : '';
}

// delete the logo file a theme is currently using, when the logo is being removed or
// replaced. A logo in the legacy uploads/appearance folder is only deleted if no other
// theme is still pointing at it, since a logo uploaded before the per-theme settings
// existed can have been carried over to more than one theme.
function formulize_deleteAppearanceLogoFile($theme, &$configItems) {
    if(!isset($configItems['appearance_logo'])) {
        return;
    }
    $values = formulize_parseAppearanceSettingValue($configItems['appearance_logo']->getConfValueForOutput());
    $logoFile = isset($values[$theme]) ? basename(trim($values[$theme])) : '';
    $path = formulize_locateAppearanceFile($logoFile, $theme);
    if(!$path) {
        return;
    }
    if(strpos($path, formulize_getLegacyAppearanceDir() . '/') === 0) {
        unset($values[$theme]);
        foreach($values as $otherFile) {
            if(basename(trim($otherFile)) === $logoFile) {
                return;
            }
        }
    }
    unlink($path);
}

if(isset($_POST['appearance_save']) OR isset($_POST['appearance_reset'])) {

    if(isset($_POST['appearance_reset'])) {

        // reset this theme's settings to the defaults, including removing any logo
        // uploaded for it
        formulize_deleteAppearanceLogoFile($selectedTheme, $configItems);
        foreach(formulize_appearanceConfigNames() as $name) {
            formulize_saveAppearanceConfig($name, '', $selectedTheme, $configItems, $errors);
        }

    } else {

        // colours
        foreach($colourMap as $key => $colour) {
            $value = formulize_sanitizeAppearanceColour(isset($_POST['appearance_' . $key]) ? $_POST['appearance_' . $key] : '');
            // store nothing when the user has kept the default, so theme defaults can evolve
            if($value == $colour['default']) {
                $value = '';
            }
            formulize_saveAppearanceConfig('appearance_' . $key, $value, $selectedTheme, $configItems, $errors);
        }

        // font
        $font = (isset($_POST['appearance_font']) AND isset($fontMap[$_POST['appearance_font']])) ? $_POST['appearance_font'] : 'geist';
        $customFont = isset($_POST['appearance_customfont']) ? trim(preg_replace('/[^a-zA-Z0-9 ]/', '', $_POST['appearance_customfont'])) : '';
        if($font == 'custom' AND !$customFont) {
            $font = 'geist';
            $errors[] = "Please enter a Google Font name to use a custom font. The default font has been kept.";
        }
        formulize_saveAppearanceConfig('appearance_font', ($font == 'geist') ? '' : $font, $selectedTheme, $configItems, $errors);
        formulize_saveAppearanceConfig('appearance_customfont', ($font == 'custom') ? $customFont : '', $selectedTheme, $configItems, $errors);

        // logo: remove and/or replace
        $currentLogo = formulize_currentAppearanceValue('appearance_logo', $selectedTheme, $configItems);
        $newUpload = (isset($_FILES['appearance_logo_file']) AND $_FILES['appearance_logo_file']['error'] == UPLOAD_ERR_OK);
        if((isset($_POST['appearance_logo_remove']) OR $newUpload) AND $currentLogo) {
            formulize_deleteAppearanceLogoFile($selectedTheme, $configItems);
            formulize_saveAppearanceConfig('appearance_logo', '', $selectedTheme, $configItems, $errors);
        }
        if($newUpload) {
            $allowedTypes = array(
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'image/gif' => 'gif',
                'image/svg+xml' => 'svg',
                'image/webp' => 'webp',
            );
            $mimeType = mime_content_type($_FILES['appearance_logo_file']['tmp_name']);
            if(isset($allowedTypes[$mimeType])) {
                $fileName = 'formulize-appearance-logo-' . time() . '.' . $allowedTypes[$mimeType];
                $appearanceDir = formulize_prepareAppearanceDir($selectedTheme);
                if($appearanceDir AND move_uploaded_file($_FILES['appearance_logo_file']['tmp_name'], $appearanceDir . '/' . $fileName)) {
                    formulize_saveAppearanceConfig('appearance_logo', $fileName, $selectedTheme, $configItems, $errors);
                } else {
                    $errors[] = "Could not move the uploaded logo into " . formulize_getAppearanceDir($selectedTheme) . ". Check the folder permissions.";
                }
            } else {
                $errors[] = "The logo must be a PNG, JPEG, GIF, SVG, or WebP image.";
            }
        }
    }

    $saved = (count($errors) == 0);
}

// prepare the selected theme's current values for the template, reading from the
// config objects so values saved above are reflected immediately
$currentValues = array();
foreach(formulize_appearanceConfigNames() as $name) {
    $currentValues[$name] = formulize_currentAppearanceValue($name, $selectedTheme, $configItems);
}

// regenerate the selected theme's appearance stylesheet whenever its settings have
// been saved, passing the just-saved values explicitly since cached config reads
// could be stale
if(isset($_POST['appearance_save']) OR isset($_POST['appearance_reset'])) {
    if(!formulize_regenerateAppearanceCss($currentValues, $selectedTheme)) {
        $errors[] = "Could not write the appearance stylesheet to " . formulize_getAppearanceDir($selectedTheme) . ". Check the folder permissions.";
        $saved = false;
    }
}

$colours = array();
foreach($colourMap as $key => $colour) {
    $colours[] = array(
        'key' => $key,
        'label' => $colour['label'],
        'description' => $colour['description'],
        'default' => $colour['default'],
        'value' => $currentValues['appearance_' . $key] ? $currentValues['appearance_' . $key] : $colour['default'],
    );
}

$fonts = array();
foreach($fontMap as $key => $font) {
    $fonts[$key] = $font['label'];
}

// the logo can still be sitting in the legacy uploads/appearance folder on a site
// that had one uploaded before appearance files moved into the theme folders, so
// the shared lookup (which checks both places) builds the preview URL
$logoPath = formulize_locateAppearanceFile($currentValues['appearance_logo'], $selectedTheme);
$logoUrl = '';
if($logoPath) {
    $logoBase = (strpos($logoPath, formulize_getLegacyAppearanceDir() . '/') === 0)
        ? formulize_getLegacyAppearanceUrl()
        : formulize_getAppearanceUrl($selectedTheme);
    $logoUrl = $logoBase . '/' . rawurlencode(basename($logoPath)) . '?v=' . filemtime($logoPath);
}

// warn up front if this theme's appearance folder can't be written, rather than
// letting the admin fill the form in and only then discover the save can't land
$appearanceDirWritable = formulize_appearanceDirIsWritable($selectedTheme);

$adminPage['home_tabs'] = getHomeTabs('appearance');
$adminPage['colours'] = $colours;
$adminPage['fonts'] = $fonts;
$adminPage['currentFont'] = $currentValues['appearance_font'] ? $currentValues['appearance_font'] : 'geist';
$adminPage['currentCustomFont'] = $currentValues['appearance_customfont'];
$adminPage['logoUrl'] = $logoUrl;
$adminPage['saved'] = $saved;
$adminPage['errors'] = $errors;
$adminPage['configsMissing'] = !isset($configItems['appearance_primary']);
$adminPage['themes'] = $themes;
$adminPage['selected_theme'] = $selectedTheme;
$adminPage['active_theme'] = formulize_getDefaultAppearanceTheme();
$adminPage['theme_supports_appearance'] = formulize_themeSupportsAppearance($selectedTheme);
$adminPage['appearance_dir'] = formulize_getAppearanceDir($selectedTheme);
$adminPage['appearance_dir_writable'] = $appearanceDirWritable;
$adminPage['template'] = "db:admin/appearance.html";

$breadcrumbtrail[1]['url'] = "page=home";
$breadcrumbtrail[1]['text'] = "Home";
$breadcrumbtrail[2]['text'] = "Appearance";
