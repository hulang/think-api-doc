<?php

declare(strict_types=1);

if (!function_exists('totrue')) {
    /**
     * 返回操作成功json信息
     * @param array $object 当前返回对象
     * @param string $special 特殊返回对象处理 有类型：select
     */
    function totrue($message = '成功', $code = 0, $is_json = true)
    {
        return tocode($code, $message, [], 0, [], $is_json);
    }
}

if (!function_exists('tofalse')) {
    /**
     * 返回json错误信息
     * @param string $status 当前错误状态
     * @param string $message 返回错误信息前追加内容,默认为空
     */
    function tofalse($message = '失败', $code = 100, $is_json = true)
    {
        return tocode($code, $message, [], 0, [], $is_json);
    }
}
if (!function_exists('todata')) {
    /**
     * 返回json错误信息
     * @param string $status 当前错误状态
     * @param string $message 返回错误信息前追加内容,默认为空
     */
    function todata($data, $count = 0, $code = 0, $is_json = true)
    {
        return tocode($code, '', $data, $count, [], $is_json);
    }
}
if (!function_exists('tocode')) {
    /**
     * json返回错误结果
     */
    function tocode($code = 0, $msg = '', $data = [], $count = 0, $other = [], $is_json = true)
    {
        $result = [];
        $result['code'] = $code;
        $result['msg'] = '';
        $result['data'] = [];
        $result['count'] = 0;
        $result['other'] = [];
        if (!empty($msg)) {
            $result['msg'] = $msg;
        }
        if (!empty($data)) {
            $result['data'] = $data;
        }
        if (!empty($count)) {
            $result['count'] = $count;
        }
        if (!empty($other)) {
            $result['other'] = $other;
        }
        if ($is_json) {
            header('Access-Control-Allow-Origin:*');
            header('Access-Control-Allow-Methods:POST,GET,PUT,DELETE,OPTIONS');
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
        return $result;
    }
}
