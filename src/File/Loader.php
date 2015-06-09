<?php

namespace Message\Mothership\FileManager\File;

use Message\User\User;
use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\Filesystem\File as FileSystemFile;
use Message\Cog\DB\Result;

class Loader
{
	/**
	 * @var
	 */
	private $_locale;

	/**
	 * @var DB\QueryBuilderFactory
	 */
	private $_queryBuilderFactory;

	/**
	 * @var TagLoader
	 */
	private $_tagLoader;

	/**
	 * @var bool
	 */
	private $_returnAsArray;

	/**
	 * @var DB\QueryBuilder
	 */
	private $_queryBuilder;

	/**
	 * var to toggle the loading of deleted files
	 *
	 * (default value: false)
	 *
	 * @var bool
	 */
	protected $_loadDeleted = false;

	public function __construct(/*\Locale*/ $locale, DB\QueryBuilderFactory $queryBuilderFactory, TagLoader $tagLoader)
	{
		$this->_locale              = $locale;
		$this->_queryBuilderFactory = $queryBuilderFactory;
		$this->_tagLoader           = $tagLoader;
	}

	/**
	 * Return an array of, or singular File object
	 *
	 * @param  int|array $fileIDs
	 * @return array|File 	File object
	 */
	public function getByID($fileIDs)
	{
		if (!is_array($fileIDs)) {
			$fileIDs = [$fileIDs];
			$this->_returnAsArray = false;
		} else {
			$this->_returnAsArray = true;
		}

		$this->_setQueryBuilder();
		$this->_queryBuilder
			->where('file.file_id IN (?ji)', [$fileIDs])
		;

		return $this->_load();
	}

	/**
	 * Returns all the files of a certain file type id
	 *
	 * @param  int 	$typeID
	 * @return array|File 	Array of File objects, or a single File object
	 */
	public function getByType($typeID)
	{
		$this->_setQueryBuilder();
		$this->_queryBuilder
			->where('file.type_id = ?i', [$typeID])
		;

		$this->_returnAsArray = true;

		return $this->_load();
	}

	/**
	 * Load files with a given filename
	 *
	 * @param $filename
	 * @return false|File
	 */
	public function getByFilename($filename)
	{
		if (!is_string($filename)) {
			throw new \InvalidArgumentException('Filename must be a string, ' . gettype($filename) . ' given');
		}

		$this->_setQueryBuilder();
		$this->_queryBuilder
			->where('file.name = ?s', [$filename])
		;

		$this->_returnAsArray = false;

		return $this->_load();
	}

	/**
	 * Load files with an extension
	 *
	 * @param $ext
	 * @return false|File
	 */
	public function getByExtension($ext)
	{
		if (!is_string($ext)) {
			throw new \InvalidArgumentException('Extension must be a string, ' . gettype($ext) . ' given');
		}

		$this->_setQueryBuilder();
		$this->_queryBuilder
			->where('file.extension = ?s', [$ext])
		;

		$this->_returnAsArray = true;

		return $this->_load();
	}

	/**
	 * Find results based on the search term
	 *
	 * @param  string $term search terms
	 * @return array|File 	Array of File objects, or a single File object
	 */
	public function getBySearchTerm($term)
	{
		// Turn the terms into an array
		$terms = explode(' ',$term);

		// Set a bunch of arrays which are used below, seems a lot but it's
		// becasue we have to pass through an array to the sql query so we have to do it twice
		$whereName = array();
		$whereTag = array();

		// Loop over the terms and add them to an array to implode in the query
		foreach ($terms as $key => $term) {
			$whereName[]  = ' name LIKE ?s';
			$whereTag[]   = ' tag_name LIKE ?s';
			$terms[$key] = '%' . trim($term) . '%';
		}

		$this->_setQueryBuilder();
		$this->_queryBuilder
			->where('(' . implode(' OR ' . $whereName) . ')', $terms)
			->where('(' . implode(' OR ' . $whereTag) . ')', $terms, false)
		;

		$this->_returnAsArray = true;

		// Return the array of results.
		return $this->_load();
	}

	/**
	 * Return all files in an array
	 * @return Array|File|false - 	returns either an array of File objects, a
	 * 								single file object or false
	 */
	public function getAll()
	{
		$this->_setQueryBuilder();

		return $this->_load();

	}

	/**
	 * @deprecated This method does nothing and will need to be removed.
	 */
	public function getByUnused()
	{

	}

	public function getByUser(User $user)
	{
		$this->_setQueryBuilder();
		$this->_queryBuilder
			->where('file.created_by = ?i', [$user->id])
		;

		return $this->_load();

	}

	/**
	 * Toggle whether or not to load deleted files
	 *
	 * @param bool $bool 	true / false as to whether to include deleted items
	 * @return 	$this 		Loader object in order to chain the methods
	 */
	public function includeDeleted($bool)
	{
		$this->_loadDeleted = $bool;

		return $this;
	}
	/**
	 * @deprecated   Do not load tags from the file loader, use the TagLoader instead
	 *
	 * Gets the tags for a file
	 * @param  File      $file file to load tags for
	 * @return array     tags for file as an array
	 */
	public function getTagsForFile(File $file)
	{
		return $this->_tagLoader->getByFile($file);
	}

	/**
	 * Sets the query builder with the appropriate SELECT and FROM statement
	 */
	private function _setQueryBuilder()
	{
		$this->_queryBuilder = $this->_queryBuilderFactory->getQueryBuilder()
			->select([
				'file.file_id AS id',
				'file.url AS url',
				'file.name AS `name`',
				'file.extension AS extension',
				'file.file_size AS fileSize',
				'file.created_at AS createdAt',
				'file.created_by AS createdBy',
				'file.updated_at AS updatedAt',
				'file.updated_by AS updatedBy',
				'file.deleted_at AS deletedAt',
				'file.deleted_by AS deletedBy',
				'file.type_id AS typeID',
				'file.checksum AS checksum',
				'file.preview_url AS previewUrl',
				'file.dimension_x AS dimensionX',
				'file.dimension_y AS dimensionY',
				'file.alt_text AS altText',
				'file.duration AS duration',
			])
			->from('file')
			->orderBy('file.created_at DESC')
		;

		if (!$this->_loadDeleted) {
			$this->_queryBuilder
				->where('file.deleted_at IS NULL');
		}
	}

	/**
	 * Loads the file data out of the table and loads in into a File Object.
	 *
	 * @return File|false return instance of the file is loaded else false
	 */
	protected function _load()
	{
		if (null === $this->_queryBuilder) {
			throw new \LogicException('Cannot load files, query builder not set');
		}

		$result = $this->_queryBuilder->getQuery()->run();

		if (count($result)) {
			return $this->_loadFile($result);
		}

		$this->_queryBuilder = null;

		return false;
	}

	/**
	 * This will load file objects for the results of _load
	 *
	 * @param  Result $results 	Results of files that need to be loaded
	 *
	 * @return array|File 		array or single Page object if only one result
	 */
	protected function _loadFile(Result $results)
	{
		$files = $results->bindTo(
			'\Message\Mothership\FileManager\File\FileProxy',
			[$this->_tagLoader]
		);

		foreach ($results as $key => $result) {

			$files[$key]->authorship->create(new DateTimeImmutable('@'.$result->createdAt), $result->createdBy);

			if ($result->updatedAt) {
				$files[$key]->authorship->update(new DateTimeImmutable('@'.$result->updatedAt), $result->updatedBy);
			}

			if ($result->deletedAt) {
				$files[$key]->authorship->delete(new DateTimeImmutable('@'.$result->deletedAt), $result->deletedBy);
			}

			$files[$key]->file = new FileSystemFile($files[$key]->url);

			// Force type to be an integer
			$files[$key]->typeID = (int) $files[$key]->typeID;
		}

		return count($files) == 1 && !$this->_returnAsArray ? array_shift($files) : $files;
	}

	/**
	 * @deprecated  Do not load tags from the file loader, use the tag loader directly instead
	 *
	 * @param File $file
	 * @return array
	 */
	protected function _loadTags(File $file)
	{
		return $this->_tagLoader->getByFile($file);
	}

}