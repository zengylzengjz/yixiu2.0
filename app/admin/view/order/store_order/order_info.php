{extend name="public/container"}
{block name="content"}
<div class="ibox-content order-info">

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    用户信息
                </div>
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-12" >用户昵称: {$userInfo.nickname}</div>
                        <div class="col-xs-12">收货人: {$orderInfo.real_name}</div>
                        <div class="col-xs-12">联系电话: {$orderInfo.user_phone}</div>
                        <div class="col-xs-12">收货地址: {$orderInfo.user_address}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    订单信息
                </div>
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-6" >订单编号: {$orderInfo.order_id}</div>
                        <div class="col-xs-6" style="color: #8BC34A;">订单状态:
                            {if condition="$orderInfo['paid'] eq 0 && $orderInfo['status'] eq 0"}
                            未支付
                            {elseif condition="$orderInfo['paid'] eq 1 && $orderInfo['status'] eq 0 && $orderInfo['refund_status'] eq 0"/}
                            未发货
                            {elseif condition="$orderInfo['paid'] eq 1 && $orderInfo['status'] eq 1 && $orderInfo['refund_status'] eq 0"/}
                            待收货
                            {elseif condition="$orderInfo['paid'] eq 1 && $orderInfo['status'] eq 2 && $orderInfo['refund_status'] eq 0"/}
                            待评价
                            {elseif condition="$orderInfo['paid'] eq 1 && $orderInfo['status'] eq 3 && $orderInfo['refund_status'] eq 0"/}
                            交易完成
                            {elseif condition="$orderInfo['paid'] eq 1 && $orderInfo['refund_status'] eq 1"/}
                            申请退款<b style="color:#f124c7">{$orderInfo.refund_reason_wap}</b>
                            {elseif condition="$orderInfo['paid'] eq 1 && $orderInfo['refund_status'] eq 2"/}
                            已退款
                            {/if}
                        </div>
                        <div class="col-xs-6">商品总价: ￥{$orderInfo.total_price}</div>
                        <div class="col-xs-6">创建时间: {$orderInfo.add_time|date="Y/m/d H:i"}</div>
                        {notempty name="orderInfo.pay_time"}
                        <div class="col-xs-6">支付时间: {$orderInfo.pay_time|date="Y/m/d H:i"}</div>
                        {/notempty}
                        <div class="col-xs-6" style="color: #ff0005">用户备注: {$orderInfo.mark?:'无'}</div>
                        {if condition="$orderInfo['product_type'] eq 1"}
                        <div class="col-xs-12" >打印机型号: {$type.printer_type}</div>
                        {/if}
                        {if condition="$orderInfo['product_type'] eq 2"}
                        <div class="col-xs-12" >电脑型号: {$type.com_type}</div>
                        <div class="col-xs-6" >CPU: {$type.cpu}</div>
                        <div class="col-xs-6" >显卡: {$type.graphics}</div>
                        <div class="col-xs-6" >硬盘: {$type.hd}</div>
                        <div class="col-xs-6" >生产日期: {$type.pro_date}</div>
                        <div class="col-xs-6" >sn码: {$type.sn_code}</div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
        {if condition="$orderInfo['delivery_type'] eq 'express'"}
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    物流信息
                </div>
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-6" >快递公司: {$orderInfo.delivery_name}</div>
                        <div class="col-xs-6">快递单号: {$orderInfo.delivery_id} | <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('物流查询','{:Url('express',array('oid'=>$orderInfo['id']))}',{w:322,h:568})">物流查询</button></div>
                    </div>
                </div>
            </div>
        </div>
        {elseif condition="$orderInfo['delivery_type'] eq 'send'"}
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    配送信息
                </div>
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-6" >维修人员姓名: {$orderInfo.delivery_name}</div>
                        <div class="col-xs-6">维修人员电话: {$orderInfo.delivery_id}</div>
                    </div>
                </div>
            </div>
        </div>
        {/if}
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    备注信息
                </div>
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-6" >{if $orderInfo.mark}{$orderInfo.mark}{else}暂无备注信息{/if}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
{/block}
{block name="script"}

{/block}
