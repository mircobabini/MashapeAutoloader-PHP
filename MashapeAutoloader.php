<?
/*
 * Mashape APIs' Autoloader library.
 *
 * Copyright (C) 2011 mirkolofio.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * The author of this software is mirkolofio.
 * For any question or feedback please
 *		contact me at: mirkolofio(at)gmail(dot)com
 *		or surf my website: http://mirkolofio.net/
 *
 *
 * Some infos about this lib: http://wp.me/p1e4Gf-6r
 */
require_once (dirname (__FILE__) . "/MashapeClient/MashapeClient.php");
abstract class MashapeAutoloader
{
	const MASHAPE_DOWNLOAD_ROOT = "http://www.mashape.com/apis/download-php-client?componentName=";

	protected static $apiStore;

	protected static $publicKey;
	protected static $privateKey;

	protected static $instances = array ();



	/**
	 * @param String $apiName
	 * @param array $arguments
	 * @return mixed
	 *
	 * (Instantiates and then) Returns the instance of invoked API interface
	 */
	public static function __callStatic ($apiName, $arguments = array ())
	{
		// check for local api store
		if (self::$apiStore === null)
			throw new Exception ("define MashapeAutoloader::\$apiStore as the place where apis stored");

		if (!isset (self::$instances[ $apiName ]))
		{
			$apiFilePath = self::$apiStore. "mashape-{$apiName}/{$apiName}.php";
			if (!self::downloadLib ($apiName)) // check for already existance is inherent in this call
				return null;

			require_once $apiFilePath;

			// if method called without keys you should have called self::auth before
			if (empty ($arguments))
				$arguments = array (self::$publicKey, self::$privateKey);

			self::$instances[ $apiName ] = new $apiName ($arguments[0], $arguments[1]);
		}

		return self::$instances[ $apiName ];
	}
	/**
	 * @param String $name
	 *
	 * Returns false if something wrong, otherwise true (API interface
	 * already exists or just well downloaded)
	 */
	public static function downloadLib ($name)
	{
		if (!is_dir (self::$apiStore))
			mkdir (self::$apiStore);

		$cwd = getcwd ();
		chdir (self::$apiStore);

		// check if the API interface already exists
		$apiFilePath = "mashape-{$name}/{$name}.php";
		if (file_exists ($apiFilePath))
		{
			chdir ($cwd);
			return true;
		}

		// download the API interface's archive
		$clientUrl = self::MASHAPE_DOWNLOAD_ROOT . $name;
		$clientArchive = @file_get_contents ($clientUrl);

		// save it locally
		$archivePath = "mashape-{$name}.zip";
		$archived = @file_put_contents ($archivePath, $clientArchive);
		if ($archived === false)
		{
			chdir ($cwd);
			return false;
		}

		// extract it
		$zip = new ZipArchive;
		if (!@$zip->open ($archivePath))
		{
			chdir ($cwd);
			return false;
		}

		$folderPath = "mashape-{$name}";
		$zip->extractTo ($folderPath);
		$zip->close();

		// delete useless archive and useless parts of API client package
		unlink ($archivePath);
		chdir ($folderPath);
		unlink ("sample.php");
		rrmdir ("mashape");

		// manipulate API interface
		$fileName = "{$name}.php";
		$content = @file_get_contents ($fileName);
		$content = str_replace ("require_once(\"mashape/MashapeClient.php\");\n", "", $content);
		$content = str_replace ("?>", "", $content);

		// save result
		@file_put_contents ($fileName, $content);
		chdir ($cwd);
		return true;
	}



	/**
	 * @param String $publicKey
	 * @param String $privateKey
	 *
	 * Set mashape authentication keys
	 */
	public static function auth ($publicKey, $privateKey)
	{
		self::$publicKey = $publicKey;
		self::$privateKey = $publicKey;
	}
	/**
	 * @param String $apiStore
	 *
	 * Set the place where apis stored
	 */
	public static function store ($apiStore)
	{
		self::$apiStore = $apiStore;
	}
	/**
	 * @param String $apiName
	 * @param String $methodName
	 * @return StdClass
	 *
	 * Compatibility with less flexible languages
	 * (optional) Third parameter and so on are method parameters
	 */
	public static function exec ($apiName, $methodName)
	{
		// get the instance of requested API interface
		list ($publicKey, $privateKey) = array (self::$publicKey, self::$privateKey);
		eval ("\$instance = self::{$apiName} (\$publicKey, \$privateKey);");

		// check method existence
		if (!method_exists ($instance, $methodName))
			return null;

		// invoke requested method
		$arguments = array_slice (func_get_args (), 2);
		$json = call_user_func_array (array ($instance, $methodName), $arguments);
		return $json;
	}
}

