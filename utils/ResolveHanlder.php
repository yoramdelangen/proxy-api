<?php

namespace Utils;

use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\HandlerResolverInterface;

class ResolveHanlder implements HandlerResolverInterface
{
    protected $router;

    public function __construct(RouteCollector $router)
    {
        $this->router = $router;
    }

    /**
     * Create an instance of the given handler.
     *
     * @param $handler
     *
     * @return array
     */
    public function resolve($handler)
    {
        if (\defined($handler[0].'::ACCESS_TOKEN')) {
            $tokens = $handler[0]::ACCESS_TOKEN;

            if ($tokens) {
            	$valid = $this->checkHeader($handler[0], $tokens);

            	if (!$valid) {
            		// 403 header was already sent.
            		return function () {
            			return ['err' => 'noop access'];
            		};
            	}
            }
        }

        if (\is_array($handler) && \is_string($handler[0])) {
            $handler[0] = new $handler[0]();
        }

        return $handler;
    }

    protected function checkHeader(string $class, array $tokens)
    {
    	$token = $_SERVER['X-TOKEN'] ?? $_SERVER['HTTP_X_TOKEN'] ?? null;

    	// in case the X-TOKEN does not exist
        if ($token === null || !in_array($token, $tokens, true)) {
            header('HTTP/1.0 403 Forbidden');
            return false;
        }

        return true;
    }
}
