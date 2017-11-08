<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


include 'cms/Parser.php';

//TODO toto prerobiť do funkcie a recurzívne pre každý <a> tag
//TODO Scripty nie su uplne OK - doriesit parser metodu
//TODO CSS

$parser = new Parser("https://theme.crumina.net/html-utouch/");
$parser->downloadContent("https://theme.crumina.net/html-utouch/");
$parser->toDB("index.html");

//$parser = new Parser("index.html");
//$parser->toDB("index.html");

