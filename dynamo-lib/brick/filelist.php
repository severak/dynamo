<?php
class brick_filelist
{
	protected $_dir = '';
	protected $_list = array();
	
	function scan($dir, $prefix='')
	{
		$this->_dir = $dir;
		$directory = opendir($dir. $prefix);
		while (($entry = readdir($directory)) !== false) {
			if($entry == "." || $entry == "..") {
				continue;
			} elseif(is_dir($dir . $prefix . '/' . $entry)) {
				$this->scan($dir,  $prefix . '/' . $entry);
			} else {
				$this->_list[] = $prefix  . '/' .$entry;
			}
		}
		closedir($directory);
		return $this->_list;
	}

	function exclude($pattern)
	{
		$filtered = array();
		foreach ($this->_list as $item) {
			if (!preg_match($pattern, $item)) {
				$filtered[] = $item;
			}
		}
		$this->_list = $filtered;
		return $this;
	}

	function only($pattern)
	{
		$filtered = array();
		foreach ($this->_list as $item) {
			if (preg_match($pattern, $item)) {
				$filtered[] = $item;
			}
		}
		$this->_list = $filtered;
		return $this;
	}
	
	function enumerate()
	{
		return $this->_list;
	}
	
	function walk()
	{
		$prefixed = array();
		foreach ($this->_list as $item) {
			$prefixed[] = $this->_dir . $item;
		}
		return new ArrayIterator($prefixed);
	}
}