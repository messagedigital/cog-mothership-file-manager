<?php 

namespace Message\Mothership\FileManager\Test\File;

use Message\Mothership\FileManager\File\Delete;
use Message\Mothership\FileManager\File\File;
use Message\Mothership\FileManager\File\Loader;

use Message\Cog\Test\Event\FauxDispatcher;
use Message\Cog\DB\Adapter\Faux\Connection as FauxConnection;
use Message\Cog\DB\Query;
use Message\Cog\ValueObject\Authorship;

class DeleteTest extends \PHPUnit_Framework_TestCase
{

	const FILE_ID = 4;

	protected $_file;

	protected $_eventDispatcher;
	protected $_query;

	protected $_loader;
	protected $_delete;

	public function setUp()
	{
		$this->_eventDispatcher = new FauxDispatcher;
		$this->_query           = $this->getMock('Message\Cog\DB\Query', array('query'), array(
			new FauxConnection(array('affectedRows' => 1))
		));
		$this->_loader = $this->getMock('Message\Mothership\FileManager\File\Loader', array('getByID'), array(), '', false);
		$this->_delete = new Delete(
			$this->_query,
			$this->_eventDispatcher
		);

		$this->_file = new File;
		$this->_file->fileID = self::FILE_ID;
		$this->_file->authorship = new Authorship;

		$this->_loader
			 ->expects($this->any())
			 ->method('getByID')
			 ->with(self::FILE_ID)
			 ->will($this->returnValue($this->_file));
	}

	public function testDelete()
	{
		$deletedFile = $this->_delete->delete($this->_loader->getByID(self::FILE_ID));

		$dateTime = new \DateTime;

		$this->assertEquals($deletedFile->authorship->deletedAt()->getTimestamp(),
			$dateTime->getTimestamp(), 2
		);
		$this->assertNotNull($deletedFile->authorship->deletedBy());
		$this->assertSame($deletedFile->fileID, self::FILE_ID);
	}

	public function testRestore()
	{


		$this->_file = new File;
		$this->_file->fileID = self::FILE_ID;
		$this->_file->authorship = new Authorship;
		$this->_file->authorship->delete(new \DateTime, 1);

		$restoredFile = $this->_delete->restore($this->_file);

		$dateTime = new \DateTime;

		$this->assertNull($restoredFile->authorship->deletedAt());
		$this->assertNull($restoredFile->authorship->deletedBy());
		$this->assertSame($restoredFile->fileID, self::FILE_ID);
	}
}

