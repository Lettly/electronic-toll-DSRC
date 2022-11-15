<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

global $sdk, $dynamoDb, $TOLL_HISTORY_TABLE;

$sdk = new Aws\Sdk([
	'region'   => 'eu-central-1',
	'version'  => 'latest'
]);
$dynamoDb =
	$sdk->createDynamoDb();

// Get Environment Variables
$TOLL_HISTORY_TABLE = getenv('TOLL_HISTORY_TABLE');


return function ($event) {
	$USER_ID = $event['userId'];
	if (!isset($USER_ID)) {
		throw new Exception('Missing parameters');
	}

	$userAmount = getUserAmount($USER_ID);


	return [
		'statusCode' => 200,
		'body' => json_encode([
			'amount' => $userAmount
		])
	];
};

function getUserAmount($userId, $LastEvaluatedKey = null)
{
	$amount = 0;
	$items = $GLOBALS['dynamoDb']->query([
		'TableName' => $GLOBALS['TOLL_HISTORY_TABLE'],
		'IndexName' => 'byUserIdDate',
		'KeyConditionExpression' => 'userId = :userId AND #date BETWEEN :start AND :end',
		'ExpressionAttributeValues' =>  [
			':userId' => ['S' => (string)$userId],
			':start' => ['S' => (string)date(DATE_ISO8601, strtotime('-1 month'))],
			':end' => ['S' => (string)date(DATE_ISO8601)],
		],
		'ExpressionAttributeNames' => [
			'#date' => 'date'
		],
		'ScanIndexForward' => false
	]);

	foreach ($items['Items'] as $item) {
		$amount += $item['fee']['N'];
	}

	if (isset($items['LastEvaluatedKey'])) {
		$amount += getUserAmount($userId, $items['LastEvaluatedKey']);
	}

	return $amount;
}
