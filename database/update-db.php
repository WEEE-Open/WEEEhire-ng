<?php

/**
 * ! HOW TO USE !
 * 
 * You don't need to look in here. All you need to do is write your querys in the database/update folder. They will be executed in order.
 * Remember also to update the version at the bottom of the weeehire.sql file.
 * 
 */



namespace WEEEOpen\WEEEHire;


if (php_sapi_name() !== 'cli') {
	http_response_code(403);
	header('Content-Type', 'text/plain');
	echo 'Available only in PHP CLI';
	return;
}

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Database.php';

$database = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sql');

if ($database === false) {
	echo 'Database file not found';
	echo PHP_EOL;
	exit(1);
}

preg_match("#\(.SchemaVersion., (\d+)\)#", $database, $matches);
if (sizeof($matches) < 1) {
	$schema = 0;
	echo 'Schema version not found, assuming 0';
	echo PHP_EOL;
} else {
	$schema = (int) $matches[1];
}

echo "Last versions found in sql files: schema $schema";
echo PHP_EOL;

try {
	$db = new Database();
	try {
		$currentVersion = (int) $db->getConfigValue('SchemaVersion');
	} catch (\Exception $e) {
		if ($e->getCode() === 404) {
			$currentVersion = 0;
		} else {
			throw $e;
		}
	}

	if ($currentVersion === $schema) {
		echo 'Database is up to date';
		return;
	}

	if ($currentVersion > $schema) {
		throw new \Exception('Database version is newer than the one in the sql file', 1);
	}

	$rawDb	= $db->getDb();

	while ($currentVersion < $schema) {
		$currentVersion++;
		$filename = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . $currentVersion . '.sql';
		if (!file_exists($filename)) {
			throw new \Exception('Update file not found: ' . $filename, 2);
		}

		$sql = file_get_contents($filename);
		$rawDb->exec('BEGIN');
		$rawDb->exec($sql);
		$db->setConfigValue('SchemaVersion', $currentVersion);
		$rawDb->exec('COMMIT');
		echo 'Updated to version ' . $currentVersion;
		echo PHP_EOL;
	}
} catch(\Exception $e) {
	echo get_class($e);
	echo PHP_EOL;
	echo $e->getMessage();
	echo PHP_EOL;
	echo $e->getTraceAsString();
	exit(1);
}

echo 'Update completed';
echo PHP_EOL;
exit(0);