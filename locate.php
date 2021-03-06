<?php


require_once "./locateconfig.php";

class db {
    private static $conn;
    //conn - connect to MYSQL
    //to make life easier for first time developers this function automatically creates the database & table if they are missing
    public static function conn() {
        self::$conn = new mysqli(locate_val("servername"), locate_val("username"), locate_val("password"));
        // Check connection
        if (self::$conn->connect_error) {
            die("Connection failed: " . self::$conn->connect_error);
        }
        //check if we need to create the database
        self::create_database();
        self::$conn->select_db(locate_val("databasename"));
        //check if we need to create tables
        self::create_tables();
        return self::$conn;
    }
    //end conn
    //create_database
    private static function create_database() {
        self::$conn->query("CREATE DATABASE IF NOT EXISTS " . locate_val("databasename")) or die("create_database failed");
    }
    //end create_database
    //create_tables
    private static function create_tables() {
        $result = self::$conn->query("select * from LOCATION limit 1");
        if(empty($result)) {
                   $query = "CREATE TABLE LOCATION (
                          id INT AUTO_INCREMENT,
			  map_x DOUBLE,
                          map_y DOUBLE,
                          map_w DOUBLE,
                          map_z DOUBLE,
                          date_created DATETIME,
                          PRIMARY KEY  (ID)
                          )";
            self::$conn->query($query) or die("create table failed");
	}
    }
    //end create_tables
} 


//coordinate class
class coordinate {

    private $conn;

    //constructor
    function __construct() {
	$this->conn = db::conn(); //connect to mysql
    }

    //locate
    public function locate(){
       $data=file_get_contents("php://input"); 
       $data = json_decode($data, TRUE);

	$this->conn->query("insert into LOCATION values(NULL," . $data['poseAMCLx'] . "," . $data['poseAMCLy'] . "," . $data['poseAMCLw'] . "," . $data['poseAMCLz'] . ",NOW() )") or die("location insert failed");

       exit;
    }
    //end locate

    //send_cor
    public function send_cor(){
       $a = array();
       $result = $this->conn->query("select * from LOCATION ORDER BY id DESC LIMIT 0, 1") or die("send failed");
       if($row =  $result->fetch_assoc()) {
       $a["x"]=$row["map_x"];
       $a["y"]=$row["map_y"];
       $a["w"]=$row["map_w"];
       $a["z"]=$row["map_z"];
       }
       $this->json_headers();
       echo json_encode($a);
       exit;
    }
    //end send_cor

    //json_headers
    private function json_headers() {
       header('Content-Type: application/json');
       header('Cache-Control: no-cache, must-revalidate');
       header("Pragma: no-cache");
       header("Expires: 0");
       header('Access-Control-Allow-Origin: *');
    }
    //end json_headers
}




	if(isset($_GET['locate'])) {
         $cor = new coordinate();
   	 $cor->locate();
	}

	if(isset($_GET['map'])) {
   	 $cor = new coordinate();
         $cor->send_cor();
	}

