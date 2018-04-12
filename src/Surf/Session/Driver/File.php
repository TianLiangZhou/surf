<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/12
 * Time: 11:09
 */

namespace Surf\Session\Driver;


use Surf\Session\DriverInterface;
use Surf\Session\SessionDriver;

class File extends SessionDriver implements DriverInterface
{

    /**
     * @var string
     */
    private $savePath = '/tmp';

    /**
     * File constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (isset($this->options['save_path']) && $this->options['save_path']) {
            $this->savePath = $this->options['save_path'];
        }
    }


    /**
     * @return string
     */
    public function open()
    {
        // TODO: Implement open() method.

        $sessionId = $this->generateId();
        return $sessionId;
    }

    /**
     * @param string $id
     * @return \ArrayIterator|mixed
     */
    public function read(string $id)
    {
        // TODO: Implement read() method.
        $file = rtrim($this->savePath, '/\\') . DIRECTORY_SEPARATOR . 'sess_' . $id;
        if (file_exists($file) && is_readable($file)) {
            $handle = fopen($file, 'r');
            if (flock($handle, LOCK_EX)) {
                $session = fread($handle, filesize($file));
                flock($handle, LOCK_UN);
                fclose($handle);
                return unserialize($session);
            }
            fclose($handle);
        }
        return new \ArrayIterator();
    }

    /**
     * @param string $id
     * @param $data
     * @return bool|int
     */
    public function save(string $id, $data)
    {
        // TODO: Implement save() method.
        $file = rtrim($this->savePath, '/\\') . DIRECTORY_SEPARATOR . 'sess_' . $id;
        if (is_writeable($this->savePath)) {
            if (!file_exists($file)) {
                touch($file);
            }
            $handle = fopen($file, 'r+');
            if (flock($handle, LOCK_EX)) {
                ftruncate($handle, 0);
                fwrite($handle, serialize($data));
                fflush($handle);
                flock($handle, LOCK_UN);
                fclose($handle);
                return true;
            }
            fclose($handle);
        }
        return false;
    }
}