<?php

/**
 * oauth-php: Example OAuth server
 *
 * This file implements the OAuth server endpoints. The most basic
 * implementation of an OAuth server.
 *
 * Call with: /oauth/request_token, /oauth/authorize, /oauth/access_token
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

require_once '../core/init.php';

$server = new OAuthServer();

switch($_SERVER['PATH_INFO'])
{
case '/request_token':
	$server->requestToken();
	exit;

case '/access_token':
	$server->accessToken();
	exit;

case '/authorize':
	# logon

	assert_logged_in();

	try
	{
		$server->authorizeVerify();
		$server->authorizeFinish(true, 1);
	}
	catch (OAuthException $e)
	{
		header('HTTP/1.1 400 Bad Request');
		header('Content-Type: text/plain');
		
		echo "Failed OAuth Request: " . $e->getMessage();
	}
	exit;

	
default:
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-Type: text/plain');
	echo "Unknown request";
}

?>