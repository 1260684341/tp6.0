<?php


namespace app\common\subscribe;


use think\Event;
use think\facade\Db;

class DbSubscribe
{
    private $start = false;

    public function onTaskStart()
    {
        if (!$this->start) {
            $this->start = true;
            Db::startTrans();
        }
    }

    public function onTaskSuccess()
    {
        if ($this->start) {
            Db::commit();
        }
    }

    public function onTaskFail()
    {
        if ($this->start) {
            Db::rollback();
        }
    }

    public function subscribe(Event $event)
    {
        $event->listen('dbStartTask', [$this,'onTaskStart']);
        $event->listen('dbCommit', [$this,'onTaskSuccess']);
        $event->listen('dbRollback',[$this,'onTaskFail']);
    }
}