<?php

/**
 * OAuth signature implementation using HMAC-SHA1
 * 
 * @version $Id$
 * @author Marc Worrell <marc@mediamatic.nl>
 * @copyright (c) 2008 Mediamatic Lab
 * @date  Sep 8, 2008 12:21:19 PM
 */


require_once dirname(__FILE__).'/OAuthSignatureMethod.class.php';


class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod
{
	public function name ()
	{
		return 'HMAC-SHA1';
	}


	/**
	 * Calculate the signature using HMAC-SHA1
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
		$key = $request->urlencode($consumer_secret).'&'.$request->urlencode($token_secret);
		if (function_exists('hash_hmac'))
		{
			$signature = base64_encode(hash_hmac("sha1", $base_string, $key, true));
		}
		else
		{
		    $blocksize	= 64;
		    $hashfunc	= 'sha1';
		    if (strlen($key) > $blocksize)
		    {
		        $key = pack('H*', $hashfunc($key));
		    }
		    $key	= str_pad($key,$blocksize,chr(0x00));
		    $ipad	= str_repeat(chr(0x36),$blocksize);
		    $opad	= str_repeat(chr(0x5c),$blocksize);
		    $hmac 	= pack(
		                'H*',$hashfunc(
		                    ($key^$opad).pack(
		                        'H*',$hashfunc(
		                            ($key^$ipad).$base_string
		                        )
		                    )
		                )
		            );
			$signature = base64_encode($hmac);
		}
		return $request->urlencode($signature);
	}
}


/* vi:set ts=4 sts=4 sw=4 binary noeol: */

?>