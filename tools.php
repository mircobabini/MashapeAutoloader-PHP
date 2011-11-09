<?
/**
 * @param String $dir
 * @return bool
 *
 * @author holger1@zentralplan.de
 * @link http://php.net/manual/en/function.rmdir.php#98622
 */
function rrmdir ($dir)
{
	if (is_dir ($dir))
	{
		$objects = scandir ($dir);
		foreach ($objects as $object)
			if ($object != "." && $object != "..")
				if (filetype ($dir . "/" . $object) == "dir")
					rrmdir ($dir . "/" . $object);
				else
					unlink ($dir . "/" . $object);

		reset ($objects);
		rmdir ($dir);

		return true;
	}

	return false;
}