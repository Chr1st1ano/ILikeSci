<?php
/**
 * Fix phpMyAdmin Configuration Storage
 * This creates the 'phpmyadmin' database and required tables
 * that phpMyAdmin needs for its extended features.
 */
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create phpmyadmin database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS phpmyadmin");
    $pdo->exec("USE phpmyadmin");

    // Create the configuration storage tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS pma__bookmark (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            dbase VARCHAR(255) NOT NULL DEFAULT '',
            user VARCHAR(255) NOT NULL DEFAULT '',
            label VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
            query TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__relation (
            master_db VARCHAR(64) NOT NULL DEFAULT '',
            master_table VARCHAR(64) NOT NULL DEFAULT '',
            master_field VARCHAR(64) NOT NULL DEFAULT '',
            foreign_db VARCHAR(64) NOT NULL DEFAULT '',
            foreign_table VARCHAR(64) NOT NULL DEFAULT '',
            foreign_field VARCHAR(64) NOT NULL DEFAULT '',
            PRIMARY KEY (master_db, master_table, master_field)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__table_info (
            db_name VARCHAR(64) NOT NULL DEFAULT '',
            table_name VARCHAR(64) NOT NULL DEFAULT '',
            display_field VARCHAR(64) NOT NULL DEFAULT '',
            PRIMARY KEY (db_name, table_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__table_coords (
            db_name VARCHAR(64) NOT NULL DEFAULT '',
            table_name VARCHAR(64) NOT NULL DEFAULT '',
            pdf_page_number INT NOT NULL DEFAULT 0,
            x FLOAT UNSIGNED NOT NULL DEFAULT 0,
            y FLOAT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (db_name, table_name, pdf_page_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__pdf_pages (
            page_nr INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            db_name VARCHAR(64) NOT NULL DEFAULT '',
            page_descr VARCHAR(50) COLLATE utf8_general_ci NOT NULL DEFAULT ''
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__column_info (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            db_name VARCHAR(64) NOT NULL DEFAULT '',
            table_name VARCHAR(64) NOT NULL DEFAULT '',
            column_name VARCHAR(64) NOT NULL DEFAULT '',
            comment VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
            mimetype VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
            transformation VARCHAR(255) NOT NULL DEFAULT '',
            transformation_options VARCHAR(255) NOT NULL DEFAULT '',
            input_transformation VARCHAR(255) NOT NULL DEFAULT '',
            input_transformation_options VARCHAR(255) NOT NULL DEFAULT ''
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__history (
            id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(64) NOT NULL DEFAULT '',
            db VARCHAR(64) NOT NULL DEFAULT '',
            `table` VARCHAR(64) NOT NULL DEFAULT '',
            timevalue TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            sqlquery TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__recent (
            username VARCHAR(64) NOT NULL PRIMARY KEY,
            tables TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__favorite (
            username VARCHAR(64) NOT NULL PRIMARY KEY,
            tables TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__table_uiprefs (
            username VARCHAR(64) NOT NULL,
            db_name VARCHAR(64) NOT NULL,
            table_name VARCHAR(64) NOT NULL,
            prefs TEXT NOT NULL,
            last_update TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (username, db_name, table_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__tracking (
            db_name VARCHAR(64) NOT NULL,
            table_name VARCHAR(64) NOT NULL,
            version INT UNSIGNED NOT NULL,
            date_created DATETIME NOT NULL,
            date_updated DATETIME NOT NULL,
            schema_snapshot TEXT NOT NULL,
            schema_sql TEXT,
            data_sql TEXT,
            tracking SET('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
            tracking_active INT UNSIGNED NOT NULL DEFAULT 1,
            PRIMARY KEY (db_name, table_name, version)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__userconfig (
            username VARCHAR(64) NOT NULL PRIMARY KEY,
            timevalue TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            config_data TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__users (
            username VARCHAR(64) NOT NULL PRIMARY KEY,
            usergroup VARCHAR(64) NOT NULL DEFAULT ''
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__usergroups (
            usergroup VARCHAR(64) NOT NULL,
            tab VARCHAR(64) NOT NULL,
            allowed ENUM('Y','N') NOT NULL DEFAULT 'N',
            PRIMARY KEY (usergroup, tab, allowed)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__navigationhiding (
            username VARCHAR(64) NOT NULL,
            item_name VARCHAR(64) NOT NULL,
            item_type VARCHAR(64) NOT NULL,
            db_name VARCHAR(64) NOT NULL,
            table_name VARCHAR(64) NOT NULL DEFAULT '',
            PRIMARY KEY (username, item_name, item_type, db_name, table_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__savedsearches (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(64) NOT NULL DEFAULT '',
            db_name VARCHAR(64) NOT NULL DEFAULT '',
            search_name VARCHAR(64) NOT NULL DEFAULT '',
            search_data TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__central_columns (
            db_name VARCHAR(64) NOT NULL,
            col_name VARCHAR(64) NOT NULL,
            col_type VARCHAR(64) NOT NULL,
            col_length TEXT,
            col_collation VARCHAR(64) NOT NULL,
            col_isNull BOOLEAN NOT NULL,
            col_extra VARCHAR(255) DEFAULT '',
            col_default TEXT,
            PRIMARY KEY (db_name, col_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__designer_settings (
            username VARCHAR(64) NOT NULL PRIMARY KEY,
            settings_data TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

        "CREATE TABLE IF NOT EXISTS pma__export_templates (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(64) NOT NULL,
            export_type VARCHAR(10) NOT NULL,
            template_name VARCHAR(64) NOT NULL,
            template_data TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin"
    ];

    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }

    echo "<h1 style='color:green;'>✅ phpMyAdmin configuration storage created!</h1>";
    echo "<p>The <code>phpmyadmin</code> database and all required <code>pma__*</code> tables have been created.</p>";
    echo "<h2>Next Step:</h2>";
    echo "<p>Make sure your <code>config.inc.php</code> in <code>C:\\Games\\xampp\\phpMyAdmin\\</code> has these lines:</p>";
    echo "<pre style='background:#1e293b;color:#e2e8f0;padding:16px;border-radius:8px;'>";
    echo "\$cfg['Servers'][\$i]['controluser'] = 'root';\n";
    echo "\$cfg['Servers'][\$i]['controlpass'] = '';\n";
    echo "\$cfg['Servers'][\$i]['pmadb'] = 'phpmyadmin';\n";
    echo "\$cfg['Servers'][\$i]['bookmarktable'] = 'pma__bookmark';\n";
    echo "\$cfg['Servers'][\$i]['relation'] = 'pma__relation';\n";
    echo "\$cfg['Servers'][\$i]['table_info'] = 'pma__table_info';\n";
    echo "\$cfg['Servers'][\$i]['table_coords'] = 'pma__table_coords';\n";
    echo "\$cfg['Servers'][\$i]['pdf_pages'] = 'pma__pdf_pages';\n";
    echo "\$cfg['Servers'][\$i]['column_info'] = 'pma__column_info';\n";
    echo "\$cfg['Servers'][\$i]['history'] = 'pma__history';\n";
    echo "\$cfg['Servers'][\$i]['recent'] = 'pma__recent';\n";
    echo "\$cfg['Servers'][\$i]['favorite'] = 'pma__favorite';\n";
    echo "\$cfg['Servers'][\$i]['table_uiprefs'] = 'pma__table_uiprefs';\n";
    echo "\$cfg['Servers'][\$i]['tracking'] = 'pma__tracking';\n";
    echo "\$cfg['Servers'][\$i]['userconfig'] = 'pma__userconfig';\n";
    echo "\$cfg['Servers'][\$i]['users'] = 'pma__users';\n";
    echo "\$cfg['Servers'][\$i]['usergroups'] = 'pma__usergroups';\n";
    echo "\$cfg['Servers'][\$i]['navigationhiding'] = 'pma__navigationhiding';\n";
    echo "\$cfg['Servers'][\$i]['savedsearches'] = 'pma__savedsearches';\n";
    echo "\$cfg['Servers'][\$i]['central_columns'] = 'pma__central_columns';\n";
    echo "\$cfg['Servers'][\$i]['designer_settings'] = 'pma__designer_settings';\n";
    echo "\$cfg['Servers'][\$i]['export_templates'] = 'pma__export_templates';\n";
    echo "</pre>";
    echo "<p>Then restart Apache in XAMPP to apply.</p>";

} catch (PDOException $e) {
    echo "<h1 style='color:red;'>Failed!</h1><p>Error: " . $e->getMessage() . "</p>";
}
?>
