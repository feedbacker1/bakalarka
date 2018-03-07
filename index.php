<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

if ( isset($_POST['server']) ) {

    if ( !file_exists('cms/db.php') ) {

        $conn = new mysqli($_POST['server'], $_POST['user'], $_POST['pass'], $_POST['table']);

        if ($conn->connect_error) {
            $msg = "CHYBA - nesprávne údaje na databázu";
        } else {

            $result = $conn->query("
                CREATE TABLE `blocks` (
                  `element_id` int(11) NOT NULL,
                  `name` text COLLATE utf8_slovak_ci NOT NULL,
                  `image` text COLLATE utf8_slovak_ci NOT NULL,
                  PRIMARY KEY (`element_id`),
                  CONSTRAINT `blocks_ibfk_1` FOREIGN KEY (`element_id`) REFERENCES `elements` (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
                
                
                CREATE TABLE `elements` (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
                
                
                CREATE TABLE `pages` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` text COLLATE utf8_slovak_ci NOT NULL,
                  `closing_tags` text COLLATE utf8_slovak_ci NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
            ");

            if ($result) {
                file_put_contents('cms/db.php', '<?php ' . PHP_EOL.PHP_EOL
                    . '$config[\'server\'] = "' . $_POST['server'] . '";' . PHP_EOL
                    . '$config[\'user\'] = "' . $_POST['user'] . '";' . PHP_EOL
                    . '$config[\'pass\'] = "' . $_POST['pass'] . '";' . PHP_EOL
                    . '$config[\'table\'] = "' . $_POST['table'] . '";' . PHP_EOL
                );

                $msg = "Inštalácia prebehla úspešne";
            }

        }
    }

}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>MisoCMS inštalátor</title>

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
        width: 320px;
        background: white;
        position:absolute;
        left:50%;
        margin-left:-160px;
        top:40px;
        padding: 20px;
        box-shadow: 1px 1px 5px 2px rgba(0,0,0,0.4);
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
</style>

<?php if ( !file_exists('cms/db.php') ) { ?>

    <div id="container">
        <h1>MisoCMS inštalátor</h1>
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
            <button type="submit" class="btn btn-success">Ďalej</button>
        </form>
    </div>

<?php } else {
    //header("Location: misocms.php");
} ?>

<?php

include 'cms/Parser.php';


$parser = new Parser("http://www.free-css.com/assets/files/free-css-templates/preview/page224/bow/","index.html");
$parser->downloadContent();
$parser->toDB();

var_dump($parser->parsedAnchors);


?>

</body>
</html>
