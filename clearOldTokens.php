<?php
$servername = "10.35.47.203";
$username = "k67483_main";
$password = "xO5n0q\$nBAMF1Wwa";
$dbname = "k67483_main";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create connection
$connection =  mysqli_connect($servername, $username, $password, $dbname);
/* change character set to utf8 */
if (!$connection->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $connection->error);
    exit();
}

function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
{
    $interval = date_diff($date_1, $date_2);

    return $interval->format($differenceFormat);
}

$sql = "SELECT loginTokens.id, loginTokens.date
        FROM loginTokens";
$result = $connection->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $date = new DateTime($row['date']);
        $now = new DateTime();
        $now->format('Y-m-d H:i:s');    // MySQL datetime format
        $now->getTimestamp();
        $difference = $date->diff($now);

        if(dateDifference($date, $now) > 30){
            $sql = "DELETE FROM loginTokens WHERE loginTokens.id = '".$row['id']."'";
            $connection->query($sql);
        }
    }
}



?>