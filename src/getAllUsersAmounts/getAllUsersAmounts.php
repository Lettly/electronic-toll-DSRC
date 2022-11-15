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
	#TODO: Convert this to a step function and parallelize the scan
	$usersAmounts = getAllUsersAmounts();

	return [
		'statusCode' => 200,
		'body' => json_encode($usersAmounts)
	];
};

function getAllUsersAmounts($LastEvaluatedKey = null)
{
	$result = $GLOBALS['dynamoDb']->scan([
		'TableName' => $GLOBALS['TOLL_HISTORY_TABLE'],
		'LastEvaluatedKey' => $LastEvaluatedKey,
		'ScanFilter' => [
			'date' => [
				'AttributeValueList' => [
					['S' => (string)date(DATE_ISO8601, strtotime('-1 month'))],
					['S' => (string)date(DATE_ISO8601)],
				],
				'ComparisonOperator' => 'BETWEEN'
			]
		]
	]);

	$items = $result['Items'];

	if (isset($result['LastEvaluatedKey'])) {
		$items = array_merge($items, getAllUsersAmounts($result['LastEvaluatedKey']));
	}

	//Create a map of userId => amount
	$usersAmounts = [];
	foreach ($items as $item) {
		(string)$userId = $item['userId']['S'];
		$amount = $item['fee']['N'];
		if (isset($usersAmounts[$userId])) {
			$usersAmounts[$userId] += $amount;
		} else {
			$usersAmounts[$userId] = $amount;
		}
	}

	return $usersAmounts;
}
