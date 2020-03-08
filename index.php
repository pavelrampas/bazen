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

// api cron part
$api = filter_input(INPUT_GET, 'api');
$token = filter_input(INPUT_GET, 'token');

if (isset($api) && isset($token) && $token == $configToken) {
	echo 'ok';

	if ((int) date('G', time()) > 6 && (int) date('G', time()) < 22) {
		$number = 0;
		$content = @file_get_contents('https://www.szcb.cz/plavecky-stadion-a-plovarna/');
		$db = new mysqli($configHost, $configUser, $configPass, $configDb);
		preg_match('/<span class=\"green-text\">(\d+)<\/span>/', $content, $data);
		if (isset($data[1])) {
			$number = $data[1];
		}
		$db->query("INSERT INTO `pool` (`number`, `datetime`) VALUES ('" . $number . "', now());");
	}

	exit;
}

// display part
$datetime = new DateTime();
$datetime->modify('-60 day');

$db = new mysqli($configHost, $configUser, $configPass, $configDb);
$result = $db->query("SELECT * FROM `pool` WHERE `datetime` > '" . $datetime->format('Y-m-d') . "' ORDER BY `datetime`");

$html = '';
$dayActual = '0';
$first = true;
while ($row = $result->fetch_assoc()) {
	$day = date('d', strtotime($row['datetime']));

	if ($day != $dayActual) {
		$dayActual = $day;
		if ($first) {
			$first = false;
		} else {
			$html .= '</tr>';
		}
		$dayOfWeek = date('N', strtotime($row['datetime']));
		$html .= '<tr class="day' . $dayOfWeek . '">';
		$html .= '<td class="grey">' . dayOfWeek($dayOfWeek) . '</td>';
		$html .= '<td class="grey">' . date('d. m.', strtotime($row['datetime'])) . '</td>';
	}

	$html .= '<td>' . $row['number'] . '</td>';
}
$html .= '</tr>' . PHP_EOL;

?>

<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>Plavecký bazén České Budějovice</title>
        <meta charset="utf-8">
        <meta name="author" content="Pavel Rampas">
		<meta name="description" content="Obsazenost plaveckého bazénu České Budějovice.">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body{margin:20px;font-family:"arial";}
			h1{margin-top:0;}
			table{border-collapse:collapse;}
            table, td, th{border:#000 solid 1px;padding:5px;text-align:center;font-size:0.9em;}
			.grey, th{background-color:#ccc;}
			.day6, .day7{background-color:#eee;}
        </style>
    </head>
    <body>
		<h1>Obsazenost plaveckého bazénu České Budějovice</h1>
		<p><a href="https://www.szcb.cz/plavecky-stadion-a-plovarna/">Sportovní zařízení města České Budějovice</a></p>
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
				<?php echo $html ?>
			</tbody>
		</table>
    </body>
</html>
