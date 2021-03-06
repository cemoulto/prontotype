<?php

namespace Prontotype\Service;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;

class Prototype implements ServiceProviderInterface
{

    protected $app;

    public function register ( Application $app )
    {
        $this->app = $app;

        $config = array();

        $filename = DOC_ROOT . "/prototypes.yml";

        if (!file_exists($filename)) {
            // default to base prototype
            $app['prototype'] = array(
                'domain' => 'default',
                'base_path' => '.',
            );

            return;
        }

        $config = Yaml::parse($filename);

        if (null === $config) {
            throw new \InvalidArgumentException(sprintf("The config file '%s' appears to be invalid YAML.", $filename));
        }

        $app['prototypes'] = $config['prototypes'];

        $host = $_SERVER['HTTP_HOST'];

        // set default prototype
        $current = $app['prototypes']['default'];

        // identify current prototype
        foreach ($app['prototypes'] as $key => $prototype) {
            if ($prototype['domain'] == $host) {
                $current = $prototype;
                $current['key'] = $key;
            }
        }

        foreach (array('domain', 'base_path') as $param) {
            if (!isset($current[$param])) {
                throw new \InvalidArgumentException(sprintf("The configuration of prototype '%s' in file '%s' misses '%s' key.", $current['key'], $filename, $param));
            }
        }

        $app['prototype'] = $current;
    }

    public function boot ( Application $app ) {}

}
