<?php

/**
 * oauth-php: Example OAuth server
 *
 * Simple logon for consumer registration at this server.
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

if (isset($_POST['username']) && isset($_POST['password']))
{
	if ($_POST['username'] == USERNAME && $_POST['password'] == PASSWORD)
	{
		$_SESSION['authorized'] = true;
		if (!empty($_REQUEST['goto']))
		{
			header('Location: ' . $_REQUEST['goto']);
			die;
		}

		echo "Logon succesfull.";
		die;
	}
}

$smarty = session_smarty();
$smarty->display('logon.tpl');

?>