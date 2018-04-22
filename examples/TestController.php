<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/2
 * Time: 18:22
 */

namespace Surf\Examples;

use Surf\Mvc\Controller\HttpController;
use Surf\Pool\PoolManager;
use Surf\Server\Http\Cookie\CookieAttributes;
use Surf\Task\PushTaskHandle;

class TestController extends HttpController
{
    public function index()
    {
        /**
         * @var $pool PoolManager
         */
        $pool = $this->container->get('pool');
        /**
         * @var $pdo \Surf\Pool\Connection
         */
        $pdo  = $pool->pop('database.default');
        $id = mt_rand(1, 10000);
        $range = range('A', 'Z');
        $suffix = $range[mt_rand(0, 25)];
        //$pdo->insert('insert into  `user` (`id`, `name`) VALUES ('. $id .', \'' . '小明' . $suffix . '\')');
        $all = $pdo->select('SELECT * FROM `user`');
        $pdo->close();

        $table = '<table>';
        if ($all) {
            foreach ($all as $item) {
                $table .= '<tr>';
                $table .= '<td>'. $item->id .'</td>';
                $table .= '<td>'. $item->name .'</td>';
                $table .= '</tr>';
            }
        }
        $table .= '</table>';
        return $table;
    }

    /**
     *
     */
    public function session()
    {
        $this->request->session->set('TEST_SESSION', 'Hello Session');

        return "Session";
    }

    /**
     * 
     */
    public function sessionCookie()
    {
        $this->request->session->set('SESSION_USER', 'Hello Session');

        $this->cookies->set(CookieAttributes::create('TEST_COOKIE', 'Hello Cookie', time() + 7200));
        return $this->session->get('SESSION_USER');
    }

    public function taskTest()
    {
        $taskId = $this->task('push all message', PushTaskHandle::class);
        $status = $this->syncTask('sync push all message', PushTaskHandle::class);
        var_dump($status);
        return "task push id:" . $taskId;
    }
}
