<?php

declare(strict_types=1);

namespace hulang\apidoc;

class Parser
{
    /**
     * 解析类的注释信息
     * 
     * 本函数旨在解析给定对象的注释,将其转换为一个数组格式,方便后续处理和使用
     * 主要用于处理和提取类、方法、属性等的文档注释,以支持API文档生成或其他需要解析注释信息的场景
     * 
     * @param object $object 需要解析的对象,可以是类、方法或属性
     * 
     * @return array 返回一个数组,包含解析后的注释信息
     */
    public static function parseClass($object)
    {
        // 调用comment2Array方法将对象的注释转换为数组
        // 再通过parseCommentArray方法进一步处理这个数组,提取出有用的注释信息
        return self::parseCommentArray(self::comment2Array($object));
    }

    /**
     * 解析类中的操作注释
     * 本函数旨在通过反射类信息,解析出类中特定操作(方法)的注释,这些注释中可能包含了该操作的URL和请求方法等重要信息
     * 如果注释中没有明确指定URL和请求方法,则会尝试根据方法名生成相应的URL
     *
     * @param \ReflectionClass $object 反射类对象,用于获取类的方法和注释信息
     *
     * @return array|bool 返回解析出的注释信息数组,如果无法解析则返回false
     */
    public static function parseAction($object)
    {
        // 解析类的注释
        $comment = self::parseClass($object);
        // 如果类注释为空,则直接返回
        if (empty($comment)) {
            return;
        }
        // 如果注释中没有指定URL,则尝试构建URL
        if (!isset($comment['url']) || !$comment['url']) {
            $buildUrl = self::buildUrl($object);
            $comment['url'] = $buildUrl['url'];
            // 如果构建URL时指定了请求方法,则添加到注释信息中
            if (!empty($buildUrl['method'])) {
                $comment['method'] =  $buildUrl['method'];
            }
        }
        // 如果注释中没有指定请求方法,则默认为POST
        if (!isset($comment['method']) || !$comment['method']) {
            $comment['method'] = '未定义method注释,内容应用默认api方法调用,对外api默认POST';
        }
        // 构建注释信息中的链接地址
        $comment['href'] = "{$object->class}::{$object->name}";
        return $comment;
    }

    /**
     * 根据反射类对象构建URL
     * 
     * 本函数用于根据给定的反射类对象,解析类名并生成相应的URL.此过程主要用于自动化URL生成
     * 以便根据类和方法名动态路由到相应的控制器和操作.如果类名符合特定的命名规范(如API类)
     * 则会生成不同的URL格式
     *
     * @param \ReflectionClass $object 反射类对象,用于获取类名和方法名
     * @return mixed 返回一个包含URL和方法名的数组.URL是根据类名和方法名动态生成的
     *               方法名用于区分API调用和其他类型的调用
     */
    private static function buildUrl($object)
    {
        // 将类名转换为小写并使用反斜杠分割,以便后续处理
        $_arr = explode('\\', strtolower($object->class));

        // 根据类名的分割结果生成基本URL
        // 这里假设类名的结构为namespace/controller/method,其中api命名空间将特殊处理
        $url = url('/' . $_arr[1] . '/' . $_arr[3] . '/' . $object->name, [], '', true);

        // 检查类名是否在'api'命名空间下,如果是,则生成API调用的URL格式,并设置方法名为'api'
        if ($_arr[2] == 'api') {
            $url = "api('{$object->class}','{$object->name}','\$data')";
            $method = 'api';
        } else {
            // 对于非API命名空间的类,如果类名还包含其他命名空间(如model),则调整URL格式
            if (count($_arr) === 5) {
                $url = url('/' . $_arr[1] . '/' . $_arr[3] . '.' . $_arr[4] . '/' . $object->name, [], '', true);
            } else {
                // 如果类名不包含额外的命名空间,保持初始生成的URL格式
            }
        }

        // 返回包含URL和方法名的数组
        // 如果方法名未被设置(即类不在'api'命名空间下),则方法名为空字符串
        return ['url' => (string)$url, 'method' => $method ?? ''];
    }

    /**
     * 将注释字符串转换为数组
     * 
     * 此方法用于解析注释中的特定标记(以 @ 开头),并将这些标记及其后续内容转换为数组形式
     * 这种转换对于后续处理注释信息,例如提取函数说明、参数描述等非常有用
     * 
     * @param string $comment 注释字符串,可以是单行或多行注释
     * @return array 返回一个数组,其中每个元素都是一个包含注释标记及其内容的子数组
     */
    private static function comment2Array($comment = '')
    {
        // 使用正则表达式替换多个连续空格为单个空格,目的是规范化注释格式
        $comment = preg_replace('/[ ]+/', ' ', (string) $comment);
        // 使用正则表达式匹配注释中的所有以 @ 开头的标记,并忽略换行符
        preg_match_all('/\*[\s+]?@(.*?)[\n|\r]/is', $comment, $matches);
        // 初始化一个空数组,用于存储解析后的标记及其内容
        $arr = [];
        // 遍历匹配结果,将每个标记及其内容拆分成数组,并添加到结果数组中
        foreach ($matches[1] as $key => $match) {
            $arr[$key] = explode(' ', $match);
        }
        // 返回解析后的注释数组
        return $arr;
    }

    /**
     * 解析注释数组
     * 
     * 该方法用于解析一个特定格式的注释数组,将其转换为一个更易于理解和使用的数组格式
     * 注释数组中的每个元素代表了一个注释类型及其内容,例如函数标题、描述、参数等
     * 解析过程中,不同类型的注释会被转换为不同的结构,以便于后续处理和使用
     * 
     * @param array $array 输入的注释数组,默认为空数组
     * @return array 解析后的注释数组
     */
    private static function parseCommentArray(array $array = [])
    {
        // 初始化一个新的数组用于存放解析后的注释信息
        $newArr = [];
        // 遍历输入的注释数组
        foreach ($array as $item) {
            // 根据注释类型的不同执行相应的处理逻辑
            switch (strtolower($item[0])) {
                    // 处理标题、描述、版本、作者等单一值的注释类型
                case 'title':
                case 'desc':
                case 'version':
                case 'author':
                default:
                    // 将注释类型和内容(如果存在)存储到新数组中,若内容不存在则使用'-'作为占位符
                    $newArr[$item[0]] = isset($item[1]) ? $item[1] : '-';
                    break;
                    // 处理URL类型的注释,由于URL可能包含特殊字符,这里使用eval直接赋值
                case 'url':
                    @eval('$newArr["url"]=$item[1];');
                    break;
                    // 处理参数和返回值类型的注释,这些注释包含多个属性,如类型、名称、默认值等
                case 'param':
                case 'return':
                    // 将参数或返回值的各个属性存储到新数组中的对应位置
                    $newArr[$item[0]][] = [
                        'type' => $item[1],
                        // 移除参数名中的美元符号
                        'name' => preg_replace('/\$/i', '', $item[2]),
                        // 默认值,如果不存在则使用'-'作为占位符
                        'default' => isset($item[3]) ? $item[3] : '-',
                        // 描述,如果不存在则使用'-'作为占位符
                        'desc' => isset($item[4]) ? $item[4] : '-',
                        // 有效性规则,如果不存在则使用'-'作为占位符
                        'valid' => isset($item[5]) ? $item[5] : '-'
                    ];
                    break;
            }
        }
        // 返回解析后的注释数组
        return $newArr;
    }
}
