<?php

function dayOfWeek(int $dayNumber) {
	switch ($dayNumber) {
		case 1:
			return 'Po';
		case 2:
			return 'Út';
		case 3:
			return 'St';
		case 4:
			return 'Čt';
		case 5:
			return 'Pá';
		case 6:
			return 'So';
		case 7:
			return 'Ne';
		default:
			return '';
	}
}

require 'config.php'; // config file

// api cron part ---------------------------------------------------------------
$api = filter_input(INPUT_GET, 'api');
$token = filter_input(INPUT_GET, 'token');

if (isset($api) && isset($token) && $token == $configToken) {
	echo 'ok';

	if ((int) date('G', time()) > 6 && (int) date('G', time()) < 22) {
		$number = 0;
		$content = @file_get_contents('https://www.szcb.cz/plavecky-stadion/');
		$db = new mysqli($configHost, $configUser, $configPass, $configDb);
		preg_match('/<div class=\"panel-ratio\">(\d+) \/ (\d+)<\/div>/', $content, $data);
		if (isset($data[1])) {
			$number = $data[1];
		}
		$db->query("INSERT INTO `pool` (`number`, `datetime`) VALUES ('" . $number . "', now());");
	}

	exit;
}

// display part ----------------------------------------------------------------
$datetime = new DateTime();
$datetime->modify('-60 day');

$db = new mysqli($configHost, $configUser, $configPass, $configDb);

// average at time for the last 60 days
$resultAvgTime = $db->query("
	SELECT ROUND(AVG(`number`)) as 'average',
		DATE_FORMAT(
			DATE_ADD(
				DATE_FORMAT(datetime, '%Y-%m-%d %H:00:00'),
				INTERVAL IF(MINUTE(datetime) < 30, 0, 30) MINUTE
			),
			'%H:%i'
		) AS 'time'
	FROM `pool`
	WHERE `number` > 0 AND `datetime` > '" . $datetime->format('Y-m-d') . "'
	GROUP BY `time`");

$htmlAvgTime = '<tr>';
while ($row = $resultAvgTime->fetch_assoc()) {
	$htmlAvgTime .= '<td>' . $row['average'] . '</td>';
}
$htmlAvgTime .= '</tr>';

// all data for the last 60 days
$resultAll = $db->query("SELECT * FROM `pool` WHERE `datetime` > '" . $datetime->format('Y-m-d') . "' ORDER BY `datetime`");

$htmlAll = '';
$dayActual = '0';
$first = true;
while ($row = $resultAll->fetch_assoc()) {
	$day = date('d', strtotime($row['datetime']));

	if ($day != $dayActual) {
		$dayActual = $day;
		if ($first) {
			$first = false;
		} else {
			$htmlAll .= '</tr>';
		}
		$dayOfWeek = date('N', strtotime($row['datetime']));
		$htmlAll .= '<tr class="day' . $dayOfWeek . '">';
		$htmlAll .= '<td class="grey">' . dayOfWeek((int) $dayOfWeek) . '</td>';
		$htmlAll .= '<td class="grey">' . date('d. m.', strtotime($row['datetime'])) . '</td>';
	}

	$htmlAll .= '<td>' . $row['number'] . '</td>';
}
$htmlAll .= '</tr>' . PHP_EOL;

?>

<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>Plavecký bazén České Budějovice</title>
        <meta charset="utf-8">
        <meta name="author" content="Pavel Rampas">
		<meta name="description" content="Obsazenost plaveckého bazénu České Budějovice.">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/style.css?v=1">
    </head>
    <body>
		<h1>Obsazenost plaveckého bazénu České Budějovice</h1>
		<p><a href="https://www.szcb.cz/plavecky-stadion/">Sportovní zařízení města České Budějovice</a></p>
		<h2>Průměrná návštěvnost v jednotlivé časy za posledních 60 dní</h2>
		<table>
			<thead>
				<tr>
					<th>7:00</th>
					<th>7:30</th>
					<th>8:00</th>
					<th>8:30</th>
					<th>9:00</th>
					<th>9:30</th>
					<th>10:00</th>
					<th>10:30</th>
					<th>11:00</th>
					<th>11:30</th>
					<th>12:00</th>
					<th>12:30</th>
					<th>13:00</th>
					<th>13:30</th>
					<th>14:00</th>
					<th>14:30</th>
					<th>15:00</th>
					<th>15:30</th>
					<th>16:00</th>
					<th>16:30</th>
					<th>17:00</th>
					<th>17:30</th>
					<th>18:00</th>
					<th>18:30</th>
					<th>19:00</th>
					<th>19:30</th>
					<th>20:00</th>
					<th>20:30</th>
					<th>21:00</th>
					<th>21:30</th>
				</tr>
			</thead>
			<tbody>
				<?php echo $htmlAvgTime ?>
			</tbody>
		</table>
		<h2>Návštěvnost za posledních 60 dní</h2>
		<table>
			<thead>
				<tr>
					<th>Den</th>
					<th>Datum</th>
					<th>7:00</th>
					<th>7:30</th>
					<th>8:00</th>
					<th>8:30</th>
					<th>9:00</th>
					<th>9:30</th>
					<th>10:00</th>
					<th>10:30</th>
					<th>11:00</th>
					<th>11:30</th>
					<th>12:00</th>
					<th>12:30</th>
					<th>13:00</th>
					<th>13:30</th>
					<th>14:00</th>
					<th>14:30</th>
					<th>15:00</th>
					<th>15:30</th>
					<th>16:00</th>
					<th>16:30</th>
					<th>17:00</th>
					<th>17:30</th>
					<th>18:00</th>
					<th>18:30</th>
					<th>19:00</th>
					<th>19:30</th>
					<th>20:00</th>
					<th>20:30</th>
					<th>21:00</th>
					<th>21:30</th>
				</tr>
			</thead>
			<tbody>
				<?php echo $htmlAll ?>
			</tbody>
		</table>
    </body>
</html>
