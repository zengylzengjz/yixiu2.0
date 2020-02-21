<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */
namespace app\admin\controller\order;

use app\admin\controller\AuthController;
use app\admin\model\system\Express;
use app\admin\model\user\UserComputer;
use app\admin\model\user\UserPrinter;
use crmeb\repositories\OrderRepository;
use crmeb\services\ExpressService;
use crmeb\services\MiniProgramService;
use crmeb\services\UtilService;
use crmeb\services\WechatService;
use crmeb\services\FormBuilder as Form;
use crmeb\services\HookService;
use crmeb\subscribes\OrderSubscribe;
use app\admin\model\order\StoreOrderStatus;
use app\admin\model\ump\StorePink;
use app\admin\model\user\User;
use app\admin\model\user\UserBill;
use crmeb\basic\BaseModel;
use crmeb\services\CacheService;
use crmeb\services\JsonService;
use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;
use think\facade\Db;
use think\facade\Route as Url;
use app\admin\model\order\StoreOrder as StoreOrderModel;
use app\admin\model\system\SystemAdmin;
/**
 * 订单管理控制器 同一个订单表放在一个控制器
 * Class StoreOrder
 * @package app\admin\controller\store
 */
class StoreOrder extends AuthController
{
    /**
     * @return mixed
     */
    public function index()
    {
        $this->assign([
            'year'=>getMonth(),
            'real_name'=>$this->request->get('real_name',''),
            'status'=>$this->request->param('status',''),
            'orderCount'=>StoreOrderModel::orderCount(),
            'payTypeCount'=>StoreOrderModel::payTypeCount(),
        ]);
        return $this->fetch();
    }
    /**
     * 获取头部订单金额等信息
     * return json
     *
     */
    public function getBadge(){
        $where = Util::postMore([
            ['status',''],
            ['real_name',''],
            ['is_del',0],
            ['data',''],
            ['type',''],
            ['pay_type',''],
            ['order','']
        ]);
        return JsonService::successful(StoreOrderModel::getBadge($where));
    }
    /**
     * 获取订单列表
     * return json
     */
    public function order_list(){
        $where = Util::getMore([
            ['status',''],
            ['real_name',$this->request->param('real_name','')],
            ['is_del',0],
            ['data',''],
            ['type',''],
            ['pay_type',''],
            ['order',''],
            ['page',1],
            ['limit',20],
            ['excel',0]
        ]);
        return JsonService::successlayui(StoreOrderModel::OrderList($where));
    }
    public function orderchart(){
        $where = Util::getMore([
            ['status',''],
            ['real_name',''],
            ['is_del',0],
            ['data',''],
            ['combination_id',''],
            ['export',0],
            ['order','id desc']
        ],$this->request);
        $limitTimeList = [
            'today'=>implode(' - ',[date('Y/m/d'),date('Y/m/d',strtotime('+1 day'))]),
            'week'=>implode(' - ',[
                date('Y/m/d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)),
                date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600))
            ]),
            'month'=>implode(' - ',[date('Y/m').'/01',date('Y/m').'/'.date('t')]),
            'quarter'=>implode(' - ',[
                date('Y').'/'.(ceil((date('n'))/3)*3-3+1).'/01',
                date('Y').'/'.(ceil((date('n'))/3)*3).'/'.date('t',mktime(0,0,0,(ceil((date('n'))/3)*3),1,date('Y')))
            ]),
            'year'=>implode(' - ',[
                date('Y').'/01/01',date('Y/m/d',strtotime(date('Y').'/01/01 + 1year -1 day'))
            ])
        ];
        if($where['data'] == '') $where['data'] = $limitTimeList['today'];
        $orderCount = [
            urlencode('未支付')=>StoreOrderModel::getOrderWhere($where,StoreOrderModel::statusByWhere(0))->count(),
            urlencode('未发货')=>StoreOrderModel::getOrderWhere($where,StoreOrderModel::statusByWhere(1))->count(),
            urlencode('待收货')=>StoreOrderModel::getOrderWhere($where,StoreOrderModel::statusByWhere(2))->count(),
            urlencode('待评价')=>StoreOrderModel::getOrderWhere($where,StoreOrderModel::statusByWhere(3))->count(),
            urlencode('交易完成')=>StoreOrderModel::getOrderWhere($where,StoreOrderModel::statusByWhere(4))->count(),
            urlencode('退款中')=>StoreOrderModel::getOrderWhere($where,StoreOrderModel::statusByWhere(-1))->count(),
            urlencode('已退款')=>StoreOrderModel::getOrderWhere($where,StoreOrderModel::statusByWhere(-2))->count()
        ];
        $model = StoreOrderModel::getOrderWhere($where,new StoreOrderModel())->field('sum(total_num) total_num,count(*) count,sum(total_price) total_price,sum(refund_price) refund_price,from_unixtime(add_time,\'%Y-%m-%d\') add_time')
            ->group('from_unixtime(add_time,\'%Y-%m-%d\')');
        $orderPrice = $model->select()->toArray();
        $orderDays = [];
        $orderCategory = [
            ['name'=>'商品数','type'=>'line','data'=>[]],
            ['name'=>'订单数','type'=>'line','data'=>[]],
            ['name'=>'订单金额','type'=>'line','data'=>[]],
            ['name'=>'退款金额','type'=>'line','data'=>[]]
        ];
        foreach ($orderPrice as $price){
            $orderDays[] = $price['add_time'];
            $orderCategory[0]['data'][] = $price['total_num'];
            $orderCategory[1]['data'][] = $price['count'];
            $orderCategory[2]['data'][] = $price['total_price'];
            $orderCategory[3]['data'][] = $price['refund_price'];
        }
        $this->assign(StoreOrderModel::systemPage($where,$this->adminId));
        $this->assign('price',StoreOrderModel::getOrderPrice($where));
        $this->assign(compact('limitTimeList','where','orderCount','orderPrice','orderDays','orderCategory'));
        return $this->fetch();
    }
    /**
     * 修改支付金额等
     * @param $id
     * @return mixed|\think\response\Json|void
     */
     public function edit($id)
        {
            if(!$id) return $this->failed('数据不存在');
            $product = StoreOrderModel::get($id);
            if(!$product) return Json::fail('数据不存在!');
            $uid=$product->getData('uid');
            $f = array();
            $f[] = Form::input('order_id','订单编号',$product->getData('order_id'))->disabled(1);
            $f[] = Form::input('real_name','真实姓名',$product->getData('real_name'));
            $f[] = Form::input('user_phone','用户电话',$product->getData('user_phone'));
            $f[] = Form::input('user_address','详细地址',$product->getData('user_address'));
            $f[] = Form::number('total_price','维修金额',$product->getData('total_price'));
            if ($product->getData('product_type')==1){
                $f[] = Form::radio('mark','处理类型')->options([['value'=>0,'label'=>'加墨'],['value'=>1,'label'=>'换硒鼓']]);
                $userPrinterInfo = UserPrinter::where('is_del',0)->where('uid', $uid)->field(['id', 'printer_type', 'is_default'])->select();
                $f[]= Form::select('type_id','打印机信息')->setOptions(function() use($userPrinterInfo) {
                    $productMenus=[];
                    foreach ($userPrinterInfo as $productmenu){
                        $productMenus[] = ['value'=>$productmenu['id'],'label'=>
                            'ID: '.$productmenu['id'].
                            '  类型:'.$productmenu['printer_type']
                        ];
                    }
                    return $productMenus;
                });
            }
            if ($product->getData('product_type')==2){
                $userComputerInfo = UserComputer::where('is_del',0)->where('uid', $uid)->field(['id', 'com_type', 'cpu','memory_size', 'graphics', 'hd', 'pro_date', 'sn_code', 'is_default'])->select();
                $f[]=Form::select('type_id','电脑信息')->setOptions(function() use($userComputerInfo) {
                    $computerMenus=[];
                    foreach ($userComputerInfo as $computermenu){
                        $computerMenus[] = ['value'=>$computermenu['id'],'label'=>
                            'ID:'.$computermenu['id'].
                            ' 型号: '.$computermenu['com_type'].
                            '  CPU:'.$computermenu['cpu'].
                            ' 显卡: '.$computermenu['graphics'].
                            ' 硬盘: '.$computermenu['hd'].
                            ' 生产日期: '.$computermenu['pro_date'].
                            ' 保修码: '.$computermenu['sn_code'].
                            ' 是否默认: '.$computermenu['is_default']
                        ];
                    }
                    return $computerMenus;
                });
                $f[] = Form::input('mark','维修备注',$product->getData('mark'));

            }
    //        $f[] = Form::radio('status','状态',$product->getData('status'))->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]]);
            $form = Form::make_post_form('修改订单',$f,Url::buildUrl('update',array('id'=>$id)));
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');

        }

        /**
         * 修改订单提交更新
         * @param $id
         */
        public function update($id)
        {
            $data = Util::postMore([
                'order_id',
                'real_name',
                'user_phone',
                'user_address',
                'total_price',
                'type_id',
                'mark'
            ]);
            if($data['mark'] == '') Json::fail('未选择处理类型!');
            if($data['mark'] == '0')
                $data['mark'] = '加墨';
            if($data['mark'] == '1')
                $data['mark'] = '换硒鼓';
            else
                $data['mark'] = '未知';
            $data['order_id'] = StoreOrderModel::changeOrderId($data['order_id']);
            StoreOrderModel::edit($data,$id);
            event('StoreProductOrderEditAfter',[$data,$id]);
            return Json::successful('修改成功!');
        }
        public function order_goods($id = 0)
            {
                $list =  Express::where('is_show',1)->order('sort desc')->column('name','id');
                $this->assign([
                    'list'=>$list,
                    'id'=>$id
                ]);
                return $this->fetch();
            }
    /*
     * 删除订单
     * */
    public function del_order()
    {
        $ids = UtilService::postMore(['ids'])['ids'];
        if(!count($ids)) return JsonService::fail('请选择需要删除的订单');
        // if(StoreOrderModel::where('is_del',0)->where('id','in',$ids)->count()) return JsonService::fail('您选择的的订单存在用户未删除的订单，无法删除用户未删除的订单');
        $res=StoreOrderModel::where('id','in',$ids)->update(['is_system_del'=>1]);
        if($res)
            return JsonService::successful('删除成功');
        else
            return JsonService::fail('删除失败');
    }

    /**
     * TODO 送货信息提交
     * @param Request $request
     * @param $id
     */
     //lancercd11/3
    public function update_delivery($id = 0){
        $data = Util::postMore([
            ['type',1],
            ['delivery_name',''],
            ['delivery_id',''],
            ['sh_delivery_name',''],
            ['sh_delivery_id',''],
        ],$this->request);
        if($data['sh_delivery_name'] == '' && $data['sh_delivery_name'] == ''){
          $data['sh_delivery_name'] = $data['delivery_name'];
          $data['sh_delivery_id'] = $data['delivery_id'];
        }
        $data['type'] = (int)$data['type'];
        if($data['type'] == 1 || $data['type'] == 2)
          $data['type'] == 2;
        switch ($data['type']){
            case 1:
                //发货
                $data['delivery_type'] = 'express';
                if(!$data['delivery_name']) return Json::fail('请选择快递公司');
                if(!$data['delivery_id']) return Json::fail('请输入快递单号');
                $data['status'] = 1;
                StoreOrderModel::edit($data,$id);
                event('StoreProductOrderDeliveryGoodsAfter',[$data,$id]);
                StoreOrderStatus::setStatus($id,'delivery_goods','已发货 快递公司：'.$data['delivery_name'].' 快递单号：'.$data['delivery_id']);
                break;
            case 2:
                //送货
                $data['delivery_type'] = 'send';
                $data['delivery_name'] = $data['sh_delivery_name'];
                $data['delivery_id'] = $data['sh_delivery_id'];
                unset($data['sh_delivery_name'],$data['sh_delivery_id']);
                if(!$data['delivery_name']) return Json::fail('请输入维修人员姓名');
                if(!(int)$data['delivery_id']) return Json::fail('请输入维修人员电话号码');
                else if(!preg_match("/^1[3456789]{1}\d{9}$/",$data['delivery_id']))  return Json::fail('请输入正确的维修人员电话号码');
                $data['status'] = 1;
                StoreOrderModel::edit($data,$id);
                event('StoreProductOrderDeliveryAfter',[$data,$id]);
                StoreOrderStatus::setStatus($id,'delivery','已配送 发货人：'.$data['delivery_name'].' 发货人电话：'.$data['delivery_id']);
                break;
            case 3:
                //上门维修
                $data['delivery_name'] = $data['sh_delivery_name'];
                $data['delivery_id'] = $data['sh_delivery_id'];
                unset($data['sh_delivery_name'],$data['sh_delivery_id']);
                if(!$data['delivery_name']) return Json::fail('请输入维修人员姓名');
                if(!(int)$data['delivery_id']) return Json::fail('请输入维修人员电话号码');
                else if(!preg_match("/^1[3456789]{1}\d{9}$/",$data['delivery_id']))  return Json::fail('请输入正确的维修人员电话号码');



                $data['delivery_type'] = 'fictitious';
                $data['status'] = 1;
                StoreOrderModel::edit($data,$id);
                event('StoreProductOrderDeliveryAfter',[$data,$id]);
                StoreOrderStatus::setStatus($id,'delivery_fictitious','已虚拟发货');
                break;
            default:
                return Json::fail('暂时不支持其他发货类型');
                break;
        }
        return Json::successful('修改成功!');
    }

    /**
     * TODO 填写送货信息
     * @param $id
     * @return mixed|void
     * @throws \think\exception\DbException
     */
    public function delivery($id){
        if(!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($product['paid'] == 1 && $product['status'] == 0) {
            $f = array();
            $f[] = Form::input('delivery_name','维修人员姓名')->required('维修人员姓名不能为空','required:true;');
            $f[] = Form::input('delivery_id','维修人员电话')->required('请输入正确电话号码','telephone');
            $form = Form::make_post_form('修改订单',$f,Url::buildUrl('updateDelivery',array('id'=>$id)),7);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }
        else $this->failedNotice('订单状态错误');
    }

    /**
     * TODO 送货信息提交
     * @param $id
     */
    public function updateDelivery($id){
        $data = Util::postMore([
            'delivery_name',
            'delivery_id',
        ]);
        $data['delivery_type'] = 'send';
        if(!$data['delivery_name']) return Json::fail('请输入维修人员姓名');
        if(!(int)$data['delivery_id']) return Json::fail('请输入维修人员电话号码');
        else if(!preg_match("/^1[3456789]{1}\d{9}$/",$data['delivery_id']))  return Json::fail('请输入正确的维修人员电话号码');
        $data['status'] = 1;
        StoreOrderModel::edit($data,$id);
        event('StoreProductOrderDeliveryAfter',[$data,$id]);
        StoreOrderStatus::setStatus($id,'delivery','已配送 发货人：'.$data['delivery_name'].' 发货人电话：'.$data['delivery_id']);
        return Json::successful('修改成功!');
    }

    /**
     * TODO 填写发货信息
     * @param $id
     * @return mixed|void
     * @throws \think\exception\DbException
     */
    public function deliver_goods($id){
        if(!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($product['paid'] == 1 && $product['status'] == 0){
            $f = array();
            $f[] = Form::select('delivery_name','快递公司')->setOptions(function(){
                        $list =  Db::name('express')->where('is_show',1)->order('sort DESC')->column('name','id');
                        $menus = [];
                        foreach ($list as $k=>$v){
                            $menus[] = ['value'=>$v,'label'=>$v];
                        }
                        return $menus;
                    })->filterable(1);
            $f[] = Form::input('delivery_id','快递单号');
            $form = Form::make_post_form('修改订单',$f,Url::buildUrl('updateDeliveryGoods',array('id'=>$id)),7);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }
        else return $this->failedNotice('订单状态错误');
    }

    /**
     * TODO 发货信息提交
     * @param $id
     */
    public function updateDeliveryGoods($id){
        $data = Util::postMore([
            'delivery_name',
            'delivery_id',
        ]);
        $data['delivery_type'] = 'express';
        if(!$data['delivery_name']) return Json::fail('请选择快递公司');
        if(!$data['delivery_id']) return Json::fail('请输入快递单号');
        $data['status'] = 1;
        StoreOrderModel::edit($data,$id);
        event('StoreProductOrderDeliveryGoodsAfter',[$data,$id]);
        StoreOrderStatus::setStatus($id,'delivery_goods','已发货 快递公司：'.$data['delivery_name'].' 快递单号：'.$data['delivery_id']);
        return Json::successful('修改成功!');
    }
    /**
     * 修改状态为已收货
     * @param $id
     * @return \think\response\Json|void
     */
    public function take_delivery($id){
        if(!$id) return $this->failed('数据不存在');
        $order = StoreOrderModel::get($id);
        if(!$order) return Json::fail('数据不存在!');
        if($order['remark'] == '') return Json::fail('该记录未填写<span style="color:#e74c3c;">[处理详情]</span>或<span style="color:#e74c3c;">[维修详情]</span>!</br> 维修详情在当前操作中!');
        if($order['status'] == 2) return Json::fail('不能重复完成!');
        if($order['paid'] == 1 && $order['status'] == 1) $data['status'] = 2;
        else if($order['pay_type'] == 'offline') $data['status'] = 2;
        $data['status'] = 3;
        // else return Json::fail('请先发货或者送货!');
        StoreOrderModel::where('id',$id)->update(['status' => 3, 'paid' => 1]);
        // halt($data);die;
        if(!StoreOrderModel::edit($data,$id))
            return Json::fail(StoreOrderModel::getErrorInfo('失败,请稍候再试!'));
        else{
            try{
                OrderRepository::storeProductOrderTakeDeliveryAdmin($order, $id);
            }catch (\Exception $e){
                return Json::fail($e->getMessage());
            }
            StoreOrderStatus::setStatus($id,'take_delivery','完成');
            return Json::successful('完成成功!');
        }
    }
    /**
     * 修改退款状态
     * @param $id
     * @return \think\response\Json|void
     */
    public function refund_y($id){
        if(!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($product['paid'] == 1){
            $f = array();
            $f[] = Form::input('order_id','退款单号',$product->getData('order_id'))->disabled(1);
            $f[] = Form::number('refund_price','退款金额',$product->getData('pay_price'))->precision(2)->min(0.01);
            $f[] = Form::radio('type','状态',1)->options([['label'=>'直接退款','value'=>1],['label'=>'退款后,返回原状态','value'=>2]]);
            $form = Form::make_post_form('退款处理',$f,Url::buildUrl('updateRefundY',array('id'=>$id)),7);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }
        else return Json::fail('数据不存在!');
    }

    /**
     * 退款处理
     * @param $id
     */
    public function updateRefundY($id){
        $data = Util::postMore([
            'refund_price',
            ['type',1],
        ]);
        if(!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($product['pay_price'] == $product['refund_price']) return Json::fail('已退完支付金额!不能再退款了');
        if(!$data['refund_price']) return Json::fail('请输入退款金额');
        $refund_price = $data['refund_price'];
        $data['refund_price'] = bcadd($data['refund_price'],$product['refund_price'],2);
        $bj = bccomp((float)$product['pay_price'],(float)$data['refund_price'],2);
        if($bj < 0) return Json::fail('退款金额大于支付金额，请修改退款金额');
        if($data['type'] == 1){
            $data['refund_status'] = 2;
        }else if($data['type'] == 2){
            $data['refund_status'] = 0;
        }
        $type =  $data['type'];
        unset($data['type']);
        $refund_data['pay_price'] = $product['pay_price'];
        $refund_data['refund_price'] = $refund_price;
        if($product['pay_type'] == 'weixin'){
            if($product['is_channel']){//小程序
                try{
                    MiniProgramService::payOrderRefund($product['order_id'],$refund_data);//2.5.36
                }catch(\Exception $e){
                    return Json::fail($e->getMessage());
                }
            }else{//TODO 公众号
                try{
                    WechatService::payOrderRefund($product['order_id'],$refund_data);
                }catch(\Exception $e){
                    return Json::fail($e->getMessage());
                }
            }
        }else if($product['pay_type'] == 'yue'){
            BaseModel::beginTrans();
            $usermoney = User::where('uid',$product['uid'])->value('now_money');
            $res1 = User::bcInc($product['uid'],'now_money',$refund_price,'uid');
            $res2 = $res2 = UserBill::income('商品退款',$product['uid'],'now_money','pay_product_refund',$refund_price,$product['id'],bcadd($usermoney,$refund_price,2),'订单退款到余额'.floatval($refund_price).'元');
            try{
                OrderRepository::storeOrderYueRefund($product, $refund_data);
            }catch (\Exception $e){
                BaseModel::rollbackTrans();
                return Json::fail($e->getMessage());
            }
            $res = $res1 && $res2;
            BaseModel::checkTrans($res);
            if(!$res) return Json::fail('余额退款失败!');
        }
        $resEdit = StoreOrderModel::edit($data,$id);
        if($resEdit){
            $data['type'] = $type;
            if($data['type'] == 1)  StorePink::setRefundPink($id);
            try{
                OrderRepository::storeProductOrderRefundY($data, $id);
            }catch (\Exception $e){
                BaseModel::rollbackTrans();
                return Json::fail($e->getMessage());
            }
            StoreOrderStatus::setStatus($id,'refund_price','退款给用户'.$refund_price.'元');
            BaseModel::commitTrans();
            return Json::successful('修改成功!');
        }else{
            StoreOrderStatus::setStatus($id,'refund_price','退款给用户'.$refund_price.'元失败');
            return Json::successful('修改失败!');
        }
    }
    public function order_info($oid = '')
    {
        if(!$oid || !($orderInfo = StoreOrderModel::get($oid)))
            return $this->failed('订单不存在!');
        if ($orderInfo['product_type']==1){
            $type=UserPrinter::get($orderInfo['type_id']);
        }
        if ($orderInfo['product_type']==2){
            $type=UserComputer::get($orderInfo['type_id']);
        }
        $userInfo = User::getUserInfos($orderInfo['uid']);
        if($userInfo['spread_uid']){
            $spread = User::where('uid',$userInfo['spread_uid'])->value('nickname');
        }else{
            $spread ='';
        }
        $this->assign(compact('orderInfo','userInfo','spread','type'));
        return $this->fetch();
    }
    public function express($oid = '')
    {
        if(!$oid || !($order = StoreOrderModel::get($oid)))
            return $this->failed('订单不存在!');
        if($order['delivery_type'] != 'express' || !$order['delivery_id']) return $this->failed('该订单不存在快递单号!');
        $cacheName = $order['order_id'].$order['delivery_id'];
        $result = CacheService::get($cacheName,null);
        if($result === null){
            $result = ExpressService::query($order['delivery_id']);
            if(is_array($result) &&
                isset($result['result']) &&
                isset($result['result']['deliverystatus']) &&
                $result['result']['deliverystatus'] >= 3)
                $cacheTime = 0;
            else
                $cacheTime = 1800;
            CacheService::set($cacheName,$result,$cacheTime);
        }
        $this->assign([
            'order'=>$order,
            'express'=>$result
        ]);
        return $this->fetch();
    }
    /**
     * 修改配送信息
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function distribution($id){
        if(!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        $deliveryName = SystemAdmin::where('is_del', 0)->where('status', 1)->field(['id', 'account', 'real_name'])->select();
        if(!$product) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('order_id','维修单号',$product->getData('order_id'))->disabled(1);

        // if($product['delivery_type'] == 'send'){
        //     $f[] = Form::input('delivery_name','维修人员姓名',$product->getData('delivery_name'));
        //     $f[] = Form::input('delivery_id','维修人员电话',$product->getData('delivery_id'));
        // }else if($product['delivery_type'] == 'express'){
        //     $f[] = Form::select('delivery_name','快递公司',$product->getData('delivery_name'))->setOptions(function (){
        //         $list =  Db::name('express')->where('is_show',1)->column('name','id');
        //         $menus = [];
        //         foreach ($list as $k=>$v){
        //             $menus[] = ['value'=>$v,'label'=>$v];
        //         }
        //         return $menus;
        //     });
        //     $f[] = Form::input('delivery_id','快递单号',$product->getData('delivery_id'));
        // }
        $f[] = Form::radio('delivery_type','处理类型')->options([['value'=>"express",'label'=>'上门维修'],['value'=>"fictitious",'label'=>'到店维修']]);
        $f[]=Form::select('delivery_name','维修人员')->setOptions(function() use($deliveryName) {
            $deliveryMenus=[];
            foreach ($deliveryName as $deliveryList){
                $deliveryMenus[] = ['value'=>$deliveryList['real_name'],'label'=>
                  $deliveryList['real_name']
                ];
            }
            return $deliveryMenus;
        })->filterable(1);
        $f[] = Form::input('delivery_id','电话');
        $form = Form::make_post_form('配送信息',$f,Url::buildUrl('my_update_delivery',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }



    public function my_update_delivery($id)
    {
      $data = Util::postMore([
          ['delivery_type', ''],
          ['delivery_name',''],
          ['delivery_id','']
      ],$this->request);
      $testInfo = [
        'id' => $id,
        'delivery_type' => $data['delivery_type'],
        'delivery_name' => $data['delivery_name'],
        'delivery_id' => $data['delivery_id']
        // 'delivery_type' => $data['delivery_type'],
        // 'delivery_type' => $data['delivery_type'],
      ];
      // halt($testInfo);die;
      //送货
      if(!$data['delivery_type']) return Json::fail('请选择处理类型!');
      if(!$data['delivery_name']) return Json::fail('请选择维修人员姓名');
      if(!(int)$data['delivery_id']) return Json::fail('请输入维修人员电话号码');
      else if(!preg_match("/^1[3456789]{1}\d{9}$/",$data['delivery_id']))  return Json::fail('请输入正确的维修人员电话号码');
      $data['status'] = 1;
      StoreOrderModel::edit($data,$id);
      event('StoreProductOrderDeliveryAfter',[$data,$id]);
      StoreOrderStatus::setStatus($id,'delivery','维修人员：'.$data['delivery_name'].' 维修人员电话：'.$data['delivery_id']);

      return Json::successful('修改成功!');
      }


    /**
     * 修改配送信息
     * @param $id
     */
    public function updateDistribution($id){
        $data = Util::postMore([
            'delivery_name',
            'delivery_id',
        ]);
        if(!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($product['delivery_type'] == 'send'){
            if(!$data['delivery_name']) return Json::fail('请输入维修人员姓名');
            if(!(int)$data['delivery_id']) return Json::fail('请输入维修人员电话号码');
            else if(!preg_match("/^1[3456789]{1}\d{9}$/",$data['delivery_id']))  return Json::fail('请输入正确的维修人员电话号码');
        }else if($product['delivery_type'] == 'express'){
            if(!$data['delivery_name']) return Json::fail('请选择快递公司');
            if(!$data['delivery_id']) return Json::fail('请输入快递单号');
        }
        StoreOrderModel::edit($data,$id);
        event('StoreProductOrderDistributionAfter',[$data,$id]);
        StoreOrderStatus::setStatus($id,'distribution','修改发货信息为'.$data['delivery_name'].'号'.$data['delivery_id']);
        return Json::successful('修改成功!');
    }
    /**
     * 修改退款状态
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function refund_n($id){
        if(!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $f[] = Form::input('order_id','退款单号',$product->getData('order_id'))->disabled(1);
        $f[] = Form::input('refund_reason','退款原因')->type('textarea');
        $form = Form::make_post_form('退款',$f,Url::buildUrl('updateRefundN',array('id'=>$id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 不退款原因
     * @param $id
     */
    public function updateRefundN($id){
        $data = Util::postMore([
            'refund_reason',
        ]);
        if(!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if(!$data['refund_reason']) return Json::fail('请输入退款原因');
        $data['refund_status'] = 0;
        StoreOrderModel::edit($data,$id);
        event('StoreProductOrderRefundNAfter',[$data['refund_reason'],$id]);
        StoreOrderStatus::setStatus($id,'refund_n','不退款原因:'.$data['refund_reason']);
        return Json::successful('修改成功!');
    }
    /**
     * 立即支付
     * @param $id
     */
    public function offline($id){
        $res = StoreOrderModel::updateOffline($id);
        if($res){
            event('StoreProductOrderOffline',[$id]);
            StoreOrderStatus::setStatus($id,'offline','线下付款');
            return Json::successful('修改成功!');
        }else{
            return Json::fail(StoreOrderModel::getErrorInfo('修改失败!'));
        }
    }
    /**
     * 修改积分和金额
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function integral_back($id){
        if(!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($product['paid'] == 1){
            $f[] = Form::input('order_id','退款单号',$product->getData('order_id'))->disabled(1);
            $f[] = Form::number('use_integral','使用的积分',$product->getData('use_integral'))->min(0)->disabled(1);
            $f[] = Form::number('use_integrals','已退积分',$product->getData('back_integral'))->min(0)->disabled(1);
            $f[] = Form::number('back_integral','可退积分',bcsub($product->getData('use_integral'),$product->getData('use_integral')))->min(0);
            $form = Form::make_post_form('退积分',$f,Url::buildUrl('updateIntegralBack',array('id'=>$id)),7);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }else{
            return Json::fail('参数错误!');
        }
        return $this->fetch('public/form-builder');
    }

    /**
     * 退积分保存
     * @param $id
     */
    public function updateIntegralBack($id){
        $data = Util::postMore([
            'back_integral',
        ]);
        if(!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($data['back_integral'] <= 0) return Json::fail('请输入积分');
        if($product['use_integral'] == $product['back_integral']) return Json::fail('已退完积分!不能再积分了');
        $back_integral = $data['back_integral'];
        $data['back_integral'] = bcadd($data['back_integral'],$product['back_integral'],2);
        $bj = bccomp((float)$product['use_integral'],(float)$data['back_integral'],2);
        if($bj < 0) return Json::fail('退积分大于支付积分，请修改退积分');
        BaseModel::beginTrans();
        $integral = User::where('uid',$product['uid'])->value('integral');
        $res1 = User::bcInc($product['uid'],'integral',$back_integral,'uid');
        $res2 = UserBill::income('商品退积分',$product['uid'],'integral','pay_product_integral_back',$back_integral,$product['id'],bcadd($integral,$back_integral,2),'订单退积分'.floatval($back_integral).'积分到用户积分');
        event('StoreOrderIntegralBack',[$product,$back_integral]);
        try{
            OrderRepository::storeOrderIntegralBack($product,$back_integral);
        }catch (\Exception $e){
            BaseModel::rollbackTrans();
            return Json::fail($e->getMessage());
        }
        $res = $res1 && $res2;
        BaseModel::checkTrans($res);
        if(!$res) return Json::fail('退积分失败!');
        StoreOrderModel::edit($data,$id);
        StoreOrderStatus::setStatus($id,'integral_back','商品退积分：'.$data['back_integral']);
        return Json::successful('退积分成功!');
    }
    public function remark(){
        $data = Util::postMore(['id','remark']);
        if(!$data['id']) return Json::fail('参数错误!');
        if($data['remark'] == '')  return Json::fail('请输入维修详情!');
        $id = $data['id'];
        unset($data['id']);
        StoreOrderModel::edit($data,$id);
        return Json::successful('添加成功!');
    }
    public function order_status($oid){
       if(!$oid) return $this->failed('数据不存在');
       $this->assign(StoreOrderStatus::systemPage($oid));
       return $this->fetch();
    }







    //lancercd 后台创建订单
}
