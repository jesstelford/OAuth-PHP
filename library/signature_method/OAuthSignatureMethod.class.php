<?php

/**
 * Interface for OAuth signature methods
 * 
 * @version $Id$
 * @author Marc Worrell <marc@mediamatic.nl>
 * @copyright (c) 2008 Mediamatic Lab
 * @date  Sep 8, 2008 12:04:35 PM
 */

abstract class OAuthSignatureMethod
{
	/**
	 * Return the name of this signature
	 * 
	 * @return string
	 */
	abstract public function name();
	
	/**
	 * Return the signature for the given request
	 * 
	 * @param OAuthRequest request
	 * @param string base_string
	 * @param string consumer_secret
	 * @param string token_secret
	 * @return string  
	 */
	abstract public function signature ( $request, $base_string, $consumer_secret, $token_secret );

	/**
	 * Check if the request signature corresponds to the one calculated for the request.
	 * 
	 * @param OAuthRequest request
	 * @param string base_string
	 * @param string consumer_secret
	 * @param string token_secret
	 * @param string signature
	 * @return string
	 */
	public function verify ( $request, $base_string, $consumer_secret, $token_secret, $signature )
	{
		return $this->signature() == $signature;
	}
}


/* vi:set ts=4 sts=4 sw=4 binary noeol: */

?>