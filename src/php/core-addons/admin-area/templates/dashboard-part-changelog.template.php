<?php /** Template version: 1.0.0 */ ?>

<?php

include(CUAR_PLUGIN_DIR . '/libs/php/automattic/parse-readme.php');

$parser = new Automattic_Readme();
$readme = $parser->parse_readme(CUAR_PLUGIN_DIR . '/readme.txt');

echo $readme['sections']['changelog'];
