<?php

// Counters
$changes = 0;
$writes = 0;

define('DS', DIRECTORY_SEPARATOR);
require('ugrsr.class.php');
$anchor_path = realpath(dirname(__FILE__) . '/../../') . DS;

// Verify path is correct
if(empty($anchor_path)) die('ERROR - COULD NOT DETERMINE ANCHOR PATH CORRECTLY - ' . dirname(__FILE__));


$write_errors = array();
if(!is_writeable($anchor_path . 'index.php')) {
	$write_errors[] = 'index.php not writeable';
}

if(!is_writeable($anchor_path . 'system' . DS . 'start.php')){
	$write_errors[] = 'system/start.php not writeable';
}

if(!is_writeable($anchor_path . 'system' . DS . 'boot.php')){
	$write_errors[] = 'system/boot.php not writeable';
}

if(!is_writeable($anchor_path . 'system' . DS . 'autoloader.php')){
	$write_errors[] = 'system/boot.php not writeable';
}

if(!is_writeable($anchor_path . 'anchor' . DS . 'run.php')){
	$write_errors[] = 'anchor/run.php not writeable';
}

if(!empty($write_errors)) {
	die(implode('<br />', $write_errors));
}

// Create new UGRSR class
$u = new UGRSR($anchor_path);

// remove the # before this to enable debugging info
$u->debug = true;

// Set file searching to off
$u->file_search = false;

// Attempt upgrade if necessary. Otherwise just continue with normal install
$u->addFile('index.php');

$u->addPattern('~\$vqmod->~', 'VQMod::');
$u->addPattern('~\$vqmod = new VQMod\(\);~', 'VQMod::bootup();');

$result = $u->run();

if($result['writes'] > 0) {
	if(file_exists('../mods.cache')) {
		unlink('../mods.cache');
	}
	die('UPGRADE COMPLETE');
}

$u->clearPatterns();
$u->resetFileList();

// Add index files to files to include
$u->addFile('index.php');

$u->addPattern('/require SYS \. \'([^\']+)\' \. EXT/', '
// VirtualQMOD
require_once(\'./vqmod/vqmod.php\');
VQMod::bootup();
require VQMod::modCheck(SYS . \'$1\' . EXT)');

// Get number of changes during run
$result = $u->run();
$writes += $result['writes'];
$changes += $result['changes'];

$u->clearPatterns();
$u->resetFileList();

// Add start files to files to include
$u->addFile('system' . DS . 'start.php');

$u->addPattern('/require SYS \. \'([^\']+)\' \. EXT/', 'require VQMod::modCheck(SYS . \'$1\' . EXT)');
$u->addPattern('/require APP \. \'([^\']+)\' \. EXT/', 'require VQMod::modCheck(APP . \'$1\' . EXT)');

// Get number of changes during run
$result = $u->run();
$writes += $result['writes'];
$changes += $result['changes'];

$u->clearPatterns();
$u->resetFileList();

// Add boot files to files to include
$u->addFile('system' . DS . 'boot.php');

$u->addPattern('/require PATH \. \'([^\']+)\' \. EXT/', 'require VQMod::modCheck(PATH . \'$1\' . EXT)');

// Get number of changes during run
$result = $u->run();
$writes += $result['writes'];
$changes += $result['changes'];

$u->clearPatterns();
$u->resetFileList();

// Add autoloader files to files to include
$u->addFile('system' . DS . 'autoloader.php');

$u->addPattern('/\$path;/', '\\VQMod::modCheck($path);');

// Get number of changes during run
$result = $u->run();
$writes += $result['writes'];
$changes += $result['changes'];

$u->clearPatterns();
$u->resetFileList();

// Add autoloader files to files to include
$u->addFile('system' . DS . 'view.php');

$u->addPattern('/\$this->path;/', '\\VQMod::modCheck($this->path);');

// Get number of changes during run
$result = $u->run();
$writes += $result['writes'];
$changes += $result['changes'];

$u->clearPatterns();
$u->resetFileList();

// Add run files to files to include
$u->addFile('anchor' . DS . 'run.php');

$u->addPattern('/require APP \. \'([^\']+)\' \. EXT/', 'require VQMod::modCheck(APP . \'$1\' . EXT)');

// Get number of changes during run
$result = $u->run();
$writes += $result['writes'];
$changes += $result['changes'];

$u->clearPatterns();
$u->resetFileList();


// output result to user
if(!$changes) die('VQMOD ALREADY INSTALLED!');
if($writes != 4) die('ONE OR MORE FILES COULD NOT BE WRITTEN');
die('VQMOD HAS BEEN INSTALLED ON YOUR SYSTEM!');