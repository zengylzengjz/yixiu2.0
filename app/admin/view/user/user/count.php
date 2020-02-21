{extend name="public/container"}
{block name="content"}
<div class="ibox-content order-info" id="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel-heading">
                --------收货信息
            </div>
            <div class="panel panel-default" v-for="item in addressInfo">
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-12" >用户昵称: {{item.real_name}}</div>
                        <div class="col-xs-12">收货人: {{item.phone}}</div>
                        <div class="col-xs-12">联系电话: {{item.province}}</div>
                        <div class="col-xs-12">收货地址: {{item.detail}}</div>
                    </div>
                </div>
            </div>
            <div class="panel-heading">
                --------打印机信息
            </div>
            <div class="panel panel-default" v-for="item in printerInfo">
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-12" >打印机型号: {{item.printer_type}}</div>
                        <div class="panel-heading">-
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-heading">
                --------电脑信息
            </div>
            <div class="panel panel-default"  v-for="item in computerInfo">
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-12" >电脑型号: {{item.com_type}}</div>
                        <div class="col-xs-12" >CPU: {{item.cpu}}</div>
                        <div class="col-xs-12" >显卡: {{item.graphics}}</div>
                        <div class="col-xs-12" >硬盘: {{item.hd}}</div>
                        <div class="col-xs-12" >保质时间: {{item.pro_date}}</div>
                        <div class="col-xs-12" >sn码: {{item.sn_code}}</div>
                        <div class="panel-heading">-
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>

<script>
    require(['vue'],function(Vue) {
        window.ex = new Vue({
            el: '#content',
        data:{
            addressInfo:<?=json_encode($addressInfo)?>,
            computerInfo:<?=json_encode($computerInfo)?>,
            printerInfo:<?=json_encode($printerInfo)?>,
        }
    });
    });

</script>
{/block}

