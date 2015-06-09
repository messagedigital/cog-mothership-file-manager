<?php

namespace Message\Mothership\FileManager\File;

use Message\Cog\DB\Entity\EntityLoaderCollection;

class FileProxy extends File
{
	protected $_tagLoader;
	protected $_loaded = false;
	
	public function __construct(TagLoader $loader)
	{
		$this->_tagLoader = $loader;
		parent::__construct();
	}

	public function getTags()
	{
		if ($this->_loaded) {
			return parent::getTags();
		}

		$tags = $this->_tagLoader->getByFile($this);
		
		if ($tags !== false) {
			$this->_tags = $this->_tags + $tags;
		}

		$this->_loaded = true;

		return parent::getTags();
	}
}