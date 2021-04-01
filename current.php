<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

require __DIR__ . '/vendor/autoload.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

setlocale(LC_TIME, 'de_DE');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

require_once("random/random.php");

$api = new Backend();
$mailer = new Mailer();

if(isset($_GET['alleUnternehmen'])){
    echo $api->alleUnternehmen($_GET['alleUnternehmen']);
} elseif(isset($_GET['unternehmen'])){
    echo $api->unternehmen($_GET['unternehmen']);
} elseif(isset($_GET['abonnierteKategorien'])){
    echo $api->abonnierteKategorien($_GET['abonnierteKategorien']);
} elseif(isset($_GET['alleAbonniertenUnternehmen'])){
    echo $api->alleAbonniertenUnternehmen($_GET['alleAbonniertenUnternehmen']);
} elseif(isset($_GET['alleAbonniertenKategorien'])){
    echo $api->alleAbonniertenKategorien($_GET['alleAbonniertenKategorien']);
} elseif(isset($_GET['alleKategorien'])){
    echo $api->alleKategorien();
} elseif(isset($_GET['meldung'])){
	echo $api->meldung();
} elseif(isset($_GET['alleMeldungen'])){
    echo $api->alleMeldungen();
}elseif(isset($_GET['alleAbonniertenMeldungen'])){
    echo $api->alleAbonniertenMeldungen($_GET['alleAbonniertenMeldungen']);
} elseif(isset($_GET['unternehmenMeldungen'])){
    echo $api->unternehmenMeldungen($_GET['unternehmenMeldungen']);
} elseif(isset($_GET['postMeldung'])){
    echo $api->postMeldung();
} elseif(isset($_GET['changeUnternehmenAboStatus'])){
    echo $api->changeUnternehmenAboStatus($_GET['userId'], $_GET['unternehmenId']);
} elseif(isset($_GET['changeKategorieAboStatus'])){
    echo $api->changeKategorieAboStatus($_GET['userId'], $_GET['kategorieId']);
}elseif(isset($_GET['updateUnternehmenInformationen'])){
    echo $api->updateUnternehmenInformationen();
}elseif(isset($_GET['login'])){
    echo $api->login();
}elseif(isset($_GET['signup'])){
    echo $api->signup();
}elseif(isset($_GET['tryOAuthLogin'])){
    echo $api->tryOAuthLogin();
} elseif (isset($_GET['changePersonalInformation'])){
    echo $api->changePersonalInformation();
} elseif (isset($_GET['changePasswort'])){
    echo $api->changePasswort();
} elseif (isset($_GET['cookieLogin'])){
    echo $api->cookieLogin();
} elseif (isset($_GET['deleteToken'])){
    echo $api->deleteToken();
} elseif (isset($_GET['verifyEmail'])){
    echo $api->verifyEmail();
} elseif (isset($_GET['addUnternehmen'])){
    echo $api->addUnternehmen();
} elseif(isset($_GET['addKategorienZurMeldung'])){
	echo $api->addKategorienZurMeldung(null, null);
} elseif(isset($_GET['addSubscription'])){
    echo $api->addSubscription();
} elseif (isset($_GET['testNotification'])){
    $notificationHandler = new NotificationHandler();
    echo "sdf";
    echo $notificationHandler->sendNotifications(5, null, "Testnotification", "Slug", "ericwaetke.com");
}



class Database{
    function connect(){
        // Create connection
        $connection =  mysqli_connect($servername, $username, $password, $dbname);
        /* change character set to utf8 */
        if (!$connection->set_charset("utf8")) {
            printf("Error loading character set utf8: %s\n", $connection->error);
            exit();
        }
        return $connection;
    }

}

class Backend{

    //Getter
    function alleUnternehmen($authorization){
        $database = new Database();
        $connection = $database->connect();
        $authorized = ($authorization == "authorized");

        $userId = $connection->real_escape_string($_POST['userId']);

        if($authorized){
            $sql = "SELECT unternehmen.id, unternehmen.name, unternehmen.url, unternehmen.beschreibung, unternehmen.kategorie, unternehmen.tel,
                        unternehmen.mail, unternehmen.web, unternehmen.whatsapp, unternehmen.url,
                        CASE 
                        WHEN abonnierteUnternehmen.abonniert = 0 THEN 'FALSE'
                        WHEN abonnierteUnternehmen.abonniert = 1 THEN 'TRUE'
                        WHEN abonnierteUnternehmen.abonniert IS NULL THEN 'FALSE'
                        END AS abonniert
                    FROM unternehmen
                    LEFT OUTER JOIN abonnierteUnternehmen
                        ON abonnierteUnternehmen.unternehmenId = unternehmen.id
                        AND abonnierteUnternehmen.account = '".$userId."'
                    WHERE unternehmen.approved = 1";
        }
        else{
            $sql = "SELECT unternehmen.id, unternehmen.name, unternehmen.url, unternehmen.beschreibung, unternehmen.kategorie, unternehmen.tel,
                        unternehmen.mail, unternehmen.web, unternehmen.whatsapp, unternehmen.url
                FROM unternehmen
                WHERE unternehmen.approved = 1";
        }

        $result = $connection->query($sql);

        $unternehmen = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if($authorized){
                    $object = [
                        "id" => $row['id'],
                        "name" => $row['name'],
	                    "url" => $row['url'],
                        "kategorie" => $row['kategorie'],
                        "beschreibung" => $row['beschreibung'],
                        "tel" => $row['tel'],
                        "mail" => $row['mail'],
                        "web" => $row['web'],
                        "whatsapp" => $row['whatsapp'],
                        "abonniert" => $row['abonniert']
                    ];
                }else{
                    $object = [
                        "id" => $row['id'],
                        "name" => $row['name'],
                        "kategorie" => $row['kategorie'],
                        "beschreibung" => $row['beschreibung'],
                        "tel" => $row['tel'],
                        "mail" => $row['mail'],
                        "web" => $row['web'],
                        "whatsapp" => $row['whatsapp'],
                        "url" => $row['url']
                    ];
                }
                array_push($unternehmen, $object);
            }
        }
        return json_encode($unternehmen);
    }
	function unternehmen($id){
        $database = new Database();
        $connection = $database->connect();

        $sql = "SELECT * FROM unternehmen WHERE id = $id";
        $result = $connection->query($sql);

        $unternehmen = null;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $unternehmen = ["name" => $row['name'],
                    "kategorie" => $row['kategorie'],
                    "beschreibung" => $row['beschreibung'],
                    "tel" => $row['tel'],
                    "mail" => $row['mail'],
                    "web" => $row['web'],
                    "whatsapp" => $row['whatsapp'],
                    "url" => $row['url']
                ];
            }
        }
        return json_encode($unternehmen);
    }
    function abonnierteKategorien($userId){
        $database = new Database();
        $connection = $database->connect();

        $sql = "SELECT kategorien.id, kategorien.name 
                FROM kategorien 
                    INNER JOIN abonnierteKategorien 
                    ON kategorien.id = abonnierteKategorien.kategorieId
                    INNER JOIN accounts
                    ON abonnierteKategorien.account = accounts.id
                WHERE accounts.id = $userId";
        $result = $connection->query($sql);

        $kategorien = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($kategorien, $row);
            }
        }
        return json_encode($kategorien);
    }
    function abonnierteUnternehmen($userId){
        $database = new Database();
        $connection = $database->connect();

        $sql = "SELECT kategorien.id, kategorien.name 
                FROM kategorien 
                    INNER JOIN abonnierteKategorien 
                    ON kategorien.id = abonnierteKategorien.kategorieId
                    INNER JOIN accounts
                    ON abonnierteKategorien.account = accounts.id
                WHERE accounts.id = $userId";
        $result = $connection->query($sql);

        $kategorien = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($kategorien, $row);
            }
        }
        return json_encode($kategorien);
    }
    function alleAbonniertenUnternehmen($userId){
        $database = new Database();
        $connection = $database->connect();

        $sql = "SELECT unternehmen.id, unternehmen.name,
                    CASE 
                        WHEN abonnierteUnternehmen.abonniert IS NULL
                        THEN 'FALSE'
                        WHEN abonnierteUnternehmen.abonniert = '1'
                        THEN 'TRUE'
                        WHEN abonnierteUnternehmen.abonniert = '0'
                        THEN 'FALSE'
                    END AS abonniert  
                FROM unternehmen
                    LEFT OUTER JOIN abonnierteUnternehmen
                        ON abonnierteUnternehmen.unternehmenId = unternehmen.id
                        AND abonnierteUnternehmen.account = ".$userId."
                WHERE unternehmen.approved = 1";
        $result = $connection->query($sql);

        $unternehmen = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($unternehmen, $row);
            }
        }
        return json_encode($unternehmen);
    }
    function alleAbonniertenKategorien($userId){
        $database = new Database();
        $connection = $database->connect();

        $sql = "SELECT kategorien.id, kategorien.name,
                    CASE 
                        WHEN abonnierteKategorien.abonniert IS NULL
                        THEN 'FALSE'
                        WHEN abonnierteKategorien.abonniert = '1'
                        THEN 'TRUE'
                        WHEN abonnierteKategorien.abonniert = '0'
                        THEN 'FALSE'
                    END AS abonniert  
                FROM kategorien
                    LEFT OUTER JOIN abonnierteKategorien
                        ON abonnierteKategorien.kategorieId = kategorien.id
                        AND abonnierteKategorien.account = ".$userId;
        $result = $connection->query($sql);

        $kategorien = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($kategorien, $row);
            }
        }
        return json_encode($kategorien);
    }
    function alleKategorien(){
        $database = new Database();
        $connection = $database->connect();

        $sql = "SELECT kategorien.id, kategorien.name
                FROM kategorien";
        $result = $connection->query($sql);

        $kategorien = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($kategorien, $row);
            }
        }
        return json_encode($kategorien);
    }
    function meldung(){
	    $database = new Database();
	    $connection = $database->connect();

	    $meldungsId = $connection->real_escape_string($_POST['meldungsId']);

	    $sql = "SELECT meldungen.id, meldungen.unternehmenId, meldungen.date, meldungen.titel, meldungen.text, unternehmen.name
                FROM meldungen
                    INNER JOIN unternehmen
                    ON meldungen.unternehmenId = unternehmen.id
                WHERE meldungen.id = ".$meldungsId;
	    $result = $connection->query($sql);

	    if ($result->num_rows > 0) {
		    while ($row = $result->fetch_assoc()) {

			    $array_to_push = [
				    "header" => $row['titel'],
				    "unternehmen" => $row['name'],
				    "text" => $row['text'],
				    "date" => $row['date'],
				    "kategorien" => []];

			    $sqlKategorien = "SELECT meldungsKategorien.kategorieId, kategorien.name
                                FROM meldungsKategorien
                                    INNER JOIN kategorien
                                    ON kategorien.id = meldungsKategorien.kategorieId
                                    WHERE meldungsKategorien.meldungsId = ".$row['id'];
			    $resultKategorien = $connection->query($sqlKategorien);

			    if ($resultKategorien->num_rows > 0) {
				    while ($rowKategorien = $resultKategorien->fetch_assoc()) {
					    array_push($array_to_push["kategorien"], $rowKategorien);
				    }
			    }

			    return json_encode([
			    	"success" => true,
				    "result" => $array_to_push
			    ]);
		    }
	    }
    }
    function alleMeldungen(){
        $database = new Database();
        $connection = $database->connect();

        $userId = $connection->real_escape_string($_POST['userId']);

        $sql = "SELECT meldungen.id, meldungen.unternehmenId, meldungen.date, meldungen.titel, meldungen.text, unternehmen.name,
      				CASE 
                        WHEN abonnierteUnternehmen.abonniert = 0 THEN 0
                        WHEN abonnierteUnternehmen.abonniert = 1 THEN 1
                        WHEN abonnierteUnternehmen.abonniert IS NULL THEN 0
                        END AS abonniert
                FROM meldungen
                    INNER JOIN unternehmen
                    ON meldungen.unternehmenId = unternehmen.id
					LEFT OUTER JOIN abonnierteUnternehmen
					ON abonnierteUnternehmen.unternehmenId = unternehmen.id AND account = ".$userId."
                    ORDER BY meldungen.date DESC";
        $result = $connection->query($sql);
		
        $meldungen = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
				
                $date = strtotime($row['date']);
                $year = date('Y',$date);
                $month = strftime ('%B',$date);

                $id = $row['id'];

                //Gucken ob das Jahr schon im Array ist
                if(!array_key_exists($year, $meldungen)){
                    $meldungen[$year] = [];
                }


                //Gucken ob der Monat schon im Array ist
                if(!array_key_exists($month, $meldungen[$year])){
                    $meldungen[$year][utf8_encode($month)] = [];
                }


                //Set Abonniert
	            if($row['abonniert']){
	            	$abonniert = true;
	            }else{
	            	$abonniert = false;
	            }

                $array_to_push = [
                	"id" => $row['id'],
                    "header" => utf8_encode($row['titel']),
                    "unternehmen" => utf8_encode($row['name']),
                    "text" => utf8_encode($row['text']),
                    "date" => date("d.m.Y", $date),
                    "kategorien" => [],
                    "abonniert" => $abonniert];
				
				var_dump($array_to_push);
				
                $sqlKategorien = "SELECT meldungsKategorien.kategorieId, kategorien.name
                                FROM meldungsKategorien
                                    INNER JOIN kategorien
                                    ON kategorien.id = meldungsKategorien.kategorieId
                                    WHERE meldungsKategorien.meldungsId = $id";
                $resultKategorien = $connection->query($sqlKategorien);

                if ($resultKategorien->num_rows > 0) {
                    while ($rowKategorien = $resultKategorien->fetch_assoc()) {
                        array_push($array_to_push["kategorien"], $rowKategorien);
                    }
                }

                array_push($meldungen[$year][$month], $array_to_push);
            }
        }

        krsort($meldungen);

		
		$behindTheScenes = new BehindTheScenes();
		return $behindTheScenes->safe_json_encode($meldungen);
    }
    function alleAbonniertenMeldungen($userId){
        $database = new Database();
        $connection = $database->connect();

        $userId = $connection->real_escape_string($userId);

        $sql = "SELECT meldungen.id, meldungen.unternehmenId, meldungen.date, meldungen.titel, meldungen.text, unternehmen.name
                FROM meldungen
                    INNER JOIN unternehmen ON meldungen.unternehmenId = unternehmen.id
                    INNER JOIN abonnierteUnternehmen ON abonnierteUnternehmen.unternehmenId = meldungen.unternehmenId
                WHERE abonnierteUnternehmen.abonniert = 1 AND abonnierteUnternehmen.account = ".$userId."
                ORDER BY meldungen.date DESC";
        $result = $connection->query($sql);

        $meldungen = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                $date = strtotime($row['date']);
                $year = date('Y',$date);
                $month = strftime ('%B',$date);

                $id = $row['id'];

                //Gucken ob das Jahr schon im Array ist
                if(!array_key_exists($year, $meldungen)){
                    $meldungen[$year] = [];
                }

                //Gucken ob der Monat schon im Array ist
                if(!array_key_exists($month, $meldungen[$year])){
                    $meldungen[$year][$month] = [];
                }

                $array_to_push = [
                    "header" => $row['titel'],
                    "unternehmen" => $row['name'],
                    "text" => $row['text'],
                    "date" => date("d.m.Y", $date),
                    "kategorien" => []];

                $sqlKategorien = "SELECT meldungsKategorien.kategorieId, kategorien.name
                                FROM meldungsKategorien
                                    INNER JOIN kategorien
                                    ON kategorien.id = meldungsKategorien.kategorieId
                                    WHERE meldungsKategorien.meldungsId = $id";
                $resultKategorien = $connection->query($sqlKategorien);

                if ($resultKategorien->num_rows > 0) {
                    while ($rowKategorien = $resultKategorien->fetch_assoc()) {
                        array_push($array_to_push["kategorien"], $rowKategorien);
                    }
                }

                array_push($meldungen[$year][$month], $array_to_push);
            }
        }
        krsort($meldungen);
        return json_encode($meldungen);
    }
    function unternehmenMeldungen($id){
        $database = new Database();
        $connection = $database->connect();

        $sql = "SELECT meldungen.id, meldungen.unternehmenId, meldungen.date, meldungen.titel, meldungen.text, unternehmen.name
                FROM meldungen
                    INNER JOIN unternehmen
                    ON meldungen.unternehmenId = unternehmen.id
                WHERE meldungen.unternehmenId = $id
                ORDER BY meldungen.date DESC";
        $result = $connection->query($sql);

        $meldungen = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                $id = $row['id'];

                $array_to_push = [
                    "header" => $row['titel'],
                    "unternehmen" => $row['name'],
                    "text" => $row['text'],
                    "date" => $row['date'],
                    "kategorien" => []];

                $sqlKategorien = "SELECT meldungsKategorien.kategorieId, kategorien.name
                                FROM meldungsKategorien
                                    INNER JOIN kategorien
                                    ON kategorien.id = meldungsKategorien.kategorieId
                                    WHERE meldungsKategorien.meldungsId = $id";
                $resultKategorien = $connection->query($sqlKategorien);

                if ($resultKategorien->num_rows > 0) {
                    while ($rowKategorien = $resultKategorien->fetch_assoc()) {
                        array_push($array_to_push["kategorien"], $rowKategorien);
                    }
                }

                array_push($meldungen, $array_to_push);
            }
        }
        krsort($meldungen);
        return json_encode($meldungen);
    }
    function checkUniqueIdentifierAvailability($uid){
        $database = new Database();
        $connection = $database->connect();

        $sql =  "SELECT uniqueIdentifier FROM accounts
                WHERE accounts.uniqueIdentifier = '$uid'";
        $result = $connection->query($sql);

        if($result->num_rows > 0){
            return false;
        }else{
            return true;
        }
    }

    //Setter
    function postMeldung(){
        $database = new Database();

        $notificationHandler = new NotificationHandler();
        $bts = new BehindTheScenes();

        $connection = $database->connect();

        $uid = $connection->real_escape_string($_POST['uid']);
        $titel = $connection->real_escape_string($_POST['titel']);
        $text = $connection->real_escape_string($_POST['text']);
        $kategorien = $connection->real_escape_string($_POST['kategorien']);

        $userId = $connection->real_escape_string($_POST['userid']);
        $token = $connection->real_escape_string($_POST['token']);

	    $date = date("y-m-d H:i:s");

        if($this->checkToken($userId, $token)) {
            $sql = "INSERT INTO meldungen
                (unternehmenId, date, titel, text)
                VALUES
                ('" . $uid . "', '" . $date . "', '" . $titel . "', '" . $text . "');";

            if ($connection->query($sql) === TRUE) {
                //Kategorien werden der Meldung zugeteilt
            	$this->addKategorienZurMeldung($connection->insert_id, $kategorien);
            	// Benachrichtigungen über diese Meldung werden verschickt
	            $url = $bts->slugify($bts->getUnternehmenName($uid))."/".$bts->slugify($titel);
				$notificationHandler->sendNotifications($uid, $kategorien, $titel, $text, $url);

                return json_encode([
                    "success" => true,
                    "result" => null
                ]);
            } else {
                return json_encode([
                    "success" => false,
                    "result" => $connection->error
                ]);
            }
        }
    }
    function addKategorienZurMeldung($meldungsId, $kategorien){
	    $database = new Database();
	    $connection = $database->connect();
    	if(!$kategorien){
    		$kategorien = json_decode($_POST['kategorien']);
	    } elseif (gettype($kategorien) == "string"){
		    $kategorien = json_decode($kategorien);
	    }

    	for ($i = 0; $i < count($kategorien); $i++){
		    $kategorieId = $kategorien[$i];
		    $sql = "INSERT INTO meldungsKategorien
                (meldungsId, kategorieId)
                VALUES
                ('" . $meldungsId . "', '" . $kategorieId . "');";

		    // Wen addition nicht geklappt hat
		    if ($connection->query($sql) !== TRUE) {
				echo $connection->error;
		    }
	    }
    }
    function changeUnternehmenAboStatus($userId, $unternehmenId){
        $database = new Database();
        $connection = $database->connect();

        $userId = $connection->real_escape_string($userId);
        $unternehmenId = $connection->real_escape_string($unternehmenId);

        $token = $connection->real_escape_string($_POST['token']);

        $date = date("y-m-d H:i:s");

        if($this->checkToken($userId, $token)) {
            //Checking if Abo has existed before
            $sql = "SELECT abonnierteUnternehmen.abonniert
                FROM abonnierteUnternehmen
                WHERE abonnierteUnternehmen.account = ".$userId." AND abonnierteUnternehmen.unternehmenId = " . $unternehmenId;
            $result = $connection->query($sql);

//            $preparedStatement = $connection->prepare("SELECT abonnierteUnternehmen.abonniert
//                FROM abonnierteUnternehmen
//                WHERE abonnierteUnternehmen.account = :userId AND abonnierteUnternehmen.unternehmenId = :unternehmenId");
//
//            $preparedStatement->execute(['userId' => $userId, "unternehmenId" => $unterenehmenId]);

            //$result = $connection->query($sql);
            //Es gibt mehr als 0 Ergebnisse (1) => Dieses dann Ändern
//            var_dump($result);
//            var_dump($connection->error);
            if ($result != null && $result->num_rows != 0) {
                echo "Eintrag gefunden, wird geändert";
                $row = $result->fetch_assoc();
                if ($row['abonniert'] == 0) {
                    $newStatus = 1;
                } elseif ($row['abonniert'] == 1) {
                    $newStatus = 0;
                }

                $sql = "UPDATE abonnierteUnternehmen
                SET abonnierteUnternehmen.abonniert = " . $newStatus . ", abonnierteUnternehmen.dateSinceLastChange = '" . $date . "'
                WHERE abonnierteUnternehmen.account = " . $userId . " AND abonnierteUnternehmen.unternehmenId = " . $unternehmenId;
                if ($connection->query($sql) === TRUE) {
                    return json_encode([
                        "success" => true,
                        "result" => null
                    ]);
                } else {
                    return json_encode([
                        "success" => false,
                        "result" => "UPDATE-ERROR: ".$connection->error
                    ]);
                }
            } //Es gibt 0 ergebnisse, also neuen Database Eintrag
            else {
                echo "Eintrag NICHT gefunden, wird inserted";
                //Da noch kein Eintrag vorhanden ist, ist das Unternehmen nicht abonniert => Status der in die Datenbank
                //eingetragen wird = 1
                $sql = "INSERT INTO abonnierteUnternehmen
                (account, unternehmenId, abonniert, dateSinceLastChange)
                VALUES
                ('".$userId."', '".$unternehmenId."', 1, '".$date."')";

                if ($connection->query($sql) === TRUE) {
                    return json_encode([
                        "success" => true,
                        "result" => null
                    ]);
                } else {
                    return json_encode([
                        "success" => false,
                        "result" => "INSERT-ERROR: ".$connection->error
                    ]);
                }
            }
        }else{
            return json_encode([
                "success" => false,
                "result" => "Etwas stimmt mit der Token-Überprüfung nicht"
            ]);
        }
    }
    function changeKategorieAboStatus($userId, $kategorieId){
        $database = new Database();
        $connection = $database->connect();

        $userId = $connection->real_escape_string($userId);
        $kategorieId = $connection->real_escape_string($kategorieId);

        $token = $connection->real_escape_string($_POST['token']);

        $date = date("y-m-d H:i:s");

        if($this->checkToken($userId, $token)) {
            //Checking if Abo has existed before
            $sql = "SELECT abonnierteKategorien.abonniert
                FROM abonnierteKategorien
                WHERE abonnierteKategorien.account = " . $userId . " AND abonnierteKategorien.kategorieId = " . $kategorieId;
            $result = $connection->query($sql);
            //Es gibt mehr als 0 Ergebnisse (1) => Dieses dann Ändern
            if ($result->num_rows != 0) {
                while ($row = $result->fetch_assoc()) {
                    if ($row['abonniert'] == 0) {
                        $newStatus = 1;
                    } elseif ($row['abonniert'] == 1) {
                        $newStatus = 0;
                    }

                    $sql = "UPDATE abonnierteKategorien
                    SET abonnierteKategorien.abonniert = " . $newStatus . ", abonnierteKategorien.dateSinceLastChange = '" . $date . "'
                    WHERE abonnierteKategorien.account = " . $userId . " AND abonnierteKategorien.kategorieId = " . $kategorieId;
                    if ($connection->query($sql) === TRUE) {
                        return json_encode([
                            "success" => true,
                            "result" => null
                        ]);
                    } else {
                        return json_encode([
                            "success" => false,
                            "result" => $connection->error
                        ]);
                    }
                }
            } //Es gibt 0 ergebnisse, also neuen Database Eintrag
            else {
                //Da noch kein Eintrag vorhanden ist, ist die Kategorie nicht abonniert => Status der in die Datenbank
                //eingetragen wird = 1
                $sql = "INSERT INTO abonnierteKategorien
                (account, kategorieId, abonniert, dateSinceLastChange)
                VALUES
                (" . $userId . ", " . $kategorieId . ", 1, '" . $date . "');";

                if ($connection->query($sql) === TRUE) {
                    return json_encode([
                        "success" => true,
                        "result" => null
                    ]);
                } else {
                    return json_encode([
                        "success" => false,
                        "result" => $connection->error
                    ]);
                }
            }
        }
    }
    function updateUnternehmenInformationen(){
        $database = new Database();
        $connection = $database->connect();

        $name = $connection->real_escape_string($_POST['name']);
        $beschreibung = $connection->real_escape_string($_POST['beschreibung']);
        $tel = $connection->real_escape_string($_POST['tel']);
        $mail = $connection->real_escape_string($_POST['mail']);
        $web = $connection->real_escape_string($_POST['web']);
        $whatsapp = $connection->real_escape_string($_POST['whatsapp']);
        $uid = $connection->real_escape_string($_POST['uid']);

        $userid = $connection->real_escape_string($_POST['userid']);
        $token = $connection->real_escape_string($_POST['token']);


        if($this->checkToken($userId, $token)){
            $sql =  "UPDATE unternehmen
                SET unternehmen.name = '".$name."', 
                unternehmen.beschreibung = '".$beschreibung."', 
                unternehmen.tel = '".$tel."', 
                unternehmen.mail = '".$mail."', 
                unternehmen.web = '".$web."', 
                unternehmen.whatsapp = '".$whatsapp."'
                WHERE unternehmen.id = ".$uid;

            if ($connection->query($sql) === TRUE) {
                return json_encode([
                    "success" => true,
                    "result" => null
                ]);
            } else {
                return json_encode([
                    "success" => false,
                    "result" => $connection->error
                ]);
            }
        }
    }
    function addSubscription(){
        $database = new Database();

        $notificationHandler = new NotificationHandler();
        $bts = new BehindTheScenes();

        $connection = $database->connect();

        $userId = $connection->real_escape_string($_POST['userid']);
        $token = $connection->real_escape_string($_POST['token']);

        $type = $connection->real_escape_string($_POST['type']);
        $endpoint = $connection->real_escape_string($_POST['endpoint']);
        $auth = $connection->real_escape_string($_POST['auth']);
        $p256dh = $connection->real_escape_string($_POST['p256dh']);

        $date = date("y-m-d H:i:s");

        if($this->checkToken($userId, $token)) {
            if($type == "push"){
                $sql = "INSERT INTO subscriptions
                (accountId, type, date, adress, auth, p256dh, active)
                VALUES
                ('" . $userId . "', 'push', '" . $date . "', '" . $endpoint . "', '" . $auth . "', '" . $p256dh . "', 1);";
            }


            if ($connection->query($sql) === TRUE) {
                return json_encode([
                    "success" => true,
                    "result" => null
                ]);
            } else {
                return json_encode([
                    "success" => false,
                    "result" => $connection->error
                ]);
            }
        }else{
            return "Token Authentifikation fehlgeschlagen";
        }
    }

    function addUnternehmen(){
        $database = new Database();
        $connection = $database->connect();

	    $name = mysqli_real_escape_string($connection, $_POST['name']);
	    $url = mysqli_real_escape_string($connection, $_POST['url']);
        $kategorie = mysqli_real_escape_string($connection, $_POST['kategorie']);
        $beschreibung = mysqli_real_escape_string($connection, $_POST['beschreibung']);
        $tel = mysqli_real_escape_string($connection, $_POST['tel']);
        $mail = mysqli_real_escape_string($connection, $_POST['mail']);
        $web = mysqli_real_escape_string($connection, $_POST['web']);
        $whatsapp = mysqli_real_escape_string($connection, $_POST['whatsapp']);

        $userId = mysqli_real_escape_string($connection, $_POST['userId']);
        $token = mysqli_real_escape_string($connection, $_POST['token']);

        if($this->checkToken($userId, $token)) {
            $sql = "INSERT INTO unternehmen 
                (name, url, kategorie, tel, mail, web, beschreibung, whatsapp) 
                VALUES 
                ('$name', '$url', '$kategorie','$tel', '$mail', '$web', '$beschreibung', '$whatsapp')";

            if ($connection->query($sql) === TRUE) {
                //Unternehmen hinzugefügt, muss jetzt noch User zugeordnet werden
                $unternehmenId = $connection->insert_id;
                $sql = "UPDATE accounts
                SET unternehmen = ".$unternehmenId."
                WHERE id = ".$userId;

                if ($connection->query($sql) === TRUE) {
                    //User Account wurde erfolgreich aktualisiert
                    $mailer = new Mailer();
                    $mailer->neuesUnternehmen($unternehmenId, $name, $kategorie, $tel, $mail, $web, $beschreibung);
                    $mailer->neuesUnternehmen_user($userId);
                    echo json_encode([
                        "success" => true,
                        "result" => $unternehmenId
                    ]);
                }else{
                    echo json_encode([
                        "success" => false,
                        "result" => "Unternehmen konnte nicht deinem User-Account zugeordnet werden: ".$connection->error
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "result" => "Das Unternehmen konnte nicht hinzugefügt werden: ".$connection->error
                ]);
            }
        }
    }
    function approveUnternehmen(){
        $database = new Database();
        $connection = $database->connect();

        $id = $connection->real_escape_string($_POST['id']);

        $sql = "UPDATE unternehmen SET approved = '1' WHERE id='$id'";

        if ($connection->query($sql) === TRUE) {
            $sql = "SELECT accounts.id
                    FROM accounts
                    WHERE accounts.unternehmen = ".$id;
	        $result = $connection->query($sql);
	        $row = $result->fetch_assoc();
	        if(!$result){
		        echo $connection->error;
		        exit();
	        }else{
		        $userId = $row['email'];
	        }
			//Email senden
	        $mailer = new Mailer();
	        $mailer->sendApprovedMail($userId);

	        echo "Unternehmen wurde freigeschaltet";
        } else {
            echo "Fehler bei der Unternehmenfreischaltung: " . $connection->error;
        }
    }

    function changePersonalInformation(){
        $database = new Database();
        $connection = $database->connect();

        $userId = $connection->real_escape_string($_POST['userId']);
        $token = $connection->real_escape_string($_POST['token']);

        $passwort = $connection->real_escape_string($_POST['password']);

        $name = $connection->real_escape_string($_POST['name']);
        $email = $connection->real_escape_string($_POST['email']);

        if($this->checkToken($userId, $token)){
            $sql = "SELECT accounts.password FROM accounts WHERE id = '".$userId."'";
            $result = $connection->query($sql);
            $row = $result->fetch_assoc();
            if(password_verify($passwort, $row['password'])){
                //Passwort stimmt, moving on
                if($email != null || $email != ""){
                    $sql =  "UPDATE accounts
                    SET accounts.name = '".$name."', 
                    accounts.email = '".$email."'
                    WHERE accounts.id = ".$userId;
                }else{
                    $sql =  "UPDATE accounts
                    SET accounts.name = '".$name."'
                    WHERE accounts.id = ".$userId;
                }

                if ($connection->query($sql) === TRUE) {
                    return json_encode([
                        "success" => true,
                        "result" => null
                    ]);
                } else {
                    return json_encode([
                        "success" => false,
                        "result" => $connection->error
                    ]);
                }
            }else{
                return json_encode([
                   "success" => false,
                   "result" => "Passwort konnte nicht verifiztiert werden"
                ]);
            }


        }
    }
    function changePasswort(){
        $database = new Database();
        $connection = $database->connect();

        $userId = $connection->real_escape_string($_POST['userId']);
        $token = $connection->real_escape_string($_POST['token']);

        $passwort = $connection->real_escape_string($_POST['password']);
        $newPasswort = $connection->real_escape_string($_POST['newPassword']);
        $newPasswort = password_hash($newPasswort, PASSWORD_DEFAULT);

        if($this->checkToken($userId, $token)){
            $sql = "SELECT accounts.password FROM accounts WHERE id = '".$userId."'";
            $result = $connection->query($sql);
            $row = $result->fetch_assoc();
            if(password_verify($passwort, $row['password'])){
                //Passwort stimmt, moving on

                $sql =  "UPDATE accounts
                SET accounts.password = '".$newPasswort."'
                WHERE accounts.id = ".$userId;

                if ($connection->query($sql) === TRUE) {
                    return json_encode([
                        "success" => true,
                        "result" => null
                    ]);
                } else {
                    return json_encode([
                        "success" => false,
                        "result" => $connection->error
                    ]);
                }
            }else{
                return json_encode([
                    "success" => false,
                    "result" => "Passwort konnte nicht verifiztiert werden"
                ]);
            }


        }
    }

    //Login & SignUp
    function login(){
        $database = new Database();
        $connection = $database->connect();

        $email = $connection->real_escape_string($_POST['email']);
        $password = $connection->real_escape_string($_POST['password']);

        $sql =  "SELECT accounts.id, accounts.password, accounts.unternehmen, accounts.name, accounts.email,
                    CASE 
                        WHEN unternehmen.approved IS NULL
                        THEN 0
                        WHEN unternehmen.approved = '1'
                        THEN 1
                        WHEN unternehmen.approved = '0'
                        THEN 0
                    END AS approved
                FROM accounts
                    LEFT OUTER JOIN unternehmen
                    ON unternehmen.id = accounts.unternehmen
                WHERE accounts.uniqueIdentifier = '$email'";
        $result = $connection->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            if(password_verify($password, $row['password'])){
                //Passwort ist richtig

                //TOKEN wird generiert, proceeds wenn generation erfolgreich
                $token = $this->setToken($row['id']);
                if ($token != "ERROR"){
                    if($row['unternehmen'] != 0){
//                    User hat Unternehmen
                        $hasUnternehmen = true;
                        $unternehmenId = $row['unternehmen'];
                    }else{
                        $hasUnternehmen = false;
                        $unternehmenId = null;
                    }
                    if($row['approved']){$approved = true;}else{$approved = false;}

                    $returnObject = [
                        "id" => $row['id'],
                        "name" => $row['name'],
                        "email" => $row['email'],
                        "unternehmen" => [
                            "hasUnternehmen" => $hasUnternehmen,
                            "unternehmenId" => $unternehmenId,
                            "approved" => $approved
                        ],
                        "token" => $token
                    ];
                    return json_encode([
                        "success" => true,
                        "result" => $returnObject
                    ]);
                }
            }else{
                //Passwort ist falsch
                return json_encode([
                    "success" => false,
                    "result" => "Das eingegebene Passwort ist falsch."
                ]);
            }
        }else{
            return json_encode([
                "success" => false,
                "result" => "Es gibt noch keinen Benutzer mit der E-Mail Adresse"
            ]);
        }
    }
    function signup(){
        $database = new Database();
        $connection = $database->connect();

        $name = $connection->real_escape_string($_POST['name']);
        $email = $connection->real_escape_string($_POST['email']);
        $password = $connection->real_escape_string($_POST['password']);


        if($name == "" || $name == null || $email == "" || $email == null || $password == "" || $password == null){
            echo "Eine Eingabe wurde nicht eingegeben!";
            exit;
        }
        $password = password_hash($password, PASSWORD_DEFAULT);

        //reCaptchav3 Integration hier

        //Check if Unique ID is already assigned
        if(!$this->checkUniqueIdentifierAvailability($email)){
            echo "Ein Benutzer für die Email Adresse ist bereits registriert. Bitte klicken Sie auf anmelden!";
            exit;
        }

        //Benutzer wird in die Datenbank hinzugefügt
        $date = date("y-m-d H:i:s");

        $sql = "INSERT INTO accounts
                (name, email, password, uniqueIdentifier, accountType, date)
                VALUES
                ('".$name."', '".$email."', '".$password."', '".$email."', 'regular', '".$date."');";

        if ($connection->query($sql) === TRUE) {
            //Email Verifizierung wird verschickt
            $mailer = new Mailer();
            $mailer->sendAccountVerificationEmail($connection->insert_id, $name, $email);
            //TOKEN wird generiert, proceeds wenn generation erfolgreich
            $token = $this->setToken($connection->insert_id);
            if ($token != "ERROR"){
                $returnObject = [
                    "id" => $connection->insert_id,
                    "name" => $name,
                    "email" => $email,
                    "unternehmen" => [
                        "hasUnternehmen" => false,
                        "unternehmenId" => null,
                        "approved" => false
                    ],
                    "token" => $token
                ];
                return json_encode([
                    "success" => true,
                    "result" => $returnObject
                ]);
            }else{
                return json_encode([
                    "success" => false,
                    "result" => "Error bei der Token Generierung."
                ]);
            }
        } else {
            return json_encode([
                "success" => false,
                "result" => "Error beim Datenbankzugriff: ".$connection->error."."
            ]);
        }

    }
    function tryOAuthLogin(){
        $database = new Database();
        $connection = $database->connect();

        $name = $connection->real_escape_string($_POST['name']);
        $email = $connection->real_escape_string($_POST['email']);
        $uniqueIdentifier = $connection->real_escape_string($_POST['uniqueIdentifier']);
        $password = $connection->real_escape_string($_POST['password']);

        if($this->checkUniqueIdentifierAvailability($uniqueIdentifier)){
            //Nutzer meldet sich zum ersten Mal an
            $date = date("y-m-d H:i:s");

            $sql = "INSERT INTO accounts
                (name, email, password, uniqueIdentifier, accountType, date)
                VALUES
                ('".$name."', '".$email."', '".$password."', '".$uniqueIdentifier."', 'oauthLogin', '".$date."');";

            if ($connection->query($sql) === TRUE) {
                $token = $this->setToken($connection->insert_id);
                if ($token != "ERROR") {
                    $returnObject = [
                        "id" => $connection->insert_id,
                        "name" => $name,
                        "email" => $email,
                        "unternehmen" => [
                            "hasUnternehmen" => false,
                            "unternehmenId" => null,
                            "approved" => false
                        ],
                        "token" => $token
                    ];
                    return json_encode([
                        "success" => true,
                        "result" => $returnObject
                    ]);
                }
            } else {
                return json_encode([
                    "success" => false,
                    "result" => "FIRST LOGIN ERROR: ".$connection->error
                ]);
            }
        }else{
            $sql =  "SELECT accounts.id, accounts.unternehmen, accounts.name, accounts.email,
                    CASE 
                        WHEN unternehmen.approved IS NULL
                        THEN 0
                        WHEN unternehmen.approved = '1'
                        THEN 1
                        WHEN unternehmen.approved = '0'
                        THEN 0
                    END AS approved
                FROM accounts
                    LEFT OUTER JOIN unternehmen
                    ON unternehmen.id = accounts.unternehmen
                WHERE accounts.uniqueIdentifier = '$uniqueIdentifier'";
            $result = $connection->query($sql);
            $row = $result->fetch_assoc();
            if($result->num_rows > 0){
                if($row['unternehmen'] != 0){
//                    User hat Unternehmen
                    $hasUnternehmen = true;
                    $unternehmenId = $row['unternehmen'];
                }else{
                    $hasUnternehmen = false;
                    $unternehmenId = null;
                }
                if($row['approved']){$approved = true;}else{$approved = false;}

                $token = $this->setToken($row['id']);
                if ($token != "ERROR") {
                    $returnObject = [
                        "id" => $row['id'],
                        "name" => $row['name'],
                        "email" => $row['email'],
                        "unternehmen" => [
                            "hasUnternehmen" => $hasUnternehmen,
                            "unternehmenId" => $unternehmenId,
                            "approved" => $approved
                        ],
                        "token" => $token
                    ];
                    return json_encode([
                        "success" => true,
                        "result" => $returnObject
                    ]);
                }
            }
            else{
                return json_encode([
                    "success" => false,
                    "result" => "NO USER FOUND?!: ".$connection->error
                ]);
            }
        }
    }
    function cookieLogin(){
        $database = new Database();
        $connection = $database->connect();

        $id = $connection->real_escape_string($_POST['id']);
        $token = $connection->real_escape_string($_POST['token']);

        $sql =  "SELECT id FROM loginTokens
                WHERE loginTokens.accountId = '$id' AND loginTokens.token = '".$token."'";
        $result = $connection->query($sql);
        if(!$result){
            printf($connection->error);
        }
        if($result->num_rows > 0){
            //Login mit Token verifiziert. User Daten werden geladen
            $sql =  "SELECT accounts.id, accounts.unternehmen, accounts.name, accounts.email, accountType,
                    CASE 
                        WHEN unternehmen.approved IS NULL
                        THEN 0
                        WHEN unternehmen.approved = '1'
                        THEN 1
                        WHEN unternehmen.approved = '0'
                        THEN 0
                    END AS approved
                FROM accounts
                    LEFT OUTER JOIN unternehmen
                    ON unternehmen.id = accounts.unternehmen
                WHERE accounts.id = '$id'";

            $result = $connection->query($sql);
            $row = $result->fetch_assoc();
            if($result == null){echo $connection->error;}
            if($row['unternehmen'] != 0){
                //User hat Unternehmen
                $hasUnternehmen = true;
                $unternehmenId = $row['unternehmen'];
            }else{
                $hasUnternehmen = false;
                $unternehmenId = null;
            }
            if($row['approved']){$approved = true;}else{$approved = false;}

            $returnObject = [
                "id" => $row['id'],
                "name" => $row['name'],
                "email" => $row['email'],
                "unternehmen" => [
                    "hasUnternehmen" => $hasUnternehmen,
                    "unternehmenId" => $unternehmenId,
                    "approved" => $approved
                ],
                "token" => $token,
                "accountType" => $row['accountType']
            ];
            return json_encode([
                "success" => true,
                "result" => $returnObject
            ]);

        }else{
            return json_encode([
                "success" => false,
                "result" => "Kein Token der ID zuweisbar"
            ]);
        }
    }

    function verifyEmail(){
        $database = new Database();
        $connection = $database->connect();

        $id = $connection->real_escape_string($_GET['verifyEmail']);

        $sql = "UPDATE accounts
                SET verified = 1
                WHERE id = ".$id;

        if ($connection->query($sql) === TRUE) {
            //TODO: Ditte hier schöner machen
            echo "E-Mail verifiziert.";
        }
    }

    function setToken($accountId){
        $database = new Database();
        $connection = $database->connect();

        $date = date("y-m-d H:i:s");
        $token = $this->generateRandomToken();

        $sql = "INSERT INTO loginTokens
                (accountId, token, date)
                VALUES
                ('".$accountId."', '".$token."', '".$date."');";

        if ($connection->query($sql) === TRUE) {
            return $token;
        } else {
            return "ERROR";
        }
    }
    function deleteToken(){
        $database = new Database();
        $connection = $database->connect();

        $userId = $connection->real_escape_string($_POST['userId']);
        $token = $connection->real_escape_string($_POST['token']);

        $sql = "DELETE FROM loginTokens
                WHERE loginTokens.accountId = '".$userId."' AND loginTokens.token = '".$token."'";
        if ($connection->query($sql) === TRUE) {
            return json_encode([
                "success" => true,
                "result" => "TOKEN REMOVED"
            ]);
        } else {
            return json_encode([
                "success" => false,
                "result" => $connection->error
            ]);
        }
    }
    function checkToken($accountId, $token){
        $database = new Database();
        $connection = $database->connect();

        $sql = "SELECT id FROM loginTokens WHERE loginTokens.accountId = '".$accountId."' AND loginTokens.token = '".$token."'";
        $result = $connection->query($sql);

        if($result != null && $result->num_rows > 0){
            //Wenn es einen Verifizierten Login mit dem Token passend zur Account ID gibt, wird TRUE zurückgegeben
            return true;
        }
        else{
            //TODO: Logout Function
            return false;
        }
    }

    function generateRandomToken(){
        try {
            $string = random_bytes(128);
        } catch (TypeError $e) {
            // Well, it's an integer, so this IS unexpected.
            die("An unexpected error has occurred");
        } catch (Error $e) {
            // This is also unexpected because 32 is a reasonable integer.
            die("An unexpected error has occurred");
        } catch (Exception $e) {
            // If you get this message, the CSPRNG failed hard.
            die("Could not generate a random string. Is our OS secure?");
        }

        return bin2hex($string);
    }
}

class NotificationHandler{
	function sendNotifications($unternehmenId, $kategorien, $header, $slug, $url){
		$database = new Database();
		$connection = $database->connect();

		$pushSubscriptions_sql = "SELECT subscriptions.adress, subscriptions.auth, subscriptions.p256dh
				FROM subscriptions
				INNER JOIN abonnierteUnternehmen
				ON abonnierteUnternehmen.account = subscriptions.accountId AND abonnierteUnternehmen.unternehmenId = ".$unternehmenId." AND abonnierteUnternehmen.abonniert = 1
				WHERE subscriptions.active = 1
				AND subscriptions.type = 'push'";

        $pushSubscriptions = $connection->query($pushSubscriptions_sql);

		$pushMeldungen = [];

		if ($pushSubscriptions->num_rows > 0) {
			while ($row = $pushSubscriptions->fetch_assoc()) {
				array_push($pushMeldungen, [
					'subscription' => Subscription::create([ // this is the structure for the working draft from october 2018 (https://www.w3.org/TR/2018/WD-push-api-20181026/)
						"endpoint" => $row['adress'],
						"keys" => [
							'p256dh' => $row['p256dh'],
							'auth' => $row['auth']
						],
					]),
					'payload' => '{"title": "'.$header.'", "body":"'.substr($slug, 0, 150).'", "url": "'.$url.'"}',
				]);
			}
		}

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
		foreach ($pushMeldungen as $notification) {
			$webPush->queueNotification(
				$notification['subscription'],
				$notification['payload'] // optional (defaults null)
			);
		}

		foreach ($webPush->flush() as $report) {
			$endpoint = $report->getRequest()->getUri()->__toString();

			if ($report->isSuccess()) {
//                Successful Messagesent -> no log required
			} else {
			    if($report->isSubscriptionExpired()){
			        echo "expired";
//			        TODO: DELETE EXPIRED SUBSCRIPTIONS
                } else{
                    echo "<br><br>[x] Message failed to sent for subscription {$endpoint}: {$report->isSubscriptionExpired()}";
                }
			}
		}


	}
}

class Mailer{
    function adminEmails(){
        return ["ewaetke10@gmail.com", "elektro-waetke@web.de"];
    }
    function neuesUnternehmen($id, $name, $kategorie, $tel, $mail, $web, $beschreibung){
        $addressaten = $this->adminEmails();

        $date = date("d.m.Y H:i:s ");
        $betreff = "Eine Aufnahmeanfrage wurde gesendet!";

        require_once("MailTemplates/neues-unternehmen.php");
        $emailBodyNoHTML = "";
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'mx2fcc.netcup.net';  // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'mail@luebz-liefert.de';                     // SMTP username
            $mail->Password   = 'e&Y1Z8Xgcn9FQ6tp';                               // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                                    // TCP port to connect to
            $mail->CharSet = 'utf-8';

            //Recipients
            $mail->setFrom('mail@luebz-liefert.de', 'NO-REPLY - Luebz-Liefert');
            for ($i=0; $i < count($addressaten); $i++) {
                $mail->addAddress($addressaten[$i]);
            }


            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $betreff;
            $mail->Body    = $emailBody;

            $mail->send();
            return true;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
    function neuesUnternehmen_user($id){
        $behindTheScenes = new BehindTheScenes();
        $betreff = "Dein Unternehmen bei Lübz Liefert";
        $name = $behindTheScenes->getUserName($id);
        $email = $behindTheScenes->getUserEmail($id);
        require_once("MailTemplates/neues-unternehmen-user.php");
        $emailBodyNoHTML = "";
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'mx2fcc.netcup.net';  // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'mail@luebz-liefert.de';                     // SMTP username
            $mail->Password   = 'e&Y1Z8Xgcn9FQ6tp';                               // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                                    // TCP port to connect to
            $mail->CharSet = 'utf-8';

            //Recipients
            $mail->setFrom('mail@luebz-liefert.de', 'NO-REPLY - Luebz-Liefert');
            $mail->addAddress($email);


            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $betreff;
            $mail->Body    = $emailBody;

            $mail->send();
            return json_encode([
                "success" => true,
                "result" => null
            ]);
        } catch (Exception $e) {
            return json_encode([
                "success" => false,
                "result" => "Error beim E-Mail Versand: ".$mail->ErrorInfo."."
            ]);
        }
    }
    function sendApprovedMail($empfänger, $url){
        $date = date("d.m.Y H:i:s ");
        $betreff = "Eine Aufnahmeanfrage wurde gesendet!";

        $emailBody = "Vielen Dank, dass Sie sich bei uns eingeschrieben haben!<br><br>Wir haben uns Ihren Antrag angeschaut und dann bestätigt und freigegeben.<br><br>Ihr Eintrag ist ab sofort
auf der Internetseite <a href='https://luebz-liefert.de'>Lübz Liefert</a> zu erreichen. Von dort aus können Sie den Eintrag auch Teilen und als Digitale Visitenkarte nutzen.";
        $emailBodyNoHTML = "Vielen Dank, dass Sie sich bei uns eingeschrieben haben! Wir haben uns Ihren Antrag angeschaut und dann bestätigt und freigegeben. Ihr Eintrag ist ab sofort
auf der Internetseite https://luebz-liefert.de zu erreichen. Von dort aus können Sie den Eintrag auch Teilen und als Digitale Visitenkarte nutzen.";
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'mx2fcc.netcup.net';  // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'mail@luebz-liefert.de';                     // SMTP username
            $mail->Password   = 'e&Y1Z8Xgcn9FQ6tp';                               // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                                    // TCP port to connect to
            $mail->CharSet = 'utf-8';

            //Recipients
            $mail->setFrom('mail@luebz-liefert.de', 'Luebz-Liefert');
            for ($i=0; $i < count($empfänger); $i++) {
                $mail->addAddress($empfänger[$i]);
            }


            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $betreff;
            $mail->Body    = $emailBody;
            $mail->AltBody = $emailBodyNoHTML;

            $mail->send();
            echo 'Ihre Kontaktanfrage wurde erfolgreich versendet!';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
    function sendAccountVerificationEmail($id, $name, $email){
        $date = date("d.m.Y H:i:s ");
        $betreff = "Fast fertig! Bestätige jetzt deine E-Mail Adresse - Lübz Liefert";

        require_once("MailTemplates/email-verification.php");
        $emailBodyNoHTML = "";
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'mx2fcc.netcup.net';  // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'mail@luebz-liefert.de';                     // SMTP username
            $mail->Password   = 'e&Y1Z8Xgcn9FQ6tp';                               // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                                    // TCP port to connect to
            $mail->CharSet = 'utf-8';

            //Recipients
            $mail->setFrom('mail@luebz-liefert.de', 'NO-REPLY - Luebz-Liefert');
            $mail->addAddress($email);


            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $betreff;
            $mail->Body    = $emailBody;

            $mail->send();
            return json_encode([
                "success" => true,
                "result" => null
            ]);
        } catch (Exception $e) {
            return json_encode([
                "success" => false,
                "result" => "Error beim E-Mail Versand: ".$mail->ErrorInfo."."
            ]);
        }
    }
}

class BehindTheScenes{
    function getUserName($id){
        $database = new Database();
        $connection = $database->connect();

        $sql = "SELECT accounts.name
                FROM accounts
                WHERE accounts.id = ".$id;
        $result = $connection->query($sql);
        $row = $result->fetch_assoc();
        if(!$result){
            return $connection->error;
        }else{
            return $row['name'];
        }
    }
    function getUserEmail($id){
        $database = new Database();
        $connection = $database->connect();

        $sql = "SELECT accounts.email
                FROM accounts
                WHERE accounts.id = ".$id;
        $result = $connection->query($sql);
        $row = $result->fetch_assoc();
        if(!$result){
            return $connection->error;
        }else{
            return $row['email'];
        }
    }
    function getUnternehmenName($id){
        $database = new Database();
        $connection = $database->connect();

        $sql = "SELECT unternehmen.name
                FROM unternehmen
                WHERE unternehmen.id = ".$id;
        $result = $connection->query($sql);
        $row = $result->fetch_assoc();
        if(!$result){
            return $connection->error;
        }else{
            return $row['name'];
        }
    }

    function slugify($string){
    	return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    }
	
	function safe_json_encode($value, $options = 0, $depth = 512, $utfErrorFlag = false) {
		$behindTheScenes = new BehindTheScenes();
		$encoded = json_encode($value, $options, $depth);
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				return $encoded;
			case JSON_ERROR_DEPTH:
				return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
			case JSON_ERROR_STATE_MISMATCH:
				return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
			case JSON_ERROR_CTRL_CHAR:
				return 'Unexpected control character found';
			case JSON_ERROR_SYNTAX:
				return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
			case JSON_ERROR_UTF8:
				$clean = $behindTheScenes->utf8ize($value);
				if ($utfErrorFlag) {
					return 'UTF8 encoding error'; // or trigger_error() or throw new Exception()
				}
				return $behindTheScenes->safe_json_encode($clean, $options, $depth, true);
			default:
				return 'Unknown error'; // or trigger_error() or throw new Exception()

		}
	}

	function utf8ize($mixed) {
		$behindTheScenes = new BehindTheScenes();
		if (is_array($mixed)) {
			foreach ($mixed as $key => $value) {
				$mixed[$key] = $behindTheScenes->utf8ize($value);
			}
		} else if (is_string ($mixed)) {
			return utf8_encode($mixed);
		}
		return $mixed;
	}
}

?>