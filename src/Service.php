<?php

declare(strict_types=1);

namespace hulang\apidoc;

use think\facade\Route;

class Service extends \think\Service
{
    protected $route_prefix = '';
    public function boot(Route $route)
    {
        $this->route_prefix = config('apidoc.route_prefix', '');
        $this->registerRoutes(function () {
            Route::group($this->route_prefix, function () {
                Route::any('/', function () {
                    return redirect($this->route_prefix . '/document?name=explain');
                });
                Route::get('assets', "\\hulang\\apidoc\\Controller@assets", ['deny_ext' => 'php|.htacess']);
                Route::get('module', "\\hulang\\apidoc\\Controller@module");
                Route::get('action', "\\hulang\\apidoc\\Controller@action");
                Route::get('document', "\\hulang\\apidoc\\Controller@document");
                Route::any('login', "\\hulang\\apidoc\\Controller@login");
                Route::any('outlogin', "\\hulang\\apidoc\\Controller@outlogin");
                Route::any('format_params', "\\hulang\\apidoc\\Controller@format_params");
            });
        });
    }
}
