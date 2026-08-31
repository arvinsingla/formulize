<?php
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

// Adds the `editdestination` column to formulize_screen_listofentries. That column backs the
// per-list "where does the edit icon go" setting (Drawer or Screen) introduced in 793efad17.
// listOfEntriesScreen writes it on every list-screen save (class/listOfEntriesScreen.php:280 for
// inserts, :373 for updates), so a site missing the column cannot save a list screen at all - it
// gets "could not save the screen properly: Unknown column 'editdestination' in 'SET'". Reads are
// unaffected, because initVar() supplies a default (class/listOfEntriesScreen.php:106) and the
// loads use SELECT *, which is why the symptom only ever appears on save. See issue #102.
//
// GATED ON WHETHER THE COLUMN EXISTS, NOT ON DBVERSION, and that is the whole point of this file.
// The column shipped on a feature branch without a dbversion bump, so master and new-theme both
// declare 'dbversion' => 17 (xoops_version.php:42) while describing different schemas. There is
// therefore no version number that reliably separates a database that has this column from one
// that does not, and the version-gated entry this file replaces was unreachable on any database
// already sitting at 17 - which is exactly how #102 happened. A schema check is correct on every
// site regardless of which branch its database was built from, and stays correct after a restore
// from a mid-upgrade backup or a hand-edited schema. 011_app_tables.php on
// FEATURE/core-changes-for-apps guards its tables the same way, for the same reasons.
//
// Deliberately does NOT bump dbversion. Bumping new-theme to 18 would collide with the 18 that
// FEATURE/core-changes-for-apps already declares for a different schema, recreating the very
// situation that caused this bug. The prompt that gets an affected admin to run the update comes
// from formulize_listScreenHasEditDestination() below, wired into $formulizeNeedsDBPatch in
// admin/ui.php, rather than from a version comparison.
//
// This replaces the $sql['add_editdestination'] entry that used to live in
// 001_schema_migrations.php, which is removed in the same change so there is exactly one owner of
// this column.
//
// Idempotent: the column is only added when it is absent, so this is safe to re-run at any
// dbversion and after a partial failure.

/**
 * Check whether the list-of-entries screen table has the editdestination column.
 * Used both by this patch and by admin/ui.php, to decide whether to prompt for an update.
 * @return bool True if the column is present.
 */
function formulize_listScreenHasEditDestination() {
    global $xoopsDB;
    // The table name is built entirely from the DB prefix and a literal, so there is no user input
    // to escape here, matching how 001_schema_migrations.php does its own SHOW COLUMNS checks.
    $table = $xoopsDB->prefix('formulize_screen_listofentries');
    if ($res = $xoopsDB->queryF("SHOW COLUMNS FROM $table LIKE 'editdestination'") AND $xoopsDB->getRowsNum($res) > 0) {
        return true;
    }
    return false;
}

// $prev_dbversion/$required_dbversion are unused on purpose: the on_update.php discovery loop
// passes them positionally to every patch, so they must be declared.
function formulize_patch_012_list_screen_edit_destination($prev_dbversion, $required_dbversion) {
    global $xoopsDB;

    if (formulize_listScreenHasEditDestination()) {
        return true; // already present, nothing to do
    }

    $table = $xoopsDB->prefix('formulize_screen_listofentries');
    // Default 'screen' to match sql/mysql.sql:208 and the initVar default in
    // listOfEntriesScreen.php:106, so upgraded sites and fresh installs agree. (The original entry
    // in 001_schema_migrations.php still said 'drawer', the pre-2ff7ca808 value.)
    $sql = "ALTER TABLE `$table` ADD `editdestination` varchar(10) NOT NULL default 'screen'";
    if (!$xoopsDB->queryF($sql)) {
        echo '<p>Error: could not add the editdestination column to ' . htmlspecialchars($table)
            . ', so list screens cannot be saved: ' . htmlspecialchars($xoopsDB->error())
            . ' Please contact <a href="mailto:info@formulize.org">info@formulize.org</a> for assistance.</p>';
        return false;
    }

    echo '<p>Added the edit destination (drawer/screen) option to list screens.</p>';
    return true;
}
