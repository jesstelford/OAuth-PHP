<?php

/**
 * Create the body for a multipart/form-data message.
 * 
 * @version $Id$
 * @author Marc Worrell <marc@mediamatic.nl>
 * @copyright (c) 2008 Mediamatic Lab
 * @date  Jan 31, 2008 12:50:05 PM
 */


class OAuthMultipartFormdata
{
    /**
     * Builds the request string.
     * 
     * The files array can be a combination of the following (either data or file):
     * 
     * file => "path/to/file", filename=, mime=, data=
     *
     * @param array params		(name => value) (all names and values should be urlencoded)
     * @param array files		(name => filedesc) (not urlencoded)
     * @return array (headers, body)
     */
    static function encodeBody ( $params, $files )
    {
    	$headers  	= array();
		$body		= '';
		$boundary	= 'OAuthRequester_'.md5(uniqid('multipart') . microtime());
		$headers['Content-Type'] = 'multipart/form-data; boundary=' . $boundary;


		// 1. Add the parameters to the post
		if (!empty($params))
		{
			foreach ($params as $name => $value)
			{
				$body .= '--'.$boundary."\r\n";
				$body .= 'Content-Disposition: form-data; name="'.$name.'"';
				$body .= "\r\n\r\n";
				$body .= urldecode($value);
				$body .= "\r\n";
			}
		}
		
		// 2. Add all the files to the post
		if (!empty($files))
		{
			$untitled = 1;
			
			foreach ($files as $name => $f)
			{
				$data     = false;
				$filename = false;

				if (isset($f['filename']))
				{
					$filename = $f['filename'];
				}

				if (!empty($f['file']))
				{
					$data = @file_get_contents($f['file']);
					if ($data === false)
					{
						throw new OAuthException(sprintf('Could not read the file "%s" for form-data part', $f['file']));
					}
					if (empty($filename))
					{
						$filename = basename($f['file']);
					}
				}
				else if (isset($f['data']))
				{
					$data = $f['data'];
				}
				
				// When there is data, add it as a form-data part, otherwise silently skip the upload
				if ($data !== false)
				{
					if (empty($filename))
					{
						$filename = sprintf('untitled-%d', $untitled++);
					}
					$mime  = !empty($f['mime']) ? $f['mime'] : 'application/octet-stream';
					$body .= '--'.$boundary."\r\n";
					$body .= 'Content-Disposition: form-data; name="'.rawurlencode($name).'"; filename="'.rawurlencode($filename).'"'."\r\n";
					$body .= 'Content-Type: '.$mime;
					$body .= "\r\n\r\n";
					$body .= $data;
					$body .= "\r\n";
				}
				
			}
		}
		$body .= '--'.$boundary."--\r\n";

		$headers['Content-Length'] = strlen($body);
		return array($headers, $body);
	}
}


/* vi:set ts=4 sts=4 sw=4 binary noeol: */


?>