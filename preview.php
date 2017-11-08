<?php

require 'cms/Parser.php';

$parser = new Parser();
$parser->fromDB($_GET['filename']);

echo $parser->toHTML();