<?php
namespace app\models\user;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 用户收货地址
 * Class UserComputer
 * @package app\models\user
 */
class UserComputer extends BaseModel
{
  /**
   * 数据表主键
   * @var string
   */
  protected $pk = 'id';

  /**
   * 模型名称
   * @var string
   */
  protected $name = 'user_computer';

  use ModelTrait;

  protected $insert = ['add_time'];

  protected $hidden = ['add_time', 'is_del', 'uid'];

  protected function setAddTimeAttr()
  {
      return time();
  }

  public static function setDefaultComputer($id,$uid)
  {
      self::beginTrans();
      $res1 = self::where('uid',$uid)->update(['is_default'=>0]);
      $res2 = self::where('id',$id)->where('uid',$uid)->update(['is_default'=>1]);
      $res =$res1 !== false && $res2 !== false;
      self::checkTrans($res);
      return $res;
  }

  public static function userValidComputerWhere($model=null,$prefix = '')
  {
      if($prefix) $prefix .='.';
      $model = self::getSelfModel($model);
      return $model->where("{$prefix}is_del",0);
  }

  public static function getUserValidComputerList($uid,$page=1,$limit=8,$field = '*')
  {
      if($page) return self::userValidComputerWhere()->where('uid',$uid)->order('add_time DESC')->field($field)->page((int)$page,(int)$limit)->select()->toArray()?:[];
      else return self::userValidComputerWhere()->where('uid',$uid)->order('add_time DESC')->field($field)->select()->toArray()?:[];
  }

  public static function getUserDefaultComputer($uid,$field = '*')
  {
      return self::userValidComputerWhere()->where('uid',$uid)->where('is_default',1)->field($field)->find();
  }
}
