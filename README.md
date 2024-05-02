##### ThinkPHP8 接口文档生成工具

#### 使用方法

##### 1.安装扩展

```php
composer require hulang/think-api-doc
```

##### 2.配置参数

- 安装好扩展后在 `config\apidoc.php` 配置文件

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
     * @title   首页信息
     * @desc    方法描述
     * @author  橘子味的心
     * @version 1.0
     * @method  POST
     * @param   int id ID YES
     * @param   int limit 10 总条数 YES
     * @param   int page 1 当前页 YES
     * @param   int key 查询条件 NO
     * @return  int code 200/100 返回参数
     * @return  string msg ok 返回信息
     * @return  array data 返回数据
     * @return  array other 返回数据
     */
    public function index()
    {
    }
    /**
     * @title   用户信息
     * @desc    方法描述
     * @author  橘子味的心
     * @version 1.0
     * @method  POST
     * @param   int id ID YES
     * @param   int limit 10 总条数 YES
     * @param   int page 1 当前页 YES
     * @param   int key 查询条件 NO
     * @return  int code 200/100 返回参数
     * @return  string msg ok 返回信息
     * @return  array data 返回数据
     * @return  array other 返回数据
     */
    public function user()
    {
    }
}
```

##### 4.访问方法

- `http://你的域名/mierdoc` 或者 `http://你的域名/index.php/mierdoc`
