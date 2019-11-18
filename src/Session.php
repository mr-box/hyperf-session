<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hf\Session;

use Hf\Session\Handler\SessionHandlerInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This's a data class, please create an new instance for each requests.
 */
class Session extends \ArrayObject implements SessionInterface
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @var
     */
    protected $name;



    /**
     * @var SessionHandlerInterface
     */
    protected $handler;
    /**
     * @var ConfigInterface config
     */
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->handler = $this->buildSessionHandler();
        $this->loadSession();
    }

    public function start(): SessionInterface
    {
        $session = Context::get(SessionInterface::class);
        if (!$session) {
            /** @var Session $session */
            $session = make(Session::class);
            Context::set(SessionInterface::class, $session);
        }
        return $session;
    }

    public function addCookieToResponse(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        if (!$this->parseSessionId($request)) {
            $cookieParams = $this->getCookieParams();
            $cookie = new Cookie(
                $this->getName(),
                $this->getId(),
                $cookieParams['lifetime'],
                $cookieParams['path'],
                $cookieParams['domain'],
                $cookieParams['secure'],
                $cookieParams['httponly'],
                false,
                $cookieParams['samesite']
            );
//            $request->withCookieParams([
//                $cookie->getName() => $cookie->getValue()
//            ]);
//            Context::set(ServerRequestInterface::class, $request);
            $response = $response->withCookie($cookie);
            return Context::set(ResponseInterface::class, $response);
        }
    }

    public function getCookieParams()
    {
        return [
            'lifetime' => $this->config->get('session.cookie_lifetime', 0),
            'path' => $this->config->get('session.cookie_path', '/'),
            'domain' => $this->config->get('session.cookie_domain', ''),
            'secure' => $this->config->get('session.cookie_secure', false),
            'httponly' => $this->config->get('session.cookie_httponly', true),
            'samesite' => $this->config->get('session.cookie_samesite', null),
        ];
    }

    public function status() : int
    {
        if (! $this->config->has('session.handler')) {
            return PHP_SESSION_DISABLED;
        }
        if (! Context::get(SessionInterface::class)) {
            return PHP_SESSION_NONE;
        }
        return PHP_SESSION_ACTIVE;
    }

    /**
     * Returns the session ID.
     *
     * @return string The session ID
     */
    public function getId(): string
    {
        return $this->id || $this->id = session_create_id();
    }

    /**
     * Returns the session name.
     */
    public function getName(): string
    {
        return $this->config->get('session.name', 'HFSESSID');
    }


    /**
     * Load the session data from the handler.
     */
    protected function loadSession(): void
    {
        if ($sessionStr = $this->readFromHandler()) {
            $this->unserialize($sessionStr);
        }
    }

    public function __destruct()
    {
        $this->handler->write($this->getId(), $this->serialize());
    }

    /**
     * Read the session data from the handler.
     */
    protected function readFromHandler(): string
    {
        return $this->handler->read($this->getId());
    }

    protected function parseSessionId(ServerRequestInterface $request): ?string
    {
        $cookies = $request->getCookieParams();
        return isset($cookies[$this->getName()]) ? strval($cookies[$this->getName()]) : null;
    }

    protected function buildSessionHandler(): SessionHandlerInterface
    {
        $handler = $this->config->get('session.handler');
        if (! $handler || ! class_exists($handler)) {
            throw new \InvalidArgumentException('Invalid handler of session');
        }
        return $this->container->get($handler);
    }
}
