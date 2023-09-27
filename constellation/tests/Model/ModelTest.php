<?php

declare(strict_types=1);

namespace Constellation\Tests\Model;

use PHPUnit\Framework\TestCase;
use Constellation\Model\Model;
use Constellation\Container\Container;
use Constellation\Database\DB;
use Exception;

class TestModel extends Model
{
    public function __construct(?array $id = null)
    {
        parent::__construct("test", ["id"], $id);
    }
}

/**
 * @class ModelTest
 */
class ModelTest extends TestCase
{
    private $db_config;

    public function setUp(): void
    {
        $this->db_config = [
            "type" => "sqlite",
            "path" => __DIR__ . "/../db/test.db",
        ];
        $container = Container::getInstance();
        $container->setDefinitions([
            DB::class => \DI\autowire()->constructorParameter(
                "config",
                $this->db_config
            ),
        ]);
        $container->build();
    }

    public function testModelFind()
    {
        $m = TestModel::find([1]);
        $this->assertSame("apple", $m->name);
    }

    public function testModelFindAll()
    {
        $m = TestModel::findAll();
        $this->assertTrue(count($m) > 0);
    }

    public function testModelFindByAttribute()
    {
        $m = TestModel::findByAttribute("name", "apple");
        $this->assertSame(1, $m->id);
    }

    public function testModelfindOrFail()
    {
        $m = TestModel::findOrFail([1]);
        $this->assertSame("apple", $m->name);
        $this->expectException(Exception::class);
        $m = TestModel::findOrFail([900000]);
    }

    public function testModelFirst()
    {
        $m = TestModel::first();
        $this->assertSame("apple", $m->name);
    }

    public function testModelPluck()
    {
        $names = TestModel::pluck("name");
        $this->assertSame(["apple", "grape", "orange"], $names);
    }

    public function testModelSelect()
    {
        $m = TestModel::select(["id", "name", "created"]);
        $this->assertSame("SELECT id, name, created FROM test", $m->getQuery());
    }

    public function testModelWhere()
    {
        $m = TestModel::select(["id", "name", "created"])->where(
            ["id = ?", "name = ?"],
            [1, "apple"]
        );
        $this->assertSame(
            "SELECT id, name, created FROM test WHERE id = ? AND name = ?",
            $m->getQuery()
        );
    }

    public function testModelMissingPlaceholderThrowsException()
    {
        $this->expectException(Exception::class);
        TestModel::select(["id", "name", "created"])->where(
            ["id = ?", "name = ?"],
            [1]
        );
    }

    public function testModelOrder()
    {
        $m = TestModel::select(["id", "name", "created"])
            ->where(["id = ?", "name = ?"], [1, "apple"])
            ->order("id DESC");
        $this->assertSame(
            "SELECT id, name, created FROM test WHERE id = ? AND name = ? ORDER BY id DESC",
            $m->getQuery()
        );
    }

    public function testModelHaving()
    {
        $m = TestModel::select(["id", "name", "created"])->having("id > 0");
        $this->assertSame(
            "SELECT id, name, created FROM test HAVING id > 0",
            $m->getQuery()
        );
    }

    public function testModelGroupBy()
    {
        $m = TestModel::select(["id", "name", "created"])->group_by("id");
        $this->assertSame(
            "SELECT id, name, created FROM test GROUP BY id",
            $m->getQuery()
        );
    }

    public function testModelIsInstanceOf()
    {
        $m = TestModel::find([1]);
        $this->assertInstanceOf(TestModel::class, $m);
    }

    public function testModelLoadAttributes()
    {
        $m = new TestModel([1]);
        $this->assertTrue($m->isLoaded());
        $this->assertSame("apple", $m->name);
    }

    public function testModelAlwaysIncludesKey()
    {
        $m = TestModel::select(["name"]);
        $this->assertSame("SELECT name, id FROM test", $m->getQuery());
    }

    public function testModelRun()
    {
        $models = TestModel::select(["name"])
            ->where(["name = ?"], ["apple"])
            ->run();
        $m = $models[0] ?? null;
        $this->assertNotNull($m);
    }
}
