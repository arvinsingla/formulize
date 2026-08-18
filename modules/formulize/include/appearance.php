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

// Appearance settings: colours, font, and logo, configured on the Appearance
// page in the Formulize admin UI, stored as module config items, and rendered
// by themes as CSS custom property overrides on :root.
//
// Settings are kept per theme, and the artifacts they produce (the generated
// appearance.css and any uploaded logo) live in an "appearance" folder inside
// the theme's own folder, ie: themes/Lyris/appearance/. That folder is inside
// the web root and is not access-protected the way uploads/ usually is, so the
// files are reachable by the browser wherever the site is deployed.

include_once XOOPS_ROOT_PATH . "/modules/formulize/include/functions.php";

/**
 * The colours users can set, with the design-system default for each, and the
 * CSS custom properties each one drives. Derived tokens use color-mix() so any
 * user-picked base colour produces coherent hover/soft/muted variants.
 *
 * @return array config name (without the appearance_ prefix) => array with
 *               'label', 'description', 'default' (hex), and 'tokens', a map of
 *               CSS custom property => value template where %s is the base colour
 */
function formulize_appearanceColourMap() {
    return array(
        'primary' => array(
            'label' => 'Primary',
            'description' => 'Buttons, links, selected rows, focus rings',
            'default' => '#2e3340',
            'tokens' => array(
                '--c-accent' => '%s',
                '--c-accent-fg' => '%s',
                '--c-accent-hover' => 'color-mix(in srgb, %s 85%%, #000)',
                '--c-accent-soft' => 'color-mix(in srgb, %s 8%%, #fff)',
                '--c-accent-soft-2' => 'color-mix(in srgb, %s 16%%, #fff)',
                '--c-border-focus' => 'color-mix(in srgb, %s 85%%, #000)',
                '--sh-focus' => '0 0 0 3px color-mix(in srgb, %s 15%%, transparent)',
            ),
        ),
        'background' => array(
            'label' => 'Page background',
            'description' => 'The backdrop behind all content',
            'default' => '#f7f7f5',
            'tokens' => array(
                '--c-bg' => '%s',
            ),
        ),
        'surface' => array(
            'label' => 'Surface',
            'description' => 'Cards, tables, panels and other raised areas',
            'default' => '#ffffff',
            'tokens' => array(
                '--c-surface' => '%s',
                '--c-surface-2' => 'color-mix(in srgb, %s 98%%, #555)',
                '--c-surface-3' => 'color-mix(in srgb, %s 94%%, #555)',
            ),
        ),
        'text' => array(
            'label' => 'Text',
            'description' => 'Main text colour; muted and subtle text are derived from it',
            'default' => '#14161a',
            'tokens' => array(
                '--c-text' => '%s',
                '--c-text-muted' => 'color-mix(in srgb, %s 65%%, #fff)',
                '--c-text-subtle' => 'color-mix(in srgb, %s 45%%, #fff)',
            ),
        ),
        'border' => array(
            'label' => 'Borders',
            'description' => 'Dividers and outlines; strong borders are derived',
            'default' => '#e6e6e1',
            'tokens' => array(
                '--c-border' => '%s',
                '--c-border-strong' => 'color-mix(in srgb, %s 90%%, #000)',
            ),
        ),
        'success' => array(
            'label' => 'Success',
            'description' => 'Positive statuses and badges',
            'default' => '#2a7a4f',
            'tokens' => array(
                '--c-success' => '%s',
                '--c-success-soft' => 'color-mix(in srgb, %s 12%%, #fff)',
            ),
        ),
        'warning' => array(
            'label' => 'Warning',
            'description' => 'Caution statuses and badges',
            'default' => '#a86a14',
            'tokens' => array(
                '--c-warning' => '%s',
                '--c-warning-soft' => 'color-mix(in srgb, %s 12%%, #fff)',
            ),
        ),
        'danger' => array(
            'label' => 'Danger',
            'description' => 'Errors, destructive actions',
            'default' => '#b3261e',
            'tokens' => array(
                '--c-danger' => '%s',
                '--c-danger-soft' => 'color-mix(in srgb, %s 12%%, #fff)',
            ),
        ),
        'info' => array(
            'label' => 'Info',
            'description' => 'Informational statuses and badges',
            'default' => '#2e5fa8',
            'tokens' => array(
                '--c-info' => '%s',
                '--c-info-soft' => 'color-mix(in srgb, %s 12%%, #fff)',
            ),
        ),
    );
}

/**
 * Curated font choices. 'google' is the family parameter for the Google Fonts
 * css2 API (false when no webfont needs loading), 'stack' is the CSS
 * font-family value for --font-sans.
 *
 * @return array font key => array with 'label', 'google', 'stack'
 */
function formulize_appearanceFontMap() {
    $fallback = "-apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif";
    return array(
        'geist' => array(
            'label' => 'Geist (default)',
            'google' => 'Geist:wght@400;500;600;700',
            'stack' => "'Geist', $fallback",
        ),
        'system' => array(
            'label' => 'System UI (no webfont)',
            'google' => false,
            'stack' => $fallback,
        ),
        'inter' => array(
            'label' => 'Inter',
            'google' => 'Inter:wght@400;500;600;700',
            'stack' => "'Inter', $fallback",
        ),
        'source-sans' => array(
            'label' => 'Source Sans 3',
            'google' => 'Source+Sans+3:wght@400;500;600;700',
            'stack' => "'Source Sans 3', $fallback",
        ),
        'ibm-plex' => array(
            'label' => 'IBM Plex Sans',
            'google' => 'IBM+Plex+Sans:wght@400;500;600;700',
            'stack' => "'IBM Plex Sans', $fallback",
        ),
        'nunito-sans' => array(
            'label' => 'Nunito Sans',
            'google' => 'Nunito+Sans:wght@400;500;600;700',
            'stack' => "'Nunito Sans', $fallback",
        ),
        'public-sans' => array(
            'label' => 'Public Sans',
            'google' => 'Public+Sans:wght@400;500;600;700',
            'stack' => "'Public Sans', $fallback",
        ),
        'work-sans' => array(
            'label' => 'Work Sans',
            'google' => 'Work+Sans:wght@400;500;600;700',
            'stack' => "'Work Sans', $fallback",
        ),
        'custom' => array(
            'label' => 'Other Google Font…',
            'google' => null, // built at runtime from the appearance_customfont setting
            'stack' => null,
        ),
    );
}

/**
 * The names of all appearance config items, as stored in the module configs
 *
 * @return array of config names
 */
function formulize_appearanceConfigNames() {
    $names = array('appearance_font', 'appearance_customfont', 'appearance_logo');
    foreach (array_keys(formulize_appearanceColourMap()) as $key) {
        $names[] = 'appearance_' . $key;
    }
    return $names;
}

/**
 * The themes installed in this site, as folder name => folder name. Same list
 * the Theme Editor and the system theme preference work from.
 *
 * @return array theme folder name => theme folder name
 */
function formulize_getAppearanceThemes() {
    static $themes = null; // scans the themes folder, and is asked for on every path lookup
    if ($themes === null) {
        $themes = icms_view_theme_Factory::getThemesList();
    }
    return $themes;
}

/**
 * The site's default theme, ie: the one the Appearance page starts on. Falls
 * back to the first installed theme if the configured one is gone (a theme can
 * be deleted from the server without the site config being updated).
 *
 * @return string theme folder name, or '' if the site has no usable themes
 */
function formulize_getDefaultAppearanceTheme() {
    global $xoopsConfig;
    $themes = formulize_getAppearanceThemes();
    $default = isset($xoopsConfig['theme_set']) ? $xoopsConfig['theme_set'] : '';
    if ($default AND isset($themes[$default])) {
        return $default;
    }
    return $themes ? reset($themes) : '';
}

/**
 * The theme the current page is actually being rendered with, which is the one
 * whose appearance settings and generated files apply right now. Users can be
 * on a theme other than the site default, so the live theme object is asked
 * first, falling back to the site default.
 *
 * @return string theme folder name, or '' if the site has no usable themes
 */
function formulize_getActiveAppearanceTheme() {
    $themes = formulize_getAppearanceThemes();
    if (isset($GLOBALS['xoTheme']) AND is_object($GLOBALS['xoTheme'])) {
        $folder = $GLOBALS['xoTheme']->folderName;
        if ($folder AND isset($themes[$folder])) {
            return $folder;
        }
    }
    return formulize_getDefaultAppearanceTheme();
}

/**
 * Resolve a theme name to work with: the one asked for when it is installed,
 * otherwise the theme rendering the site. Used everywhere a theme name can
 * come in from a request or from settings saved for a theme that has since
 * been deleted, so nothing downstream ever builds a path from a bogus name.
 *
 * @param string|null $theme requested theme folder name, or null for the active theme
 * @return string an installed theme's folder name, or '' if the site has no usable themes
 */
function formulize_resolveAppearanceTheme($theme = null) {
    if ($theme !== null AND $theme !== '') {
        $themes = formulize_getAppearanceThemes();
        if (isset($themes[$theme])) {
            return $theme;
        }
    }
    return formulize_getActiveAppearanceTheme();
}

/**
 * Whether a theme actually does anything with the appearance settings, ie: it
 * calls formulize_renderAppearanceHead() to pull in the generated stylesheet.
 * Themes that don't (older themes that aren't built on the Formulize design
 * tokens) can still have settings saved for them, but they won't show, so the
 * admin page says so rather than leaving the admin wondering.
 *
 * @param string|null $theme theme folder name, defaults to the active theme
 * @return boolean
 */
function formulize_themeSupportsAppearance($theme = null) {
    static $support = array();
    $theme = formulize_resolveAppearanceTheme($theme);
    if (!isset($support[$theme])) {
        $themeFile = ICMS_THEME_PATH . '/' . $theme . '/theme.html';
        $support[$theme] = ($theme AND file_exists($themeFile)
            AND strpos(file_get_contents($themeFile), 'formulize_renderAppearanceHead') !== false);
    }
    return $support[$theme];
}

/**
 * Split a stored appearance config value into its per-theme values.
 *
 * Values are stored one config item per setting, with the per-theme values
 * packed into the item as "Lyris:#ff0000|Anari:#00ff00". Neither ":" nor "|"
 * can occur in a theme folder name that we accept or in any value we write
 * (colours are hex, fonts are keys or alphanumeric family names, logos are
 * filenames we generate), so the packing is unambiguous.
 *
 * A value with no ":" in it is in the pre-per-theme format, when there was one
 * site-wide set of settings. That is exactly what the site is displaying today,
 * so it is read as belonging to the site's default theme. Nothing is rewritten
 * on read: the old value keeps working until the admin saves that theme, at
 * which point formulize_setAppearanceSettingValue() carries it over into the
 * per-theme format (see the note there).
 *
 * @param string $stored the raw config value
 * @return array theme folder name => value
 */
function formulize_parseAppearanceSettingValue($stored) {
    $stored = trim((string) $stored);
    if ($stored === '') {
        return array();
    }
    if (strpos($stored, ':') === false) {
        $default = formulize_getDefaultAppearanceTheme();
        return $default ? array($default => $stored) : array();
    }
    $values = array();
    foreach (explode('|', $stored) as $entry) {
        $position = strpos($entry, ':');
        if ($position === false OR $position === 0) {
            continue;
        }
        $values[substr($entry, 0, $position)] = trim(substr($entry, $position + 1));
    }
    return $values;
}

/**
 * Pack per-theme values back into a single config value. Themes with an empty
 * value are dropped, so "no entry" and "set to the default" are the same thing.
 * Entries for themes that are no longer installed are kept as they are found,
 * so removing a theme temporarily (or renaming a folder) doesn't throw away
 * its settings.
 *
 * @param array $values theme folder name => value
 * @return string the packed config value
 */
function formulize_encodeAppearanceSettingValue($values) {
    $parts = array();
    foreach ($values as $theme => $value) {
        $value = str_replace(array('|', ':'), '', trim((string) $value));
        if ($value === '' OR $theme === '' OR strpbrk($theme, '|:') !== false) {
            continue;
        }
        $parts[] = $theme . ':' . $value;
    }
    return implode('|', $parts);
}

/**
 * Set one theme's value inside a stored config value, leaving every other
 * theme's value alone.
 *
 * Because formulize_parseAppearanceSettingValue() maps a pre-per-theme value
 * onto the site's default theme, the first save after upgrading rewrites the
 * item in the per-theme format with the old site-wide setting intact as the
 * default theme's setting. Existing settings are therefore never lost, and
 * never silently applied to themes the admin hasn't configured.
 *
 * @param string $stored the raw config value being updated
 * @param string $theme  theme folder name to set the value for
 * @param string $value  the new value ('' to clear it)
 * @return string the packed config value to store
 */
function formulize_setAppearanceSettingValue($stored, $theme, $value) {
    $values = formulize_parseAppearanceSettingValue($stored);
    if (trim((string) $value) === '') {
        unset($values[$theme]);
    } else {
        $values[$theme] = $value;
    }
    return formulize_encodeAppearanceSettingValue($values);
}

/**
 * Read a theme's saved appearance settings from the module configs. Empty
 * string means "use the theme default". Cached per theme for the request.
 *
 * @param string|null $theme theme folder name, defaults to the theme rendering the page
 * @return array config name => saved value (all appearance_* keys present)
 */
function formulize_getAppearanceSettings($theme = null) {
    static $cache = array();
    $theme = formulize_resolveAppearanceTheme($theme);
    if (!isset($cache[$theme])) {
        $config_handler = xoops_gethandler('config');
        $formulizeConfig = $config_handler->getConfigsByCat(0, getFormulizeModId());
        $settings = array();
        foreach (formulize_appearanceConfigNames() as $name) {
            $values = formulize_parseAppearanceSettingValue(isset($formulizeConfig[$name]) ? $formulizeConfig[$name] : '');
            $settings[$name] = isset($values[$theme]) ? trim($values[$theme]) : '';
        }
        $cache[$theme] = $settings;
    }
    return $cache[$theme];
}

/**
 * Validate a user-supplied colour value. Only hex colours are accepted.
 *
 * @param string $value the submitted colour
 * @return string the normalized hex colour, or '' if invalid/empty
 */
function formulize_sanitizeAppearanceColour($value) {
    $value = trim($value);
    return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : '';
}

/**
 * Resolve the Google Fonts css2 URL and the --font-sans value for the current
 * settings. Geist Mono is always requested alongside, since --font-mono uses it.
 *
 * @param array|null $settings appearance settings to use, defaults to the saved ones
 * @return array with 'url' (string|false) and 'stack' (string|false when default)
 */
function formulize_getAppearanceFont($settings = null) {
    $settings = is_array($settings) ? $settings : formulize_getAppearanceSettings();
    $fonts = formulize_appearanceFontMap();
    $choice = isset($fonts[$settings['appearance_font']]) ? $settings['appearance_font'] : 'geist';
    $googleFamily = $fonts[$choice]['google'];
    $stack = $fonts[$choice]['stack'];
    if ($choice == 'custom') {
        $family = trim(preg_replace('/[^a-zA-Z0-9 ]/', '', $settings['appearance_customfont']));
        if ($family) {
            $googleFamily = str_replace(' ', '+', $family) . ':wght@400;500;600;700';
            $stack = "'" . $family . "', " . $fonts['system']['stack'];
        } else {
            // no usable custom family entered, fall back to the default font
            $choice = 'geist';
            $googleFamily = $fonts['geist']['google'];
            $stack = $fonts['geist']['stack'];
        }
    }
    $url = false;
    if ($googleFamily) {
        $url = 'https://fonts.googleapis.com/css2?family=' . $googleFamily . '&family=Geist+Mono:wght@400;500&display=swap';
    }
    return array(
        'url' => $url,
        'stack' => ($choice == 'geist') ? false : $stack, // false means the theme's own default applies
    );
}

/**
 * The folder where a theme's appearance artifacts live: the uploaded logo and
 * the generated appearance.css. Inside the theme's own folder, so both are
 * served directly as static files from a location that is publicly reachable
 * (unlike uploads/, which is commonly protected by the web server).
 *
 * @param string|null $theme theme folder name, defaults to the active theme
 * @return string filesystem path of the appearance folder, no trailing slash
 */
function formulize_getAppearanceDir($theme = null) {
    return ICMS_THEME_PATH . '/' . formulize_resolveAppearanceTheme($theme) . '/appearance';
}

/**
 * The URL of a theme's appearance folder
 *
 * @param string|null $theme theme folder name, defaults to the active theme
 * @return string URL of the appearance folder, no trailing slash
 */
function formulize_getAppearanceUrl($theme = null) {
    return ICMS_THEME_URL . '/' . formulize_resolveAppearanceTheme($theme) . '/appearance';
}

/**
 * The folder appearance artifacts were written to before they moved into the
 * theme folders. Still read from, so a site that has a logo uploaded under the
 * old scheme keeps showing it until a new one is uploaded. Never written to.
 *
 * @return string filesystem path of the legacy appearance folder, no trailing slash
 */
function formulize_getLegacyAppearanceDir() {
    return XOOPS_ROOT_PATH . '/uploads/appearance';
}

/**
 * The URL of the legacy appearance folder
 *
 * @return string URL of the legacy appearance folder, no trailing slash
 */
function formulize_getLegacyAppearanceUrl() {
    return XOOPS_URL . '/uploads/appearance';
}

/**
 * Deprecated aliases for formulize_getAppearanceDir()/Url(), from when there
 * was a single appearance folder in uploads/. Kept so any theme or custom code
 * calling them keeps working, and keeps pointing at the file that is actually
 * in use.
 *
 * @param string|null $theme theme folder name, defaults to the active theme
 * @return string path/URL of the appearance folder, no trailing slash
 */
function formulize_getAppearanceUploadDir($theme = null) {
    return formulize_getAppearanceDir($theme);
}
function formulize_getAppearanceUploadUrl($theme = null) {
    return formulize_getAppearanceUrl($theme);
}

/**
 * Locate one of a theme's appearance files, checking the theme's appearance
 * folder first and then the legacy uploads folder, so files written before the
 * move are still found.
 *
 * @param string $file  bare filename, as stored in the settings
 * @param string|null $theme theme folder name, defaults to the active theme
 * @return string path of the file, or '' if it isn't in either folder
 */
function formulize_locateAppearanceFile($file, $theme = null) {
    $file = basename((string) $file);
    if ($file === '') {
        return '';
    }
    foreach (array(formulize_getAppearanceDir($theme), formulize_getLegacyAppearanceDir()) as $dir) {
        if (file_exists($dir . '/' . $file)) {
            return $dir . '/' . $file;
        }
    }
    return '';
}

/**
 * The filesystem path of the uploaded custom logo, if any
 *
 * @param string|null $theme theme folder name, defaults to the active theme
 * @return string path of the logo file, or '' when no custom logo is set
 */
function formulize_getAppearanceLogoPath($theme = null) {
    $settings = formulize_getAppearanceSettings($theme);
    // stored as a bare filename in the theme's appearance folder
    return formulize_locateAppearanceFile($settings['appearance_logo'], $theme);
}

/**
 * The URL of the uploaded custom logo, if any. A direct static URL, with the
 * file's modification time included for cache busting.
 *
 * @param string|null $theme theme folder name, defaults to the active theme
 * @return string URL of the logo, or '' when no custom logo is set
 */
function formulize_getAppearanceLogoUrl($theme = null) {
    $path = formulize_getAppearanceLogoPath($theme);
    if ($path) {
        $base = (strpos($path, formulize_getLegacyAppearanceDir() . '/') === 0)
            ? formulize_getLegacyAppearanceUrl()
            : formulize_getAppearanceUrl($theme);
        return $base . '/' . rawurlencode(basename($path)) . '?v=' . filemtime($path);
    }
    return '';
}

/**
 * The filesystem path of a theme's generated appearance stylesheet
 *
 * @param string|null $theme theme folder name, defaults to the active theme
 * @return string path of the generated CSS file
 */
function formulize_getAppearanceCssPath($theme = null) {
    return formulize_getAppearanceDir($theme) . '/appearance.css';
}

/**
 * Make sure a theme's appearance folder exists and can be written to, creating
 * it and trying to correct the permissions if not. A theme folder that the web
 * server can't write is a normal state on a locked-down deployment, so this
 * reports failure rather than raising anything: callers surface it to the admin
 * (the Appearance page) or fall back to inline styles (the theme head).
 *
 * @param string|null $theme theme folder name, defaults to the active theme
 * @return string the folder path, or false if it can't be created/written
 */
function formulize_prepareAppearanceDir($theme = null) {
    $theme = formulize_resolveAppearanceTheme($theme);
    if ($theme === '') {
        return false;
    }
    $dir = formulize_getAppearanceDir($theme);
    if (!is_dir($dir)) {
        if (!is_dir(dirname($dir)) OR !is_writable(dirname($dir))) {
            return false;
        }
        if (!@mkdir($dir, 0755, true) AND !is_dir($dir)) {
            return false;
        }
    }
    if (!is_writable($dir)) {
        @chmod($dir, 0755);
    }
    return is_writable($dir) ? $dir : false;
}

/**
 * Whether a theme's appearance folder can actually be written, established by
 * writing a file and deleting it again. is_writable() is not enough on its own:
 * on some deployments (containers with bind-mounted volumes, for instance) it
 * reports a folder as writable that the web server still can't write to, and
 * the point of asking is to warn the admin before they fill in the form.
 *
 * @param string|null $theme theme folder name, defaults to the active theme
 * @return boolean
 */
function formulize_appearanceDirIsWritable($theme = null) {
    $dir = formulize_prepareAppearanceDir($theme);
    if ($dir === false) {
        return false;
    }
    $probe = $dir . '/.formulize-write-test';
    if (@file_put_contents($probe, '') === false) {
        return false;
    }
    @unlink($probe);
    return true;
}

/**
 * The CSS custom property overrides the current settings call for: the font
 * stack when a non-default font is chosen, and the colour tokens (with their
 * derived variants) for every colour that differs from the design defaults.
 *
 * @param array|null $settings appearance settings to use, defaults to the saved ones
 * @return array CSS custom property => value
 */
function formulize_getAppearanceCssOverrides($settings = null) {
    $settings = is_array($settings) ? $settings : formulize_getAppearanceSettings();
    $overrides = array();
    $font = formulize_getAppearanceFont($settings);
    if ($font['stack']) {
        $overrides['--font-sans'] = $font['stack'];
    }
    foreach (formulize_appearanceColourMap() as $key => $colour) {
        $value = formulize_sanitizeAppearanceColour($settings['appearance_' . $key]);
        if ($value AND $value != $colour['default']) {
            foreach ($colour['tokens'] as $token => $template) {
                $overrides[$token] = sprintf($template, $value);
            }
        }
    }
    return $overrides;
}

/**
 * Build the appearance stylesheet for a set of settings, from the
 * appearance.css.tpl Smarty template.
 *
 * @param array|null $settings appearance settings to use, defaults to the saved ones
 * @return string the CSS
 */
function formulize_buildAppearanceCss($settings = null) {
    $font = formulize_getAppearanceFont($settings);
    require_once XOOPS_ROOT_PATH . '/class/template.php';
    $tpl = new XoopsTpl();
    $tpl->assign('fontUrl', $font['url']);
    $tpl->assign('overrides', formulize_getAppearanceCssOverrides($settings));
    return $tpl->fetch('file:' . XOOPS_ROOT_PATH . '/modules/formulize/templates/appearance.css.tpl');
}

/**
 * Regenerate a theme's appearance stylesheet and write it to that theme's
 * appearance folder. Called when settings are saved, and lazily by
 * formulize_renderAppearanceHead when the file is missing. The file is always
 * generated, even with all-default settings, so themes can link it
 * unconditionally (the default file just imports the default webfont).
 *
 * @param array|null $settings appearance settings to use, defaults to the saved
 *                             ones. Pass the values explicitly when regenerating
 *                             right after a save, to sidestep stale config caches.
 * @param string|null $theme   theme folder name, defaults to the active theme
 * @return boolean whether the file was written successfully
 */
function formulize_regenerateAppearanceCss($settings = null, $theme = null) {
    $theme = formulize_resolveAppearanceTheme($theme);
    $settings = is_array($settings) ? $settings : formulize_getAppearanceSettings($theme);
    $dir = formulize_prepareAppearanceDir($theme);
    if ($dir === false) {
        return false;
    }
    return file_put_contents($dir . '/appearance.css', formulize_buildAppearanceCss($settings)) !== false;
}

/**
 * Render the head markup a theme needs for the appearance settings: preconnect
 * hints for the webfont host, and the link tag for the generated stylesheet,
 * which is regenerated first if it doesn't exist yet. Goes after the theme's
 * own stylesheet links, so the overrides win the cascade.
 *
 * The settings and the stylesheet are those of the theme rendering the page,
 * so each theme gets its own look. If the stylesheet can't be written (a theme
 * folder the web server has no write access to), the same CSS is emitted inline
 * instead, so the configured appearance is never simply lost.
 *
 * @return string HTML to print in the head, after the theme stylesheet links
 */
function formulize_renderAppearanceHead() {
    $theme = formulize_getActiveAppearanceTheme();
    $cssPath = formulize_getAppearanceCssPath($theme);
    $cssExists = file_exists($cssPath);
    if (!$cssExists) {
        $cssExists = formulize_regenerateAppearanceCss(null, $theme);
    }
    $html = '';
    $font = formulize_getAppearanceFont(formulize_getAppearanceSettings($theme));
    if ($font['url']) {
        $html .= '<link rel="preconnect" href="https://fonts.googleapis.com" />' . "\n";
        $html .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />' . "\n";
    }
    if ($cssExists) {
        $html .= '<link rel="stylesheet" type="text/css" media="all" href="' . formulize_getAppearanceUrl($theme) . '/appearance.css?v=' . filemtime($cssPath) . '" />' . "\n";
    } else {
        $html .= '<style type="text/css" media="all">' . "\n" . formulize_buildAppearanceCss(formulize_getAppearanceSettings($theme)) . "\n" . '</style>' . "\n";
    }
    return $html;
}
