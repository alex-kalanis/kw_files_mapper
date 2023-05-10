<?php

namespace MapperNoRootTests;


use CommonTestClass;
use kalanis\kw_files\Interfaces\IProcessDirs;
use kalanis\kw_files\Interfaces\IProcessFiles;
use kalanis\kw_files\Interfaces\IProcessNodes;
use kalanis\kw_files_mapper\Processing\MapperNoRoot\ProcessDir;
use kalanis\kw_files_mapper\Processing\MapperNoRoot\ProcessFile;
use kalanis\kw_files_mapper\Processing\MapperNoRoot\ProcessNode;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Storage\Database\Config;
use kalanis\kw_mapper\Storage\Database\ConfigStorage;
use kalanis\kw_mapper\Storage\Database\DatabaseSingleton;
use kalanis\kw_mapper\Storage\Database\PDO\SQLite;
use PDO;


abstract class AStorageTest extends CommonTestClass
{
    /** @var null|SQLite */
    protected $database = null;
    /** @var bool */
    protected $skipIt = false;

    /**
     * @throws MapperException
     */
    protected function setUp(): void
    {
        $skipIt = getenv('SQSKIP');
        $this->skipIt = false !== $skipIt && boolval(intval(strval($skipIt)));

        $conf = Config::init()->setTarget(
            IDriverSources::TYPE_PDO_SQLITE,
            'test_sqlite_local',
            ':memory:',
            0,
            null,
            null,
            ''
        );
        $conf->setParams(86000, true);
        ConfigStorage::getInstance()->addConfig($conf);
        $this->database = DatabaseSingleton::getInstance()->getDatabase($conf);
        $this->database->addAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    protected function getNodeLib(): IProcessNodes
    {
        return new ProcessNode(new SQLiteTestRecord());
    }

    protected function getNodeFailLib(): IProcessNodes
    {
        return new XFailProcessNode(new SQLiteTestRecord());
    }

    protected function getFileLib(): IProcessFiles
    {
        return new ProcessFile(new SQLiteTestRecord());
    }

    protected function getFileFailLib(): IProcessFiles
    {
        return new XFailProcessFile(new SQLiteTestRecord());
    }

    protected function getFileFailRecLib(): IProcessFiles
    {
        return new ProcessFile(new XFailSaveRecord());
    }

    protected function getDirLib(): IProcessDirs
    {
        return new ProcessDir(new SQLiteTestRecord());
    }

    protected function getDirFailLib(): IProcessDirs
    {
        return new XFailProcessDir(new SQLiteTestRecord());
    }

    /**
     * @throws MapperException
     */
    protected function dataRefill(): void
    {
        $this->assertTrue($this->database->exec($this->dropTable(), []));
        $this->assertTrue($this->database->exec($this->basicTable(), []));
        $this->assertTrue($this->database->exec($this->fillTable(), []));
        $this->assertEquals(12, $this->database->rowCount());
    }

    /**
     * @throws MapperException
     */
    protected function dataClear(): void
    {
        $this->assertTrue($this->database->exec($this->dropTable(), []));
        $this->assertTrue($this->database->exec($this->basicTable(), []));
    }

    protected function dropTable(): string
    {
        return 'DROP TABLE IF EXISTS "my_local_data"';
    }

    protected function basicTable(): string
    {
        return 'CREATE TABLE IF NOT EXISTS "my_local_data" (
  "md_id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "md_parent" INTEGER NULL,
  "md_name" TEXT NULL,
  "md_content" TEXT NULL,
  "md_created" INTEGER NULL
)';
    }

    protected function fillTable(): string
    {
        return 'INSERT INTO "my_local_data" ("md_id", "md_parent", "md_name", "md_content", "md_created") VALUES
-- ( 1, null, "",           "' . IProcessNodes::STORAGE_NODE_KEY . '", null), -- /data/tree
-- ( 2,  1,   "data",       "' . IProcessNodes::STORAGE_NODE_KEY . '", 123456789),
-- ( 3,  2,   "tree",       "' . IProcessNodes::STORAGE_NODE_KEY . '", 123456789),
( 4,  null,   "last_one",   "' . IProcessNodes::STORAGE_NODE_KEY . '", 123456789),
( 5,  4,   ".gitkeep",   "123456", 123456789),
( 6,  null,   "next_one",   "' . IProcessNodes::STORAGE_NODE_KEY . '", 123456789),
( 7,  6,   "sub_one",    "' . IProcessNodes::STORAGE_NODE_KEY . '", 123456789),
( 8,  7,   ".gitkeep",   "789123", 123456789),
( 9,  null,   "sub",        "' . IProcessNodes::STORAGE_NODE_KEY . '", 123456789),
(10,  9,   "dummy3.txt", "qwertzuiopasdfghjklyxcvbnm0123456789", 123456789),
(11,  9,   "dummy4.txt", "qwertzuiopasdfghjklyxcvbnm0123456789", 123456789),
(12,  null,   "dummy1.txt", "qwertzuiopasdfghjklyxcvbnm0123456789", 123456789),
(13,  null,   "dummy2.txt", "qwertzuiopasdfghjklyxcvbnm0123456789", 123456789),
(14,  null,   "other1.txt", "qwertzuiopasdfghjklyxcvbnm0123456789", 123456789),
(20,  null,   "other2.txt", "qwertzuiopasdfghjklyxcvbnm0123456789", 123456789)
';
    }
}


/**
 * Class SQLiteTestRecord
 * @property int $id
 * @property int $parentId
 * @property string $name
 * @property string $content
 * @property SQLiteTestRecord[] $pars
 */
class SQLiteTestRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 1024);
        $this->addEntry('parentId', IEntryType::TYPE_INTEGER, 1024);
        $this->addEntry('name', IEntryType::TYPE_STRING, 255);
        $this->addEntry('content', IEntryType::TYPE_STRING, PHP_INT_MAX);
        $this->addEntry('created', IEntryType::TYPE_INTEGER, 999999999);
        $this->addEntry('pars', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper(SQLiteTestMapper::class);
    }
}


class SQLiteTestMapper extends ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('test_sqlite_local');
        $this->setTable('my_local_data');
        $this->setRelation('id', 'md_id');
        $this->setRelation('parentId', 'md_parent');
        $this->setRelation('name', 'md_name');
        $this->setRelation('content', 'md_content');
        $this->setRelation('created', 'md_created');
        $this->addPrimaryKey('id');
        $this->addForeignKey('pars', SQLiteTestRecord::class, 'parentId', 'id');
    }
}


class XFailSaveRecord extends SQLiteTestRecord
{
    protected function addEntries(): void
    {
        parent::addEntries();
        $this->setMapper(SQLiteFailSaveTestMapper::class);
    }
}


class SQLiteFailSaveTestMapper extends SQLiteTestMapper
{
    protected function beforeSave(ARecord $record): bool
    {
        return false;
    }
}


class XFailProcessDir extends ProcessDir
{
    protected function getLookupRecord(): ARecord
    {
        throw new MapperException('mock');
    }
}


class XFailProcessFile extends ProcessFile
{
    protected function getLookupRecord(): ARecord
    {
        throw new MapperException('mock');
    }
}


class XFailProcessNode extends ProcessNode
{
    protected function getLookupRecord(): ARecord
    {
        throw new MapperException('mock');
    }
}
