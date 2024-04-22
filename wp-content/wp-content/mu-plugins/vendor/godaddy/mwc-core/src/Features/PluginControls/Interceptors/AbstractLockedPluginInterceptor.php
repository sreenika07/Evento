<?php

namespace GoDaddy\WordPress\MWC\Core\Features\PluginControls\Interceptors;

use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;

/**
 * Base class for interceptors used to prevent users from modifying locked plugins.
 */
abstract class AbstractLockedPluginInterceptor extends AbstractInterceptor
{
    /**
     * Determines whether the plugin with the given basename is one of the locked plugins.
     *
     * @param string $basename
     *
     * @return bool
     */
    public function isPluginLocked(string $basename) : bool
    {
        foreach ($this->getLockedPlugins() as $plugin) {
            if ($basename === ArrayHelper::get((array) $plugin, 'basename')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the information for the locked plugins.
     *
     * @return array<mixed>
     */
    protected function getLockedPlugins() : array
    {
        return (array) Configuration::get('wordpress.plugins.locked');
    }

    /**
     * Gets the name of the plugin with the given basename.
     *
     * If the plugin with the given basename is not a locked plugin, the method returns null.
     *
     * @param string $basename
     *
     * @return string|null
     */
    protected function getLockedPluginName(string $basename) : ?string
    {
        foreach ($this->getLockedPlugins() as $plugin) {
            if ($basename === ArrayHelper::get((array) $plugin, 'basename')) {
                $pluginName = ArrayHelper::get((array) $plugin, 'name', '');

                return is_string($pluginName) ? $pluginName : '';
            }
        }

        return null;
    }

    /**
     * Gets a comma separated list of plugins that have one of the given basenames and are locked.
     *
     * @param string[] $basenames
     *
     * @return string|null
     */
    protected function prepareLockedPluginNames(array $basenames) : ?string
    {
        $names = array_filter(array_map(fn ($basename) => $this->getLockedPluginName($basename), $basenames));

        return empty($names) ? null : implode(', ', $names);
    }

    /**
     * A convenience wrapper for wp_die().
     *
     * @param string $message
     * @return void
     */
    protected function die(string $message) : void
    {
        if (function_exists('wp_die')) {
            wp_die($message);
        }

        die($message);
    }
}
