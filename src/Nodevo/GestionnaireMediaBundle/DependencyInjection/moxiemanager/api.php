<?php
/**
 * api.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */
//require_once('./classes/MOXMAN.php');
require_once(dirname(__FILE__).'/classes/MOXMAN_NODEVO.php');

define("MOXMAN_API_FILE", __FILE__);

$context = MOXMAN_Http_Context::getCurrent();
$pluginManager = MOXMAN::getPluginManager();
foreach ($pluginManager->getAll() as $plugin) {
	if ($plugin instanceof MOXMAN_Http_IHandler) {
		$plugin->processRequest($context);
	}
}

?>