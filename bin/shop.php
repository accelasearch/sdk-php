<?php
use \AccelaSearch\ProductMapper\Api\Client;
use \AccelaSearch\ProductMapper\Api\Shop as ShopApi;

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
      . "\t " . $argv[0] . " <accelasearch-url> <api-key> <command>" . PHP_EOL
      . "Where:" . PHP_EOL
      . "\tapi-key:          AccelaSearch API key" . PHP_EOL
      . "\taccelasearch-url: base URL for AccelaSearch APIs" . PHP_EOL
      . "\tcommand:          one of" . PHP_EOL
      . "\t\tnotify:               Notifies AccelaSearch that a shop has been added to collector" . PHP_EOL
      . "\t\tsync-start <shop id>: Notifies AccelaSearch of a synchronization start" . PHP_EOL
      . "\t\tsync-end <shop id>:   Notifies AccelaSearch of a synchronization start" . PHP_EOL
      . "\t\tindex <shop id>:      Asks for a reindex for given shop" . PHP_EOL
      . "\t\tconvert <shop id>:    Convert a collector shop identifier into a shop identifier" . PHP_EOL
    );
}

////////////////////////////////////////////////////////////////////////
// Reads parameters
$base_url = trim($argv[1]);
$api_key = trim($argv[2]);
$command = trim($argv[3]);

////////////////////////////////////////////////////////////////////////
// Connects to collector
$client = new Client($base_url, $api_key);
$shop_api = ShopApi::fromClient($client);

////////////////////////////////////////////////////////////////////////
// Executes command
if ($command === 'notify') {
    $shop_api->notify();
}
elseif ($command === 'sync-start') {
    $shop_api->startSynchronization($argv[4]);
}
elseif ($command === 'sync-end') {
    $shop_api->endSynchronization($argv[4]);
}
elseif ($command === 'index') {
    $shop_api->index($argv[4]);
}
elseif ($command === 'convert') {
    $shop_identifier = $shop_api->convertShopIndentifier(trim($argv[4]));
    echo "Shop identifier:           " . $shop_identifier . PHP_EOL;
    echo "Collector shop identifier: " . $argv[4] . PHP_EOL;
}
else {
    echo "Unknown command \"$command\"." . PHP_EOL;
}
