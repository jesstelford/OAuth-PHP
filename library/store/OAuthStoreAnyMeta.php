<?php

/**
 * Storage container for the oauth credentials, both server and consumer side.
 * This file can only be used in conjunction with anyMeta.
 * 
 * @version $Id$
 * @author Marc Worrell <marc@mediamatic.nl>
 * @copyright (c) 2007 Mediamatic Lab
 * @date  Nov 16, 2007 4:03:30 PM
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

require_once dirname(__FILE__) . '/../../../../core/inc/any_database.inc.php';
require_once dirname(__FILE__) . '/OAuthStoreMySQL.php';


class OAuthStoreAnymeta extends OAuthStoreMySQL
{
	/**
	 * Construct the OAuthStoreAnymeta
	 * 
	 * @param array options
	 */
	function __construct ( $options = array() )
	{
		parent::__construct(array('conn' => any_db_conn()));
	}


	/**
	 * Add an entry to the log table
	 * 
	 * @param array keys (osr_consumer_key, ost_token, ocr_consumer_key, oct_token)
	 * @param string received
	 * @param string sent
	 * @param string base_string
	 * @param string notes
	 * @param int (optional) user_id
	 */
	public function addLog ( $keys, $received, $sent, $base_string, $notes, $user_id = null )
	{
		if (is_null($user_id) && isset($GLOBALS['any_auth']))
		{
			$user_id = $GLOBALS['any_auth']->getUserId();
		}
		parent::addLog($keys, $received, $sent, $base_string, $notes, $user_id);
	}
	
	
	/**
	 * Get a page of entries from the log.  Returns the last 100 records
	 * matching the options given.
	 * 
	 * @param array options
	 * @param int user_id	current user
	 * @return array log records
	 */
	public function listLog ( $options, $user_id )
	{
		$where = array();
		$args  = array();
		if (empty($options))
		{
			$where[] = 'olg_usa_id_ref = %d';
			$args[]  = $user_id;
		}
		else
		{
			foreach ($options as $option => $value)
			{
				if (strlen($value) > 0)
				{
					switch ($option)
					{
					case 'osr_consumer_key':
					case 'ocr_consumer_key':
					case 'ost_token':
					case 'oct_token':
						$where[] = 'olg_'.$option.' = \'%s\'';
						$args[]  = $value;	
						break;				
					}
				}
			}
			
			$where[] = '(olg_usa_id_ref IS NULL OR olg_usa_id_ref = %d)';
			$args[]  = $user_id;
		}

		$rs = any_db_query_all_assoc('
					SELECT olg_id,
							olg_osr_consumer_key 	AS osr_consumer_key,
							olg_ost_token			AS ost_token,
							olg_ocr_consumer_key	AS ocr_consumer_key,
							olg_oct_token			AS oct_token,
							olg_usa_id_ref			AS user_id,
							olg_received			AS received,
							olg_sent				AS sent,
							olg_base_string			AS base_string,
							olg_notes				AS notes,
							olg_timestamp			AS timestamp,
							INET_NTOA(olg_remote_ip) AS remote_ip
					FROM oauth_log
					WHERE '.implode(' AND ', $where).'
					ORDER BY olg_id DESC
					LIMIT 0,100', $args);

		return $rs;
	}



	/**
	 * Initialise the database
	 */
	public function install ()
	{
		parent::install();

		any_db_alter_add_fk('oauth_consumer_registry', 'ocr_usa_id_ref', 'any_user_auth(usa_id_ref)', 'on update cascade on delete set null');
		any_db_alter_add_fk('oauth_consumer_token',    'oct_usa_id_ref', 'any_user_auth(usa_id_ref)', 'on update cascade on delete set cascade');
		any_db_alter_add_fk('oauth_server_registry',   'osr_usa_id_ref', 'any_user_auth(usa_id_ref)', 'on update cascade on delete set null');
		any_db_alter_add_fk('oauth_server_token',      'ost_usa_id_ref', 'any_user_auth(usa_id_ref)', 'on update cascade on delete set cascade');
		any_db_alter_add_fk('oauth_log',               'olg_usa_id_ref', 'any_user_auth(usa_id_ref)', 'on update cascade on delete set cascade');
	}

	
	
	/** Some simple helper functions for querying the mysql db **/

	/**
	 * Perform a query, ignore the results
	 * 
	 * @param string sql
	 * @param vararg arguments (for sprintf)
	 */
	protected function query ( $sql )
	{
		list($sql, $args) = $this->sql_args(func_get_args());
		any_db_query($sql, $args);
	}
	

	/**
	 * Perform a query, ignore the results
	 * 
	 * @param string sql
	 * @param vararg arguments (for sprintf)
	 * @return array
	 */
	protected function query_all_assoc ( $sql )
	{
		list($sql, $args) = $this->sql_args(func_get_args());
		return any_db_query_all_assoc($sql, $args);
	}
	
	
	/**
	 * Perform a query, return the first row
	 * 
	 * @param string sql
	 * @param vararg arguments (for sprintf)
	 * @return array
	 */
	protected function query_row_assoc ( $sql )
	{
		list($sql, $args) = $this->sql_args(func_get_args());
		return any_db_query_row_assoc($sql, $args);
	}

	
	/**
	 * Perform a query, return the first row
	 * 
	 * @param string sql
	 * @param vararg arguments (for sprintf)
	 * @return array
	 */
	protected function query_row ( $sql )
	{
		list($sql, $args) = $this->sql_args(func_get_args());
		return any_db_query_row($sql, $args);
	}
	
		
	/**
	 * Perform a query, return the first column of the first row
	 * 
	 * @param string sql
	 * @param vararg arguments (for sprintf)
	 * @return mixed
	 */
	protected function query_one ( $sql )
	{
		list($sql, $args) = $this->sql_args(func_get_args());
		return any_db_query_one($sql, $args);
	}
	
	
	/**
	 * Return the number of rows affected in the last query
	 * 
	 * @return int
	 */
	protected function query_affected_rows ()
	{
		return any_db_affected_rows();
	}


	/**
	 * Return the id of the last inserted row
	 * 
	 * @return int
	 */
	protected function query_insert_id ()
	{
		return any_db_insert_id();
	}
	
	
	private function sql_args ( $args )
	{
		$sql = array_shift($args);
		if (count($args) == 1 && is_array($args[0]))
		{
			$args = $args[0];
		}
		return array($sql, $args);
	}

}


/* vi:set ts=4 sts=4 sw=4 binary noeol: */

?>