<?php
/**
 * @author Nathan Guse (EXreaction) http://lithiumstudios.org
 * @author David Lewis (Highway of Life) highwayoflife@gmail.com
 * @package phpBB3 UMIL - Unified MOD Install Library
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (!class_exists('umil'))
{

/**
* Multicall instructions
*
* With the "multicall" (as I am calling it) you can make a single function call and have it repeat the actions multiple times on information sent from an array.
*
* To do this (it does not work on the _exists functions), all you must do is send the first variable in the function call as an array and for each item, send an array for each of the variables in order.
*
* Example:
* $umil->config_add(array(
*	array('config_name', 'config_value', false),
*	array('config_name1', 'config_value1', false),
*	array('config_name2', 'config_value2', false),
*	array('config_name3', 'config_value3', false),
* );
*/

/**
* UMIL - Unified MOD Installation File class
*
* Cache Functions
*	cache_purge($type = '', $style_id = 0)
*
* Config Functions:
*	config_exists($config_name, $return_result = false)
*	config_add($config_name, $config_value, $is_dynamic = false)
*	config_update($config_name, $config_value, $is_dynamic = false)
*	config_remove($config_name)
*
* Module Functions
*	module_exists($class, $parent, $module)
*	module_add($class, $parent, $module)
*	module_remove($class, $parent, $module)
*
* Permissions/Auth Functions
*	permission_exists($auth_option, $global = true)
*	permission_add($auth_option, $global = true)
*	permission_remove($auth_option, $global = true)
*
* Table Functions
*	table_exists($table_name)
*	table_add($table_name, $table_data)
*	table_remove($table_name)
*
* Table Column Functions
*	table_column_exists($table_name, $column_name)
*	table_column_add($table_name, $column_name, $column_data)
*	table_column_update($table_name, $column_name, $column_data)
*	table_column_remove($table_name, $column_name)
*
* Table Key/Index Functions
*	table_index_exists($table_name, $index_name)
*	table_index_add($table_name, $index_name, $column)
*	table_index_remove($table_name, $index_name)
*
*/
class umil
{
	/**
	* This will hold the text output for the inputted command (if the mod author would like to display the command that was ran)
	*
	* @var string
	*/
	var $command = '';

	/**
	* This will hold the text output for the result of the command.  $user->lang['SUCCESS'] if everything worked.
	*
	* @var string
	*/
	var $result = '';

	/**
	* Auto run $this->display_results after running a command
	*/
	var $auto_display_results = false;

	/**
	* Stand Alone option (this makes it possible to just use the single umil file and not worry about any language stuff
	*/
	var $stand_alone = false;

	/**
	* Constructor
	*/
	function umil($stand_alone = false)
	{
		global $config, $user, $phpbb_root_path, $phpEx;

		$this->stand_alone = $stand_alone;
		if (!$stand_alone)
		{
			/* Does not have the fall back option to use en/ if the user's language file does not exist, so we will not use it...unless that is changed.
			if (method_exists('user', 'set_custom_lang_path'))
			{
				$user->set_custom_lang_path($phpbb_root_path . 'umil/language/');
				$user->add_lang('umil');
				$user->set_custom_lang_path($phpbb_root_path . 'language/');
			}
			else
			{*/
				// Include the umil language file.  First we check if the language file for the user's language is available, if not we check if the board's default language is available, if not we use the english file.
				$path = './../../umil/language/';
				if (isset($user->data['user_lang']) && file_exists("{$phpbb_root_path}umil/language/{$user->data['user_lang']}/umil.$phpEx"))
				{
					$path .= $user->data['user_lang'];
				}
				else if (file_exists("{$phpbb_root_path}umil/language/" . basename($config['default_lang']) . "/umil.$phpEx"))
				{
					$path .= basename($config['default_lang']);
				}
				else if (file_exists("{$phpbb_root_path}umil/language/en/umil.$phpEx"))
				{
					$path .= 'en';
				}
				$user->add_lang($path . '/umil');
			//}
		}
	}

	/**
	* umil_start
	*
	* A function which runs (almost) every time a function here is ran
	*/
	function umil_start()
	{
		global $db, $user;

		// Set up the command.  This will get the arguments sent to the function.
		$this->command = '';
		$args = func_get_args();
		if (sizeof($args))
		{
			$lang_key = array_shift($args);

			if (sizeof($args))
			{
				$this->command = @vsprintf(((isset($user->lang[$lang_key])) ? $user->lang[$lang_key] : $lang_key), $args);
			}
			else
			{
				$this->command = ((isset($user->lang[$lang_key])) ? $user->lang[$lang_key] : $lang_key);
			}
		}

		$this->result('SUCCESS');
		$db->sql_return_on_error(true);

		//$db->sql_transaction('begin');
	}

	/**
	* result function
	*
	* This makes it easy to manage the stand alone version.
	*/
	function result()
	{
		global $user;

		// Set up the command.  This will get the arguments sent to the function.
		$args = func_get_args();
		if (sizeof($args))
		{
			$lang_key = array_shift($args);

			if (sizeof($args))
			{
				$this->result = @vsprintf(((isset($user->lang[$lang_key])) ? $user->lang[$lang_key] : $lang_key), $args);
			}
			else
			{
				$this->result = ((isset($user->lang[$lang_key])) ? $user->lang[$lang_key] : $lang_key);
			}
		}
	}

	/**
	* umil_end
	*
	* A function which runs (almost) every time a function here is ran
	*/
	function umil_end()
	{
		global $db, $user;

		if ($db->sql_error_triggered)
		{
			if ($this->result == ((isset($user->lang['SUCCESS'])) ? $user->lang['SUCCESS'] : 'SUCCESS'))
			{
				$this->result = 'SQL ERROR ' . $db->sql_error_returned['message'];
			}
			else
			{
				$this->result .= '<br /><br />SQL ERROR ' . $db->sql_error_returned['message'];
			}
		}
		else
		{
			//$db->sql_transaction('commit');
		}

		$db->sql_return_on_error(false);

		// Auto output if requested.
		if ($this->auto_display_results && method_exists($this, 'display_results'))
		{
			$this->display_results();
		}

		return '<strong>' . $this->command . '</strong><br />' . $this->result;
	}

	/**
	* Run Actions
	*
	* Do-It-All function that can do everything required for installing/updating/uninstalling a mod based on an array of actions and the versions.
	*
	* @param string $action The action. install|update|uninstall
	* @param array $versions The array of versions and the actions for each
	* @param string $current_version The current version to install/update to
	* @param string|bool $db_version The current version installed to update to/remove from
	*/
	function run_actions($action, $versions, $current_version, $version_config_name, $version_select = '')
	{
		$db_version = '';
		if ($this->config_exists($version_config_name))
		{
			global $config;
			$db_version = $config[$version_config_name];
		}

		if ($action == 'install' || ($action == 'update' && $db_version))
		{
			$version_installed = $db_version;
			foreach ($versions as $version => $version_actions)
			{
				// If we are updating
				if ($db_version && version_compare($version, $db_version, '<='))
				{
					continue;
				}

				if ($version_select && version_compare($version, $version_select, '>'))
				{
					break;
				}

				foreach ($version_actions as $method => $params)
				{
					if ($method == 'custom')
					{
						if (function_exists($params))
						{
							call_user_func($params, $action, $version);
						}
					}
					else
					{
						if (method_exists($this, $method))
						{
							call_user_func(array($this, $method), $params);
						}
					}
				}

				$version_installed = $version;
			}

			// update the version number or add it
			if ($this->config_exists($version_config_name))
			{
				$this->config_update($version_config_name, $version_installed);
			}
			else
			{
				$this->config_add($version_config_name, $version_installed);
			}
		}
		else if ($action == 'uninstall' && $db_version)
		{
			// reverse version list
			$versions = array_reverse($versions);

			foreach ($versions as $version => $version_actions)
			{
				// Uninstalling and this listed version is newer than installed
				if (version_compare($version, $db_version, '>'))
				{
					continue;
				}

				// Version selection stuff
				if ($version_select && version_compare($version, $version_select, '<='))
				{
					// update the version number
					$this->config_update($version_config_name, $version);
					break;
				}

				$version_actions = array_reverse($version_actions);
				foreach ($version_actions as $method => $params)
				{
					if ($method == 'custom')
					{
						if (function_exists($params))
						{
							call_user_func($params, $action, $version);
						}
					}
					else
					{
						// update mode (reversing an action) isn't possible for uninstallations
						if (strpos($method, 'update'))
						{
							continue;
						}

						// reverse function call
						$method = str_replace(array('add', 'remove', 'temp'), array('temp', 'add', 'remove'), $method);

						if (method_exists($this, $method))
						{
							call_user_func(array($this, $method), ((is_array($params) ? array_reverse($params) : $params)));
						}
					}
				}
			}

			if (!$version_select)
			{
				// Unset the version number
				$this->config_remove($version_config_name);
			}
		}
	}

	/**
	* Cache Purge
	*
	* @param string $type The type of cache you want purged.  Available types: auth, imageset, template, theme.  Anything else sent will purge the forum's cache.
	* @param int $style_id The id of the item you want purged (if the type selected is imageset/template/theme, 0 for all items in that section)
	*/
	function cache_purge($type = '', $style_id = 0)
	{
		global $auth, $cache, $db, $user, $phpbb_root_path, $phpEx;

		// Multicall
		if (is_array($type))
		{
			if (!empty($type)) // Allow an empty array sent for the cache purge.
			{
				foreach ($type as $params)
				{
					call_user_func_array(array($this, 'cache_purge'), $params);
				}
				return;
			}
		}

		$style_id = (int) $style_id;

		switch ($type)
		{
			case 'auth' :
				$this->umil_start('AUTH_CACHE_PURGE');
				$cache->destroy('_acl_options');
				$auth->acl_clear_prefetch();

				return $this->umil_end();
			break;

			case 'imageset' :
				if ($style_id == 0)
				{
					$return = array();
					$sql = 'SELECT imageset_id
						FROM ' . STYLES_IMAGESET_TABLE;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$return[] = $this->cache_purge('imageset', $row['imageset_id']);
					}
					$db->sql_freeresult($result);

					return implode('<br /><br />', $return);
				}
				else
				{
					$sql = 'SELECT *
						FROM ' . STYLES_IMAGESET_TABLE . "
						WHERE imageset_id = $style_id";
					$result = $db->sql_query($sql);
					$imageset_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$imageset_row)
					{
						$this->umil_start('IMAGESET_CACHE_PURGE', 'UNKNOWN');
						$this->result('FAIL');
						return $this->umil_end();
					}

					$this->umil_start('IMAGESET_CACHE_PURGE', $imageset_row['imageset_name']);

					// The following is from includes/acp/acp_styles.php (edited)
					$sql_ary = array();

					$cfg_data_imageset = parse_cfg_file("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/imageset.cfg");

					$sql = 'DELETE FROM ' . STYLES_IMAGESET_DATA_TABLE . '
						WHERE imageset_id = ' . $style_id;
					$result = $db->sql_query($sql);

					foreach ($cfg_data_imageset as $image_name => $value)
					{
						if (strpos($value, '*') !== false)
						{
							if (substr($value, -1, 1) === '*')
							{
								list($image_filename, $image_height) = explode('*', $value);
								$image_width = 0;
							}
							else
							{
								list($image_filename, $image_height, $image_width) = explode('*', $value);
							}
						}
						else
						{
							$image_filename = $value;
							$image_height = $image_width = 0;
						}

						if (strpos($image_name, 'img_') === 0 && $image_filename)
						{
							$image_name = substr($image_name, 4);

							$sql_ary[] = array(
								'image_name'		=> (string) $image_name,
								'image_filename'	=> (string) $image_filename,
								'image_height'		=> (int) $image_height,
								'image_width'		=> (int) $image_width,
								'imageset_id'		=> (int) $style_id,
								'image_lang'		=> '',
							);
						}
					}

					$sql = 'SELECT lang_dir
						FROM ' . LANG_TABLE;
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						if (@file_exists("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/{$row['lang_dir']}/imageset.cfg"))
						{
							$cfg_data_imageset_data = parse_cfg_file("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/{$row['lang_dir']}/imageset.cfg");
							foreach ($cfg_data_imageset_data as $image_name => $value)
							{
								if (strpos($value, '*') !== false)
								{
									if (substr($value, -1, 1) === '*')
									{
										list($image_filename, $image_height) = explode('*', $value);
										$image_width = 0;
									}
									else
									{
										list($image_filename, $image_height, $image_width) = explode('*', $value);
									}
								}
								else
								{
									$image_filename = $value;
									$image_height = $image_width = 0;
								}

								if (strpos($image_name, 'img_') === 0 && $image_filename)
								{
									$image_name = substr($image_name, 4);
									$sql_ary[] = array(
										'image_name'		=> (string) $image_name,
										'image_filename'	=> (string) $image_filename,
										'image_height'		=> (int) $image_height,
										'image_width'		=> (int) $image_width,
										'imageset_id'		=> (int) $style_id,
										'image_lang'		=> (string) $row['lang_dir'],
									);
								}
							}
						}
					}
					$db->sql_freeresult($result);

					$db->sql_multi_insert(STYLES_IMAGESET_DATA_TABLE, $sql_ary);

					$cache->destroy('sql', STYLES_IMAGESET_DATA_TABLE);

					return $this->umil_end();
				}
			break;
			//case 'imageset' :

			case 'template' :
				if ($style_id == 0)
				{
					$return = array();
					$sql = 'SELECT template_id
						FROM ' . STYLES_TEMPLATE_TABLE;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$return[] = $this->cache_purge('template', $row['template_id']);
					}
					$db->sql_freeresult($result);

					return implode('<br /><br />', $return);
				}
				else
				{
					$sql = 'SELECT *
						FROM ' . STYLES_TEMPLATE_TABLE . "
						WHERE template_id = $style_id";
					$result = $db->sql_query($sql);
					$template_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$template_row)
					{
						$this->umil_start('TEMPLATE_CACHE_PURGE', 'UNKNOWN');
						$this->result('FAIL');
						return $this->umil_end();
					}

					$this->umil_start('TEMPLATE_CACHE_PURGE', $template_row['template_name']);

					// The following is from includes/acp/acp_styles.php
					if ($template_row['template_storedb'] && file_exists("{$phpbb_root_path}styles/{$template_row['template_path']}/template/"))
					{
						$filelist = array('' => array());

						$sql = 'SELECT template_filename, template_mtime
							FROM ' . STYLES_TEMPLATE_DATA_TABLE . "
							WHERE template_id = $style_id";
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
//							if (@filemtime("{$phpbb_root_path}styles/{$template_row['template_path']}/template/" . $row['template_filename']) > $row['template_mtime'])
//							{
								// get folder info from the filename
								if (($slash_pos = strrpos($row['template_filename'], '/')) === false)
								{
									$filelist[''][] = $row['template_filename'];
								}
								else
								{
									$filelist[substr($row['template_filename'], 0, $slash_pos + 1)][] = substr($row['template_filename'], $slash_pos + 1, strlen($row['template_filename']) - $slash_pos - 1);
								}
//							}
						}
						$db->sql_freeresult($result);

						$includes = array();
						foreach ($filelist as $pathfile => $file_ary)
						{
							foreach ($file_ary as $file)
							{
								if (!($fp = @fopen("{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file", 'r')))
								{
									$this->result('FAIL');
									return $this->umil_end();
								}
								$template_data = fread($fp, filesize("{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file"));
								fclose($fp);

								if (preg_match_all('#<!-- INCLUDE (.*?\.html) -->#is', $template_data, $matches))
								{
									foreach ($matches[1] as $match)
									{
										$includes[trim($match)][] = $file;
									}
								}
							}
						}

						foreach ($filelist as $pathfile => $file_ary)
						{
							foreach ($file_ary as $file)
							{
								// Skip index.
								if (strpos($file, 'index.') === 0)
								{
									continue;
								}

								// We could do this using extended inserts ... but that could be one
								// heck of a lot of data ...
								$sql_ary = array(
									'template_id'			=> (int) $style_id,
									'template_filename'		=> "$pathfile$file",
									'template_included'		=> (isset($includes[$file])) ? implode(':', $includes[$file]) . ':' : '',
									'template_mtime'		=> (int) filemtime("{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file"),
									'template_data'			=> (string) file_get_contents("{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file"),
								);

								$sql = 'UPDATE ' . STYLES_TEMPLATE_DATA_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
									WHERE template_id = $style_id
										AND template_filename = '" . $db->sql_escape("$pathfile$file") . "'";
								$db->sql_query($sql);
							}
						}
						unset($filelist);
					}

					return $this->umil_end();
				}
			break;
			//case 'template' :

			case 'theme' :
				if ($style_id == 0)
				{
					$return = array();
					$sql = 'SELECT theme_id
						FROM ' . STYLES_THEME_TABLE;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$return[] = $this->cache_purge('theme', $row['theme_id']);
					}
					$db->sql_freeresult($result);

					return implode('<br /><br />', $return);
				}
				else
				{
					$sql = 'SELECT *
						FROM ' . STYLES_THEME_TABLE . "
						WHERE theme_id = $style_id";
					$result = $db->sql_query($sql);
					$theme_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$theme_row)
					{
						$this->umil_start('THEME_CACHE_PURGE', 'UNKNOWN');
						$this->result('FAIL');
						return $this->umil_end();
					}

					$this->umil_start('THEME_CACHE_PURGE', $theme_row['theme_name']);

					// The following is from includes/acp/acp_styles.php
					if ($theme_row['theme_storedb'] && file_exists("{$phpbb_root_path}styles/{$theme_row['theme_path']}/theme/stylesheet.css"))
					{
						$stylesheet = file_get_contents($phpbb_root_path . 'styles/' . $theme_row['theme_path'] . '/theme/stylesheet.css');

						// Match CSS imports
						$matches = array();
						preg_match_all('/@import url\(["\'](.*)["\']\);/i', $stylesheet, $matches);

						if (sizeof($matches))
						{
							foreach ($matches[0] as $idx => $match)
							{
								$content = trim(file_get_contents("{$phpbb_root_path}styles/{$theme_row['theme_path']}/theme/{$matches[1][$idx]}"));
								$stylesheet = str_replace($match, $content, $stylesheet);
							}
						}

						// adjust paths
						$db_theme_data = str_replace('./', 'styles/' . $theme_row['theme_path'] . '/theme/', $stylesheet);

						// Save CSS contents
						$sql_ary = array(
							'theme_mtime'	=> (int) filemtime("{$phpbb_root_path}styles/{$theme_row['theme_path']}/theme/stylesheet.css"),
							'theme_data'	=> $db_theme_data,
						);

						$sql = 'UPDATE ' . STYLES_THEME_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE theme_id = $style_id";
						$db->sql_query($sql);

						$cache->destroy('sql', STYLES_THEME_TABLE);
					}

					return $this->umil_end();
				}
			break;
			//case 'theme' :

			default:
				$this->umil_start('CACHE_PURGE');
				$cache->purge();

				return $this->umil_end();
			break;
		}
	}

	/**
	* Check if a config setting exists
	*
	* @param string $config_name
	* @param bool $return_result - return the config value/default if true : default false.
	*
	* @return bool true/false if config exists
	*/
	function config_exists($config_name, $return_result = false)
	{
		global $config, $db, $cache;

		$sql = 'SELECT *
				FROM ' . CONFIG_TABLE . "
				WHERE config_name = '" . $db->sql_escape($config_name) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			if (!isset($config[$config_name]))
			{
				$config[$config_name] = $row['config_value'];

				if (!$row['is_dynamic'])
				{
					$cache->destroy('config');
				}
			}

			return ($return_result) ? $row : true;
		}

		// this should never happen, but if it does, we need to remove the config from the array
		if (isset($config[$config_name]))
		{
			unset($config[$config_name]);
			$cache->destroy('config');
		}

		return false;
	}

	/**
	* Add a config setting.
	*
	* @param string $config_name
	* @param mixed $config_value
	* @param bool $is_dynamic
	*
	* @return result
	*/
	function config_add($config_name, $config_value = '', $is_dynamic = false)
	{
		// Multicall
		if (is_array($config_name))
		{
			foreach ($config_name as $params)
			{
				call_user_func_array(array($this, 'config_add'), $params);
			}
			return;
		}

		$this->umil_start('CONFIG_ADD', $config_name);

		if ($this->config_exists($config_name))
		{
			global $user;
			$this->result('CONFIG_ALREADY_EXISTS', $config_name);
			return $this->umil_end();
		}

		set_config($config_name, $config_value, $is_dynamic);

		return $this->umil_end();
	}

	/**
	* Update a config setting.
	*
	* @param string $config_name
	* @param mixed $config_value
	* @param bool $is_dynamic
	*
	* @return result
	*/
	function config_update($config_name, $config_value = '', $is_dynamic = false)
	{
		// Multicall
		if (is_array($config_name))
		{
			foreach ($config_name as $params)
			{
				call_user_func_array(array($this, 'config_update'), $params);
			}
			return;
		}

		$this->umil_start('CONFIG_UPDATE', $config_name);

		if (!$this->config_exists($config_name))
		{
			global $user;
			$this->result('CONFIG_NOT_EXIST', $config_name);
			return $this->umil_end();
		}

		set_config($config_name, $config_value, $is_dynamic);

		return $this->umil_end();
	}

	/**
	* Remove a config setting
	*
	* @param string $config_name
	*
	* @return result
	*/
	function config_remove($config_name)
	{
		global $cache, $config, $db;

		// Multicall
		if (is_array($config_name))
		{
			foreach ($config_name as $params)
			{
				call_user_func_array(array($this, 'config_remove'), $params);
			}
			return;
		}

		$this->umil_start('CONFIG_REMOVE', $config_name);

		if (!$this->config_exists($config_name))
		{
			global $user;
			$this->result('CONFIG_NOT_EXIST', $config_name);
			return $this->umil_end();
		}

		$sql = 'DELETE FROM ' . CONFIG_TABLE . " WHERE config_name = '" . $db->sql_escape($config_name) . "'";
		$db->sql_query($sql);

		unset($config[$config_name]);
		$cache->destroy('config');

		return $this->umil_end();
	}

	/**
	* Module Exists
	*
	* Check if a module exists
	*
	* @param string $class The module class(acp|mcp|ucp)
	* @param int|string|bool $parent The parent module_id|module_langname (0 for no parent).  Use false to ignore the parent check and check class wide.
	* @param mixed $module The module_langname you would like to check for to see if it exists
	*/
	function module_exists($class, $parent, $module)
	{
		global $db;

		$class = $db->sql_escape($class);
		$module = $db->sql_escape($module);

		// Allows '' to be sent
		$parent = (!$parent) ? 0 : $parent;

		$parent_sql = '';
		if ($parent !== false)
		{
			if (!is_numeric($parent))
			{
				$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
					WHERE module_langname = '" . $db->sql_escape($parent) . "'
					AND module_class = '$class'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					return false;
				}

				$parent_sql = 'AND parent_id = ' . (int) $row['module_id'];
			}
			else
			{
				$parent_sql = 'AND parent_id = ' . (int) $parent;
			}
		}

		$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
			WHERE module_class = '$class'
			$parent_sql
			AND module_langname = '$module'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			return true;
		}

		return false;
	}

	/**
	* Module Add
	*
	* Add a new module
	*
	* @param string $class The module class(acp|mcp|ucp)
	* @param int|string $parent The parent module_id|module_langname (0 for no parent)
	* @param array $data an array of the data on the new module.  This can be setup in two different ways.
	*	1. The "manual" way.  For inserting a category or one at a time.  It will be merged with the base array shown a bit below,
	*		but at the least requires 'module_langname' to be sent, and, if you want to create a module (instead of just a category) you must send module_basename and module_mode.
	* array(
	*		'module_enabled'	=> 1,
	*		'module_display'	=> 1,
	*		'module_basename'	=> '',
	*		'module_class'		=> $class,
	*		'parent_id'			=> (int) $parent,
	*		'module_langname'	=> '',
	*		'module_mode'		=> '',
	*		'module_auth'		=> '',
	*	)
	*	2. The "automatic" way.  For inserting multiple at a time based on the specs in the info file for the module(s).  For this to work the modules must be correctly setup in the info file.
	*		An example follows (this would insert the settings, log, and flag modes from the includes/acp/info/acp_asacp.php file):
	* array(
	* 		'module_basename'	=> 'asacp',
	* 		'modes'				=> array('settings', 'log', 'flag'),
	* )
	* 		Optionally you may not send 'modes' and it will insert all of the modules in that info file.
	*/
	function module_add($class, $parent = 0, $data = array())
	{
		global $cache, $db, $user, $phpbb_root_path, $phpEx;

		// Multicall
		if (is_array($class))
		{
			foreach ($class as $params)
			{
				call_user_func_array(array($this, 'module_add'), $params);
			}
			return;
		}

        // Allows '' to be sent
		$parent = (!$parent) ? 0 : $parent;

		// allow sending the name as a string in $data to create a category
		if (!is_array($data))
		{
			$data = array('module_langname' => $data);
		}

		if (!isset($data['module_langname']))
		{
			// The "automatic" way
			$basename = (isset($data['module_basename'])) ? $data['module_basename'] : '';
			$basename = preg_replace('#([^a-zA-Z0-9])#', '', $basename);
			$class = preg_replace('#([^a-zA-Z0-9])#', '', $class);
			$info_file = "{$phpbb_root_path}includes/$class/info/{$class}_$basename.$phpEx";

			// The manual and automatic ways both failed...
			if (!file_exists($info_file))
			{
				$this->umil_start('MODULE_ADD', $class, 'UNKNOWN');
				$this->result('FAIL');
				return $this->umil_end();
			}

			include($info_file);
			$classname = "{$class}_{$basename}_info";
			$info = new $classname;
			$module = $info->module();
			unset($info);

			$result = '';
			foreach ($module['modes'] as $mode => $module_info)
			{
				if (!isset($data['modes']) || in_array($mode, $data['modes']))
				{
					$new_module = array(
						'module_basename'	=> $basename,
						'module_langname'	=> $module_info['title'],
						'module_mode'		=> $mode,
						'module_auth'		=> $module_info['auth'],
					);

					// Run the "manual" way with the data we've collected.
					$result .= ((isset($data['spacer'])) ? $data['spacer'] : '<br />') . $this->module_add($class, $parent, $new_module);
				}
			}

			return $result;
		}

		// The "manual" way
		$this->umil_start('MODULE_ADD', $class, ((isset($user->lang[$data['module_langname']])) ? $user->lang[$data['module_langname']] : $data['module_langname']));

		$class = $db->sql_escape($class);

		if (!is_numeric($parent))
		{
			$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
				WHERE module_langname = '" . $db->sql_escape($parent) . "'
				AND module_class = '$class'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				$this->result('FAIL');
				return $this->umil_end();
			}

			$data['parent_id'] = $row['module_id'];
		}

		if (!class_exists('acp_modules'))
		{
			include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
			$user->add_lang('acp/modules');
		}
		$acp_modules = new acp_modules();

		$data = array_merge(array(
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'module_basename'	=> '',
			'module_class'		=> $class,
			'parent_id'			=> (int) $parent,
			'module_langname'	=> '',
			'module_mode'		=> '',
			'module_auth'		=> '',
		), $data);
		$result = $acp_modules->update_module_data($data, true);

		// update_module_data can either return a string, an empty array, or an array with a language string in...
		if (is_array($result) && !empty($result))
		{
			$this->result = implode('<br />', $result);
		}
		else if (!is_array($result) && $result !== '')
		{
			$this->result($result);
		}

		// Clear the Modules Cache
		$cache->destroy("_modules_$class");

		return $this->umil_end();
	}

	/**
	* Module Remove
	*
	* Remove a module
	*
	* @param string $class The module class(acp|mcp|ucp)
	* @param int|string|bool $parent The parent module_id|module_langname (0 for no parent).  Use false to ignore the parent check and check class wide.
	* @param int|string $module The module id|module_langname
	*/
	function module_remove($class, $parent = 0, $module = '')
	{
		global $cache, $db, $user, $phpbb_root_path, $phpEx;

		// Multicall
		if (is_array($class))
		{
			foreach ($class as $params)
			{
				call_user_func_array(array($this, 'module_remove'), $params);
			}
			return;
		}

        // Allows '' to be sent
		$parent = (!$parent) ? 0 : $parent;

		// Imitation of module_add's "automatic" and "manual" method so the uninstaller works from the same set of instructions for umil_auto
		if (is_array($module))
		{
			if (!isset($module['module_basename']))
			{
				if (isset($module['module_langname']))
				{
					// Manual Method
					call_user_func(array($this, 'module_remove'), $class, $parent, $module['module_langname']);
				}

				return;
			}

			// Automatic method
			$basename = preg_replace('#([^a-zA-Z0-9])#', '', $module['module_basename']);
			$class = preg_replace('#([^a-zA-Z0-9])#', '', $class);
			$info_file = "{$phpbb_root_path}includes/$class/info/{$class}_$basename.$phpEx";

			if (!file_exists($info_file))
			{
				return;
			}

			include($info_file);
			$classname = "{$class}_{$basename}_info";
			$info = new $classname;
			$module_info = $info->module();
			unset($info);

			foreach ($module_info['modes'] as $mode)
			{
				if (!isset($module['modes']) || isset($module['modes'][$mode]))
				{
					call_user_func(array($this, 'module_remove'), $class, $parent, $mode['title']);
				}
			}
		}
		else
		{
			$class = $db->sql_escape($class);

			if (!$this->module_exists($class, $parent, $module))
			{
				$this->umil_start('MODULE_REMOVE', $class, ((isset($user->lang[$module])) ? $user->lang[$module] : $module));
				$this->result('MODULE_NOT_EXIST');
				return $this->umil_end();
			}

			$parent_sql = '';
			if ($parent !== false)
			{
				if (!is_numeric($parent))
				{
					$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
						WHERE module_langname = '" . $db->sql_escape($parent) . "'
						AND module_class = '$class'";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					// we know it exists from the module_exists check

					$parent_sql = 'AND parent_id = ' . (int) $row['module_id'];
				}
				else
				{
					$parent_sql = 'AND parent_id = ' . (int) $parent;
				}
			}

			$module_ids = array();
			if (!is_numeric($module))
			{
				$module = $db->sql_escape($module);
				$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
					WHERE module_langname = '$module'
					AND module_class = '$class'
					$parent_sql";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$module_ids[] = (int) $row['module_id'];
				}
				$db->sql_freeresult($result);

				$module_name = $module;
			}
			else
			{
				$module = (int) $module;
				$sql = 'SELECT module_langname FROM ' . MODULES_TABLE . "
					WHERE module_id = $module
					AND module_class = '$class'
					$parent_sql";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$module_name = $row['module_langname'];
				$module_ids[] = $module;
			}

			$this->umil_start('MODULE_REMOVE', $class, ((isset($user->lang[$module_name])) ? $user->lang[$module_name] : $module_name));

			if (!class_exists('acp_modules'))
			{
				include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
				$user->add_lang('acp/modules');
			}
			$acp_modules = new acp_modules();
			$acp_modules->module_class = $class;

			foreach ($module_ids as $module_id)
			{
				$result = $acp_modules->delete_module($module_id);
				if (!empty($result))
				{
					if ($this->result == ((isset($user->lang['SUCCESS'])) ? $user->lang['SUCCESS'] : 'SUCCESS'))
					{
						$this->result = implode('<br />', $result);
					}
					else
					{
						$this->result .= '<br />' . implode('<br />', $result);
					}
				}
			}

			$cache->destroy("_modules_$class");

			return $this->umil_end();
		}
	}

	/**
	* Permission Exists
	*
	* Check if a permission (auth) setting exists
	*
	* @param string $auth_option
	* @param bool $global True for checking a global permission setting, False for a local permission setting
	*
	* @return bool true if it exists, false if not
	*/
	function permission_exists($auth_option, $global = true)
	{
		global $db;

		if ($global)
		{
			$type_sql = ' AND is_global = 1';
		}
		else
		{
			$type_sql = ' AND is_local = 1';
		}

		$sql = 'SELECT auth_option_id
				FROM ' . ACL_OPTIONS_TABLE . "
				WHERE auth_option = '" . $db->sql_escape($auth_option) . "'"
				. $type_sql;
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			return true;
		}

		return false;
	}

	/**
	* Permission Add
	*
	* Add a permission (auth) option
	*
	* @param string $auth_option
	* @param bool $global True for checking a global permission setting, False for a local permission setting
	*
	* @return result
	*/
	function permission_add($auth_option, $global = true)
	{
		global $db;

		// Multicall
		if (is_array($auth_option))
		{
			foreach ($auth_option as $params)
			{
				call_user_func_array(array($this, 'permission_add'), $params);
			}
			return;
		}

		$this->umil_start('PERMISSION_ADD', $auth_option);

		if ($this->permission_exists($auth_option, $global))
		{
			global $user;
			$this->result('PERMISSION_ALREADY_EXISTS', $auth_option);
			return $this->umil_end();
		}

		if (!class_exists('auth_admin'))
		{
			global $phpbb_root_path, $phpEx;

			include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
		}
		$auth_admin = new auth_admin();

		// in the acl_add_option function it already checks if the auth option exists already or not.
		if ($global)
		{
			$auth_admin->acl_add_option(array('global' => array($auth_option)));
		}
		else
		{
			$auth_admin->acl_add_option(array('local' => array($auth_option)));
		}

		return $this->umil_end();
	}

	/**
	* Permission Remove
	*
	* Remove a permission (auth) option
	*
	* @param string $auth_option
	* @param bool $global True for checking a global permission setting, False for a local permission setting
	*
	* @return result
	*/
	function permission_remove($auth_option, $global = true)
	{
		global $auth, $cache, $db;

		// Multicall
		if (is_array($auth_option))
		{
			foreach ($auth_option as $params)
			{
				call_user_func_array(array($this, 'permission_remove'), $params);
			}
			return;
		}

		$this->umil_start('PERMISSION_REMOVE', $auth_option);

		if (!$this->permission_exists($auth_option, $global))
		{
			global $user;
			$this->result('PERMISSION_NOT_EXIST', $auth_option);
			return $this->umil_end();
		}

		$sql = 'SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option = '" . $db->sql_escape($auth_option) . "'
			AND is_global = " . (($global) ? '1' : '0');
		$db->sql_query($sql);
		$id = $db->sql_fetchfield('auth_option_id');

		// Delete time
		$db->sql_query('DELETE FROM ' . ACL_GROUPS_TABLE . ' WHERE auth_option_id = ' . $id);
		$db->sql_query('DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option_id = ' . $id);
		$db->sql_query('DELETE FROM ' . ACL_ROLES_DATA_TABLE . ' WHERE auth_option_id = ' . $id);
		$db->sql_query('DELETE FROM ' . ACL_USERS_TABLE . ' WHERE auth_option_id = ' . $id);

		// Purge the auth cache
		$cache->destroy('_acl_options');
		$auth->acl_clear_prefetch();

		return $this->umil_end();
	}

	/**
	* Table Exists
	*
	* Check if a table exists in the DB or not
	*
	* @param string $table_name The table name to check for
	*
	* @return bool true if the table exists, false if not
	*/
	function table_exists($table_name)
	{
		global $db, $table_prefix;

		$table_name = str_replace('phpbb_', $table_prefix, $table_name);

		if (!function_exists('get_tables'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
		}

		$tables = get_tables($db);

		if (in_array($table_name, $tables))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Table Add
	*
	* Currently this only supports input from the array format of db_tools or create_schema_files.
	* Input of a MySQL formatted creation query is a planned option for the future (that method would create the array format required from the SQL query)
	*/
	function table_add($table_name, $table_data = array())
	{
		global $db, $dbms, $table_prefix, $user;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_add'), $params);
			}
			return;
		}

		$table_name = str_replace('phpbb_', $table_prefix, $table_name);

		$this->umil_start('TABLE_ADD', $table_name);

		if ($this->table_exists($table_name))
		{
			$this->result('TABLE_ALREADY_EXISTS', $table_name);
			return $this->umil_end();
		}

		if (!is_array($table_data))
		{
			$this->result('FAIL');
			return $this->umil_end();
		}

		$sql = $this->create_table_sql($table_name, $table_data);
		$db->sql_query($sql);

		return $this->umil_end();
	}

	/**
	* Table Remove
	*
	* Delete/Drop a DB table
	*/
	function table_remove($table_name)
	{
		global $db, $table_prefix;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_remove'), $params);
			}
			return;
		}

		$table_name = str_replace('phpbb_', $table_prefix, $table_name);

		$this->umil_start('TABLE_REMOVE', $table_name);

		if (!$this->table_exists($table_name))
		{
			global $user;
			$this->result('TABLE_NOT_EXIST', $table_name);
			return $this->umil_end();
		}

		$db->sql_query('DROP TABLE ' . $table_name);

		return $this->umil_end();
	}

	/**
	* Table Column Exists
	*
	* Check to see if a column exists in a table
	*/
	function table_column_exists($table_name, $column_name)
	{
		global $db, $table_prefix;

		$table_name = str_replace('phpbb_', $table_prefix, $table_name);

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);
		return $db_tools->sql_column_exists($table_name, $column_name);
	}

	/**
	* Table Column Add
	*
	* Add a new column to a table.
	*/
	function table_column_add($table_name, $column_name = '', $column_data = array())
	{
		global $db, $table_prefix;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_column_add'), $params);
			}
			return;
		}

		$table_name = str_replace('phpbb_', $table_prefix, $table_name);

		$this->umil_start('TABLE_COLUMN_ADD', $table_name, $column_name);

		if ($this->table_column_exists($table_name, $column_name))
		{
			global $user;
			$this->result('TABLE_COLUMN_ALREADY_EXISTS', $table_name, $column_name);
			return $this->umil_end();
		}

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);
		$db_tools->sql_column_add($table_name, $column_name, $column_data);

		return $this->umil_end();
	}

	/**
	* Table Column Update
	*
	* Alter/Update a column in a table.  You can not change a column name with this.
	*/
	function table_column_update($table_name, $column_name = '', $column_data = array())
	{
		global $db, $table_prefix;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_column_update'), $params);
			}
			return;
		}

		$table_name = str_replace('phpbb_', $table_prefix, $table_name);

		$this->umil_start('TABLE_COLUMN_UPDATE', $table_name, $column_name);

		if (!$this->table_column_exists($table_name, $column_name))
		{
			global $user;
			$this->result('TABLE_COLUMN_NOT_EXIST', $table_name, $column_name);
			return $this->umil_end();
		}

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);
		$db_tools->sql_column_change($table_name, $column_name, $column_data);

		return $this->umil_end();
	}

	/**
	* Table Column Remove
	*
	* Remove a column from a table
	*/
	function table_column_remove($table_name, $column_name = '')
	{
		global $db, $table_prefix;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_column_remove'), $params);
			}
			return;
		}

		$table_name = str_replace('phpbb_', $table_prefix, $table_name);

		$this->umil_start('TABLE_COLUMN_REMOVE', $table_name, $column_name);

		if (!$this->table_column_exists($table_name, $column_name))
		{
			global $user;
			$this->result('TABLE_COLUMN_NOT_EXIST', $table_name, $column_name);
			return $this->umil_end();
		}

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);
		$db_tools->sql_column_remove($table_name, $column_name);

		return $this->umil_end();
	}

	/**
	* Table Index Exists
	*
	* Check if a table key/index exists on a table (can not check primary or unique)
	*/
	function table_index_exists($table_name, $index_name)
	{
		global $db, $table_prefix;

		$table_name = str_replace('phpbb_', $table_prefix, $table_name);

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);

		$indexes = $db_tools->sql_list_index($table_name);

		if (in_array($index_name, $indexes))
		{
			return true;
		}

		return false;
	}

	/**
	* Table Index Add
	*
	* Add a new key/index to a table
	*/
	function table_index_add($table_name, $index_name = '', $column = array())
	{
		global $db, $table_prefix;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_index_add'), $params);
			}
			return;
		}

		$table_name = str_replace('phpbb_', $table_prefix, $table_name);

		$this->umil_start('TABLE_KEY_ADD', $table_name, $index_name);

		if ($this->table_index_exists($table_name, $index_name))
		{
			global $user;
			$this->result('TABLE_KEY_ALREADY_EXIST', $table_name, $index_name);
			return $this->umil_end();
		}

		if (!is_array($column))
		{
			$column = array($column);
		}

		if (empty($column))
		{
			$column = array($index_name);
		}

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);
		$db_tools->sql_create_index($table_name, $index_name, $column);

		return $this->umil_end();
	}

	/**
	* Table Index Remove
	*
	* Remove a key/index from a table
	*/
	function table_index_remove($table_name, $index_name = '')
	{
		global $db, $table_prefix;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_index_remove'), $params);
			}
			return;
		}

		$table_name = str_replace('phpbb_', $table_prefix, $table_name);

		$this->umil_start('TABLE_KEY_REMOVE', $table_name, $index_name);

		if (!$this->table_index_exists($table_name, $index_name))
		{
			global $user;
			$this->result('TABLE_KEY_NOT_EXIST', $table_name, $index_name);
			return $this->umil_end();
		}

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);
		$db_tools->sql_index_drop($table_name, $index_name);

		return $this->umil_end();
	}

	/**
	* Create table SQL
	*
	* Create the SQL query for the specified DBMS on the fly from a create_schema_files type of table array
	*
	* @param string $table_name The name of the table
	* @param array $table_data The table data (formatted in the array format used by create_schema_files)
	* @param string $dbms The dbms this will be built for (for testing only, leave blank to use the current DBMS)
	*
	* @return The sql query to run for the submitted dbms to insert the table
	*/
	function create_table_sql($table_name, $table_data, $dbms = '')
	{
		global $db;
		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}
		$db_tools = new phpbb_db_tools($db);

		// To allow testing
		$dbms = ($dbms) ? $dbms : $db_tools->sql_layer;

		// A list of types being unsigned for better reference in some db's
		$unsigned_types = array('UINT', 'UINT:', 'USINT', 'BOOL', 'TIMESTAMP');
		$supported_dbms = array('firebird', 'mssql', 'mysql_40', 'mysql_41', 'oracle', 'postgres', 'sqlite');

		$sql = '';

		// Create Table statement
		$generator = $textimage = false;

		switch ($dbms)
		{
			case 'mysql_40':
			case 'mysql_41':
			case 'firebird':
			case 'oracle':
			case 'sqlite':
			case 'postgres':
				$sql .= "CREATE TABLE {$table_name} (\n";
			break;

			case 'mssql':
				$sql .= "CREATE TABLE [{$table_name}] (\n";
			break;
		}

		// Table specific so we don't get overlap
		$modded_array = array();

		// Write columns one by one...
		foreach ($table_data['COLUMNS'] as $column_name => $column_data)
		{
			// Get type
			if (strpos($column_data[0], ':') !== false)
			{
				list($orig_column_type, $column_length) = explode(':', $column_data[0]);
				if (!is_array($db_tools->dbms_type_map[$dbms][$orig_column_type . ':']))
				{
					$column_type = sprintf($db_tools->dbms_type_map[$dbms][$orig_column_type . ':'], $column_length);
				}
				else
				{
					if (isset($db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['rule']))
					{
						switch ($db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['rule'][0])
						{
							case 'div':
								$column_length /= $db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['rule'][1];
								$column_length = ceil($column_length);
								$column_type = sprintf($db_tools->dbms_type_map[$dbms][$orig_column_type . ':'][0], $column_length);
							break;
						}
					}

					if (isset($db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['limit']))
					{
						switch ($db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['limit'][0])
						{
							case 'mult':
								$column_length *= $db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['limit'][1];
								if ($column_length > $db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['limit'][2])
								{
									$column_type = $db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['limit'][3];
									$modded_array[$column_name] = $column_type;
								}
								else
								{
									$column_type = sprintf($db_tools->dbms_type_map[$dbms][$orig_column_type . ':'][0], $column_length);
								}
							break;
						}
					}
				}
				$orig_column_type .= ':';
			}
			else
			{
				$orig_column_type = $column_data[0];
				$column_type = $db_tools->dbms_type_map[$dbms][$column_data[0]];
				if ($column_type == 'text' || $column_type == 'blob')
				{
					$modded_array[$column_name] = $column_type;
				}
			}

			// Adjust default value if db-dependant specified
			if (is_array($column_data[1]))
			{
				$column_data[1] = (isset($column_data[1][$dbms])) ? $column_data[1][$dbms] : $column_data[1]['default'];
			}

			switch ($dbms)
			{
				case 'mysql_40':
				case 'mysql_41':
					$sql .= "\t{$column_name} {$column_type} ";

					// For hexadecimal values do not use single quotes
					if (!is_null($column_data[1]) && substr($column_type, -4) !== 'text' && substr($column_type, -4) !== 'blob')
					{
						$sql .= (strpos($column_data[1], '0x') === 0) ? "DEFAULT {$column_data[1]} " : "DEFAULT '{$column_data[1]}' ";
					}
					$sql .= 'NOT NULL';

					if (isset($column_data[2]))
					{
						if ($column_data[2] == 'auto_increment')
						{
							$sql .= ' auto_increment';
						}
						else if ($dbms === 'mysql_41' && $column_data[2] == 'true_sort')
						{
							$sql .= ' COLLATE utf8_unicode_ci';
						}
					}

					$sql .= ",\n";
				break;

				case 'sqlite':
					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$sql .= "\t{$column_name} INTEGER PRIMARY KEY ";
						$generator = $column_name;
					}
					else
					{
						$sql .= "\t{$column_name} {$column_type} ";
					}

					$sql .= 'NOT NULL ';
					$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}'" : '';
					$sql .= ",\n";
				break;

				case 'firebird':
					$sql .= "\t{$column_name} {$column_type} ";

					if (!is_null($column_data[1]))
					{
						$sql .= 'DEFAULT ' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ' ';
					}

					$sql .= 'NOT NULL';

					// This is a UNICODE column and thus should be given it's fair share
					if (preg_match('/^X?STEXT_UNI|VCHAR_(CI|UNI:?)/', $column_data[0]))
					{
						$sql .= ' COLLATE UNICODE';
					}

					$sql .= ",\n";

					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$generator = $column_name;
					}
				break;

				case 'mssql':
					if ($column_type == '[text]')
					{
						$textimage = true;
					}

					$sql .= "\t[{$column_name}] {$column_type} ";

					if (!is_null($column_data[1]))
					{
						// For hexadecimal values do not use single quotes
						if (strpos($column_data[1], '0x') === 0)
						{
							$sql .= 'DEFAULT (' . $column_data[1] . ') ';
						}
						else
						{
							$sql .= 'DEFAULT (' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ') ';
						}
					}

					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$sql .= 'IDENTITY (1, 1) ';
					}

					$sql .= 'NOT NULL';
					$sql .= " ,\n";
				break;

				case 'oracle':
					$sql .= "\t{$column_name} {$column_type} ";
					$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';

					// In Oracle empty strings ('') are treated as NULL.
					// Therefore in oracle we allow NULL's for all DEFAULT '' entries
					$sql .= ($column_data[1] === '') ? ",\n" : "NOT NULL,\n";

					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$generator = $column_name;
					}
				break;

				case 'postgres':
					$sql .= "\t{$column_name} {$column_type} ";

					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$sql .= "DEFAULT nextval('{$table_name}_seq'),\n";

						// Make sure the sequence will be created before creating the table
						$sql .= "CREATE SEQUENCE {$table_name}_seq;\n\n" . $sql;
					}
					else
					{
						$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';
						$sql .= "NOT NULL";

						// Unsigned? Then add a CHECK contraint
						if (in_array($orig_column_type, $unsigned_types))
						{
							$sql .= " CHECK ({$column_name} >= 0)";
						}

						$sql .= ",\n";
					}
				break;
			}
		}

		switch ($dbms)
		{
			case 'firebird':
				// Remove last line delimiter...
				$sql = substr($sql, 0, -2);
				$sql .= "\n);;\n\n";
			break;

			case 'mssql':
				$sql = substr($sql, 0, -2);
				$sql .= "\n) ON [PRIMARY]" . (($textimage) ? ' TEXTIMAGE_ON [PRIMARY]' : '') . "\n";
				$sql .= "GO\n\n";
			break;
		}

		// Write primary key
		if (isset($table_data['PRIMARY_KEY']))
		{
			if (!is_array($table_data['PRIMARY_KEY']))
			{
				$table_data['PRIMARY_KEY'] = array($table_data['PRIMARY_KEY']);
			}

			switch ($dbms)
			{
				case 'mysql_40':
				case 'mysql_41':
				case 'postgres':
					$sql .= "\tPRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
				break;

				case 'firebird':
					$sql .= "ALTER TABLE {$table_name} ADD PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ");;\n\n";
				break;

				case 'sqlite':
					if ($generator === false || !in_array($generator, $table_data['PRIMARY_KEY']))
					{
						$sql .= "\tPRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
					}
				break;

				case 'mssql':
					$sql .= "ALTER TABLE [{$table_name}] WITH NOCHECK ADD \n";
					$sql .= "\tCONSTRAINT [PK_{$table_name}] PRIMARY KEY  CLUSTERED \n";
					$sql .= "\t(\n";
					$sql .= "\t\t[" . implode("],\n\t\t[", $table_data['PRIMARY_KEY']) . "]\n";
					$sql .= "\t)  ON [PRIMARY] \n";
					$sql .= "GO\n\n";
				break;

				case 'oracle':
					$sql .= "\tCONSTRAINT pk_{$table_name} PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
				break;
			}
		}

		switch ($dbms)
		{
			case 'oracle':
				// UNIQUE contrains to be added?
				if (isset($table_data['KEYS']))
				{
					foreach ($table_data['KEYS'] as $key_name => $key_data)
					{
						if (!is_array($key_data[1]))
						{
							$key_data[1] = array($key_data[1]);
						}

						if ($key_data[0] == 'UNIQUE')
						{
							$sql .= "\tCONSTRAINT u_phpbb_{$key_name} UNIQUE (" . implode(', ', $key_data[1]) . "),\n";
						}
					}
				}

				// Remove last line delimiter...
				$sql = substr($sql, 0, -2);
				$sql .= "\n)\n/\n\n";
			break;

			case 'postgres':
				// Remove last line delimiter...
				$sql = substr($sql, 0, -2);
				$sql .= "\n);\n\n";
			break;

			case 'sqlite':
				// Remove last line delimiter...
				$sql = substr($sql, 0, -2);
				$sql .= "\n);\n\n";
			break;
		}

		// Write Keys
		if (isset($table_data['KEYS']))
		{
			foreach ($table_data['KEYS'] as $key_name => $key_data)
			{
				if (!is_array($key_data[1]))
				{
					$key_data[1] = array($key_data[1]);
				}

				switch ($dbms)
				{
					case 'mysql_40':
					case 'mysql_41':
						$sql .= ($key_data[0] == 'INDEX') ? "\tKEY" : '';
						$sql .= ($key_data[0] == 'UNIQUE') ? "\tUNIQUE" : '';
						foreach ($key_data[1] as $key => $col_name)
						{
							if (isset($modded_array[$col_name]))
							{
								switch ($modded_array[$col_name])
								{
									case 'text':
									case 'blob':
										$key_data[1][$key] = $col_name . '(255)';
									break;
								}
							}
						}
						$sql .= ' ' . $key_name . ' (' . implode(', ', $key_data[1]) . "),\n";
					break;

					case 'firebird':
						$sql .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
						$sql .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

						$sql .= ' ' . $table_name . '_' . $key_name . ' ON ' . $table_name . '(' . implode(', ', $key_data[1]) . ");;\n";
					break;

					case 'mssql':
						$sql .= ($key_data[0] == 'INDEX') ? 'CREATE  INDEX' : '';
						$sql .= ($key_data[0] == 'UNIQUE') ? 'CREATE  UNIQUE  INDEX' : '';
						$sql .= " [{$key_name}] ON [{$table_name}]([" . implode('], [', $key_data[1]) . "]) ON [PRIMARY]\n";
						$sql .= "GO\n\n";
					break;

					case 'oracle':
						if ($key_data[0] == 'UNIQUE')
						{
							continue;
						}

						$sql .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';

						$sql .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ")\n";
						$sql .= "/\n";
					break;

					case 'sqlite':
						$sql .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
						$sql .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

						$sql .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ");\n";
					break;

					case 'postgres':
						$sql .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
						$sql .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

						$sql .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ");\n";
					break;
				}
			}
		}

		switch ($dbms)
		{
			case 'mysql_40':
				// Remove last line delimiter...
				$sql = substr($sql, 0, -2);
				$sql .= "\n);\n\n";
			break;

			case 'mysql_41':
				// Remove last line delimiter...
				$sql = substr($sql, 0, -2);
				$sql .= "\n) CHARACTER SET `utf8` COLLATE `utf8_bin`;\n\n";
			break;

			// Create Generator
			case 'firebird':
				if ($generator !== false)
				{
					$sql .= "\nCREATE GENERATOR {$table_name}_gen;;\n";
					$sql .= 'SET GENERATOR ' . $table_name . "_gen TO 0;;\n\n";

					$sql .= 'CREATE TRIGGER t_' . $table_name . ' FOR ' . $table_name . "\n";
					$sql .= "BEFORE INSERT\nAS\nBEGIN\n";
					$sql .= "\tNEW.{$generator} = GEN_ID({$table_name}_gen, 1);\nEND;;\n\n";
				}
			break;

			case 'oracle':
				if ($generator !== false)
				{
					$sql .= "\nCREATE SEQUENCE {$table_name}_seq\n/\n\n";

					$sql .= "CREATE OR REPLACE TRIGGER t_{$table_name}\n";
					$sql .= "BEFORE INSERT ON {$table_name}\n";
					$sql .= "FOR EACH ROW WHEN (\n";
					$sql .= "\tnew.{$generator} IS NULL OR new.{$generator} = 0\n";
					$sql .= ")\nBEGIN\n";
					$sql .= "\tSELECT {$table_name}_seq.nextval\n";
					$sql .= "\tINTO :new.{$generator}\n";
					$sql .= "\tFROM dual;\nEND;\n/\n\n";
				}
			break;
		}

		return $sql;
	}
}

} //if (!class_exists('umil'))

?>