<?php if (!defined('APPLICATION')) exit();

// Register Yaga library classes and interfaces in the autoloader
$map = Gdn_Autoloader::MAP_LIBRARY;
$context = Gdn_Autoloader::CONTEXT_APPLICATION;
$path = PATH_PLUGINS.'/yaga/library';
$options = ['Extension' => 'yaga'];

Gdn_Autoloader::start();
Gdn_Autoloader::registerMap($map, $context, $path, $options);

require_once(PATH_PLUGINS.'/yaga/library/functions.render.php');
