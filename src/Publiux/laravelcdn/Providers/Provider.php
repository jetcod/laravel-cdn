<?php

namespace Publiux\laravelcdn\Providers;

use Publiux\laravelcdn\Providers\Contracts\ProviderInterface;

/**
 * Class Provider.
 *
 * @category Drivers Abstract Class
 *
 * @author   Mahmoud Zalt <mahmoud@vinelab.com>
 */
abstract class Provider implements ProviderInterface
{
    /**
     * @var Instance of the console object
     */
    public $console;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $region;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $url;

    abstract public function upload($assets);
}
