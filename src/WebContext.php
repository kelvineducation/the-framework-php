<?php

namespace K;

class WebContext extends AppContext
{
    public static function init()
    {
        return new HttpContext(new WebContext());
    }

    public function configure(App $app)
    {
        ini_set('session.save_handler', option('session_save_handler'));
        ini_set('session.save_path', option('session_save_path'));
        option('session', [
            'lifetime' => strtotime("2 hours") - time(),
            'path'     => '/',
            'domain'   => '',
            'secure'   => true,
            'httponly' => true,
            'name'     => 'K',
        ]);
    }

    public function handleServerError(\K\ResponseWriterInterface $w, \Throwable $e)
    {
        $honeybadger = option('honeybadger');
        $honeybadger->notify($e);
        html($w, 'error.phtml', 'error_layout.phtml', [
            'e'      => $e,
            'is_dev' => getenv('APP_ENV') === ENV_DEVELOPMENT
        ], SERVER_ERROR);
    }

    public function handleNotFound(\K\ResponseWriterInterface $w)
    {
        html($w, 'not_found.phtml', 'error_layout.phtml', []);
    }
}
