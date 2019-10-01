<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema;

use Iola\Api\Contract\Schema\DataLoaderInterface;
use Overblog\DataLoader\DataLoader as OverblogDataLoader;

class DataLoaderDecorator implements DataLoaderInterface
{
    /**
     * @var OverblogDataLoader
     */
    protected $loader;

    /**
     * @var callable
     */
    protected $keyBuilder;

    public function __construct(OverblogDataLoader $loader, callable $keyBuilder)
    {
        $this->loader = $loader;
        $this->keyBuilder = $keyBuilder;
    }

    protected function buildKey($key, array $args = []) {
        return call_user_func($this->keyBuilder, $key, $args);
    }

    public function load($key, array $args = [])
    {
        return $this->loader->load(
            $this->buildKey($key, $args)
        );
    }

    public function loadMany($keys, array $args = [])
    {
        return $this->loader->loadMany(array_map(function($key) use($args) {
            return $this->buildKey($key, $args);
        }, $keys));
    }

    public function clear($key, array $args = [])
    {
        $this->loader->clear(
            $this->buildKey($key, $args)
        );

        return $this;
    }

    public function clearAll()
    {
        $this->loader->clearAll();

        return $this;
    }

    public function prime($key, $value, array $args = []) {
        $this->loader->prime(
            $this->buildKey($key, $args),
            $value
        );

        return $this;
    }
}