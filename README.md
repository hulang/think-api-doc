##### ThinkPHP 8.0.0+ API 接口文档生成工具

#### 使用方法

##### 1.安装扩展

```php
composer require hulang/think-api-doc
```

##### 2.配置参数

- 安装好扩展后在 `config\apidoc.php` 配置文件
- 需要指定`layui`目录
- 如路由`css`和`图片`未生效,请复制`assets`文件夹到你的静态(static)路径中,再配置文件`config\apidoc.php`配置`static_assets`参数进行指定目录

##### 3.书写规范

```php
<?php

namespace app\api\controller;

/**
 * @title API接口
 */
class Index
{
    /**
     * @title 首页信息
     * @desc 方法描述
     * @author 橘子味的心
     * @version 1.0
     * @method POST
     * @param int id ID YES
     * @param int limit 10 总条数 YES
     * @param int page 1 当前页 YES
     * @param int key 查询条件 NO
     * @return int code 200/100 返回参数
     * @return string msg ok 返回信息
     * @return array data 返回数据
     * @return array other 返回数据
     */
    public function index()
    {
    }
    /**
     * @title 用户信息
     * @desc 方法描述
     * @author 橘子味的心
     * @version 1.0
     * @method POST
     * @param int id ID YES
     * @param int limit 10 总条数 YES
     * @param int page 1 当前页 YES
     * @param int key 查询条件 NO
     * @return int code 200/100 返回参数
     * @return string msg ok 返回信息
     * @return array data 返回数据
     * @return array other 返回数据
     */
    public function user()
    {
    }
}
```

##### 4.访问方法

- `http://你的域名/apidoc`
- `http://你的域名/index.php/apidoc`
