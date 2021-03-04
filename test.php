<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require __DIR__ . '/vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

$notifications = [
//	[
//		'subscription' => Subscription::create([ // this is the structure for the working draft from october 2018 (https://www.w3.org/TR/2018/WD-push-api-20181026/)
//			"endpoint" => "https://updates.push.services.mozilla.com/wpush/v2/gAAAAABfhskWxELsCdF80Id0-Oz7QTpjzRxFTZ7s_41N0E_FOrVBSpCPYgqz9kP90Yqh5i9f-jLMKAALk2O-f23jUKhWXxDi_Vx5wz9XLbk2HsjcwIGJZPS1PW9t8bj4JEwzLoyEhFcipSMaYMv7YpCVezcX4aMubBrZGnqUTNJL45qe0Cu7Zjg",
//			"keys" => [
//				'p256dh' => 'BBtRYjFAJMIEvHf5fbLMST5786HL6ctlIHhZE_WaSYT4QNfPHY82xSmkAgKPCQiBUN8lx8amwyLF2NpmcjeNn4I',
//				'auth' => 'n4Z_naQASNWA8D9IptsONA'
//			],
//		]),
//		'payload' => '{"msg":"Hello World!"}',
//	],
	[
		'subscription' => Subscription::create([ // this is the structure for the working draft from october 2018 (https://www.w3.org/TR/2018/WD-push-api-20181026/)
			"endpoint" => "https://fcm.googleapis.com/fcm/send/fxybl8nYFks:APA91bHyV5TwmzRY1GV-58Fh5avEBjnVNV3RQ5oVy8u1wTLgt1JWzSpySfjPHVswTXecnHQJO06D_rhJWppN-DI0elvpWCkaxoATuhn1EmXlxnnCKDzsMy79JXyfyQJCt4jSeMwbCGVE",
			"keys" => [
				'p256dh' => 'BNI6ZpXwuS9LDbu6t2BqWwXfE3MDXb5zthxKEy-v7duaeEJZccrUnrH86Ee9SY81nyiRM09oz7AqSTgQTmH00M4',
				'auth' => 'nTWv5UVojtLsZAbSPegvLw'
			],
		]),
		'payload' => '{"body":"Hello World!", "title": "Dis da Title"}',
	],
	//Lübz Liefert Endpoint
	[
		'subscription' => Subscription::create([ // this is the structure for the working draft from october 2018 (https://www.w3.org/TR/2018/WD-push-api-20181026/)
			"endpoint" => "https://updates.push.services.mozilla.com/wpush/v2/gAAAAABfl8_lXayOgVR1BQ5x8CfPDRPUWNqCSY6FO0aOIXTnf4_tpJgwsx7G3ybbR3Ul7Y9k5kPt6O83MjKUrohKK9m_D6eoPUS8-1oYtHQPoImEHjR4RR7hZtjFdBV3hEnjaDGdXx_MlU1QiNeMp1mbQ4_fcXskgk-8Fmzh6IfGZGjAYrn5yx0",
			"keys" => [
				'p256dh' => 'BIiLiiWCfP7T2hqnpnSH8rwZh2WBjn7yEFxBbihf-pPDIfYrbjWL8mUajE7XE3MjzyAVZvyURGXjMhwl7t5EkNU',
				'auth' => 'E8CvwCrPP4RM0CP1i3sDdQ'
			],
		]),
		'payload' => '{"body":"Hello World!"}',


	],
];
$publicVapidKey = "BId01NRWE8B81ljciZiBR4jKWg80gYgvCOnD4JG6broITnDvdhPD4OrOyX4d1EFeI0ieKuYKxRo9t1EAoZeeV_k";
$privateVapidKey = "uPec-0e5LxTToDgjOcWKlE79CcwRiIThjk03PvXNFMc";
$auth = [
	'VAPID' => [
		'subject' => 'mailto:email@ericwaetke.com', // can be a mailto: or your website address
		'publicKey' => $publicVapidKey, // (recommended) uncompressed public key P-256 encoded in Base64-URL
		'privateKey' => $privateVapidKey // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
	],
];

$webPush = new WebPush($auth);

// send multiple notifications with payload
foreach ($notifications as $notification) {
	$webPush->queueNotification(
		$notification['subscription'],
		$notification['payload'] // optional (defaults null)
	);
}

foreach ($webPush->flush() as $report) {
	$endpoint = $report->getRequest()->getUri()->__toString();

	if ($report->isSuccess()) {
		echo "<br><br>[v] Message sent successfully for subscription {$endpoint}.";
	} else {
		echo "<br><br>[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
	}
}

?>