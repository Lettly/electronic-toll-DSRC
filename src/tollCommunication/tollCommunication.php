<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

global $sdk, $dynamoDb, $TOLL_HISTORY_TABLE, $CHARGEMASTER_TABLE;

$sdk = new Aws\Sdk([
	'region'   => 'eu-central-1',
	'version'  => 'latest'
]);
$dynamoDb =
	$sdk->createDynamoDb();

// Get Environment Variables
$TOLL_HISTORY_TABLE = getenv('TOLL_HISTORY_TABLE');
$CHARGEMASTER_TABLE = getenv('CHARGEMASTER_TABLE');


return function ($event) {
	$DEVICE_ID = $event['deviceId'];
	$USER_ID = $event['userId']; //TODO: get from device id
	$TOLL_ID = $event['tollId'];
	if (!isset($DEVICE_ID) || !isset($USER_ID) || !isset($TOLL_ID)) {
		throw new Exception('Missing parameters');
	}

	$lastTollId = getLastEntry($DEVICE_ID);

	if ($lastTollId) {
		if ($lastTollId == $TOLL_ID) {
			throw new Exception('Device used twice at the same toll');
		}

		$fee = getFeeAmount($lastTollId, $TOLL_ID);

		// put the entry in the database 
		$GLOBALS['dynamoDb']->putItem([
			'TableName' => $GLOBALS['TOLL_HISTORY_TABLE'],
			'Item' => [
				'deviceId' => ['S' => $DEVICE_ID],
				'date' => ['S' => date(DATE_ISO8601)],
				'userId' => ['S' => $USER_ID],
				'entryId' => ['S' => $lastTollId],
				'exitId' => ['S' => $TOLL_ID],
				'fee' => ['N' => (string)$fee]
			]
		]);
	} else {
		// if there is no entry, this is the first time the device is used
		$GLOBALS['dynamoDb']->putItem([
			'TableName' => $GLOBALS['TOLL_HISTORY_TABLE'],
			'Item' => [
				'deviceId' => ['S' => $DEVICE_ID],
				'date' => ['S' => date(DATE_ISO8601)],
				'userId' => ['S' => $USER_ID],
				'entryId' => ['S' => $TOLL_ID],
				'exitId' => ['S' => $TOLL_ID],
			]
		]);
	}

	return [
		'statusCode' => 200,
		'body' => json_encode([
			'success' => true,
			'fee' => $fee ?? null,
		])
	];
};

// read the last entry for this device from the database
function getLastEntry($deviceId)
{
	$result = $GLOBALS['dynamoDb']->query([
		'TableName' => $GLOBALS['TOLL_HISTORY_TABLE'],
		'KeyConditionExpression' => 'deviceId = :deviceId',
		'ExpressionAttributeValues' =>  [
			':deviceId' => ['S' => $deviceId]
		],
		'ScanIndexForward' => false,
		'Limit' => 1
	]);

	return $result['Items'][0]['exitId']['S'] ?? null;
}

// read the price for this route from the database
function getFeeAmount($entryId, $exitId)
{
	$result = $GLOBALS['dynamoDb']->getItem([
		'TableName' => $GLOBALS['CHARGEMASTER_TABLE'],
		'Key' => [
			'entryId' => ['S' => $entryId],
			'exitId' => ['S' => $exitId]
		]
	]);

	if (empty($result['Item']['fee']['N'])) {
		throw new Exception('No price found for this route: ' . $entryId . ' -> ' . $exitId);
	}

	return $result['Item']['fee']['N'];
}
