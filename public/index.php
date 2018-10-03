<?php

require_once __DIR__ . '/../bootstrap.php';

use function K\{html, option};
use K\{App, View};

class WebApp extends App
{
    protected function configure()
    {
        ini_set('session.save_handler', option('session_save_handler'));
        ini_set('session.save_path', option('session_save_path'));
        option('session', [
            'lifetime' => strtotime("20 minutes") - time(),
            'path'     => '/',
            'domain'   => '',
            'secure'   => true,
            'httponly' => true,
            'name'     => 'K',
        ]);
        // Register Honeybadger handlers
        $honeybadger = option('honeybadger');
    }

    protected function initialize()
    {
        $this->dispatch('/login', '\K\Pages\LoginPage', [
            'auth' => false,
        ]);
        $this->dispatch('/login/auth', '\K\Pages\LoginAuthPage', [
            'auth' => false,
        ]);
        $this->dispatch('/logout', '\K\Pages\LogoutPage');

        $this->dispatch('/', '\K\Pages\DashboardPage');
        $this->dispatch('/explore', '\K\Pages\ReportSummaryPage');
        $this->dispatch('/organizations', '\K\Pages\OrganizationsPage');

        $this->dispatch('/questions', '\K\Pages\QuestionsPage');
        $this->dispatchPost('/questions/add', '\K\Pages\QuestionAddPage');

        $this->dispatch('/reports/comments', '\K\Pages\CommentReportPage');

        $this->dispatch('/pulses', '\K\Pages\PulsesPage');
        $this->dispatch('/pulses/remove', '\K\Pages\PulseRemovePage');
        $this->dispatchPost('/pulses/add', '\K\Pages\PulseAddPage');

        $this->dispatch('/settings', '\K\Pages\SettingsPage');
        $this->dispatch('/admin', '\K\Pages\AdminPage');

        $this->dispatch('/api/v1/pulse', '\K\Pages\ApiPulsePage');
        $this->dispatchPost('/api/v1/pulse', '\K\Pages\ApiReceivePulsePage');
        $this->dispatchOptions('/api/v1/pulse', '\K\Pages\ApiCorsPage');
    }

    protected function handleServerError(\K\ResponseWriterInterface $w, \Throwable $e)
    {
        html($w, 'error.phtml', 'error_layout.phtml', [
            'e'      => $e,
            'is_dev' => getenv('APP_ENV') === ENV_DEVELOPMENT
        ], SERVER_ERROR);
    }

    protected function handleNotFound(\K\ResponseWriterInterface $w)
    {
        html($w, 'not_found.phtml', 'error_layout.phtml', [], NOT_FOUND);
    }

    protected function handleNotAllowed(\K\ResponseWriterInterface $w)
    {
        $this->handleNotFound();
    }
}

WebApp::run();
