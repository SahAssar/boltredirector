<?php

namespace Bolt\Extension\Sahassar\Boltredirector\Redirector;

use Bolt\Extension\Sahassar\Boltredirector\Config\Config;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

/**
 * Boltredirector Redirector class.
 *
 * @author Svante Richter <svante.richter@gmail.com>
 */
class Redirector
{
    private $config;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Before handler that handles redirects
     *
     * @param Request     $request
     * @param Application $app
     */
    public function handle(Request $request, Application $app)
    {
        $path = trim($request->getPathInfo(), '/');
        foreach ($this->config->getRedirects() as $redirect) {

            $redirect->prepare();
            $match = $redirect->match($path);

            if ($match === false) {
                $app['logger.flash']->error('Error while parsing the boltredirector <a href="/bolt/file/edit/extensions_config/boltredirector.sahassar.yml">configuration</a>.');
            }

            if ($match) {
                $result = $redirect->getResult($path);
                $status = $redirect->getStatusCode();

                // Only prefix Bolt redirects
                if (!preg_match("~^(https?|ftps?)\://~", $result)) {
                    $result = '/' . $result;
                }

                return $app->redirect($result, $status);
            }
        }
    }
}