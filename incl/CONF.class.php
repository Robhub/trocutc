<?php
class CONF
{
/*
	public static function path()
	{
		return dirname($_SERVER['SCRIPT_FILENAME']).'/';
	}
	public static function classpath()
	{
		return self::path().'incl';
	}
	*/
	public static function keygen($login)
	{
		return md5("trocutc-$login-salt");
	}
	public static function serviceurl()
	{
		return 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';
	}
	public static function optoutlink($login)
	{
		return self::serviceurl().'optout.php?key='.self::keygen($login);
	}
	public static function optinlink($login)
	{
		return self::serviceurl().'optin.php?key='.self::keygen($login);
	}
}
?>