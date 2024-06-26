<?php

namespace Publiux\laravelcdn\Tests;

use Mockery as M;
use Publiux\laravelcdn\CdnFacade;
use Publiux\laravelcdn\Exceptions\EmptyPathException;
use Publiux\laravelcdn\Validators\CdnFacadeValidator;

/**
 * Class CdnFacadeTest.
 *
 * @category Test
 *
 * @author   Mahmoud Zalt <mahmoud@vinelab.com>
 *
 * @internal
 *
 * @coversNothing
 */
class CdnFacadeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $configuration_file = [
            'bypass'    => false,
            'default'   => 'AwsS3',
            'url'       => 'https://s3.amazonaws.com',
            'threshold' => 10,
            'providers' => [
                'aws' => [
                    's3' => [
                        'upload_folder' => '',
                        'region'  => 'rrrrrrrrrrrgggggggggnnnnn',
                        'version' => 'vvvvvvvvssssssssssnnnnnnn',
                        'buckets' => [
                            'bbbuuuucccctttt' => '*',
                        ],
                        'acl'        => 'public-read',
                        'cloudfront' => [
                            'use'     => false,
                            'cdn_url' => '',
                        ],
                        'version' => '1',
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
        ];

        $this->asset_path = 'foo/bar.php';
        $this->path_path  = 'public/foo/bar.php';
        $this->asset_url  = 'https://bucket.s3.amazonaws.com/public/foo/bar.php';

        $this->provider = M::mock('Publiux\laravelcdn\Providers\AwsS3Provider');

        $this->provider_factory = M::mock('Publiux\laravelcdn\Contracts\ProviderFactoryInterface');
        $this->provider_factory->shouldReceive('create')->once()->andReturn($this->provider);

        $this->helper = M::mock('Publiux\laravelcdn\Contracts\CdnHelperInterface');
        $this->helper->shouldReceive('getConfigurations')->once()->andReturn($configuration_file);
        $this->helper->shouldReceive('cleanPath')->andReturn($this->asset_path);
        $this->helper->shouldReceive('startsWith')->andReturn(true);

        $this->validator = new CdnFacadeValidator();

        $this->facade = new CdnFacade(
            $this->provider_factory,
            $this->helper,
            $this->validator
        );
    }

    public function tearDown(): void
    {
        M::close();
        parent::tearDown();
    }

    public function testAssetIsCallingUrlGenerator()
    {
        $this->provider->shouldReceive('urlGenerator')
            ->once()
            ->andReturn($this->asset_url)
        ;

        $result = $this->facade->asset($this->asset_path);
        // assert is calling the url generator
        $this->assertEquals($result, $this->asset_url);
    }

    public function testPathIsCallingUrlGenerator()
    {
        $this->provider->shouldReceive('urlGenerator')
            ->once()
            ->andReturn($this->asset_url)
        ;

        $result = $this->facade->asset($this->path_path);
        // assert is calling the url generator
        $this->assertEquals($result, $this->asset_url);
    }

    /**
     * @expectedException \Publiux\laravelcdn\Exceptions\EmptyPathException
     */
    public function testUrlGeneratorThrowsException()
    {
        $this->expectException(EmptyPathException::class);

        $this->invokeMethod($this->facade, 'generateUrl', [null, null]);
    }
}
