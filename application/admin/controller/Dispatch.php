<?php



namespace app\admin\controller;




class Dispatch extends AdminBase
{
    public function dispatch_error($msg)
    {
        return $this->_error($msg);
    }

}