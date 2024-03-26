<?php

namespace Publiux\laravelcdn\Tests;

use Mockery as M;

/**
 * Class ProviderFactoryTest.
 *
 * @category Test
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 */
class ProviderFactoryTest extends TestCase
{

    public function tearDown(): void
    {
        M::close();
        parent::tearDown();
    }

    public function testCreateReturnCorrectProviderObject()
    {
        $configurations = ['default' => 'AwsS3'];

        $mockAwsS3Prvoider = M::mock(\Publiux\laravelcdn\Providers\AwsS3Provider::class);
        $this->app->instance(\Publiux\laravelcdn\Providers\AwsS3Provider::class, $mockAwsS3Prvoider);

        $providerFactory = app()->make(\Publiux\laravelcdn\ProviderFactory::class);

        $mockAwsS3Prvoider->shouldReceive('init')
            ->with($configurations)
            ->once()
            ->andReturnSelf()
        ;

        $provider = $providerFactory->create($configurations);

        $this->assertEquals($provider, $mockAwsS3Prvoider);
    }

    /**
     * @expectedException \Publiux\laravelcdn\Exceptions\MissingConfigurationException
     */
    public function testCreateThrowsExceptionWhenMissingDefaultConfiguration()
    {
        $configurations = ['default' => ''];

        $mockAwsS3Prvoider = M::mock(\Publiux\laravelcdn\Providers\AwsS3Provider::class);
        $providerFactory = app()->make(\Publiux\laravelcdn\ProviderFactory::class);

        $this->expectException(\Publiux\laravelcdn\Exceptions\MissingConfigurationException::class);

        $providerFactory->create($configurations);
    }
}
