<?php

namespace Publiux\laravelcdn;

use Illuminate\Config\Repository;
use Publiux\laravelcdn\Contracts\CdnHelperInterface;
use Publiux\laravelcdn\Exceptions\MissingConfigurationException;
use Publiux\laravelcdn\Exceptions\MissingConfigurationFileException;

/**
 * Class CdnHelper
 * Helper class containing shared functions.
 *
 * @category General Helper
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 */
class CdnHelper implements CdnHelperInterface
{
    /**
     * An object of the 'Repository' class that allows reading the laravel config files.
     *
     * @var Repository
     */
    protected $configurations;

    public function __construct(Repository $configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * Check if the config file exist and return it or
     * throw an exception.
     *
     * @return array
     *
     * @throws MissingConfigurationFileException
     */
    public function getConfigurations()
    {
        $configurations = $this->configurations->get('cdn');

        if (!$configurations) {
            throw new MissingConfigurationFileException("CDN 'config file' (cdn.php) not found");
        }

        $uploadDirectory = trim($configurations['providers']['aws']['s3']['upload_folder'], ' \/');
        $cdnVersion = trim($configurations['providers']['aws']['s3']['cloudfront']['cdn_version'], ' \/');
        $configurations['providers']['aws']['s3']['upload_folder'] = trim(implode(DIRECTORY_SEPARATOR, [$uploadDirectory, $cdnVersion]), ' \/');

        return $configurations;
    }

    /**
     * Checks for any required configuration is missed.
     *
     * @throws MissingConfigurationException
     */
    public function validate($configuration, $required)
    {
        // search for any null or empty field to throw an exception
        $missing = '';
        foreach ($configuration as $key => $value) {
            if (in_array($key, $required)
                && (empty($value) || null == $value || '' == $value)
            ) {
                $missing .= ' ' . $key;
            }
        }

        if ($missing) {
            throw new MissingConfigurationException('Missed Configuration:' . $missing);
        }
    }

    /**
     * Take url as string and return it parsed object.
     *
     * @return mixed
     */
    public function parseUrl($url)
    {
        return parse_url($url);
    }

    /**
     * check if a string starts with a string.
     *
     * @return bool
     */
    public function startsWith($with, $str)
    {
        return substr($str, 0, strlen($with)) === $with;
    }

    /**
     * remove any extra slashes '/' from the path.
     *
     * @return string
     */
    public function cleanPath($path)
    {
        return rtrim(ltrim($path, '/'), '/');
    }

    /**
     * Sets the CDN version to be used.
     *
     * @param string $version The version to set.
     *
     * @return $this The current instance for method chaining.
     */
    public function setVersion(?string $version): self
    {
        $this->configurations->set('cdn.providers.aws.s3.cloudfront.cdn_version', !is_null($version) ? trim($version, ' \/') : null);

        return $this;
    }
}
