<?php

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Mockery as m;
use Recca0120\Terminal\Application as Artisan;
use Recca0120\Terminal\Console\Commands\Find;
use Symfony\Component\Finder\Finder;

class FindTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    protected function getArtisan()
    {
        $events = m::mock(Dispatcher::class);
        $app = m::mock(Application::class.','.ArrayAccess::class);
        $request = m::mock(Request::class);

        $request->shouldReceive('ajax')->andReturn(true);
        $events->shouldReceive('fire');

        $app
            ->shouldReceive('offsetGet')->with('request')->andReturn($request)
            ->shouldReceive('basePath')->andReturn(__DIR__)
            ->shouldReceive('storagePath')->andReturn(__DIR__);

        return new Artisan($app, $events, 'testing');
    }

    public function test_handle()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $artisan = $this->getArtisan();
        $finder = m::mock(Finder::class);
        $command = new Find();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $artisan->add($command);
        $finder
            ->shouldReceive('in')->with(__DIR__)
            ->shouldReceive('name')->with('*')
            ->shouldReceive('depth')->with('<1')
            ->shouldReceive('files')
            ->shouldReceive('getIterator')->andReturn(new AppendIterator());

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $artisan->getLaravel()->shouldReceive('call')->andReturnUsing(function () use ($command) {
            $finder = m::mock(Finder::class)
                ->shouldReceive('in')->with(__DIR__)
                ->shouldReceive('name')->with('*')
                ->shouldReceive('depth')->with('<1')
                ->shouldReceive('files')
                ->shouldReceive('getIterator')->andReturn(new AppendIterator())
                ->mock();
            $filesystem = m::mock(Filesystem::class);
            $command->handle($finder, $filesystem);
        });

        $artisan->call('find ./ -name * -type f -maxdepth 1 -delete');
    }

    public function test_handle_directory()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $artisan = $this->getArtisan();
        $finder = m::mock(Finder::class);
        $command = new Find();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $artisan->add($command);
        $finder
            ->shouldReceive('in')->with(__DIR__)
            ->shouldReceive('name')->with('*')
            ->shouldReceive('depth')->with('<1')
            ->shouldReceive('directories')
            ->shouldReceive('getIterator')->andReturn(new AppendIterator());

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $artisan->getLaravel()->shouldReceive('call')->andReturnUsing(function () use ($command, $finder) {
            $filesystem = m::mock(Filesystem::class);
            $command->handle($finder, $filesystem);
        });

        $artisan->call('find ./ -name * -type d -maxdepth 0 -delete');
    }
}
