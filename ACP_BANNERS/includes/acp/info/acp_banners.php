<?php
/**
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class acp_banners_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_banners',
			'title'		=> 'ACP_BANNERS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'index'	=> array('title' => 'ACP_BANNERS_INDEX_TITLE', 'auth' => 'acl_a_', 'cat' => array('')),
		));
	}


	function install()
	{
	}

	function uninstall()
	{
	}
}

?>