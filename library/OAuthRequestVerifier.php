<?php

/**
 * Verify the current request.  Checks if signed and if the signature is correct.
 * When correct then also figures out on behalf of which user this request is being made.
 *  
 * @version $Id$
 * @author Marc Worrell <marcw@pobox.com>
 * @date  Nov 16, 2007 4:35:03 PM
 * 
 * 
 * The MIT License
 * 
 * Copyright (c) 2007-2008 Mediamatic Lab
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once dirname(__FILE__) . '/OAuthStore.php';
require_once dirname(__FILE__) . '/OAuthRequest.php';


class OAuthRequestVerifier extends OAuthRequest
{
	private $request;
	private $store;
	
	/**
	 * Construct the request to be verified
	 * 
	 * @param string request
	 * @param string method
	 * @param string postdata
	 */
	function __construct ()
	{
		$this->store = OAuthStore::instance();
		parent::__construct();
		
		OAuthRequestLogger::start($this);
	}
	
	
	/**
	 * See if the current request is signed with OAuth
	 * 
	 * @return boolean
	 */
	static public function requestIsSigned ()
	{
		if (isset($_REQUEST['oauth_signature']))
		{
			$signed = true;
		}
		else
		{
			$hs = getallheaders();
			if (isset($hs['Authorization']) && strpos($hs['Authorization'], 'oauth_signature') !== false)
			{
				$signed = true;
			}
			else
			{
				$signed = false;
			}
		}
		return $signed;
	}


	/**
	 * Verify the request if it seemed to be signed.
	 * 
	 * @param string token_type the kind of token needed, defaults to 'access'
	 * @exception OAuthException thrown when the request did not verify
	 * @return boolean	true when signed, false when not signed
	 */
	public function verifyIfSigned ( $token_type = 'access' )
	{
		if ($this->getParam('oauth_consumer_key'))
		{
			OAuthRequestLogger::start($this);
			$this->verify($token_type);
			$signed = true;
			OAuthRequestLogger::flush();
		}
		else
		{
			$signed = false;
		}
		return $signed;
	}

	
	/**
	 * Verify the request
	 * 
	 * @param string token_type the kind of token needed, defaults to 'access' (false, 'access', 'request')
	 * @exception OAuthException thrown when the request did not verify
	 * @return int user_id associated with token (false when no user associated)
	 */
	public function verify ( $token_type = 'access' )
	{
		$consumer_key = $this->getParam('oauth_consumer_key');
		$token        = $this->getParam('oauth_token');
		$user_id      = false;

		if ($consumer_key && ($token_type === false || $token))
		{
			$secrets = $this->store->getSecretsForVerify(	$this->urldecode($consumer_key), 
															$this->urldecode($token), 
															$token_type);

			$this->store->checkServerNonce(	$this->urldecode($consumer_key),
											$this->urldecode($token),
											$this->getParam('oauth_timestamp', true),
											$this->getParam('oauth_nonce', true));

			$signature = $this->calculateSignature($secrets['consumer_secret'], $secrets['token_secret'], $token_type);
			$oauth_sig = $this->getParam('oauth_signature');

			if (	empty($oauth_sig) 
				||	!$this->verifySignature(	$this->getParam('oauth_signature_method'), 
												$oauth_sig,
												$signature))
			{
				throw new OAuthException('Verification of signature failed (signature base string was "'.$this->signatureBaseString().'").');
			}
			
			// Check the optional body signature
			if ($this->getParam('xoauth_body_signature'))
			{
				$method = $this->getParam('xoauth_body_signature_method');
				if (empty($method))
				{
					$method = $this->getParam('oauth_signature_method');
				}
				$body_signature = $this->calculateDataSignature($this->getBody(), $secrets['consumer_secret'], $secrets['token_secret'], $method);
				if (!$this->verifySignature(	$method, 
												$this->getParam('xoauth_body_signature'),
												$body_signature))
				{
					throw new OAuthException('Verification of body signature failed');
				}
			}
			
			// All ok - fetch the user associated with this request
			if (isset($secrets['user_id']))
			{
				$user_id = $secrets['user_id'];
			}
		}
		else
		{
			throw new OAuthException('Can\'t verify request, missing oauth_consumer_key or oauth_token');
		}
		return $user_id;
	}

	
	/**
	 * Verify if the two signatures are equal.
	 * 
	 * @param string method
	 * @param string sigA
	 * @param string sigB
	 * @return boolean		true when equal
	 */
	public function verifySignature ( $method, $sigA, $sigB )
	{
		$a = $this->urldecode($sigA);
		$b = $this->urldecode($sigB);

		switch (strtoupper($method))
		{
		case 'PLAINTEXT':
			$equal = ($this->urldecode($a) == $this->urldecode($b));
			break;

		case 'MD5':
		case 'HMAC-SHA1':
		case 'HMAC_SHA1':
			// We have to compare the decoded values
			$valA  = base64_decode($a);
			$valB  = base64_decode($b);
			// Crude binary comparison
			$equal = (rawurlencode($a) == rawurlencode($b));
			break;
		
		default:
			$equal = ($sigA == $sigB);
			break;
		}
		return $equal;
	}
}


/* vi:set ts=4 sts=4 sw=4 binary noeol: */

?>