<?php
class CONF
{
	public static function serviceurl()
	{
		return 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';
	}
}
?>