<?php
namespace app\api\controller\user;

use app\http\validates\user\AddressValidate;
use think\exception\ValidateException;
use app\Request;
use crmeb\services\ExpressService as Express;
use app\admin\model\system\SystemAttachment;
use app\models\routine\RoutineQrcode;
use app\models\user\UserLevel;
use app\models\user\UserSign;
use app\models\routine\RoutineCode;
use app\models\routine\RoutineFormId;
use app\models\store\StoreBargain;
use app\models\store\StoreCart;
use app\models\store\StoreCombination;
use app\models\store\StoreCouponUser;
use app\models\store\StoreOrder;
use app\models\store\StoreOrderCartInfo;
use app\models\store\StoreProductRelation;
use app\models\store\StoreProductReply;
use app\models\store\StoreSeckill;
use app\models\user\User;
use app\models\user\UserAddress;
use app\models\user\UserAddressConf;
//lancercd  修改
use app\models\user\UserComputer;
use app\models\user\UserPrinter;

use app\models\user\UserBill;
use app\models\user\UserExtract;
use app\models\user\UserNotice;
use app\models\user\UserRecharge;
use crmeb\services\CacheService;
use crmeb\services\GroupDataService;
use crmeb\services\SystemConfigService;
use crmeb\services\UploadService;
use crmeb\services\UtilService;
use think\facade\Cache;

/**
 * 用户类
 * Class UserController
 * @package app\api\controller\store
 */
class UserController
{

    public function userInfo(Request $request)
    {
        return app('json')->success($request->user()->toArray());
    }

    /**
     * 用户资金统计
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function balance(Request $request)
    {
        $uid = $request->uid();
        $user['now_money'] = User::getUserInfo($uid, 'now_money')['now_money'];//当前总资金
        $user['recharge'] = UserBill::getRecharge($uid);//累计充值
        $user['orderStatusSum'] = StoreOrder::getOrderStatusSum($uid);//累计消费
        return app('json')->successful($user);
    }
    /**
     * 个人中心
     * @param Request $request
     * @return mixed
     */
    public function user(Request $request)
    {
        $user = $request->user();
        $user = $user->toArray();
        $user['couponCount'] = StoreCouponUser::getUserValidCouponCount($user['uid']);
        $user['like'] = StoreProductRelation::getUserIdCollect($user['uid']);
        $user['orderStatusNum'] = StoreOrder::getOrderData($user['uid']);
        $user['notice'] = UserNotice::getNotice($user['uid']);
        $user['brokerage'] = UserBill::getBrokerage($user['uid']);//获取总佣金
        $user['recharge'] = UserBill::getRecharge($user['uid']);//累计充值
        $user['orderStatusSum'] = StoreOrder::getOrderStatusSum($user['uid']);//累计消费
        $user['extractTotalPrice'] = UserExtract::userExtractTotalPrice($user['uid']);//累计提现
        $user['extractPrice'] = $user['brokerage_price'];//可提现
        $user['statu'] = (int)SystemConfigService::get('store_brokerage_statu');
        if(!SystemConfigService::get('vip_open'))
            $user['vip']=false;
        else{
            $vipId=UserLevel::getUserLevel($user['uid']);
            $user['vip']=$vipId !==false ? true : false;
            if($user['vip']){
                $user['vip_id']=$vipId;
                $user['vip_icon']=UserLevel::getUserLevelInfo($vipId,'icon');
                $user['vip_name']=UserLevel::getUserLevelInfo($vipId,'name');
            }
        }
        $user['yesterDay'] = UserBill::yesterdayCommissionSum($user['uid']);
        $user['recharge_switch'] = (int)SystemConfigService::get('recharge_switch');//充值开关
        $user['adminid'] = (boolean)\app\models\store\StoreService::orderServiceStatus($user['uid']);
        if($user['phone'] && $user['user_type'] != 'h5'){
            $user['switchUserInfo'][] = $request->user();
            if($h5UserInfo = User::where('account',$user['phone'])->where('user_type','h5')->find()){
                $user['switchUserInfo'][] = $h5UserInfo;
            }
        }else if($user['phone'] && $user['user_type'] == 'h5'){
            if($wechatUserInfo = User::where('phone',$user['phone'])->where('user_type','<>','h5')->find()){
                $user['switchUserInfo'][] = $wechatUserInfo;
            }
            $user['switchUserInfo'][] = $request->user();
        }else if(!$user['phone']){
            $user['switchUserInfo'][] = $request->user();
        }

        return app('json')->successful($user);
    }

    /**
     * 地址 获取单个
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function address(Request $request, $id)
    {
        $addressInfo = [];
        if($id && is_numeric($id) && UserAddress::be(['is_del'=>0,'id'=>$id,'uid'=>$request->uid()])){
            $addressInfo = UserAddress::find($id)->toArray();
        }
        return app('json')->successful($addressInfo);
    }

    public function computer(Request $request, $id)
    {
        $computerInfo = [];
        //var_dump(4);die;
        if($id && is_numeric($id) && UserComputer::be(['is_del'=>0,'id'=>$id,'uid'=>$request->uid()])){
            $computerInfo = UserComputer::find($id)->toArray();
        }
        return app('json')->successful($computerInfo);
    }

    public function printer(Request $request, $id)
    {
        $printerInfo = [];
        if($id && is_numeric($id) && UserPrinter::be(['is_del'=>0,'id'=>$id,'uid'=>$request->uid()])){
            $printerInfo = UserPrinter::find($id)->toArray();
        }
        return app('json')->successful($printerInfo);
    }

    /**
     * 地址列表
     * @param Request $request
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function address_list(Request $request)
    {
        list($page, $limit) = UtilService::getMore([['page',0], ['limit',20]],$request, true);
        $list = UserAddress::getUserValidAddressList($request->uid(),$page,$limit,'id,real_name,phone,province,city,district,detail,is_default');
        return app('json')->successful($list);
    }

    public function computer_list(Request $request)
    {

        list($page, $limit) = UtilService::getMore([['page',0], ['limit',20]],$request, true);
        // if($limit == 'undefined'){
          $limit = 8;
        // }
        $list = UserComputer::getUserValidComputerList($request->uid(),$page,$limit,'id,com_type,cpu,memory_size,graphics,hd,pro_date,sn_code,is_default');
        return app('json')->successful($list);
    }
    public function address_building(Request $request){
      $uid = $request->uid();
      $uidType = User::where('uid', $uid)->field('s_type')->find();
      $field = 'province, group_concat(district) as district, sort';
      if(!$uidType)
          return app('json')->failed('未找到改用户信息!');
      if($uidType['s_type'] == '老师')
          $res = UserAddressConf::where('is_del', 0)->where('mark', 0)->field($field)->order('sort DESC')->group('province')->select()->toArray();

      else if($uidType['s_type'] == '学生')
          $res = UserAddressConf::where('is_del', 0)->where('mark', 1)->order('sort DESC')->field($field)->group('province')->select()->toArray();
      else
           $res = UserAddressConf::where('is_del', 0)->order('sort DESC')->field($field)->group('province')->select()->toArray();
      foreach ($res as &$value) {
          $tempKeyName = $value['province'];
          $value[$tempKeyName] = explode(',', $value['district']);
          sort($value[$tempKeyName]);
          unset($value['district']);
          unset($value['province']);
          unset($value['sort']);
      }
      unset($value);
      // unset()
      return app('json')->successful($res);
    }

    public function printer_list(Request $request)
    {
        list($page, $limit) = UtilService::getMore([['page',0], ['limit',20]],$request, true);
        // if($limit == 'undefined'){
          $limit = 8;
        // }
        $list = UserPrinter::getUserValidPrinterList($request->uid(),$page,$limit,'id,printer_type,is_default');
        return app('json')->successful($list);
    }

    /**
     * 设置默认地址
     *
     * @param Request $request
     * @return mixed
     */
    public function address_default_set(Request $request)
    {
        list($id) = UtilService::getMore([['id',0]],$request, true);
        if(!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        if(!UserAddress::be(['is_del'=>0,'id'=>$id,'uid'=>$request->uid()]))
            return app('json')->fail('地址不存在!');
        $res = UserAddress::setDefaultAddress($id,$request->uid());
        if(!$res)
            return app('json')->fail('地址不存在!');
        else
            return app('json')->successful();
    }


    public function computer_default_set(Request $request)
    {
        list($id) = UtilService::getMore([['id',0]],$request, true);
        if(!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        if(!UserComputer::be(['is_del'=>0,'id'=>$id,'uid'=>$request->uid()]))
            return app('json')->fail('电脑不存在!');
        $res = UserComputer::setDefaultComputer($id,$request->uid());
        if(!$res)
            return app('json')->fail('电脑不存在!');
        else
            return app('json')->successful();
    }

    public function printer_default_set(Request $request)
    {
        list($id) = UtilService::getMore([['id',0]],$request, true);
        if(!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        if(!UserPrinter::be(['is_del'=>0,'id'=>$id,'uid'=>$request->uid()]))
            return app('json')->fail('打印机不存在!');
        $res = UserPrinter::setDefaultPrinter($id,$request->uid());
        if(!$res)
            return app('json')->fail('打印机不存在!');
        else
            return app('json')->successful();
    }

    /**
     * 获取默认地址
     * @param Request $request
     * @return mixed
     */
    public function address_default(Request $request)
    {
        $defaultAddress = UserAddress::getUserDefaultAddress($request->uid(),'id,real_name,phone,province,city,district,detail,is_default');
        if($defaultAddress) {
            $defaultAddress = $defaultAddress->toArray();
            return app('json')->successful('ok',$defaultAddress);
        }
        return app('json')->successful('empty',[]);
    }


    public function computer_default(Request $request)
    {
        $defaultComputer = UserComputer::getUserDefaultComputer($request->uid(),'id,com_type,cpu,graphics,hd,pro_date,sn_code,is_default');
        if($defaultComputer) {
            $defaultComputer = $defaultComputer->toArray();
            return app('json')->successful('ok',$defaultComputer);
        }
        return app('json')->successful('empty',[]);
    }



    public function printer_default(Request $request)
    {
        $defaultPrinter = UserPrinter::getUserDefaultPrinter($request->uid(),'id,printer_type,is_default');
        if($defaultPrinter) {
            $defaultPrinter = $defaultPrinter->toArray();
            return app('json')->successful('ok',$defaultPrinter);
        }
        return app('json')->successful('empty',[]);
    }


    /**
     * 修改 添加地址
     * @param Request $request
     * @return mixed
     */
    public function address_edit(Request $request)
    {
        $addressInfo = UtilService::postMore([
            ['province',0],
            ['is_default',false],
            ['real_name',''],
            ['post_code',''],
            ['phone',''],
            ['city',0],
            ['detail',''],
            ['id',0]
        ], $request);


        switch ((int)$addressInfo['province']) {
          case 0:
            $addressInfo['province'] = '博雅楼';
            break;
          case 1:
            $addressInfo['province'] = '校友会堂';
            break;
          case 2:
            $addressInfo['province'] = '行政楼';
            break;
          case 3:
            $addressInfo['province'] = '弘德楼';
            break;
          case 4:
            $addressInfo['province'] = '集贤楼';
            break;
          case 5:
            $addressInfo['province'] = '田家炳';
            break;
          case 6:
            $addressInfo['province'] = '知行楼';
            break;
          case 7:
            $addressInfo['province'] = '励志楼';
            break;
          case 8:
            $addressInfo['province'] = '田家炳';
            break;
          case 9:
            $addressInfo['province'] = '汇贤楼';
            break;
          case 10:
            $addressInfo['province'] = '图书馆';
            break;
          case 11:
            $addressInfo['province'] = '美术学院';
            break;
          case 12:
            $addressInfo['province'] = '音乐学院';
            break;
          case 13:
            $addressInfo['province'] = '物电学院';
            break;
          case 14:
            $addressInfo['province'] = '畅风苑';
            break;
          case 15:
            $addressInfo['province'] = '雅风苑';
            break;
          case 16:
            $addressInfo['province'] = '惠风苑';
            break;
          case 17:
            $addressInfo['province'] = '清风苑';
            break;
          case 18:
            $addressInfo['province'] = '嘉风苑';
            break;
          case 19:
            $addressInfo['province'] = '和风苑';
            break;
          case 20:
            $addressInfo['province'] = '清风苑';
            break;
          case 21:
            $addressInfo['province'] = '其他';
            break;
          default:
            $addressInfo['province'] = '啦啦啦   不知道';
            break;
        }
//      switch ()
//			{
//				case 0:
//				  $addressInfo[province] = '综合楼';
//				  break;
////				case 1:
////				  expression = label2 时执行的代码 ;
////				  break;
//				default:
////				  表达式的值不等于 label1 及 label2 时执行的代码;
//			}
//      if($addressInfo[province] == 0){
//      	$addressInfo[province] == '综合楼';
//      }

//      if(!isset($addressInfo['address']['province'])) return app('json')->fail('收货地址格式错误!');
//      if(!isset($addressInfo['address']['city'])) return app('json')->fail('收货地址格式错误!');
//      if(!isset($addressInfo['address']['district'])) return app('json')->fail('收货地址格式错误!');
//      $addressInfo['province'] = $addressInfo['address']['province'];
//      $addressInfo['city'] = $addressInfo['address']['city'];
//      $addressInfo['district'] = $addressInfo['address']['district'];

        $addressInfo['is_default'] = (int)$addressInfo['is_default'] == true ? 1 : 0;
        $addressInfo['uid'] = $request->uid();
        unset($addressInfo['address']);
//      try {
//          validate(AddressValidate::class)->check($addressInfo);
//      } catch (ValidateException $e) {
//          return app('json')->fail($e->getError());
//      }
        if($addressInfo['id'] && UserAddress::be(['id'=>$addressInfo['id'],'uid'=>$request->uid(),'is_del'=>0])){
            //var_dump($addressInfo);die;
            $id = $addressInfo['id'];
            unset($addressInfo['id']);
            if(UserAddress::edit($addressInfo,$id,'id')){
                if($addressInfo['is_default'])
                    UserAddress::setDefaultAddress($id,$request->uid());
                return app('json')->successful();
            }else
                return app('json')->fail('编辑收货地址失败!');
        }else{
            $addressInfo['add_time'] = time();
            if($address = UserAddress::create($addressInfo))
            {
                if($addressInfo['is_default'])
                {
                    UserAddress::setDefaultAddress($address->id,$request->uid());
                }
                return app('json')->successful(['id'=>$address->id]);
            }else{
                return app('json')->fail('添加收货地址失败!');
            }
        }
    }

    public function computer_edit(Request $request)
    {
//  	var_dump(2);die;
        $computerInfo = UtilService::postMore([
            ['computer',[]],
            ['is_default',false],
            ['com_type',''],
            ['cpu',''],
            ['memory_size', ''],
            ['graphics',''],
            ['hd',''],
            ['pro_date',''],
            ['sn_code',''],
            ['id',0]
        ], $request);

//      $computerInfo['com_type'] = $computerInfo['computer']['com_type'];
//      $computerInfo['cpu'] = $computerInfo['computer']['cpu'];
//      $computerInfo['graphics'] = $computerInfo['computer']['graphics'];
//      $computerInfo['hd'] = $computerInfo['computer']['hd'];
//      $computerInfo['pro_date'] = $computerInfo['computer']['pro_date'];
//      $computerInfo['sn_code'] = $computerInfo['computer']['sn_code'];
        $computerInfo['is_default'] = (int)$computerInfo['is_default'] == true ? 1 : 0;
        $computerInfo['uid'] = $request->uid();
        unset($computerInfo['computer']);

        if($computerInfo['id'] && UserComputer::be(['id'=>$computerInfo['id'],'uid'=>$request->uid(),'is_del'=>0])){
            $id = $computerInfo['id'];
            unset($computerInfo['id']);
            if(UserComputer::edit($computerInfo,$id,'id')){
                if($computerInfo['is_default'])
                    UserComputer::setDefaultComputer($id,$request->uid());
                return app('json')->successful();
            }else
                return app('json')->fail('编辑电脑信息失败!');
        }else{
            $computerInfo['add_time'] = time();
            if($computer = UserComputer::create($computerInfo))
            {
                if($computerInfo['is_default'])
                {
                    UserComputer::setDefaultComputer($computer->id,$request->uid());
                }
                return app('json')->successful(['id'=>$computer->id]);
            }else{
                return app('json')->fail('添加电脑信息失败!');
            }
        }
    }


    public function printer_edit(Request $request)
    {

        $printerInfo = UtilService::postMore([
            ['printer',[]],
            ['printer_type',''],
            ['is_default',false],
            ['printer_type',''],
            ['id',0]
        ], $request);
        $printerInfo['printer']['printer_type'] = $printerInfo['printer_type'];
        if(!isset($printerInfo['printer']['printer_type'])) return app('json')->fail('不能未空');
        $printerInfo['printer_type'] = $printerInfo['printer']['printer_type'];
        $printerInfo['is_default'] = (int)$printerInfo['is_default'] == true ? 1 : 0;
        $printerInfo['uid'] = $request->uid();
        unset($printerInfo['printer']);
//      try {
//          validate(PrinterValidate::class)->check($printerInfo);
//      } catch (ValidateException $e) {
//          return app('json')->fail($e->getError());
//      }
        if($printerInfo['id'] && UserPrinter::be(['id'=>$printerInfo['id'],'uid'=>$request->uid(),'is_del'=>0])){
            $id = $printerInfo['id'];
            unset($printerInfo['id']);
            if(UserPrinter::edit($printerInfo,$id,'id')){
                if($printerInfo['is_default'])
                    UserPrinter::setDefaultPrinter($id,$request->uid());
                return app('json')->successful();
            }else
                return app('json')->fail('编辑打印机信息失败!');
        }else{
            $printerInfo['add_time'] = time();
            if($printer = UserPrinter::create($printerInfo))
            {
                if($printerInfo['is_default'])
                {
                    UserPrinter::setDefaultPrinter($printer->id,$request->uid());
                }
                return app('json')->successful(['id'=>$printer->id]);
            }else{
                return app('json')->fail('添加打印机信息失败!');
            }
        }
    }

    /**
     * 删除地址
     *
     * @param Request $request
     * @return mixed
     */
    public function address_del(Request $request)
    {
        list($id) = UtilService::postMore([['id',0]], $request, true);
        if(!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        if(!UserAddress::be(['is_del'=>0,'id'=>$id,'uid'=>$request->uid()]))
            return app('json')->fail('地址不存在!');
        if(UserAddress::edit(['is_del'=>'1'],$id,'id'))
            return app('json')->successful();
        else
            return app('json')->fail('删除地址失败!');
    }

    public function computer_del(Request $request)
    {
        list($id) = UtilService::postMore([['id',0]], $request, true);
        if(!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        if(!UserComputer::be(['is_del'=>0,'id'=>$id,'uid'=>$request->uid()]))
            return app('json')->fail('电脑信息不存在!');
        if(UserComputer::edit(['is_del'=>'1'],$id,'id'))
            return app('json')->successful();
        else
            return app('json')->fail('删除电脑信息失败!');
    }

    public function printer_del(Request $request)
    {
        list($id) = UtilService::postMore([['id',0]], $request, true);
        if(!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        if(!UserPrinter::be(['is_del'=>0,'id'=>$id,'uid'=>$request->uid()]))
            return app('json')->fail('打印机信息不存在!');
        if(UserPrinter::edit(['is_del'=>'1'],$id,'id'))
            return app('json')->successful();
        else
            return app('json')->fail('删除打印机信息失败!');
    }



    /**
     * 获取收藏产品
     *
     * @param Request $request
     * @return mixed
     */
    public function collect_user(Request $request)
    {
        list($page, $limit) = UtilService::getMore([
            ['page',0],
            ['limit',0]
        ], $request, true);
        if(!(int)$limit) return  app('json')->successful([]);
        $productRelationList = StoreProductRelation::getUserCollectProduct($request->uid(), (int)$page, (int)$limit);
        return app('json')->successful($productRelationList);
    }

    /**
     * 添加收藏
     * @param Request $request
     * @param $id
     * @param $category
     * @return mixed
     */
    public function collect_add(Request $request)
    {
        list($id, $category) = UtilService::postMore([['id',0], ['category','product']], $request, true);
        if(!$id || !is_numeric($id)) return app('json')->fail('参数错误');
        $res = StoreProductRelation::productRelation($id, $request->uid(),'collect', $category);
        if(!$res) return app('json')->fail(StoreProductRelation::getErrorInfo());
        else return app('json')->successful();
    }

    /**
     * 取消收藏
     *
     * @param Request $request
     * @return mixed
     */
    public function collect_del(Request $request)
    {
        list($id, $category) = UtilService::postMore([['id',0], ['category','product']], $request, true);
        if(!$id || !is_numeric($id)) return app('json')->fail('参数错误');
        $res = StoreProductRelation::unProductRelation($id, $request->uid(),'collect', $category);
        if(!$res) return app('json')->fail(StoreProductRelation::getErrorInfo());
        else return app('json')->successful();
    }

    /**
     * 批量收藏
     * @param Request $request
     * @return mixed
     */
    public function collect_all(Request $request)
    {
        $collectInfo = UtilService::postMore([
            ['id',[]],
            ['category','product'],
        ], $request);
        if(!count($collectInfo['id'])) return app('json')->fail('参数错误');
        $productIdS = $collectInfo['id'];
        $res = StoreProductRelation::productRelationAll($productIdS, $request->uid(),'collect', $collectInfo['category']);
        if(!$res) return app('json')->fail(StoreProductRelation::getErrorInfo());
        else return app('json')->successful('收藏成功');
    }

    /**
     * 添加点赞
     *
     * @param Request $request
     * @return mixed
     */
//    public function like_add(Request $request)
//    {
//        list($id, $category) = UtilService::postMore([['id',0], ['category','product']], $request, true);
//        if(!$id || !is_numeric($id))  return app('json')->fail('参数错误');
//        $res = StoreProductRelation::productRelation($id,$request->uid(),'like',$category);
//        if(!$res) return  app('json')->fail(StoreProductRelation::getErrorInfo());
//        else return app('json')->successful();
//    }

    /**
     * 取消点赞
     *
     * @param Request $request
     * @return mixed
     */
//    public function like_del(Request $request)
//    {
//        list($id, $category) = UtilService::postMore([['id',0], ['category','product']], $request, true);
//        if(!$id || !is_numeric($id)) return app('json')->fail('参数错误');
//        $res = StoreProductRelation::unProductRelation($id, $request->uid(),'like',$category);
//        if(!$res) return app('json')->fail(StoreProductRelation::getErrorInfo());
//        else return app('json')->successful();
//    }

    /**
     * 签到 配置
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sign_config()
    {
       $signConfig = GroupDataService::getData('sign_day_num') ?? [];
       return app('json')->successful($signConfig);
    }

    /**
     * 签到 列表
     * @param Request $request
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function sign_list(Request $request)
    {
        list($page, $limit) = UtilService::getMore([
            ['page',0],
            ['limit',0]
        ], $request, true);
        if(!$limit) return  app('json')->successful([]);
        $signList = UserSign::getSignList($request->uid(),(int)$page,(int)$limit);
        if($signList) $signList = $signList->toArray();
        return app('json')->successful($signList);
    }

    /**
     * 签到
     * @param Request $request
     * @return mixed
     */
    public function sign_integral(Request $request)
    {
        $signed = UserSign::getToDayIsSign($request->uid());
        if($signed) return app('json')->fail('已签到');
        if(false !== ($integral = UserSign::sign($request->uid())))
            return app('json')->successful('签到获得'.floatval($integral).'积分',['integral'=>$integral]);
        return app('json')->fail(UserSign::getErrorInfo('签到失败'));
    }

    /**
     * 签到用户信息
     * @param Request $request
     * @return mixed
     */
    public function sign_user(Request $request)
    {
        list($sign,$integral,$all) = UtilService::postMore([
            ['sign',0],
            ['integral',0],
            ['all',0],
        ],$request,true);
        $user = $request->user();
        //是否统计签到
        if($sign || $all){
            $user['sum_sgin_day'] = UserSign::getSignSumDay($user['uid']);
            $user['is_day_sgin'] = UserSign::getToDayIsSign($user['uid']);
            $user['is_YesterDay_sgin'] = UserSign::getYesterDayIsSign($user['uid']);
            if(!$user['is_day_sgin'] && !$user['is_YesterDay_sgin']){ $user['sign_num'] = 0;}
        }
        //是否统计积分使用情况
        if($integral || $all){
            $user['sum_integral'] = (int)UserBill::getRecordCount($user['uid'],'integral','sign,system_add,gain');
            $user['deduction_integral'] = (int)UserBill::getRecordCount($user['uid'],'integral','deduction') ?? 0;
            $user['today_integral'] = (int)UserBill::getRecordCount($user['uid'],'integral','sign,system_add,gain','today');
        }
        unset($user['pwd']);
        if(!$user['is_promoter']){
            $user['is_promoter']=(int)SystemConfigService::get('store_brokerage_statu') == 2 ? true : false;
        }
        return app('json')->successful($user->hidden(['account','real_name','birthday','card_id','mark','partner_id','group_id','add_time','add_ip','phone','last_time','last_ip','spread_uid','spread_time','user_type','status','level','clean_time','addres'])->toArray());
    }

    /**
     * 签到列表（年月）
     *
     * @param Request $request
     * @return mixed
     */
    public function sign_month(Request $request)
    {
        list($page, $limit) = UtilService::getMore([
            ['page',0],
            ['limit',0]
        ], $request, true);
        if(!$limit) return  app('json')->successful([]);
        $userSignList = UserSign::getSignMonthList($request->uid(), (int)$page, (int)$limit);
        return app('json')->successful($userSignList);
    }

    /**
     * 获取活动状态
     * @return mixed
     */
    public function activity()
    {
        $data['is_bargin'] = StoreBargain::validBargain() ? true : false;
        $data['is_pink'] = StoreCombination::getPinkIsOpen() ? true : false;
        $data['is_seckill'] = StoreSeckill::getSeckillCount() ? true : false;
        return app('json')->successful($data);
    }

    /**
     * 用户修改信息
     * @param Request $request
     * @return mixed
     */
    public function edit(Request $request)
    {
        list($avatar,$nickname) = UtilService::postMore([
            ['avatar',''],
            ['nickname',''],
        ],$request,true);
        if(User::editUser($avatar,$nickname,$request->uid())) return app('json')->successful('修改成功');
        return app('json')->fail('修改失败');
    }

    //获取打印机订单
    public function get_printer_order(Request $request)
   {
       $uid=$request->uid();
       $list=StoreOrder::where('uid',$uid)->where('product_type','1')->where('status',3)->where('is_del','0')->field([
         'id', 'order_id', 'type_id', 'product_type', 'user_address','total_price', 'paid', 'pay_time', 'pay_type', 'add_time', 'status', 'delivery_name', 'delivery_id', 'delivery_type', 'mark', 'remark'
         ])->select();
       $list=$list->toArray();
       foreach ($list as $key => $value){
         $printer_type = UserPrinter::where('id', $list[$key]['type_id'])->field('printer_type')->find();
           $list[$key]['add_time']=date('Y-m-d H:m:s',$value['add_time']);
           $list[$key]['printer_type'] = $printer_type;
       };
       list($type,$page,$limit,$search) = UtilService::getMore([
           ['type',''],
           ['page',0],
           ['limit',''],
           ['search',''],
       ],$request,true);
       if(empty($list)) return app('json')->successful("未找到该设备维修记录");
       return app('json')->successful($list);
   }

    public function get_computer_order(Request $request)
    {
        $uid=$request->uid();
        $list=StoreOrder::where('uid',$uid)->where('product_type','2')->where('is_del','0')->where('status',3)->field([
          'id', 'order_id', 'type_id', 'product_type', 'user_address', 'paid', 'pay_time', 'pay_type', 'add_time', 'status', 'delivery_name', 'total_price', 'delivery_id', 'delivery_type', 'mark', 'remark'
          ])->select();
          // halt($list);die;
        $list=$list->toArray();
        foreach ($list as $key => $value){
          $com_type = UserComputer::where('id', $list[$key]['type_id'])->field('com_type')->find();
            $list[$key]['add_time']=date('Y-m-d H:m:s',$value['add_time']);
            $list[$key]['com_type'] = $com_type;
        };
        list($type,$page,$limit,$search) = UtilService::getMore([
            ['type',''],
            ['page',0],
            ['limit',''],
            ['search',''],
        ],$request,true);
        if(empty($list)) return app('json')->successful("未找到该设备维修记录");
        return app('json')->successful($list);
    }
    // lancercd
    public function set_sch_userType(Request $request){
      $userTypeInfo = UtilService::postMore([
          ['flag',],//1学生 0老师
          ['school_num',''],//学号或工号
          ['college',''],//学院
          ['id', 0]
      ], $request);
      if(!isset($userTypeInfo['flag'])) return app('json')->fail('用户类型格式错误！');
      if(!isset($userTypeInfo['school_num'])) return app('json')->fail('学号或工号输入错误');
      if(!isset($userTypeInfo['college'])) return app('json')->fail('学院输入错误!');
      $userTypeInfo['flag'] = ($userTypeInfo['flag'] == 0)? '学生':'老师';
      switch ($userTypeInfo['college']) {
       case 1:
         $userTypeInfo['college'] = '计信学院';
         break;
       case 2:
         $userTypeInfo['college'] = '文学院';
         break;
       case 3:
         $userTypeInfo['college'] = '历史与社会学院';
         break;
       case 4:
         $userTypeInfo['college'] = '地理与旅游学院';
         break;
       case 5:
         $userTypeInfo['college'] = '数学科学学院';
         break;
       case 6:
         $userTypeInfo['college'] = '经济与管理学院';
         break;
       case 7:
         $userTypeInfo['college'] = '物电学院';
         break;
       case 8:
         $userTypeInfo['college'] = '音乐学院';
         break;
       case 9:
         $userTypeInfo['college'] = '继续教育学院';
         break;
       case 10:
         $userTypeInfo['college'] = '生命科学学院';
         break;
       case 11:
         $userTypeInfo['college'] = '马克思主义学院';
         break;
       case 12:
         $userTypeInfo['college'] = '外国语学院';
         break;
       case 13:
         $userTypeInfo['college'] = '国际汉语文化学院';
         break;
       case 14:
         $userTypeInfo['college'] = '职教师资学院';
         break;
       case 15:
         $userTypeInfo['college'] = '初等教育学院';
         break;
       case 16:
         $userTypeInfo['college'] = '美术学院';
         break;
       case 17:
         $userTypeInfo['college'] = '教育科学学院';
         break;
       case 18:
         $userTypeInfo['college'] = '新闻与传媒学院';
         break;
       case 19:
         $userTypeInfo['college'] = '体育学院';
         break;
       case 20:
         $userTypeInfo['college'] = '化学学院';
         break;
       default:
         $userTypeInfo['college'] = '其他学院';
         break;
      }
      $userTypeInfo['uid'] = $request->uid();
      if($res = User::where('uid',$request->uid())->update([
              's_type'=>$userTypeInfo['flag'],
              'stu_num'=>$userTypeInfo['school_num'],
              'college'=>$userTypeInfo['college'],
      ]))
      {
        return app('json')->successful($userTypeInfo);
      }else{
          return app('json')->fail('添加信息失败!');
      }
    }



    public function get_sch_userType(Request $request){
      $uid = $request->uid();
      if($res = User::where('uid', $uid)->field(['s_type', 'stu_num', 'college'])->find())
      {
        return app('json')->successful($res->toArray());
      }else{
          return app('json')->fail('添加信息失败!');
      }
    }





    function test(){
        $order_id=StoreOrder::max('id');                                        //TODO    这里是是判断类型
        $type=StoreOrder::where('id',$order_id)->value('cart_id');
        $type=substr($type,1,-1);
        $type=StoreCart::where('id',$type)->value('product_id');
        if($type==2) StoreOrder::where('id',$order_id)->update(['product_type' => 1]);
        if($type==1) StoreOrder::where('id',$order_id)->update(['product_type' => 0]);
    }

    function printer_record(Request $res){
        $type_id=$res->get('id');
        if (!$type_id) return app('json')->fail('查找失败！');
        $data=StoreOrder::where('type_id',$type_id)->where('product_type', 1)->select()->toArray();
        if(!$data) return app('json')->successful("该设备没有维修记录");
        foreach ($data as $key => $value){
          $type=UserPrinter::where('id',$value['type_id'])->field('printer_type')->find()->toArray();
          $data[$key]['type']=$type['printer_type'];
          $data[$key]['add_time']=date('Y-m-d H:m:s',$value['add_time']);
        }

        return app('json')->successful($data);
    }


    function computer_record(Request $res){
        $type_id=$res->get('id');
        if (!$type_id) return app('json')->fail('查找失败！');
        $data=StoreOrder::where('type_id',$type_id)->where('product_type', 2)->select()->toArray();
        if(!$data) return app('json')->successful("该设备没有维修记录");
        foreach ($data as $key => $value){
            $type=UserComputer::where('id',$value['type_id'])->field('com_type')->find()->toArray();
            $data[$key]['type']=$type['com_type'];
            $data[$key]['add_time']=date('Y-m-d H:m:s',$value['add_time']);
        }
        return app('json')->successful($data);
    }





}
