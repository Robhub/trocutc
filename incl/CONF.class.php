<?php
class CONF
{
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