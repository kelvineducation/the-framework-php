<?php

namespace The;

class ApiContext extends AppContext
{
    public static function init()
    {
        return new HttpContext(new ApiContext());
    }

    public function handleServerError(\The\ResponseWriterInterface $w, \Throwable $e)
    {
        $honeybadger = option('honeybadger');
        $honeybadger->notify($e);

        $error = [
            'code'  => SERVER_ERROR,
            'error' => "Server error",
        ];
        if (getenv('APP_ENV') === ENV_DEVELOPMENT) {
            $error['details'] = "$e";
        }
        json($w, $error, SERVER_ERROR);
    }

    public function handleNotFound(\The\ResponseWriterInterface $w)
    {
        json($w, [
            'code'  => NOT_FOUND,
            'error' => "Not found",
        ], NOT_FOUND);
    }
}
