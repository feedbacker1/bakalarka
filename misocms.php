<?php

error_reporting(E_ALL & ~E_NOTICE);

if (!isset($_POST['start']) && !isset($_GET['finished'])) {

    if (!file_exists("cms")) {
        die("Priečinok 'cms' nebol nájdený");
        exit;
    }

    $found_files = false;
    $files = [];

    foreach (scandir('.') as $file) {
        if (substr($file, -5) == ".html") {
            $found_files = true;
            $files[] = $file;
        }
    }

    if (!empty($files)) {
        echo "<h2>Nájdené podstránky, ktoré budú pridané do CMS:</h2>";
        echo "<ul>";
    } else {
        die("Žiadne .html súbory nenájdené");
        exit;
    }

    foreach ($files as $file) {
        echo "<li>" . $file . "</li>";
    }

    echo "<br /><br /><form method='post'><button name='start' type='submit'>Začať!</button></form>";

} else if (isset($_POST['start'])) {

    include 'cms/Parser.php';

    foreach (scandir('.') as $file) {
        if (substr($file, -5) == ".html") {
            $parser = new Parser($file);
            $parser->toDB();
        }
    }

    echo "<h2><a href='misocms.php?finished=true'>Zobraziť výsledky</a></h2>";

} else if (isset($_GET['finished'])) {

    foreach (scandir('.') as $file) {
        if (substr($file, -5) == ".html") {
            $found_files = true;
            $files[] = $file;
        }
    }

    echo "<h2>Podstránky pridané do CMS:</h2>";

    foreach ($files as $file) {
        echo "<li>" . $file . " <a href='preview.php?filename=$file'>Zobraziť spracovanú</a> <a href='liveedit.php?filename=$file'>EDITOVAŤ</a></li>";
    }
}