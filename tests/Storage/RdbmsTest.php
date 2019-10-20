<?php

namespace Test\Storage;

use B8\B8;
use B8\Degenerator\Config;
use B8\Factory;
use B8\Storage\Rdbms;
use B8\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

class RdbmsTest extends BaseTest
{
    protected function setUp()
    {
        $this->path = "/tmp/sqlite.db";
        $this->tearDown();
        copy(__DIR__ . "/../db/sqlite.db", $this->path);

        $degenerator = Factory::getInstance(
            Factory::Degenerator,
            "Standard",
            (new Config())
                ->setMultibyte(true)
        );

        $uri = new \ByJG\Util\Uri("sqlite://" . $this->path);
        $this->storage = new Rdbms(
            $uri,
            $degenerator
        );
    }
}
