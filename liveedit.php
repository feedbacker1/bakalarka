<?php

require 'cms/Parser.php';

if (isset($_GET['filename'])) {
    $parser = new Parser();
    $parser->fromDB($_GET['filename']);

    echo $parser->toHTML(true);
}