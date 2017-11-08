<?php

error_reporting(0);

require_once "Cms.php";
require_once "Parser.php";

$cms = new Cms();

if ($_POST['type'] == "get_element") {

    $cms->getTextElement($_POST['id'], $_POST['filename']);

} else if ($_POST['type'] == "parse") {

    $parser = new Parser($_POST['html']);
    $parser->toDB($_POST['filename']);

} else if ($_POST['type'] == "save") {

    $parser = new Parser();
    $parser->toFile($_POST['filename']);

} else if ($_POST['type'] == "create_block") {

    $cms->createBlock($_POST['filename'], $_POST['id'], $_POST['image'], $_POST['name']);

} else if ($_POST['type'] == "get_blocks") {

    $cms->getBlocks();

} else if ($_POST['type'] == "get_html_of_element") {

    $parser = new Parser();
    $parser->getElementHtmlFromDB($_POST['id']);

}