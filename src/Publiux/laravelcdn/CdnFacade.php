<?php

namespace Publiux\laravelcdn;

use Illuminate\Support\Facades\Request;
use Publiux\laravelcdn\Contracts\CdnFacadeInterface;
use Publiux\laravelcdn\Contracts\CdnHelperInterface;
use Publiux\laravelcdn\Contracts\ProviderFactoryInterface;
use Publiux\laravelcdn\Exceptions\EmptyPathException;
use Publiux\laravelcdn\Providers\Contracts\ProviderInterface;
use Publiux\laravelcdn\Validators\CdnFacadeValidator;

/**
 * Class CdnFacade.
 *
 * @category
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 */
class CdnFacade implements CdnFacadeInterface
{
    /**
     * @var array
     */
    protected $configurations;

    /**
     * @var ProviderFactoryInterface
     */
    protected $provider_factory;

    /**
     * instance of the default provider object.
     *
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * @var CdnHelperInterface
     */
    protected $helper;

    /**
     * @var CdnFacadeValidator
     */
    protected $cdn_facade_validator;

    /**
     * Calls the provider initializer.
     */
    public function __construct(
        ProviderFactoryInterface $provider_factory,
        CdnHelperInterface $helper,
        CdnFacadeValidator $cdn_facade_validator
    ) {
        $this->provider_factory     = $provider_factory;
        $this->helper               = $helper;
        $this->cdn_facade_validator = $cdn_facade_validator;

        $this->init();
    }

    /**
     * this function will be called from the 'views' using the
     * 'Cdn' facade {{Cdn::asset('')}} to convert the path into
     * it's CDN url.
     *
     * @return mixed
     *
     * @throws EmptyPathException
     */
    public function asset($path)
    {
        // if asset always append the public/ dir to the path (since the user should not add public/ to asset)
        return $this->generateUrl($path, 'public/');
    }

    /**
     * this function will be called from the 'views' using the
     * 'Cdn' facade {{Cdn::mix('')}} to convert the Laravel 5.4 webpack mix
     * generated file path into it's CDN url.
     *
     * @return mixed
     *
     * @throws Exceptions\EmptyPathException, \InvalidArgumentException
     */
    public function mix($path)
    {
        static $manifest = null;
        if (is_null($manifest)) {
            $manifest = json_decode(file_get_contents(public_path('mix-manifest.json')), true);
        }
        if (isset($manifest['/' . $path])) {
            return $this->generateUrl($manifest['/' . $path], 'public/');
        }
        if (isset($manifest[$path])) {
            return $this->generateUrl($manifest[$path], 'public/');
        }

        throw new \InvalidArgumentException("File {$path} not defined in asset manifest.");
    }

    /**
     * this function will be called from the 'views' using the
     * 'Cdn' facade {{Cdn::elixir('')}} to convert the elixir generated file path into
     * it's CDN url.
     *
     * @return mixed
     *
     * @throws Exceptions\EmptyPathException, \InvalidArgumentException
     */
    public function elixir($path)
    {
        static $manifest = null;
        if (is_null($manifest)) {
            $manifest = json_decode(file_get_contents(public_path('build/rev-manifest.json')), true);
        }
        if (isset($manifest[$path])) {
            return $this->generateUrl('build/' . $manifest[$path], 'public/');
        }

        throw new \InvalidArgumentException("File {$path} not defined in asset manifest.");
    }

    /**
     * this function will be called from the 'views' using the
     * 'Cdn' facade {{Cdn::path('')}} to convert the path into
     * it's CDN url.
     *
     * @return mixed
     *
     * @throws EmptyPathException
     */
    public function path($path)
    {
        return $this->generateUrl($path);
    }

    /**
     * Read the configuration file and pass it to the provider factory
     * to return an object of the default provider specified in the
     * config file.
     */
    private function init()
    {
        // return the configurations from the config file
        $this->configurations = $this->helper->getConfigurations();

        // return an instance of the corresponding Provider concrete according to the configuration
        $this->provider = $this->provider_factory->create($this->configurations);
    }

    /**
     * check if package is surpassed or not then
     * prepare the path before generating the url.
     *
     * @param string $prepend
     *
     * @return mixed
     */
    private function generateUrl($path, $prepend = '')
    {
        // if the package is surpassed, then return the same $path
        // to load the asset from the localhost
        if (isset($this->configurations['bypass']) && $this->configurations['bypass']) {
            return Request::root() . '/' . $path;
        }

        if (!isset($path)) {
            throw new EmptyPathException('Path does not exist.');
        }

        // Add version number
        // $path = str_replace(
        //    "build",
        //    $this->configurations['providers']['aws']['s3']['version'],
        //    $path
        // );

        // remove slashes from begging and ending of the path
        // and append directories if needed
        $clean_path = $prepend . $this->helper->cleanPath($path);

        // call the provider specific url generator
        return $this->provider->urlGenerator($clean_path);
    }
}
