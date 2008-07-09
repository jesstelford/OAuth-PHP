<?php

/**
 * oauth-php: Example OAuth server
 *
 * XRDS discovery for OAuth. This file helps the consumer program to
 * discover where the OAuth endpoints for this server are.
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

header('Content-Type: application/xrds+xml');

$server = $_SERVER['SERVER_NAME'];

echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";

?>
<XRDS xmlns="xri://$xrds">
    <XRD xmlns:simple="http://xrds-simple.net/core/1.0" xmlns="xri://$XRD*($v*2.0)" xmlns:openid="http://openid.net/xmlns/1.0" version="2.0" xml:id="main">
	<Type>xri://$xrds*simple</Type>
	<Service>
	    <Type>http://oauth.net/discovery/1.0</Type>
	    <URI>#main</URI>
	</Service>
	<Service>
	    <Type>http://oauth.net/core/1.0/endpoint/request</Type>
	    <Type>http://oauth.net/core/1.0/parameters/auth-header</Type>
	    <Type>http://oauth.net/core/1.0/parameters/uri-query</Type>
	    <Type>http://oauth.net/core/1.0/signature/HMAC-SHA1</Type>
	    <Type>http://oauth.net/core/1.0/signature/PLAINTEXT</Type>
	    <URI>http://<?=$server?>/oauth/request_token</URI>
	</Service>
	<Service>
	    <Type>http://oauth.net/core/1.0/endpoint/authorize</Type>
	    <Type>http://oauth.net/core/1.0/parameters/uri-query</Type>
	    <URI>http://<?=$server?>/oauth/authorize</URI>
	</Service>
	<Service>
	    <Type>http://oauth.net/core/1.0/endpoint/access</Type>
	    <Type>http://oauth.net/core/1.0/parameters/auth-header</Type>
	    <Type>http://oauth.net/core/1.0/parameters/uri-query</Type>
	    <Type>http://oauth.net/core/1.0/signature/HMAC-SHA1</Type>
	    <Type>http://oauth.net/core/1.0/signature/PLAINTEXT</Type>
	    <URI>http://<?=$server?>/oauth/access_token</URI>
	</Service>
    </XRD>
</XRDS>
