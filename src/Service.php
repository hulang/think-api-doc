<?php

namespace hulang\apidoc;

use think\facade\Route;

class Service extends \think\Service
{
    public function boot(Route $route)
    {
        $this->route_prefix = config('apidoc.route_prefix', '');
        $this->registerRoutes(function () {
            Route::group($this->route_prefix, function () {
                Route::any('/', function () {
                    return redirect($this->route_prefix . '/document?name=explain');
                });
                Route::get('assets', "\\x_mier\\apidoc\\Controller@assets", ['deny_ext' => 'php|.htacess']);
                Route::get('module', "\\x_mier\\apidoc\\Controller@module");
                Route::get('action', "\\x_mier\\apidoc\\Controller@action");
                Route::get('document', "\\x_mier\\apidoc\\Controller@document");
                Route::any('login$', "\\x_mier\\apidoc\\Controller@login");
                Route::any('outlogin$', "\\x_mier\\apidoc\\Controller@outlogin");
                Route::any('format_params', "\\x_mier\\apidoc\\Controller@format_params");
            });
        });
    }
}
