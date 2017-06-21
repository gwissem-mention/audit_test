<?php

namespace Search\Service;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Yaml;

/**
 * Global query settings
 *
 */
final class QueryConfigurator
{
    protected $config;

    public function __construct()
    {
        $this->config = Yaml::parse(file_get_contents(__DIR__.'/../../../etc/config.yaml'));
    }

    public function get($config, $type = "global")
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $key = $this->parseKey($type . '.' . $config);

        $value = $accessor->getValue($this->config, $key);

        if (null === $value && "global" !== $type) {
            return $this->get($config);
        }

        return $value;
    }

    private function parseKey($key)
    {
        $key = explode('.', $key);
        return implode("", array_map(function ($key) {
            return "[$key]";
        }, $key));
    }
}
