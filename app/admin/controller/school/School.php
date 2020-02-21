<?php
namespace app\admin\controller\school;

use crmeb\services\UtilService as Util;
use app\admin\controller\AuthController;
use app\models\school\School as SchoolModel;
use app\Request;
use crmeb\services\FormBuilder as Form;
use crmeb\services\JsonService as Json;




class School extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }

    public function add()
    {
        return "this is School's add method";
    }





    public function get_school_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['id',''],
            ['name',''],
            ['sort','']
        ]);
        return Json::successlayui(SchoolModel::getSchoolAll($where));
        // return "ok";
    }
}
