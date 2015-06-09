<?php

namespace Message\Mothership\FileManager\File;

use Message\Cog\DB;

/**
 * Class TagLoader
 * @package Message\Mothership\FileManager\File
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class for loading tags assigned to files
 */
class TagLoader
{
	/**
	 * @var DB\QueryBuilderFactory
	 */
	private $_queryBuilderFactory;

	/**
	 * @var DB\QueryBuilder
	 */
	private $_queryBuilder;

	public function __construct(DB\QueryBuilderFactory $queryBuilderFactory)
	{
		$this->_queryBuilderFactory = $queryBuilderFactory;
	}

	/**
	 * Get tags belonging to a file
	 *
	 * @param File $file
	 *
	 * @return array
	 */
	public function getByFile(File $file)
	{
		$this->_setQueryBuilder();

		$this->_queryBuilder
			->where('file_tag.tag_id = ?i', [$file->id])
		;

		return $this->_load();
	}

	/**
	 * Set up a new instance of the query builder with the SELECT and FROM statements set
	 */
	private function _setQueryBuilder()
	{
		$this->_queryBuilder = $this->_queryBuilderFactory
			->getQueryBuilder()
			->select('file_tag.tag_name')
			->from('file_tag')
		;
	}

	/**
	 * Load the tags via the query builder
	 *
	 * @throws \LogicException    Throws exception if query builder is not set yet
	 *
	 * @return array
	 */
	private function _load()
	{
		if (null === $this->_queryBuilder) {
			throw new \LogicException('Cannot load tags, query builder not set!');
		}

		$tags = $this->_queryBuilder->getQuery()->run()->flatten();

		$this->_queryBuilder = null;

		return $tags;
	}
}