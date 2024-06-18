<?php

declare(strict_types=1);

namespace hulang\apidoc;

use think\facade\Route;

class Service extends \think\Service
{
    protected $route_prefix = '';
    /**
     * 应用启动时执行的方法
     * 该方法主要用于设置路由前缀,并注册相关路由
     */
    public function boot()
    {
        // 从配置文件中获取路由前缀,如果不存在则使用空字符串
        $this->route_prefix = config('apidoc.route_prefix', '');

        // 注册路由,使用匿名函数来定义路由组
        $this->registerRoutes(function () {
            // 使用定义的路由前缀创建一个路由组
            Route::group($this->route_prefix, function () {
                // 定义路由到根路径,用于重定向到文档页面
                Route::any('/', function () {
                    return redirect($this->route_prefix . '/document?name=explain');
                });
                // 定义路由到资产目录,用于获取前端资源
                Route::get('assets', "\\hulang\\apidoc\\Controller@assets", ['deny_ext' => 'php|.htacess']);
                // 分别定义路由到模块、动作、文档、登录、权限检查、退出登录和参数格式化方法
                Route::get('module', "\\hulang\\apidoc\\Controller@module");
                Route::get('action', "\\hulang\\apidoc\\Controller@action");
                Route::get('document', "\\hulang\\apidoc\\Controller@document");
                Route::any('login', "\\hulang\\apidoc\\Controller@login");
                Route::any('check', "\\hulang\\apidoc\\Controller@check");
                Route::any('outlogin', "\\hulang\\apidoc\\Controller@outlogin");
                Route::any('format_params', "\\hulang\\apidoc\\Controller@format_params");
            });
        });
    }
}
