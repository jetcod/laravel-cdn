<?php

namespace Publiux\laravelcdn\Validators;

use Publiux\laravelcdn\Exceptions\MissingConfigurationException;
use Publiux\laravelcdn\Validators\Contracts\ProviderValidatorInterface;

/**
 * Class ProviderValidator.
 *
 * @category
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 */
class ProviderValidator extends Validator implements ProviderValidatorInterface
{
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
}
