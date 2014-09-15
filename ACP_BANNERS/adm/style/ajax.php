<?php

/**
 * -------------------------------------------------------
 * Class to database connect
 * @author = Erick Bruno <erickfabiani123@gmail.com>
 * @date = 2014-07-23 12h47 AM
 * 	
 */
class DevXIII_Connection 
{
	/**
	 * Instance of PDO Class
	 */
	private $_connection;
	
	/**
	 * The Database host
	 * e.g: localhost
	 * @var string
	 */
	private $_host;

	/**
	 * The database that we'll be working
	 * @var string
	 */
	private $_database;

	/**
	 * The database user
	 * @var string
	 */
	private $_user;

	/**
	 * The database password
	 * @var string
	 */
	private $_pass;

	public function __construct( $host, $database, $user, $pass )
	{
		$this->_host 	 = $host;
		$this->_database = $database;
		$this->_user 	 = $user;
		$this->_pass 	 = $pass;

		return $this;
	}

	public function connect() 
	{
		return $this->_connection = new PDO("mysql:host=".$this->_host.";dbname=".$this->_database, $this->_user, $this->_pass);
	}

}

$parent_id = $_POST['parent_id'];
$PDO = new DevXIII_Connection( 'localhost', 'phpBB', 'root', 'root' );

$result = $PDO->connect()->query("
	SELECT
		forum_id,
		forum_name
	FROM
		phpbb_forums 
	WHERE
		parent_id = $parent_id;	
");

$json = array();
while ($row = $result->fetch(PDO::FETCH_OBJ)){
	$json[] = array('forum_id' => $row->forum_id, 'forum_name' => $row->forum_name);	
}
echo(json_encode($json));















