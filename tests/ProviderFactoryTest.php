<?php

namespace Publiux\laravelcdn\Tests;

use Mockery as M;
use Publiux\laravelcdn\Exceptions\MissingConfigurationException;
use Publiux\laravelcdn\ProviderFactory;
use Publiux\laravelcdn\Providers\AwsS3Provider;

/**
 * Class ProviderFactoryTest.
 *
 * @category Test
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 *
 * @internal
 *
 * @coversNothing
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

        $mockAwsS3Prvoider = M::mock(AwsS3Provider::class);
        $this->app->instance(AwsS3Provider::class, $mockAwsS3Prvoider);

        $providerFactory = app()->make(ProviderFactory::class);

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

        $mockAwsS3Prvoider = M::mock(AwsS3Provider::class);
        $providerFactory   = app()->make(ProviderFactory::class);

        $this->expectException(MissingConfigurationException::class);

        $providerFactory->create($configurations);
    }
}
