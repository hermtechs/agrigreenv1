<?php

namespace WordProof\SDK\Helpers;

use WordProof\SDK\WordPressSDK;

class ReflectionHelper
{
    /**
     * @param class $instance The class from which to get the name.
     * @return false|string
     */
    public static function name($instance)
    {
        if ($instance instanceof WordPressSDK) {
            $reflector = new \ReflectionClass($instance);
            return $reflector->getName();
        }

        return false;
    }
}
