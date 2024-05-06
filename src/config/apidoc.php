<?php

return [
    'title' => 'API-DOC', # 文档title
    'version' => '1.0.0', # 文档版本
    'copyright' => 'Powered By Api Doc', # 版权信息
    'password' => '123456', # 访问密码，为空不需要密码
    'route_prefix' => 'apidoc', // 路由前缀 通过访问 /table 访问表格页面
    'save' => 'file', // 默认 存储类型
    'engine' => [
        'file' => [
            'save_path' => runtime_path('doc'), // 数据保存地址 确保路径的可读写
        ],
    ],
    'document' => [
        'explain' => [
            'name' => '说明',
            'list' => [
                '返回Json' => [
                    "数据正确，返回json数据使用totrue()方法",
                    "数据错误，返回json数据使用tofalse()方法",
                    "返回的统一格式为
                        {
                            \"status\":\"状态码\",
                            \"message\":\"操作描述\",
                            \"data\":'业务数据'
                        }",
                ],
                '自动生成文档说明' => [
                    '在该配置文件的api_type中配置需要展示的应用信息',
                    'name为标题，用于显示在菜单',
                    'app为应用名，配置正确的应用名，系统会自动读取相应的注释文档',
                    'type为应用类型，1为领域应用，系统会读取api目录文件，2为对外应用，系统会读取controller目录文件',
                ],
                '类注释说明' => [
                    "@title   模块名称",
                    "@desc    我是模块名称",
                ],
                '方法注释说明' => [
                    "@title 方法标题",
                    "@desc 方法描述",
                    "@author 方法作者",
                    "@version 方法版本",
                    '@param int $page  0 分页数，指定获取第几页的数据  require_分页数不能为空.number',
                    'param注释， 第一位类型  第二位变量 第三位默认值 第四位说明 第五位数据验证，多个验证用.分割，描述用_分割',
                    '@return int $id 0 索引',
                    'retrun注释, 第一位类型  第二位变量 第三位默认值 第四位说明',
                ],
                '领域应用' => [
                    "领域应用主要有api、logic、dao目录，api提供对其它应用的调用，logic负责该领域的业务，dao负责该领域的数据",
                    '使用rpc函数调用领域dao、logic例子：rpc("app\website\dao\article\GetFind","add",$object);  第一个参数为类名，第二个参数为方法名，第三个参数为数据',
                ],
                '对外应用' => [
                    "对外应用主要只有controller一个目录，它通过路由提供对外服务，需要数据，通过api方法调用对应领域数据",
                    '使用api函数调用领域应用例子：api("app\website\api\article\GetFind","add",$object);  第一个参数为类名，第二个参数为方法名，第三个参数为数据',
                ],
            ],
        ],
        'code' => [
            'name' => '返回码',
            'list' => [],
        ],
        'global_settings' => [
            'name' => '全局设置',
            'list' => [
                '全局参数(数据全部缓存在前端)' => []
            ],
        ],
    ],
    // 全局请求header,一般存放token之类的
    'header' => [
        'token' => '',
        'Authorization' => '',
    ],
    'is_header' => 1,
    // 全局请求参数
    'params' => [
        '__uid' => 2,
    ],
    // API分类
    'api_type' => [
        [
            'name' => 'Api接口', //分类标题
            'app' => 'api', //对应应用
            'type' => 2, //类型，1为内部应用，2为对外api接口
        ],
        [
            'name' => '后台接口', //分类标题
            'app' => 'system', //对应应用
            'type' => 1, //类型，1为内部应用，2为对外api接口
        ],
        [
            'name' => '前台接口', //分类标题
            'app' => 'index', //对应应用
            'type' => 2, //类型，1为内部应用，2为对外api接口
        ],
    ],
    // 过滤、不解析的方法名称
    'filter_method' => [
        '_empty',
        'initialize',
        '_initialize',
        '__construct',
        '__destruct',
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__cal',
        '__clone',
        '__tosring',
        '__debuginfo',
    ],
    'static_path' => '/static/plug',
    'static_assets' => '/static/doc',
    'return_format' => [
        'status' => '200/300/301/302',
        'message' => '提示信息',
    ],
    'code' => [
        //成功
        '0' => '操作成功',
        '200' => '操作成功',
        //错误
        '1' => '操作失败',
        '100' => '操作失败',
        '101' => '签名不正确',
        '102' => 'token失效',
        '103' => 'token过期',
        '104' => 'token错误',
        //系统错误（前后，对外：系统错误+错误码）
        '300' => '', //自定义错误信息
        '301' => '系统错误，错误码301',
    ],
    //密码加密随机字符串
    'passwd_key' => 'mierhadNkAIZqB1cK#DPW6pjaSbUda8$Luq8',
];
