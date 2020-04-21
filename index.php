<?
define('DB_HOST', 'localhost');
define('DB_NAME', 'starconn_crm');
define('DB_USER', 'starconn_test');
define('DB_PASS', 'salut111');
define('TESTING', 1);

require("database.php");
class main extends database {
	public function index() {
		var_dump($this);
		$all = $this->query("SHOW TABLES");
			var_dump($all);   
	}
}

$main = new main();  
$main->index(); 

if(TESTING==1) {
	echo $GLOBALS['test'];
}

?>
