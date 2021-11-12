<?
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_name');
define('DB_USER', 'db_user');
define('DB_PASS', 'db_pass');
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
