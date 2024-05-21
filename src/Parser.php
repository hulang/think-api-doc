<?php

declare(strict_types=1);

namespace hulang\apidoc;

class Parser
{
    /**
     * 解析类
     * @param $object
     *
     * @return array
     */
    public static function parse_class($object)
    {
        return self::parseCommentArray(self::comment2Array($object));
    }
    /**
     * @param \ReflectionClass $object
     *
     * @return array|bool
     */
    public static function parse_action($object)
    {
        $comment = self::parse_class($object);
        if (empty($comment)) {
            return;
        }
        if (!isset($comment['url']) || !$comment['url']) {
            $buildUrl = self::buildUrl($object);
            $comment['url'] = $buildUrl['url'];
            if (!empty($buildUrl['method'])) {
                $comment['method'] =  $buildUrl['method'];
            }
        }
        if (!isset($comment['method']) || !$comment['method']) {
            if (!isset($comment['method'])) {
                $comment['method'] = '未定义method注释，内容应用默认api方法调用，对外api默认POST';
            }
        }
        $comment['href'] = "{$object->class}::{$object->name}";
        return $comment;
    }
    /**
     * @param \ReflectionClass $object
     *
     * @return mixed
     */
    private static function buildUrl($object)
    {
        $_arr = explode('\\', strtolower($object->class));

        $url = url('/' . $_arr[1] . '/' . $_arr[3] . '/' . $object->name, [], '', true);

        if ($_arr[2] == 'api') {
            $url = "api('{$object->class}','{$object->name}','\$data')";
            $method = 'api';
        } else {
            if (count($_arr) === 5) {
                $url = url('/' . $_arr[1] . '/' . $_arr[3] . '.' . $_arr[4] . '/' . $object->name, [], '', true);
            } else {
                $url = url('/' . $_arr[1] . '/' . $_arr[3] . '/' . $object->name, [], '', true);
            }
        }

        return ['url' => (string)$url, 'method' => $method ?? ''];
    }
    /**
     * 注释字符串转数组
     *
     * @param string $comment
     *
     * @return array
     */
    private static function comment2Array($comment = '')
    {
        // 多空格转换成单空格
        $comment = preg_replace('/[ ]+/', ' ', (string) $comment);
        preg_match_all('/\*[\s+]?@(.*?)[\n|\r]/is', $comment, $matches);
        $arr = [];
        foreach ($matches[1] as $key => $match) {
            $arr[$key] = explode(' ', $match);
        }
        return $arr;
    }
    /**
     * 解析注释数组
     *
     * @param array $array
     *
     * @return array
     */
    private static function parseCommentArray(array $array = [])
    {
        $newArr = [];
        foreach ($array as $item) {
            switch (strtolower($item[0])) {
                case 'title':
                case 'desc':
                case 'version':
                case 'author':
                default:
                    $newArr[$item[0]] = isset($item[1]) ? $item[1] : '-';
                    break;
                case 'url':
                    @eval('$newArr["url"]=$item[1];');
                    break;
                case 'param':
                case 'return':
                    $newArr[$item[0]][] = [
                        'type' => $item[1],
                        'name' => preg_replace('/\$/i', '', $item[2]),
                        'default' => isset($item[3]) ? $item[3] : '-',
                        'desc' => isset($item[4]) ? $item[4] : '-',
                        'valid' => isset($item[5]) ? $item[5] : '-'
                    ];
                    break;
            }
        }
        return $newArr;
    }
}
