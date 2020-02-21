<?php

/**
 *
 * @author: lancercd
 * @day: 2020/2/16
 */
namespace app\models\school;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;


class School extends BaseModel
{
    protected $pk = 'id';
    // protected $hidden = ['id', 'sort'];


    public static function setSchool($date)
    {
        if(is_array($date)){
            return self::create([
                'name' => $date['name'],
                'sort' => is_null($date['sort'])? 99:$data['sort']
            ]);
        }else if(is_string($data)){
            return self::create([
                'name' => $date['name'],
                'sort' => is_null($date['sort'])? 99:$data['sort']
            ]);
        }else{
            return false;
        }
    }

    public static function getSchoolAll($where)
    {
        $data = self::page((int)$where['page'],(int)$where['limit'])->order('sort', 'asc')->select();
        $count = self::count();
        return ['count'=>$count,'data'=>$data];
    }

    public static function getSchool($id)
    {
        return self::where('id', $id)->find()->toArray();
    }

    public static function updateSort(array $date)
    {
        self::where('id', $date['id'])->update(['sort'=>$date['sort']]);
    }
}
