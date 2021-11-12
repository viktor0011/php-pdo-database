<? 
define('TESTING',0);
class database {
	static public $db;  //for singleton
	private $sQuery;
	private $bConnected = false;	
	private $log;
	private $parameters;
		public function __construct()
		{ 		
			$this->parameters = array();
		}
		private function __clone() {
		}
		private function __wakeup() {
			
		}
		private function Connect($hostname, $database, $username, $password)
		{
           if (!(self::$db instanceof PDO)) {
			$dsn = 'mysql:dbname='.$database.';host='.$hostname.";charset=utf8";
			try 
			{
				self::$db = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				// treb sa o testez in adminka
               // self::$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
				self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
				self::$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
				self::$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
			
				$this->bConnected = true;
			}
			catch (PDOException $e) 
			{ 
				 $this->ExceptionLog($e->getMessage());
			}
			  }
			   return self::$db;

		}
        
	 	protected function CloseConnection()
	 	{
	 		self::$db = null;
	 	}
 
		protected function Init($query,$parameters = "")
		{        

			IF(TESTING==1) {
                $time = microtime();
				$time = explode(' ', $time);
				$time = $time[1] + $time[0];
				$start = $time;  
			}
			if(!$this->bConnected) {
				$this->Connect(DB_HOST, DB_NAME,DB_USER, DB_PASS);
            }
			try {
				$this->sQuery = self::$db->prepare($query);
				$this->bindMore($parameters);
				if(!empty($this->parameters)) {
					foreach($this->parameters as $param)
					{
						$parameters = explode("\x7F",$param);
						$this->sQuery->bindParam($parameters[0],$parameters[1]);
					}		
				} 
				$this->success = $this->sQuery->execute();		
			}
			catch(PDOException $e)
			{
					$this->ExceptionLog($e->getMessage()." ", json_encode($this->sQuery) );
			}
			if(TESTING==1){
				$time = microtime();
				$time = explode(' ', $time);
				$time = $time[1] + $time[0];
				$finish = $time;
				$total_time = round(($finish - $start), 4);
				$GLOBALS['test'] .= "<div style='color:red;'>";
				$GLOBALS['test'] .= $query."<br>";
                $GLOBALS['test'] .= json_encode($this->parameters)."<br>";
				$GLOBALS['test'] .= 'Query generated in '.$total_time.' seconds.<br></div>'; 
			}
			$this->parameters = array();
	}
	
	protected function closetags($html) {
		#put all opened tags into an array
		preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
		$openedtags = $result[1];   #put all closed tags into an array
		preg_match_all('#</([a-z]+)>#iU', $html, $result);
		$closedtags = $result[1];
		$len_opened = count($openedtags);
		# all tags are closed
		if (count($closedtags) == $len_opened) {
			return $html;
		}
		$openedtags = array_reverse($openedtags);
		# close tags
		for ($i=0; $i < $len_opened; $i++) {
			if (!in_array($openedtags[$i], $closedtags)){
				$html .= '</'.$openedtags[$i].'>';
			} else {
				unset($closedtags[array_search($openedtags[$i], $closedtags)]); 
			}
		} 
		return $html;
	} 
	protected function bind($para, $value){	
		if(!is_array($value)) {
			$value = $this->removeTags($value,array("img","div","script","style"));
			$value = $this->closetags($value);
			$value = iconv("UTF-8", "ASCII//IGNORE", $value); // "%#dsdeaa.,d s#$4.sedf;21df"
			if(is_array($this->parameters))
				$this->parameters[count($this->parameters)] = ":" . $para . "\x7F" . utf8_encode($value);
			else
				$this->parameters[] = ":" . $para . "\x7F" . utf8_encode($value);
		}
	}
	protected function bindMore($parray){
		if(empty($this->parameters) && is_array($parray)) {
			$columns = array_keys($parray);
			foreach($columns as $i => &$column)	{
				$this->bind($column, $parray[$column]);
			}
		}
	}	
	protected function htmlspecial_array(&$variable) {
		foreach ($variable as &$value) {
			if (!is_array($value)) { 
				$value = htmlspecialchars($value);
			} else { 
				$this->htmlspecial_array($value);
			}
		}
	}		
	protected function removeTags($html, $tags){
		$html = preg_replace('/(<[^>]+) onclick=".*?"/i', '$1', $html);
		$existing_tags = $this->getAllTagNames($html);
		$allowable_tags = '<'.implode('><', array_diff($existing_tags, $tags)).'>';
		return strip_tags($html, $allowable_tags);
	}
	/**
	 * Get a list of tag names in the provided HTML string
	 * @return Array
	 */
	protected function getAllTagNames($html){	
		$tags = array();
		$part = explode("<", $html);
		foreach($part as $tag){
			$chunk = explode(" ", $tag);
			if(empty($chunk[0]) || $chunk[0][0] == "/") continue;
			$tag = trim($chunk[0], " >");
			if(!in_array($tag, $tags)) $tags[] = $tag;
		}
		return $tags;
	}

	protected function query($query,$params = null, $fetchmode = PDO::FETCH_ASSOC){
		$query = trim($query);
		$this->Init($query,$params);
		$rawStatement = explode(" ", $query);
		$statement = strtolower($rawStatement[0]);
		if ($statement === 'insert' ||  $statement === 'update' || $statement === 'delete') {
			return $this->sQuery->rowCount();	
		} else if($statement === 'alter'||$statement==='create') {
			return 1;
		} else {
			//for select, show
			$return = $this->sQuery->fetchAll($fetchmode);
			$this->htmlspecial_array($return);
			return $return;
		}
	}
	
	protected function lastInsertId() {
		return self::$db->lastInsertId();
	}	
		
	protected function column($query,$params = null){
		$this->Init($query,$params);
		$Columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);		
		$this->sQuery->closeCursor();
		$column = null;
		foreach($Columns as $cells) {
			$column[] = $cells[0];
		}
		return $column;
	}	
  
	protected function row($query,$params = null,$fetchmode = PDO::FETCH_ASSOC){				
		$this->Init($query,$params);
		return $this->sQuery->fetch($fetchmode);			
	}

	protected function single($query,$params = null){
		$this->Init($query,$params);
		return $this->sQuery->fetchColumn();
	}
	public function ExceptionLog($message , $sql = ""){
		$this->log = new Log();	
		$exception  = 'Unhandled Exception. <br />';
		$exception .= $message;
		$exception .= "<br /> You can find the error back in the log.";
		if(!empty($sql)) {
			$message .= "\r\nRaw SQL : "  . $sql;
		}
		$this->log->write($message);
		return $message;
		throw new Exception($message);
	}		
}

	class Log {
			
		    # @string, Log directory name
		    	private $path = 'logs/';
			
		    # @void, Default Constructor, Sets the timezone and path of the log files.
			public function __construct() {
				global $root;
				//date_default_timezone_set('Europe/Amsterdam');	
				$this->path  = $root.$this->path;	
			}
			
		   /**
		    *   @void 
		    *	Creates the log
		    *
		    *   @param string $message the message which is written into the log.
		    *	@description:
		    *	 1. Checks if directory exists, if not, create one and call this method again.
	            *	 2. Checks if log already exists.
		    *	 3. If not, new log gets created. Log is written into the logs folder.
		    *	 4. Logname is current date(Year - Month - Day).
		    *	 5. If log exists, edit method called.
		    *	 6. Edit method modifies the current log.
		    */	
			public function write($message) {
				$date = new DateTime();
				$log = $this->path . $date->format('Y-m-d').".txt";

				if(is_dir($this->path)) {
					if(!file_exists($log)) {
						$fh  = fopen($log, 'a+') or die("Fatal Error !");
						$logcontent = "Time : " . $date->format('H:i:s')."\r\n" . $message ."\r\n";
						fwrite($fh, $logcontent);
						fclose($fh);
					}
					else {
						$this->edit($log,$date, $message);
					}
				}
				else {
					  if(mkdir($this->path,0777) === true) 
					  {
 						 $this->write($message);  
					  }	
				}
			 }
			
			/** 
			 *  @void
			 *  Gets called if log exists. 
			 *  Modifies current log and adds the message to the log.
			 *
			 * @param string $log
			 * @param DateTimeObject $date
			 * @param string $message
			 */
			    private function edit($log,$date,$message) {
				$logcontent = "Time : " . $date->format('H:i:s')."\r\n" . $message ."\r\n\r\n";
				$logcontent = $logcontent . file_get_contents($log);
				file_put_contents($log, $logcontent);
			    }
		}
?>
