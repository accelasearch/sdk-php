<?php
use \AccelaSearch\ProductMapper\Api\Client;
use \AccelaSearch\ProductMapper\DataMapper\Api\Collector as CollectorMapper;

foreach (['vendor/autoload.php', __DIR__ . '/../../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

////////////////////////////////////////////////////////////////////////
// Sanity check
if ($argc < 3) {
    die(
        "Usage:" . PHP_EOL
      . "\t " . $argv[0] . " <api-key> <command> [<accelasearch-url>]" . PHP_EOL
      . "Where:" . PHP_EOL
      . "\tapi-key:          AccelaSearch API key" . PHP_EOL
      . "\taccelasearch-url: base URL for AccelaSearch APIs" . PHP_EOL
      . "\tcommand:          one of" . PHP_EOL
      . "\t\treset:          deletes every information from collector" . PHP_EOL
    );
}

////////////////////////////////////////////////////////////////////////
// Reads parameters
$api_key = trim($argv[1]);
$command = trim($argv[2]);
$base_url = $argc >= 3 ? trim($argv[3]) : Client::DEFAULT_BASE_URL;

////////////////////////////////////////////////////////////////////////
// Connects to collector
$client = new Client($base_url, $api_key);
$collector_mapper = new CollectorMapper($client);
$collector = $collector_mapper->read();
$dbh = new PDO(
    'mysql:host=' . $collector->getHostname() . ';dbname=' . $collector->getDatabaseName(),
    $collector->getUsername(),
    $collector->getPassword(),
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

////////////////////////////////////////////////////////////////////////
// Executes command
if ($command === 'reset') {
    $dbh->query('DELETE FROM categories');
    $dbh->query('DELETE FROM prices');
    $dbh->query('DELETE FROM products_attr_text');
    $dbh->query('DELETE FROM products_categories');
    $dbh->query('DELETE FROM products_children');
    $dbh->query('DELETE FROM products_images');
    $dbh->query('DELETE FROM stocks');
    $dbh->query('DELETE FROM products');
    $dbh->query('DELETE FROM users_groups');
    $dbh->query('DELETE FROM warehouses');
}
else {
    echo "Unknown command \"$command\"." . PHP_EOL;
}
