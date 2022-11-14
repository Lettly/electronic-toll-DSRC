<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

global $sdk, $dynamoDb;
$sdk = new Aws\Sdk([
	'region'   => 'eu-central-1',
	'version'  => 'latest'
]);
$dynamoDb =
	$sdk->createDynamoDb();

//load the tolls.txt file into an array
$tolls = explode("\n", file_get_contents(__DIR__ . '/tolls.txt'));

// Route to load 
$MAX_ROUTE = 10;

for ($i = 0; $i < $MAX_ROUTE; $i++) {
	for ($j = 0; $j < $MAX_ROUTE; $j++) {
		loadFee($tolls[$i], $tolls[$j], rand(1, 60));
	}
}

function loadFee(string $entryId, string $exitId, int $price)
{
	$GLOBALS['dynamoDb']->putItem([
		'TableName' => 'e-toll-prod-chargeMaster-1ELIIGE6EH032',
		'Item' => [
			'entryId' => ['S' => $entryId],
			'exitId' => ['S' => $exitId],
			'fee' => ['N' => (string)$price]
		]
	]);
	$GLOBALS['dynamoDb']->putItem([
		'TableName' => 'e-toll-prod-chargeMaster-1ELIIGE6EH032',
		'Item' => [
			'entryId' => ['S' => $exitId],
			'exitId' => ['S' => $entryId],
			'fee' => ['N' => (string)$price]
		]
	]);
}
