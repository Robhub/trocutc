<?php
class BDD extends PDO
{
	private $DB_OPTIONS = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
	public function __construct()
	{
		//if (_SERVER["SERVER_ADDR"] == '127.0.0.1') $b = json_decode(file_get_contents('BDDlocalhost.json'));
		$b = json_decode(file_get_contents('BDD.json'));
		try 
		{
			parent::__construct($b->type.':host='.$b->host.';dbname='.$b->dbname, $b->user, $b->pass, $this->DB_OPTIONS);
		}
		catch (PDOException $e)  { BDD::meurt('__construct',$e); }
	}
	public function execute($s, $p)
	{
		try { return $s->execute($p); }
		catch (PDOException $e) { BDD::meurt('execute',$e); }
	}
	public function exec($p)
	{
		try { return parent::exec($p); }
		catch (PDOException $e) { BDD::meurt('exec',$e); }
	}
	public function query($p)
	{
		try { return parent::query($p); }
		catch (PDOException $e) { BDD::meurt('query',$e); }
	}
	private static function meurt($type, PDOException $e)
	{
		$signature = date('Y/m/d H:i:s').'	'.$_SERVER['REMOTE_ADDR'];
		file_put_contents(__DIR__.'/../logs/bdd.exception.txt',$signature.'	'.$type.'	'.$e->getMessage()."\r\n", FILE_APPEND);
		die('SQL Error loged : '.$signature);
	}
}
?>