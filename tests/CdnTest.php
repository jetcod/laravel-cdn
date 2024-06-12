<?php

namespace Publiux\laravelcdn\Tests;

use Mockery as M;
use Publiux\laravelcdn\CdnServiceProvider;
use Publiux\laravelcdn\Providers\AwsS3Provider;

/**
 * Class CdnTest.
 *
 * @category Test
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 *
 * @internal
 *
 * @coversNothing
 */
class CdnTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        M::close();
        parent::tearDown();
    }

    public function testPushCommandReturnTrue()
    {
        $m_asset = M::mock('Publiux\laravelcdn\Contracts\AssetInterface');
        $m_asset->shouldReceive('init')->once()->andReturnSelf();
        $m_asset->shouldReceive('setAssets')->once();

        $m_asset->shouldReceive('getAssets')->once()->andReturn(collect());

        $m_finder = M::mock('Publiux\laravelcdn\Contracts\FinderInterface');
        $m_finder->shouldReceive('read')->once()->with($m_asset)->andReturn(collect());

        $m_provider = M::mock('Publiux\laravelcdn\Providers\Provider');
        $m_provider->shouldReceive('upload')->once()->andReturnTrue();

        $m_provider_factory = M::mock('Publiux\laravelcdn\Contracts\ProviderFactoryInterface');
        $m_provider_factory->shouldReceive('create')->once()->andReturn($m_provider);

        $m_helper = M::mock('Publiux\laravelcdn\Contracts\CdnHelperInterface');
        $m_helper->shouldReceive('getConfigurations')->twice()->andReturn([]);

        $this->app->bind('Publiux\laravelcdn\Contracts\AssetInterface', function () use ($m_asset) {
            return $m_asset;
        });

        $this->app->bind('Publiux\laravelcdn\Contracts\FinderInterface', function () use ($m_finder) {
            return $m_finder;
        });

        $this->app->bind('Publiux\laravelcdn\Contracts\ProviderFactoryInterface', function () use ($m_provider_factory) {
            return $m_provider_factory;
        });

        $this->app->bind('Publiux\laravelcdn\Contracts\CdnHelperInterface', function () use ($m_helper) {
            return $m_helper;
        });

        $this->artisan('cdn:push', ['--no-interaction' => true])
            ->expectsOutput('Your assets will be uploaded to the root of CDN path.')
            ->assertExitCode(0)
        ;
    }

    public function testPushCommand()
    {
        $m_console = M::mock('Symfony\Component\Console\Output\ConsoleOutput');
        $m_console->shouldReceive('writeln')->atLeast(2);

        $m_validator = M::mock('Publiux\laravelcdn\Validators\Contracts\ProviderValidatorInterface');
        $m_validator->shouldReceive('validate');

        $m_helper = M::mock('Publiux\laravelcdn\CdnHelper');

        $p_aws_s3_provider = M::mock('Publiux\laravelcdn\Providers\AwsS3Provider[connect]', [
            $m_console,
            $m_validator,
            $m_helper,
        ]);

        $m_s3 = M::mock('Aws\S3\S3Client')->shouldIgnoreMissing();
        $m_s3->shouldReceive('factory')->andReturn('Aws\S3\S3Client');
        $m_command = M::mock('Aws\Command');
        $m_s3->shouldReceive('getCommand')->andReturn($m_command);
        $m_s3->shouldReceive('execute');

        $p_aws_s3_provider->setS3Client($m_s3);

        $p_aws_s3_provider->shouldReceive('connect')->andReturn(true);

        $this->app->bind(AwsS3Provider::class, function () use ($p_aws_s3_provider) {
            return $p_aws_s3_provider;
        });

        $this->artisan('cdn:push', ['--no-interaction' => true])
            ->expectsOutput('Your assets will be uploaded to the root of CDN path.')
            ->assertExitCode(0)
        ;
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cdn', [
            'bypass'    => false,
            'default'   => 'AwsS3',
            'url'       => 'https://s3.amazonaws.com',
            'threshold' => 10,
            'providers' => [
                'aws' => [
                    's3' => [
                        'region'  => 'us-standard',
                        'version' => 'latest',
                        'upload_folder' => '',
                        'buckets' => [
                            'my-bucket-name' => '*',
                        ],
                        'acl'        => 'public-read',
                        'cloudfront' => [
                            'use'     => false,
                            'cdn_url' => '',
                            'cdn_version' => '',
                        ],
                        'metadata' => [],

                        'expires' => gmdate('D, d M Y H:i:s T', strtotime('+5 years')),

                        'cache-control' => 'max-age=2628000',

                        'version' => '',
                    ],
                ],
            ],
            'include' => [
                'directories' => [__DIR__],
                'extensions'  => [],
                'patterns'    => [],
            ],
            'exclude' => [
                'directories' => [],
                'files'       => [],
                'extensions'  => [],
                'patterns'    => [],
                'hidden'      => true,
            ],
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [CdnServiceProvider::class];
    }
}
