<?php

declare(strict_types=1);

namespace hulang\apidoc;

use think\facade\Request;
use think\facade\View;

class Controller
{
    protected $assets_path = '';
    protected $doc = '';
    protected $route_prefix = '';
    protected $view_path = '';
    protected $root = '';

    protected $request; # Request 实例
    protected $view; # 视图类实例

    # 资源类型
    protected $mimeType = [
        'xml' => 'application/xml,text/xml,application/x-xml',
        'json' => 'application/json,text/x-json,application/jsonrequest,text/json',
        'js' => 'text/javascript,application/javascript,application/x-javascript',
        'css' => 'text/css',
        'rss' => 'application/rss+xml',
        'yaml' => 'application/x-yaml,text/yaml',
        'atom' => 'application/atom+xml',
        'pdf' => 'application/pdf',
        'text' => 'text/plain',
        'png' => 'image/png',
        'jpg' => 'image/jpg,image/jpeg,image/pjpeg',
        'gif' => 'image/gif',
        'csv' => 'text/csv',
        'html' => 'text/html,application/xhtml+xml,*/*',
    ];
    public function __construct()
    {
        //有些程序配置了默认json问题
        $this->assets_path = __DIR__ . '/assets/';
        $this->doc = new Doc(config('apidoc'));
        $this->route_prefix = $this->doc->route_prefix;
        $config = [
            'view_path' => __DIR__ . '/view/',
            'default_filter' => '',
        ];

        View::config(['view_path' => __DIR__ . '/view/', 'view_suffix' => 'html']);

        View::assign('web', $this->doc->__get());
        View::assign('route_prefix', $this->route_prefix);

        $this->assets_path = $this->doc->__get("static_path") ?: '/static/' . $this->route_prefix;
        View::assign('assets', $this->assets_path);
        $this->root = request()->root() ?: request()->domain();
        if (
            request()->session($this->route_prefix . '.is_login') !== $this->doc->__get('password')
            && $this->doc->__get('password')
            && request()->url() !== '/' . $this->route_prefix . '/login'
            && stristr(request()->url(), '/assets') == false
        ) {
            session($this->route_prefix . '.request_url', Request::url(true));
            header('location:/' . $this->route_prefix . '/login');
            exit();
        }

        // 序言文档
        View::assign('document', $this->doc->__get('document'));

        // 分类
        View::assign('versions', $this->doc->__get('api_type'));

        // 左侧菜单
        View::assign('menu', $this->doc->get_api_list(input('version', 0, 'intval')));
    }

    # 解析资源
    public function assets()
    {
        $assets_path = __DIR__ . '/assets/';
        $path = str_replace($this->route_prefix . "/assets", "", request()->pathinfo());
        $ext = request()->ext();
        if ($ext) {
            $type = "text/html";
            $content = file_get_contents($assets_path . $path);
            if (array_key_exists($ext, $this->mimeType)) {
                $type = $this->mimeType[$ext];
            }
            return response($content, 200, ['Content-Length' => strlen($content)])->contentType($type);
        }
    }

    /** 显示模板
     */
    protected function template($name, $vars = [], $config = [])
    {
        $vars = array_merge(['root' => $this->root], $vars);
        return View($name);
    }

    public function index()
    {
        return $this->template('index');
    }

    public function module($name = '')
    {
        if (class_exists($name)) {
            $reflection = new \ReflectionClass($name);
            $doc_str = $reflection->getDocComment();
            $doc = new Parser();
            # 解析类
            $class_doc = $doc->parse_class($doc_str);
            View::assign('data', $class_doc);
        }
        return $this->template('module');
    }

    public function action($name = '')
    {
        if (request()->isAjax()) {
            list($class, $action) = explode("::", $name);
            $data = $this->doc->get_api_detail($class, $action);
            $data['is_header'] = $this->doc->__get('is_header');
            # 全局header
            $data['_header'] = $this->doc->__get('header');
            # 全局参数
            $data['_params'] = $this->doc->__get('params');
            return json($data);
        } else {
            return $this->template('action');
        }
    }

    public function document($name = 'explain')
    {
        if ($name == 'code') {
            $data['list'] = config('helper')[$name];
            View::assign('data', $data);
        } else {
            View::assign('data', $this->doc->__get('document')[$name]);
        }
        // dd($this->template('doc_' . $name));
        return $this->template('doc_' . $name);
    }

    // debug 格式化参数
    public function format_params()
    {
        $header = $this->format(request()->param('header'));
        // $header["Cookie"] = request()->param('cookie');
        // $header["token"] = request()->param('token');

        $url = request()->param('url');
        $method = request()->param('method');

        $data = $this->format(request()->param('params'));

        if ($method == 'API') {
            $arr = explode('::', $url);
            return json(api($arr[0], $arr[1], $data));
        }

        return json(['params' => $data, 'header' => $header]);
    }

    private function format($data = [])
    {
        if (!$data || count($data) < 1) {
            return [];
        }
        $result = [];
        foreach ($data['name'] as $k => $v) {
            $result[$v] = $data['value'][$k];
        }
        return $result;
    }

    public function login()
    {
        if (request()->isPost()) {
            if (input('post.password') != $this->doc->__get('password')) {
                die('<script>alert("密码错误！");window.location.href="/' . $this->route_prefix . '";</script>');
            } else {
                session($this->route_prefix . '.is_login', input('post.password'));
                return redirect(session($this->route_prefix . '.request_url') ?: '/' . $this->route_prefix);
            }
        } else {
            if (session($this->route_prefix . '.is_login') == $this->doc->__get('password')) {
                header('location:/' . $this->route_prefix);
            } else {
                return $this->template('login');
            }
        }
    }
    public function outlogin()
    {
        session($this->route_prefix . '.is_login', null);
        return redirect('/' . $this->route_prefix);
    }
}
