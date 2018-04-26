<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once 'cms/Parser.php';
require_once 'cms/Cms.php';

if ( !isset($_GET['parse']) && !isset($_GET['admin']) && file_exists('cms/db.php') ) {

    $CMS = new Cms();

    if (count($CMS->getPages()) > 0)
    header("Location: ?admin");
}

if ( isset($_POST['server']) ) {

    if ( !file_exists('cms/db.php') ) {

        $conn = new mysqli($_POST['server'], $_POST['user'], $_POST['pass'], $_POST['table']);

        if ($conn->connect_error) {
            $msg = "CHYBA - nesprávne údaje na databázu";
        } else {

            $r1 = $conn->query("CREATE TABLE IF NOT EXISTS `pages` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` text COLLATE utf8_slovak_ci NOT NULL,
                  `closing_tags` text COLLATE utf8_slovak_ci NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci");

            $r2 = $conn->query("CREATE TABLE IF NOT EXISTS `elements` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `page_id` int(11) DEFAULT NULL,
                  `element_id` char(32) COLLATE utf8_slovak_ci NOT NULL,
                  `tag` varchar(16) COLLATE utf8_slovak_ci NOT NULL,
                  `attr` text COLLATE utf8_slovak_ci NOT NULL,
                  `text` text COLLATE utf8_slovak_ci NOT NULL,
                  `parent_id` char(32) COLLATE utf8_slovak_ci NOT NULL,
                  `self_closing` tinyint(1) DEFAULT '0',
                  PRIMARY KEY (`id`),
                  KEY `page_id` (`page_id`),
                  CONSTRAINT `elements_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci");


            $r3 = $conn->query("CREATE TABLE IF NOT EXISTS `blocks` (
                  `element_id` int(11) NOT NULL,
                  `name` text COLLATE utf8_slovak_ci NOT NULL,
                  `image` text COLLATE utf8_slovak_ci NOT NULL,
                  PRIMARY KEY (`element_id`),
                  CONSTRAINT `blocks_ibfk_1` FOREIGN KEY (`element_id`) REFERENCES `elements` (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci");

            if ($r1 && $r2 && $r3) {
                file_put_contents('cms/db.php', '<?php ' . PHP_EOL.PHP_EOL
                    . '$config[\'server\'] = "' . $_POST['server'] . '";' . PHP_EOL
                    . '$config[\'user\'] = "' . $_POST['user'] . '";' . PHP_EOL
                    . '$config[\'pass\'] = "' . $_POST['pass'] . '";' . PHP_EOL
                    . '$config[\'table\'] = "' . $_POST['table'] . '";' . PHP_EOL
                );

                $msg = "Inštalácia prebehla úspešne";
            } else {
                $msg = "CHYBA".$conn->error;
            }

        }
    }

} else if (isset($_POST['start2_anchors'])) {

    $parsedAnchors = [];

    foreach ($_POST['anchors'] as $anchor) {
        $parsedAnchors[$anchor] = $anchor.'.html';
    }

    foreach ($_POST['anchors'] as $anchor) {
        $parser = new Parser($_POST['url']."/".$anchor, $anchor.".html");

        $parser->downloadContent();
        $parser->toDB();
    }

    header("Location: ?admin");

} else if (isset($_POST['start1'])) {

    $CMS = new Cms();

    $CMS->clearDB();

    foreach (scandir('.') as $file) {
        if (substr($file, -5) == ".html") {
            $parser = new Parser($file);
            $parser->toDB();
        }
    }

    header("Location: ?admin");

} else if (isset($_POST['start2'])) {

    $CMS = new Cms();

    $CMS->clearDB();
    //TODO remove files
    //TODO input url validation

    $parser = new Parser($_POST['url'], "index.html");
    $parser->downloadContent();
    $parser->toDB();

} else if($_FILES["zipfile"]["name"]) {

    $filename = $_FILES["zipfile"]["name"];
    $source = $_FILES["zipfile"]["tmp_name"];
    $type = $_FILES["zipfile"]["type"];

    $name = explode(".", $filename);
    $accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
    foreach($accepted_types as $mime_type) {
        if($mime_type == $type) {
            $okay = true;
            break;
        }
    }

    $continue = strtolower($name[1]) == 'zip' ? true : false;
    if(!$continue) {
        die("Súbor nie je zip!");
    }

    $target_path = realpath(dirname(__FILE__))."/".$filename;
    if(move_uploaded_file($source, $target_path)) {
        $zip = new ZipArchive();
        $x = $zip->open($target_path);
        if ($x === true) {
            $zip->extractTo(realpath(dirname(__FILE__)));
            $zip->close();

            unlink($target_path);
        }
    }

    header("Location: ?parse");

}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>BakCMS inštalátor</title>

    <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<style>
    body {
        background: lightblue;
        font-family: 'Lato', sans-serif;
    }
    #container {
        margin-top: 40px;
        background: white;
        box-shadow: 1px 1px 5px 2px rgba(0,0,0,0.4);
        padding:30px;
    }
    #container h1 {
        text-align: center;
        font-size: 26px;
        margin-bottom: 20px;
    }
    #container h4 {
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 20px;
    }
    input {
        margin: 4px;
    }
    label {
        display: inline-block;
        width:80px;
    }
    a:hover, a:active, a:focus {
        text-decoration: none;
    }
</style>

<?php if ( !file_exists('cms/db.php') ) { ?>

    <div class="container">
        <div class="row">
            <div id="container" class="col-lg-4 col-lg-offset-4 col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1">
                <h1>BakCMS inštalátor</h1>
                <h4>1. krok - spojenie s DB</h4>
                <?= $msg ?>
                <form action="" method="post">
                    <label for="server">Server:</label>
                    <input type="text" name="server" class="form-control">
                    <label for="user">Užívatel:</label>
                    <input type="text" name="user" class="form-control">
                    <label for="pass">Heslo:</label>
                    <input type="text" name="pass" class="form-control">
                    <label for="table">Tabuľka:</label>
                    <input type="text" name="table" class="form-control">
                    <br />
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">Ďalej</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php } else if ( isset($_POST['start2']) || isset($_POST['start2_anchors']) ) { ?>

    <script>
        function checkall() {
            checkboxes = document.getElementsByName('anchors[]');
            for(var i=0, n=checkboxes.length;i<n;i++) {
                checkboxes[i].checked = true;
            }
        }
        function uncheckall() {
            checkboxes = document.getElementsByName('anchors[]');
            for(var i=0, n=checkboxes.length;i<n;i++) {
                checkboxes[i].checked = false;
            }
        }
    </script>

    <div class="container">
        <div class="row">
            <div id="container" class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-12 col-xs-offset-0">
                <h1>BakCMS Admin</h1>
                <h4>Stránka bola úspešne vložená.</h4>
                <h5>Našli sa v nej ďalšie stránky, chcete ich tiež pridať?</h5>
                <form action="" method="post">
                    <input type="hidden" name="start2_anchors">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr><th>Podstránka</th><th>Akcia</th></tr>
                        </thead>
                        <?php foreach ($parser->parsedAnchors as $old_link => $new_link) {
                            if ($old_link == 'url') {
                                echo "<input type='hidden' name='url' value='".$new_link."'>";
                            } else {
                                echo "<tr>
                                    <td>" . $old_link . "</td>
                                    <td>
                                        <input class='form-control' type='checkbox' name='anchors[]' value='" . $old_link . "'>
                                    </td>
                                </tr>";
                            }
                        } ?>
                    </table>
                    <a href="#" onclick="checkall()">Označiť všetky</a>
                    <a href="#" onclick="uncheckall()">Zrušiť označenia</a>
                    <button type="submit" class="btn btn-success">Pridať</button>
                </form>
                <br /><br /><br />
                <div class="text-center">
                    <a href="?admin">
                        <button type="button" class="btn btn-primary">Nechcem pridať ďalšie</button>
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php } else if ( !isset($_GET['admin']) || isset($_GET['parse']) ) { ?>
    <div class="container">
        <div class="row">
            <div id="container" class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-12 col-xs-offset-0">
                <h1>BakCMS inštalátor</h1>
                <h4>2. krok - upload stránky</h4>
                <div class="row">
                    <div class="col-xs-6">
                        <?php

                            $found_files = false;
                            $files = [];

                            foreach (scandir('.') as $file) {
                                if (substr($file, -5) == ".html") {
                                    $found_files = true;
                                    $files[] = $file;
                                }
                            }
                        ?>

                        <?php if (!empty($files)) { ?>
                            <h3>Nájdené stránky:</h3>
                            <ul>
                                <?php foreach ($files as $file) {
                                    echo "<li>" . $file . "</li>";
                                }

                                ?>
                            </ul>
                            <br />
                            <form method="post">
                                <div class="text-center">
                                    <button name="start1" type="submit" class="btn btn-primery">Nahrať!</button>
                                </div>
                            </form>
                        <?php } else { ?>
                            <h5>Žiadne .html súbory nenájdené</h5>
                            <br />
                            <p>Môžete nahrať tému v .zip formáte:</p>
                            <form method="post" action="admin.php" enctype="multipart/form-data">
                                <div class="text-center">
                                    <input type="file" name="zipfile"  accept='.zip' style="margin-left: -15px;"/>
                                    <button type="submit" class="btn btn-primery">Nahrať!</button>
                                </div>
                            </form>
                        <?php } ?>

                    </div>
                    <div class="col-xs-6" style="border-left:1px solid lightgrey">
                        <h3>Upload z URL:</h3>
                        <form method="post">
                            <div class="text-center">
                                <input class="form-control" type="text" name="url"><br />
                                <button name="start2" type="submit" class="btn btn-primery">Nahrať!</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php } else if (isset($_GET['admin'])) { ?>

    <?php $CMS = new Cms(); ?>

    <div class="container">
        <div class="row">
            <div id="container" class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-12 col-xs-offset-0">
                <h1>BakCMS Admin</h1>
                <h4>Zoznam stránok:</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr><th>Stránka</th><th>Akcia</th></tr>
                    </thead>
                    <?php foreach ($CMS->getPages() as $page) {
                        echo "<tr>
                                <td>" . $page['name'] . "</td>
                                <td>
                                    <a href='preview.php?filename=".$page["name"]."'>
                                        <button class='btn btn-primary btn-sm'>Zobraziť</button>
                                    </a>
                                    <a href='liveedit.php?filename=".$page["name"]."'>
                                        <button class='btn btn-warning btn-sm'>Editovať</button>
                                    </a>
                                </td>
                            </tr>";
                    } ?>
                </table>
                <div class="text-center">
                    <a href="?parse" onclick="return confirm('Vymaže to Vašu doterajšiu prácu. Naozaj to chcete urobiť?')">
                        <button class="btn btn-danger">Nahrať novú tému</button>
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php } ?>

<?php
// http://www.free-css.com/assets/files/free-css-templates/preview/page224/bow/

?>

</body>
</html>
