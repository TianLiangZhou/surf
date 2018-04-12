<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/11
 * Time: 21:02
 */

namespace Surf\Session;

/**
 * Interface DriverInterface
 * @package Surf\Session
 */
interface DriverInterface
{
    /**
     * 启动时开启session, 该函数应该返回一个唯一的sessionId
     * @return mixed
     */
    public function open();

    /**
     * 读取$id 中的内容
     * @param string $id
     * @return mixed
     */
    public function read(string $id);

    /**
     * 保存数据到$id中
     * @param string $id
     * @param $data
     * @return mixed
     */
    public function save(string $id, $data);
}
