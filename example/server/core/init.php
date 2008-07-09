<?php

/**
 * oauth-php: Example OAuth server
 *
 * Global initialization file for the server, defines some helper
 * functions, required includes, and starts the session.
 *
 * @author Arjan Scherpenisse <arjan@scherpenisse.net>
 * @copyright 2008, Mediamatic Lab
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/*
 * Simple 'user management'
 */
define ('USERNAME', 'sysadmin');
define ('PASSWORD', 'sysadmin');


/*
 * Always announce XRDS OAuth discovery
 */
header('X-XRDS-Location: http://' . $_SERVER['SERVER_NAME'] . '/services.xrds');


/*
 * Initialize the database connection
 */
$info = parse_url(getenv('DB_DSN'));
($GLOBALS['db_conn'] = mysql_connect($info['host'], $info['user'], $info['pass'])) || die(mysql_error());
mysql_select_db(basename($info['path']), $GLOBALS['db_conn']) || die(mysql_error());
unset($info);


require_once '../../../library/OAuthServer.php';

/*
 * Initialize OAuth store
 */
require_once '../../../library/OAuthStore.php';
OAuthStore::instance('MySQL', array('conn' => $GLOBALS['db_conn']));


/*
 * Session
 */
session_start();


/*
 * Template handling
 */
require_once 'smarty/libs/Smarty.class.php';
function session_smarty()
{
	if (!isset($GLOBALS['smarty']))
	{
		$GLOBALS['smarty'] = new Smarty;
		$GLOBALS['smarty']->template_dir = dirname(__FILE__) . '/templates/';
		$GLOBALS['smarty']->compile_dir = dirname(__FILE__) . '/../cache/templates_c';
	}
	
	return $GLOBALS['smarty'];
}

function assert_logged_in()
{
	if (empty($_SESSION['authorized']))
	{
		$uri = $_SERVER['REQUEST_URI'];
		header('Location: /logon?goto=' . urlencode($uri));
	}
}

function assert_request_vars()
{
	foreach(func_get_args() as $a)
	{
		if (!isset($_REQUEST[$a]))
		{
			header('HTTP/1.1 400 Bad Request');
			echo 'Bad request.';
			exit;
		}
	}
}

function assert_request_vars_all()
{
	foreach($_REQUEST as $row)
	{
		foreach(func_get_args() as $a)
		{
			if (!isset($row[$a]))
			{
				header('HTTP/1.1 400 Bad Request');
				echo 'Bad request.';
				exit;
			}
		}
	}
}

?>