<?php

/**
 * OAuth signature implementation using MD5
 * 
 * @version $Id$
 * @author Marc Worrell <marc@mediamatic.nl>
 * @copyright (c) 2008 Mediamatic Lab
 * @date  Sep 8, 2008 12:09:43 PM
 */

require_once dirname(__FILE__).'/OAuthSignatureMethod.class.php';


class OAuthSignatureMethod_MD5 extends OAuthSignatureMethod
{
	public function name ()
	{
		return 'MD5';
	}


	/**
	 * Calculate the signature using MD5
	 * Binary md5 digest, as distinct from PHP's built-in hexdigest.
	 * This function is copyright Andy Smith, 2007.
	 * 
	 * @param OAuthRequest request
	 * @param string base_string
	 * @param string consumer_secret
	 * @param string token_secret
	 * @return string  
	 */
	function signature ( $request, $base_string, $consumer_secret, $token_secret )
	{
		$s  .= '&'.$request->urlencode($consumer_secret).'&'.$request->urlencode($token_secret);
		$md5 = md5($base_string);
		$bin = '';
		
		for ($i = 0; $i < strlen($md5); $i += 2)
		{
		    $bin .= chr(hexdec($md5{$i+1}) + hexdec($md5{$i}) * 16);
		}
		return $request->urlencode(base64_encode($bin));
	}
	
}

/* vi:set ts=4 sts=4 sw=4 binary noeol: */

?>