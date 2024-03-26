<?php

namespace Publiux\laravelcdn\Tests;

use Illuminate\Support\Collection;
use Mockery as M;
use Publiux\laravelcdn\Asset;
use Publiux\laravelcdn\Finder;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

/**
 * Class FinderTest.
 *
 * @category Test
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 *
 * @internal
 *
 * @coversNothing
 */
class FinderTest extends TestCase
{
    public function tearDown(): void
    {
        M::close();
        parent::tearDown();
    }

    public function testReadReturnCorrectDataType()
    {
        $asset_holder = new Asset();

        $asset_holder->init([
            'include' => [
                'directories' => [__DIR__],
            ],
        ]);

        $console_output = M::mock('Symfony\Component\Console\Output\ConsoleOutput');
        $console_output->shouldReceive('writeln')
            ->atLeast(1)
        ;

        $finder = new Finder($console_output);

        $result = $finder->read($asset_holder);

        $this->assertInstanceOf('Symfony\Component\Finder\SplFileInfo', $result->first());

        $this->assertEquals($result, new Collection($result->all()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReadThrowsException()
    {
        $asset_holder = new Asset();

        $asset_holder->init(['include' => []]);

        $console_output = M::mock('Symfony\Component\Console\Output\ConsoleOutput');
        $console_output->shouldReceive('writeln')
            ->atLeast(1)
        ;

        $this->expectException(DirectoryNotFoundException::class);

        $finder = new Finder($console_output);

        $finder->read($asset_holder);
    }
}
