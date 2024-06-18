<?php

declare(strict_types=1);

namespace hulang\apidoc;

use think\facade\Request;
use think\facade\View;
use think\facade\Cookie;

class Controller
{
    protected $static_path = '';
    protected $static_assets = '';
    protected $view_path = '';
    protected $doc;
    protected $route_prefix = '';
    protected $root = '';
    /**
     * Request 实例
     *
     * @return mixed|array
     */
    protected $request;
    /**
     * 视图类实例
     *
     * @return mixed|array
     */
    protected $view;
    /**
     * 资源类型
     *
     * @return mixed|array
     */
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
    /**
     * 构造函数,用于初始化文档查看器
     * 在构造函数中,配置了视图路径、静态文件路径,以及根据配置和请求状态进行了一些逻辑处理,如登录检查和页面重定向
     */
    public function __construct()
    {
        // 设置静态资源路径
        // 有些程序配置了默认json问题
        $this->static_path = __DIR__ . '/assets/';
        // 初始化文档对象,配置基于apidoc的配置
        $this->doc = new Doc(config('apidoc'));
        // 从文档对象中获取并设置路由前缀
        $this->route_prefix = $this->doc->route_prefix;
        // 配置视图引擎,设置视图路径和后缀
        View::config(['view_path' => __DIR__ . '/view/', 'view_suffix' => 'html']);
        // 分配变量到视图,用于页面显示
        View::assign('web', $this->doc->__get());
        View::assign('route_prefix', $this->route_prefix);
        // 根据文档配置,设置静态资源路径,如果未配置,则使用默认路径[layui]
        $this->static_path = $this->doc->__get('static_path') ?: '/static/' . $this->route_prefix;
        View::assign('static_path', $this->static_path);
        // 根据文档配置,设置静态assets路径,如果未配置,则使用默认路径[assets]
        $this->static_assets = $this->doc->__get('static_assets') ?: '/' . $this->route_prefix;
        View::assign('static_assets', $this->static_assets);
        // 获取应用的根目录URL[root]
        $this->root = request()->root() ?: request()->domain();
        // 登录验证逻辑,如果用户未登录且请求的不是登录页面,则重定向到登录页面
        if (
            Cookie::get($this->route_prefix . '.is_login') !== $this->doc->__get('password')
            && $this->doc->__get('password')
            && request()->url() !== '/' . $this->route_prefix . '/login'
            && stristr(request()->url(), '/assets') == false
        ) {
            Cookie::get($this->route_prefix . '.request_url', Request::url(true));
            header('location:/' . $this->route_prefix . '/login');
            exit();
        }
        // 分配文档介绍和版本信息到视图[序言文档]
        View::assign('document', $this->doc->__get('document'));
        // 分类
        View::assign('versions', $this->doc->__get('api_type'));
        // 根据请求的版本信息,获取相应的API列表,分配到视图[左侧菜单]
        View::assign('menu', $this->doc->get_api_list(input('version', 0, 'intval')));
    }

    /**
     * 解析并返回对应的静态资源
     *
     * 本函数用于处理应用程序静态资源的请求.当请求的路径匹配到静态资源路径时
     * 本函数将返回该资源的内容.如果请求的资源文件存在,并且文件扩展名在支持的
     * MIME类型列表中,将返回带有正确MIME类型的资源内容
     *
     * @return mixed|array|bool 返回静态资源的内容,如果资源不存在或不支持,则返回false
     */
    public function assets()
    {
        // 定义静态资源的目录路径
        $assets_path = __DIR__ . '/assets/';
        // 移除路由前缀和'/assets'部分,获取静态资源的相对路径
        $path = str_replace($this->route_prefix . '/assets', '', request()->pathinfo());
        // 获取请求的文件扩展名
        $ext = request()->ext();
        // 如果请求的文件有扩展名
        if ($ext) {
            // 默认设置为HTML类型
            $type = 'text/html';
            // 读取并返回静态资源的内容
            $content = file_get_contents($assets_path . $path);
            // 如果扩展名在MIME类型列表中,设置对应的MIME类型
            if (array_key_exists($ext, $this->mimeType)) {
                $type = $this->mimeType[$ext];
            }
            // 返回带有内容长度和MIME类型的响应
            return response($content, 200, ['Content-Length' => strlen($content)])->contentType($type);
        }
    }

    /**
     * 将数据转换为标准的响应格式
     * 
     * 本函数旨在将处理后的数据封装成一个标准的响应体,方便前端或调用方进行解析
     * 响应体包括代码（code）、消息（msg）和数据（data）三个部分.其中
     * - code 表示操作的状态码,这里固定为 200,表示操作成功
     * - msg 表示操作的结果消息,这里固定为 'ok',表示操作正常
     * - data 表示实际的数据部分,这里是传入函数的数据直接返回
     * 这种标准化的响应格式有利于统一处理前端或API接口的响应数据
     *
     * @param mixed $data 要返回的数据,可以是任意类型
     * @return mixed|array 返回封装后的数据,通常是一个包含 code、msg 和 data 的数组
     */
    public function totrue($data)
    {
        // 将处理结果封装为标准的JSON格式返回
        return json(['code' => 200, 'msg' => 'ok', 'data' => $data]);
    }

    /**
     * 显示模板
     * 
     * 本函数用于加载并显示指定的模板文件.它首先将当前实例的根目录路径
     * 添加到变量数组中,然后将这个数组与传入的变量数组合并,最后通过View
     * 函数加载并显示合并后的变量数组对应的模板文件
     *
     * @param string $name 模板文件名
     * @param array $vars 用于模板的变量数组
     * @return mixed|array|string 返回View函数的执行结果,可能是混合类型、数组或布尔值
     */
    protected function template($name, $vars = [])
    {
        // 将根目录路径添加到变量数组中,并合并传入的变量数组
        $vars = array_merge(['root' => $this->root], $vars);
        // 加载并显示合并后的变量数组对应的模板文件
        return View($name);
    }

    /**
     * 控制器的默认操作方法
     * 
     * 本方法作为控制器中的默认操作,当用户请求控制器而没有指定具体的操作时,会调用此方法
     * 它的主要作用是加载并渲染默认的视图模板,以便向用户展示预期的页面内容
     * 
     * @return mixed 返回渲染后的视图模板内容
     */
    public function index()
    {
        // 调用template方法来加载并返回'index'模板的内容
        return $this->template('index');
    }

    /**
     * 根据模块名加载对应的类文档并渲染模板
     * 
     * 本函数主要用于处理模块的加载和渲染.它首先检查传入的模块名是否对应一个存在的类
     * 如果存在,则通过反射获取该类的文档注释,进一步解析这个注释以获取类的文档信息
     * 最后,将解析得到的类文档信息分配给视图,然后渲染模板
     * 
     * @param string $name 模块名,对应一个类的名字.如果未提供,则默认为空字符串
     * @return mixed|array|string 返回渲染后的模板结果.根据实现的不同,可能返回字符串、数组或其它混合类型
     */
    public function module($name = '')
    {
        // 检查类是否存在
        if (class_exists($name)) {
            $reflection = new \ReflectionClass($name);
            $doc_str = $reflection->getDocComment();
            // 解析类文档注释,获取文档信息
            $class_doc = Parser::parseClass($doc_str);
            // 将类文档信息分配给视图
            View::assign('data', $class_doc);
        }
        // 渲染模块模板并返回结果
        return $this->template('module');
    }

    /**
     * 根据请求类型执行不同的动作
     * 如果是Ajax请求,根据提供的方法名获取API详情并返回
     * 如果不是Ajax请求,渲染并返回动作模板
     * 
     * @param string $name 方法名,用于非Ajax请求时指定要执行的动作
     * @return mixed 如果是Ajax请求,返回API详情;否则返回动作模板
     */
    public function action($name = '')
    {
        // 检查当前请求是否为Ajax请求
        if (request()->isAjax()) {
            // 解析方法名,获取类名和方法名
            list($class, $action) = explode('::', $name);
            // 从文档对象中获取指定类和方法的API详情
            $data = $this->doc->get_api_detail($class, $action);
            // 设置API详情中的是否为头部文档的标记
            $data['is_header'] = $this->doc->__get('is_header');
            // 获取并设置全局头部信息
            $data['_header'] = $this->doc->__get('header');
            // 获取并设置全局参数信息
            $data['_params'] = $this->doc->__get('params');
            // 处理并返回数据
            return $this->totrue($data);
        } else {
            // 非Ajax请求,返回动作模板
            return $this->template('action');
        }
    }

    /**
     * 根据提供的文档名称生成文档视图
     * 本函数主要用于根据不同的文档名称,从配置或文档对象中获取相应的文档数据
     * 并渲染相应的模板,以展示不同的文档内容
     * 
     * @param string $name 文档的名称.默认为'explain',用于指定要展示的文档类型
     *                    如果名称为'code',则从配置文件中获取特定的文档列表
     * @return mixed|array|string 根据不同的情况返回不同类型的结果.通常是渲染后的文档视图
     *                            但当$name为'code'时,可能会返回一个数组,用于在视图中展示代码列表
     */
    public function document($name = 'explain')
    {
        // 当文档名称为'code'时,从配置文件中获取代码文档列表,并分配给视图
        if ($name == 'code') {
            $data['list'] = config('apidoc')[$name];
            View::assign('data', $data);
        } else {
            // 对于其他文档名称,从文档对象中获取相应的文档内容,并分配给视图
            View::assign('data', $this->doc->__get('document')[$name]);
        }
        // 根据文档名称渲染相应的模板,并返回渲染后的结果
        return $this->template('doc_' . $name);
    }

    /**
     * 格式化请求参数
     * 
     * 本函数旨在处理和格式化来自HTTP请求的参数,根据请求类型和URL路径
     * 将参数组织成不同的格式,以便于后续的处理和调用
     * 
     * @return mixed|array|string 根据请求类型和URL路径,返回格式化后的参数,可能是一个数组、字符串或者复合数据结构
     */
    public function format_params()
    {
        // 格式化请求头参数
        $header = $this->format(request()->param('header'));
        // 获取请求的URL参数
        $url = request()->param('url');
        // 获取请求的方法类型
        $method = request()->param('method');
        // 格式化请求的主体参数
        $data = $this->format(request()->param('params'));
        // 判断请求方法是否为'API'类型
        if ($method == 'API') {
            // 对URL进行拆分,以获取控制器和方法名
            $arr = explode('::', $url);
            // 调用totrue方法,传入拆分后的控制器名、方法名和格式化后的参数,返回处理结果
            return $this->totrue([$arr[0], $arr[1], $data]);
        }
        // 对于非'API'类型的请求,将格式化后的参数和请求头参数一起封装成数组,然后返回
        return $this->totrue(['params' => $data, 'header' => $header]);
    }

    /**
     * 格式化输入的数据数组,将其转换为键值对的形式
     * 
     * 此函数接收一个数据数组作为输入,该数组预期包含两个子数组：'name' 和 'value'
     * 'name' 数组提供键名,'value' 数组提供对应的值.函数的目的是根据 'name' 数组的元素
     * 将 'value' 数组的元素重新组织为一个索引由 'name' 数组值决定的新数组
     * 
     * 如果输入的数据数组为空或者不满足预期的格式（即不包含 'name' 和 'value' 键）,则返回一个空数组
     * 
     * @param array $data 输入的数据数组,预期包含 'name' 和 'value' 两个子数组
     * @return array 返回一个新数组,其中键来自 $data['name'],值来自 $data['value']
     */
    private function format($data = [])
    {
        // 检查输入数据是否为空或不满足预期格式
        if (!$data || count($data) < 1) {
            return [];
        }
        // 初始化结果数组
        $result = [];
        // 遍历 $data['name'] 数组,以其值作为键,将对应的 $data['value'] 元素值添加到结果数组中
        foreach ($data['name'] as $k => $v) {
            $result[$v] = $data['value'][$k];
        }
        // 返回格式化后的结果数组
        return $result;
    }

    /**
     * 登录方法
     * 
     * 本方法用于处理用户的登录行为.它首先检查用户是否已经通过Cookie登录
     * 如果已登录,则重定向到主页面;否则,显示登录表单
     * 
     * @return mixed 如果用户已登录,则重定向到主页面;否则返回登录模板
     */
    public function login()
    {
        // 构建登录状态Cookie的名称
        $cookieName = $this->route_prefix . '.is_login';
        // 检查用户是否已通过Cookie登录
        if (Cookie::has($cookieName)) {
            // 从Cookie中读取登录状态数据
            $data = Cookie::get($cookieName);
            // 验证Cookie中的数据是否与用户密码匹配
            if ($data == $this->doc->__get('password')) {
                // 如果匹配,重定向到主页面
                header('location:/' . $this->route_prefix);
            } else {
                // 如果不匹配,返回登录模板
                return $this->template('login');
            }
        } else {
            // 如果Cookie不存在,返回登录模板
            return $this->template('login');
        }
    }

    /**
     * 检查登录状态的方法
     * 
     * 本方法主要用于处理用户登录请求.当请求为POST时,它将验证用户的密码
     * 如果密码正确,它将设置登录[cookie]并重定向到请求的页面;如果密码错误
     * 它将显示一个错误消息并重定向到登录页面
     *
     * @return mixed|array|string 根据不同的执行路径返回不同的结果：重定向、字符串消息或数组参数
     */
    public function check()
    {
        // 定义登录[cookie]的名称
        $cookieName = $this->route_prefix . '.is_login';
        // 检查当前请求是否为POST请求
        if (request()->isPost()) {
            // 获取POST请求的参数
            $param = Request::param();
            // 验证密码是否正确
            if ($param['pwd'] != $this->doc->__get('password')) {
                // 密码错误时,输出JavaScript提示并重定向到登录页面
                die('<script>alert("密码错误！");window.location.href="/' . $this->route_prefix . '";</script>');
            } else {
                // 密码正确时,设置登录[cookie]并重定向到原先请求的页面
                // 设置[cookie]有效期为[10]天
                Cookie::set($cookieName, $param['pwd'], (3600 * 24) * 10);
                // 重定向到请求的页面或默认的登录成功页面
                return redirect(Cookie::get($this->route_prefix . '.request_url') ?: '/' . $this->route_prefix);
            }
        }
    }

    /**
     * 注销登录操作
     * 
     * 本函数用于执行用户注销登录的操作.它通过删除登录状态cookie来实现
     * 然后重定向到登录页面,以便用户可以再次登录
     * 
     * @return mixed|array|string 重定向到登录页面的URL
     */
    public function outlogin()
    {
        // 构建登录状态[cookie]的名称
        $cookieName = $this->route_prefix . '.is_login';
        // 删除登录状态[cookie],以注销用户
        Cookie::delete($cookieName);
        // 重定向到登录页面
        return redirect('/' . $this->route_prefix);
    }
}
