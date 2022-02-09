<?php

namespace The;

use Throwable;

class HttpContext extends AppContext
{
    /**
     * @var AppContext $child_context
     */
    private $child_context;

    public function __construct(AppContext $child_context = null)
    {
        parent::__construct();

        $this->child_context = $child_context ?? new AppContext();
    }

    public function configure(App $app)
    {
        $this->child_context->configure($app);

        if ($session = option('session')) {
            $this->startSession($session);
        }
    }

    public function run()
    {
        try {
            $request = new Request();
            option('instrumentor')->startHttpRequest($request);

            $page_url = PageUrl::fromUrl($request->getUri());
            $page_class = $page_url->getPageClass();

            if (!class_exists($page_class)) {
                $response = new Response();

                $this->handleNotFound($response);
                option('instrumentor')->endHttpRequest($response);

                $response->send();

                return;
            }

            $response = new Response();
            $page = call_user_func(["$page_class", 'factory']);
            $page->__invoke($response, $request, $page_url->getPathParams());

            option('instrumentor')->endHttpRequest($response);

            $response->send();
        } catch (Throwable $e) {
            $this->defaultErrorHandler($e);
        }
    }

    public function defaultErrorHandler(Throwable $e)
    {
        $w = new Response();
        $w->withStatus(500);

        $this->handleServerError($w, $e);
        option('instrumentor')->endHttpRequest($w);

        $w->send();
    }

    private function handleServerError(ResponseWriterInterface $w, Throwable $e)
    {
        if (method_exists($this->child_context, 'handleServerError')) {
            $this->child_context->handleServerError($w, $e);
        }
    }

    private function handleNotFound(ResponseWriterInterface $w)
    {
        if (method_exists($this->child_context, 'handleNotFound')) {
            $this->child_context->handleNotFound($w);
        }
    }

    private function startSession(array $session)
    {
        if (isset($_COOKIE[$session['name']])) {
            setcookie(
                $session['name'],
                $_COOKIE[$session['name']],
                time() + $session['lifetime'],
                $session['path'],
                $session['domain'],
                $session['secure'],
                $session['httponly']
            );
        }
        session_start([
            'name'            => $session['name'],
            'cookie_lifetime' => $session['lifetime'],
            'cookie_path'     => $session['path'],
            'cookie_domain'   => $session['domain'],
            'cookie_secure'   => $session['secure'],
            'cookie_httponly' => $session['httponly'],
            'cache_limiter'   => '',
            'gc_maxlifetime'  => $session['lifetime'] * 3,
        ]);
    }
}
