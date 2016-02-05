<?php
// include some libraries and configuration
require_once('includes/bootstrap.php');

// we want to return json data
header('Content-Type: application/json');

function rfc3339date_to_mysql($rfc3339date) {
	$date = new DateTime($rfc3339date);
	return $date->format('Y-m-d H:i:s');
}

// according to RFC 746, we won't get more than this
define('JSON_MAX_DEPTH', 3);

// get the json data
$json = file_get_contents('php://input');
$obj = json_decode($json, false, JSON_MAX_DEPTH);

if (is_null($obj)) {
	die(json_encode((object) array('success'=>false, 'message' => 'Could not decode JSON data')));
}

if (is_object($obj) && isset($obj->{'hostname'})) {
	// insert into database
	$query = "INSERT INTO `%table` SET
		`reporter-ip` = :ip,
		`date-time` = :time,
		`hostname` = :hostname,
		`port` =  :port,
		`effective-expiration-date` = :expiration,
		`include-subdomains` = :subdomains,
		`noted-hostname` = :noted_host,
		`served-certificate-chain` = :chain_served,
		`validated-certificate-chain` = :chain_validated,
		`known-pins` = :pins";
	$values = array(
		'%table' => TABLE_REPORT,
		':ip' => Utils::getCanonicalIPv6($_SERVER['REMOTE_ADDR']),
		':time' => property_exists($obj, 'date-time') ? rfc3339date_to_mysql($obj->{'date-time'}) : null,
		':hostname' => $obj->{'hostname'},
		':port' => property_exists($obj, 'port') ? $obj->{'port'} : null,
		':expiration' => property_exists($obj, 'effective-expiration-date') ? rfc3339date_to_mysql($obj->{'effective-expiration-date'}) : null,
		':subdomains' => property_exists($obj, 'include-subdomains') ? $obj->{'include-subdomains'} : null,
		':noted_host' => property_exists($obj, 'noted-hostname') ? $obj->{'noted-hostname'} : null,
		':chain_served' => property_exists($obj, 'served-certificate-chain') ? json_encode($obj->{'served-certificate-chain'}) : array(),
		':chain_validated' => property_exists($obj, 'validated-certificate-chain') ? json_encode($obj->{'validated-certificate-chain'}) : array(),
		':pins' => json_encode( property_exists($obj, 'known-pins') ? $obj->{'known-pins'} : array() ),
	);
	$stmt = db()->preparedStatement($query, $values);
	if (!$stmt->success) {
		die(json_encode((object) array('success'=>false, 'message' => 'Could not insert into database')));
	}

	if (ALERTS_ENABLED) {
		if (ALERTS_THROTTLE_BY_DOMAIN) {
			$domain = $obj->{'hostname'};
		} else {
			$domain = '*';
		}
		
		$send_alert = true;
		if (ALERTS_THROTTLE_HOURS > 0) {
			$stmt = db()->preparedStatement(
				"SELECT TIMEDIFF(NOW(), `last_alert`) FROM `%table` WHERE `hostname` = :domain",
				array('%table' => TABLE_EMAIL, ':domain' => $domain)
			);
			if ($stmt->success && $stmt->foundRows >= 1) {
				$difference = $stmt->fetchColumn(0);
				if ($difference <= ALERTS_THROTTLE_HOURS){
					$send_alert = false;
				}
			} else {
				// by default, we send the alert
			}
		}
		
		if ($send_alert) {
            $values[':subdomains'] = ($values[':subdomains'] ? 'true' : 'false');

			// send email
		    $headers  = "From: <hpkp-reports@" . gethostname() . ">\r\n";
            $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
            $headers .= 'MIME-Version: 1.0'."\r\n";
            $headers .= 'Content-type: text/plain; charset=UTF-8';

            $subject = '=?UTF-8?B?'.base64_encode('Possible MITM on ' . $obj->{'hostname'}).'?=';
            $message = <<<ENDOFMESSAGE

HPKP validation for host {$obj->hostname} failed.

Reporting IP:    {$values[':ip']}
Report Time:     {$values[':time']}
Hostname:        {$values[':hostname']}
Port:            {$values[':port']}

Pins cached for: {$values[':noted_host']}
Expiration:      {$values[':expiration']}
Subdomains:      {$values[':subdomains']}
Known pins:      {$values[':pins']}

Certificate Chain Served:
{$values[':chain_served']}

Certificate Chain Validated:
{$values[':chain_validated']}
ENDOFMESSAGE;

            $message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);
            mail(ALERTS_EMAIL, $subject, $message, $headers);

			// update alert table
			db()->preparedStatement(
				"INSERT INTO `%table` SET `hostname` = :domain, `last_alert` = NOW()
				 ON DUPLICATE KEY UPDATE `last_alert` = NOW()",
				array('%table' => TABLE_EMAIL, ':domain' => $domain)
			);
		}
	}
}
print(json_encode((object) array('success'=>true, 'message' => 'report saved')));
