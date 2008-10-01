<?php

/**
 * OAuth signature implementation using PLAINTEXT
 * 
 * @version $Id$
 * @author Marc Worrell <marc@mediamatic.nl>
 * @copyright (c) 2008 Mediamatic Lab
 * @date  Sep 8, 2008 12:00:14 PM
 */

class OAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod
{
	public function name() 
	{
		return 'RSA-SHA1';
	}
	

	/**
	 * Fetch the public CERT key for the signature
	 * 
	 * @param OAuthRequest request
	 * @return string public key
	 */
	protected function fetch_public_cert ( $request )
	{
		// not implemented yet, ideas are:
		// (1) do a lookup in a table of trusted certs keyed off of consumer
		// (2) fetch via http using a url provided by the requester
		// (3) some sort of specific discovery code based on request
		//
		// either way should return a string representation of the certificate
		throw OAuthException("OAuthSignatureMethod_RSA_SHA1::fetch_public_cert not implemented");
	}
	
	
	/**
	 * Fetch the private CERT key for the signature
	 * 
	 * @param OAuthRequest request
	 * @return string private key
	 */
	protected function fetch_private_cert ( $request )
	{
		// not implemented yet, ideas are:
		// (1) do a lookup in a table of trusted certs keyed off of consumer
		//
		// either way should return a string representation of the certificate
		throw OAuthException("OAuthSignatureMethod_RSA_SHA1::fetch_private_cert not implemented");
	}


	/**
	 * Calculate the signature using RSA-SHA1
	 * This function is copyright Andy Smith, 2008.
	 * 
	 * @param OAuthRequest request
	 * @param string base_string
	 * @param string consumer_secret
	 * @param string token_secret
	 * @return string  
	 */
	public function signature ( $request, $base_string, $consumer_secret, $token_secret )
	{
		// Fetch the private key cert based on the request
		$cert = $this->fetch_private_cert($request);
		
		// Pull the private key ID from the certificate
		$privatekeyid = openssl_get_privatekey($cert);
		
		// Sign using the key
		$sig = false;
		$ok  = openssl_sign($base_string, $sig, $privatekeyid);   
		
		// Release the key resource
		openssl_free_key($privatekeyid);
		  
		return $request->urlencode(base64_encode($sig));
	}
	

	/**
	 * Check if the request signature is the same as the one calculated for the request.
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
		$decoded_sig = base64_decode($request->urldecode($signature));
		  
		// Fetch the public key cert based on the request
		$cert = $this->fetch_public_cert($request);
		
		// Pull the public key ID from the certificate
		$publickeyid = openssl_get_publickey($cert);
		
		// Check the computed signature against the one passed in the query
		$ok = openssl_verify($base_string, $decoded_sig, $publickeyid);   
		
		// Release the key resource
		openssl_free_key($publickeyid);
		return $ok == 1;
	}

}

/* vi:set ts=4 sts=4 sw=4 binary noeol: */

?>