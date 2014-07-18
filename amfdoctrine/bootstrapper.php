<?
// mark the application environment
if(!defined("APPLICATION_ENV")) {
	define("APPLICATION_ENV", "development");
}

if(!isset($GLOBALS['autoloaderPrepared']) || $GLOBALS['autoloaderPrepared'] == false) {
	$GLOBALS['autoloaderPrepared'] = true;
	// setup the autoloader
	require 'Doctrine/Common/ClassLoader.php';
	$loader = new Doctrine\Common\ClassLoader("Doctrine", '/usr/share/php'); // SSMU server
	$loader->register();
	
	$loader = new Doctrine\Common\ClassLoader("org", '/home/orientation/html/amfdoctrine/services/vo'); // SSMU server
	$loader->register();
	
	$loader = new Doctrine\Common\ClassLoader("services", '/home/orientation/html/amfdoctrine');
	$loader->register();
}

$config = new Doctrine\ORM\Configuration();

// proxy configuration
$config->setProxyDir('/home/orientation/html/amfdoctrine/services/vo/org/fos/proxies');
$config->setProxyNamespace('org\\fos\proxies');
$config->setAutoGenerateProxyClasses((APPLICATION_ENV == "development"));

// mapping configuration
$driverImpl = new Doctrine\ORM\Mapping\Driver\XmlDriver(__DIR__ . "/mappings");
$config->setMetadataDriverImpl($driverImpl);

// caching configuration
if (APPLICATION_ENV == "development") {
	$cache = new \Doctrine\Common\Cache\ArrayCache();
} else {
	$cache = new \Doctrine\Common\Cache\ApcCache();
}
$config->setMetadataCacheImpl($cache);
$config->setQueryCacheImpl($cache);

// database configuration parameters
$conn = array(
	'dbname' => 'fos',
	'user' => 'orientation2011',
	'password' => 'regerd8',
	'host' => 'localhost',
	'driver' => 'pdo_mysql',
);

// obtaining the entity manager
$evm = new Doctrine\Common\EventManager();
$entityManager = \Doctrine\ORM\EntityManager::create($conn, $config, $evm);
?>