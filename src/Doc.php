<?php

declare(strict_types=1);

namespace hulang\apidoc;

class Doc
{
    protected $config = [
        'title' => 'Api Doc', # 文档title
        'version' => '1.0.0', # 文档版本
        'copyright' => 'Powered By hulangfy@163.com', # 版权信息
        'password' => '123456', # 访问密码，为空不需要密码
        'document' => [
            "explain" => [
                'name' => '说明',
                'list' => [],
            ],
            "code" => [
                'name' => '返回码(复制codemsg)',
                'list' => [],
            ],
        ],
        'is_header' => 1,
        // 全局请求header,一般存放token之类的
        'header' => [],
        // 全局请求参数
        'params' => [
            '__uid' => 2,
        ],
        // API分类
        'api_type' => [],
        // 过滤、不解析的方法名称
        'filter_method' => [],
        'static_path' => '/static/plug',
        'return_format' => [
            'status' => "200/300/301/302",
            'message' => "提示信息",
        ],
    ];

    protected $actionkey = [
        "title" => "未定义方法标题",
        "desc" => "未定义方法描述",
        "author" => "无名氏",
        "version" => "1.0",
        "param" => [],
        "return" => [],
        "url" => '',
        "method" => '',
        "href" => '',
    ];

    protected $classkey = [
        "title" => '未定义标题',
        "desc" => "未定义描述",
        "class" => '',
        "action" => [],
    ];

    /**
     * 架构方法 设置参数
     *
     * @access public
     *
     * @param array $config 配置参数
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 使用 $this->name 获取配置
     *
     * @access public
     *
     * @param string $name 配置名称
     *
     * @return mixed 配置值
     */
    public function __get($name = null)
    {
        if ($name) {
            return $this->config[$name];
        } else {
            return $this->config;
        }
    }

    /**
     * 设置
     *
     * @access public
     *
     * @param string $name  配置名称
     * @param string $value 配置值
     *
     * @return void
     */
    public function __set($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 检查配置
     *
     * @access public
     *
     * @param string $name 配置名称
     *
     * @return mixed|bool
     */
    public function __isset($name)
    {
        return isset($this->config[$name]);
    }
    /**
     * 获取接口列表
     *
     * @access public
     *
     * @param int $version 版本
     *
     * @return mixed|bool
     */
    public function get_api_list($version = 0)
    {
        $list = [];
        if ($this->config['api_type'][$version]['type'] == 1) {
            $file = $this->listDirFiles($this->config['api_type'][$version]['app'] . '/api');
        } else {
            $file = $this->listDirFiles($this->config['api_type'][$version]['app'] . '/controller');
        }
        foreach ($file as $k => $class) {
            $class = "app\\" . $class;
            if (class_exists($class)) {

                $reflection = new \ReflectionClass($class);
                $doc_str = $reflection->getDocComment();
                $doc = new Parser();
                # 解析类
                $class_doc = $doc->parse_class($doc_str);
                if (!empty($class_doc)) {
                    $list[$k] = array_merge($this->classkey, $class_doc);
                    $list[$k]['class'] = $class;
                    $method = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                    # 过滤不需要解析的方法以及非当前类的方法(父级方法)
                    $filter_method = array_merge(['__construct'], $this->config['filter_method']);
                    foreach ($method as $key => $action) {
                        if (!in_array($action->name, $filter_method) && $action->class === $class) {
                            $res = $doc->parse_action($action);
                            if ($res) {
                                $list[$k]['action'][$key] = array_merge($this->actionkey, $doc->parse_action($action));
                            }
                        }
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 获取接口详情
     * @param string $class
     * @param string $action
     *
     * @return mixed|array|bool
     */
    public function get_api_detail($class = '', $action = '')
    {
        $method = (new \ReflectionClass($class))->getMethod($action);
        $data = (new Parser())->parse_action($method);
        return array_merge($this->actionkey, $data);
    }

    /**
     * 获取文件夹内的所有文件
     * @param string $class
     * @param string $action
     *
     * @return mixed|array|bool
     */
    protected function listDirFiles($app, $isapp = true)
    {
        $arr = [];
        $base = base_path();
        if ($isapp) {
            $dir = $base . $app;
        } else {
            $dir = $app;
        }

        if (is_dir($dir)) { //如果是目录，则进行下一步操作
            $d = opendir($dir); //打开目录
            if ($d) { //目录打开正常
                while (($file = readdir($d)) !== false) { //循环读出目录下的文件，直到读不到为止
                    if ($file != '.' && $file != '..') { //排除一个点和两个点
                        if (is_dir($dir . '/' . $file)) { //如果当前是目录
                            $arr = array_merge($arr, self::listDirFiles($dir . '/' . $file, false)); //进一步获取该目录里的文件
                        } else {
                            if (pathinfo($dir . '/' . $file)['extension'] == 'php') {
                                $arr[] = str_replace([$base, '/', '.php'], ['', '\\', ''], $dir . '/' . $file); //进一步获取该目录里的文件
                            }
                        }
                    }
                }
            }
            closedir($d); //关闭句柄
        }
        asort($arr);
        return $arr;
    }
}
