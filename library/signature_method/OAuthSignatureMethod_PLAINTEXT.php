<?php

/**
 * OAuth signature implementation using PLAINTEXT
 * 
 * @version $Id$
 * @author Marc Worrell <marc@mediamatic.nl>
 * @copyright (c) 2008 Mediamatic Lab
 * @date  Sep 8, 2008 12:09:43 PM
 */

require_once dirname(__FILE__).'/OAuthSignatureMethod.class.php';


class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod
{
	public function name ()
	{
		return 'PLAINTEXT';
	}


	/**
	 * Calculate the signature using PLAINTEXT
	 * 
	 * @param OAuthRequest request
	 * @param string base_string
	 * @param string consumer_secret
	 * @param string token_secret
	 * @return string  
	 */
	function signature ( $request, $base_string, $consumer_secret, $token_secret )
	{
		return $request->urlencode($request->urlencode($consumer_secret).'&'.$request->urlencode($token_secret));
	}
}

/* vi:set ts=4 sts=4 sw=4 binary noeol: */

?>