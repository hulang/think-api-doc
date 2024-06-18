<?php

declare(strict_types=1);

namespace hulang\apidoc;

class Doc
{
    protected $config = [
        'title' => 'API-DOC', # 文档title
        'version' => '1.0.0', # 文档版本
        'copyright' => 'Powered By Api Doc', # 版权信息
        'password' => '123123', # 访问密码,为空不需要密码
        'document' => [
            'explain' => [
                'name' => '说明',
                'list' => [],
            ],
            'code' => [
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
        'static_assets' => '',
        'return_format' => [
            'status' => '200/300/301/302',
            'message' => '提示信息',
        ],
    ];

    protected $actionkey = [
        'title' => '未定义方法标题',
        'desc' => '未定义方法描述',
        'author' => '无名氏',
        'version' => '1.0',
        'param' => [],
        'return' => [],
        'url' => '',
        'method' => '',
        'href' => '',
    ];

    protected $classkey = [
        'title' => '未定义标题',
        'desc' => '未定义描述',
        'class' => '',
        'action' => [],
    ];

    /**
     * 构造函数,用于初始化类的配置
     * 
     * 本函数通过合并传入的配置数组与类内部默认的配置数组
     * 来设置类的配置.这样可以在不修改类内部默认配置的情况下
     * 根据具体使用场景对配置进行个性化设置
     * 
     * @param array $config 一个包含配置项的数组.如果未提供,则使用默认配置
     */
    public function __construct($config = [])
    {
        // 合并传入的配置数组与类内部默认配置数组
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 动态获取配置项的值
     * 通过重载魔术方法__get,使得可以像访问属性一样获取配置数组中的值
     * 如果没有指定名称,则返回整个配置数组
     *
     * @param string|null $name 配置项的名称.如果为null,则返回所有配置项
     * @return mixed 如果指定了配置项名称,则返回该配置项的值;否则返回整个配置数组
     */
    public function __get($name = null)
    {
        if ($name) {
            // 当指定配置项名称时,返回对应的配置值
            return $this->config[$name];
        } else {
            // 当没有指定配置项名称时,返回所有的配置项
            return $this->config;
        }
    }

    /**
     * 设置配置项的值
     * 
     * 通过重载魔术方法__set,实现动态设置类中配置数组的值
     * 如果尝试设置的配置项已存在,则更新其值;如果不存在,则不做任何操作
     * 这种方法的设计允许在不预先知道所有配置项的情况下,灵活地设置配置
     * 
     * @param string $name 配置项的名称
     * @param mixed $value 配置项的值
     */
    public function __set($name, $value)
    {
        // 检查配置项是否已存在,如果存在,则更新其值
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 检查配置项是否已设置
     * 
     * 该方法用于判断指定的配置项是否存在于配置数组中
     * 如果配置项存在,返回true;如果不存在,返回false
     * 
     * @param string $name 配置项的名称
     * @return bool 配置项是否存在
     */
    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    /**
     * 获取指定版本的API列表
     * 该方法通过遍历指定路径下的文件和类,解析类和方法的注释,来构建API列表
     * 如果API类型设置为1,则搜索api目录;否则,搜索controller目录
     * 对于每个找到的类,它会检查该类是否存在,并且是否包含可解析的API注释
     * @param int $version 版本号,用于确定API的类型和搜索路径
     * @return mixed|array|bool 返回API列表的数组,如果找不到任何API则返回空数组,出现错误则返回false
     */
    public function get_api_list($version = 0)
    {
        // 初始化API列表数组
        $list = [];
        // 根据API类型确定搜索的目录
        if ($this->config['api_type'][$version]['type'] == 1) {
            $file = $this->listDirFiles($this->config['api_type'][$version]['app'] . '/api');
        } else {
            $file = $this->listDirFiles($this->config['api_type'][$version]['app'] . '/controller');
        }
        // 遍历找到的文件,构建API列表
        foreach ($file as $k => $class) {
            // 构建类的完全限定名
            $class = "app\\" . $class;
            // 检查类是否存在
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                $doc_str = $reflection->getDocComment();
                // 解析类的注释,获取API的基本信息
                $class_doc = Parser::parseClass($doc_str);
                if (!empty($class_doc)) {
                    // 合并类信息和预定义的关键字
                    $list[$k] = array_merge($this->classkey, $class_doc);
                    $list[$k]['class'] = $class;
                    // 获取类的所有公共方法
                    $method = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                    // 过滤不需要解析的方法和非当前类的方法(父级方法)
                    $filter_method = array_merge(['__construct'], $this->config['filter_method']);
                    foreach ($method as $key => $action) {
                        // 检查方法是否应该被解析
                        if (!in_array($action->name, $filter_method) && $action->class === $class) {
                            $res = Parser::parseAction($action);
                            if ($res) {
                                // 合并方法信息和预定义的关键字,并添加到API列表中
                                $list[$k]['action'][$key] = array_merge($this->actionkey, Parser::parseAction($action));
                            }
                        }
                    }
                }
            }
        }
        // 返回构建好的API列表
        return $list;
    }

    /**
     * 获取API详细信息
     * 
     * 通过反射获取指定类的方法细节,并结合解析器解析该方法,以获取更丰富的信息
     * 主要用于在API管理系统中,获取某个API接口的详细定义和配置
     * 
     * @param string $class API接口类的名称
     * @param string $action API接口类中的具体方法名
     * @return mixed|array|bool 返回解析后的API详细信息,如果无法获取则返回false
     */
    public function get_api_detail($class = '', $action = '')
    {
        // 使用反射获取指定类的方法
        $method = (new \ReflectionClass($class))->getMethod($action);
        // 使用Parser类解析方法,获取方法的详细信息
        $data = Parser::parseAction($method);
        // 将解析后的信息与预定义的actionkey数组合并,返回完整的API详细信息
        return array_merge($this->actionkey, $data);
    }

    /**
     * 列出指定应用目录下的所有PHP文件
     * 
     * 该方法用于递归地列出指定应用目录及其子目录中的所有PHP文件
     * 它可以帮助定位和获取应用程序中所有可执行文件的列表,这对于诸如自动加载类或执行某些批处理操作非常有用
     * 
     * @param string $app 目标应用目录的名称
     * @param bool $isapp 指示是否将basePath应用于$app参数,默认为true
     * 
     * @return array 返回一个包含所有找到的PHP文件路径的数组
     */
    protected function listDirFiles($app, $isapp = true)
    {
        // 初始化一个空数组,用于存储找到的PHP文件路径
        $arr = [];
        // 获取应用程序的基路径
        $base = base_path();
        // 根据$isapp参数决定是否应用基路径到$app
        if ($isapp) {
            $dir = $base . $app;
        } else {
            $dir = $app;
        }
        // 检查给定的目录是否存在
        if (is_dir($dir)) {
            // 打开目录以进行读取
            $d = opendir($dir);
            // 确保目录被成功打开
            if ($d) {
                // 递归地读取目录中的每个文件
                while (($file = readdir($d)) !== false) {
                    // 排除当前目录和父目录,以免陷入无限循环
                    if ($file != '.' && $file != '..') {
                        // 如果当前项是子目录,则递归调用本方法以列出其内容
                        if (is_dir($dir . '/' . $file)) {
                            // 进一步获取该目录里的文件
                            $arr = array_merge($arr, self::listDirFiles($dir . '/' . $file, false));
                        } else {
                            // 如果当前项是PHP文件,则将其路径添加到结果数组中
                            // 这里使用pathinfo来获取文件扩展名,并检查是否为'php'
                            if (pathinfo($dir . '/' . $file)['extension'] == 'php') {
                                // 调整文件路径,移除基路径和扩展名,以便于后续使用
                                $arr[] = str_replace([$base, '/', '.php'], ['', '\\', ''], $dir . '/' . $file);
                            }
                        }
                    }
                }
            }
            // 关闭目录句柄
            closedir($d);
        }
        // 对结果数组进行排序,确保文件路径的顺序一致
        asort($arr);
        // 返回找到的所有PHP文件的路径数组
        return $arr;
    }
}
