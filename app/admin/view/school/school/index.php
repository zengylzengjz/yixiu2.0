{extend name="public/container"}
{block name="head_top"}
<script src="{__PLUG_PATH}city.js"></script>
<style>
    .layui-btn-xs{margin-left: 0px !important;}
    legend{
        width: auto;
        border: none;
        font-weight: 700 !important;
    }
    .site-demo-button{
        padding-bottom: 20px;
        padding-left: 10px;
    }
    .layui-form-label{
        width: auto;
    }
    .layui-input-block input{
        width: 50%;
        height: 34px;
    }
    .layui-form-item{
        margin-bottom: 0;
    }
    .layui-input-block .time-w{
        width: 200px;
    }
    .layui-table-body{overflow-x: hidden;}
    .layui-btn-group button i{
        line-height: 30px;
        margin-right: 3px;
        vertical-align: bottom;
    }
    .back-f8{
        background-color: #F8F8F8;
    }
    .layui-input-block button{
        border: 1px solid #e5e5e5;
    }
    .avatar{width: 50px;height: 50px;}
    .layui-table-body{
        overflow-x: unset;
    }
</style>
{/block}
{block name="content"}

<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="table-responsive">
                    <div class="layui-btn-group conrelTable">
                        <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="refresh"><i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                    </div>
                    <table class="layui-hide" id="userList" lay-filter="userList"></table>
                    <script type="text/html" id="nickname">
                        {{d.nickname}}
                        {{# if(d.vip_name){ }}
                        <p style="color:#dab176">{{d.vip_name}}</p>
                        {{# } }}
                    </script>
                    <script type="text/html" id="data_time">
                        <p>首次：{{d.add_time}}</p>
                        <p>最近：{{d.last_time}}</p>
                    </script>
                    <script type="text/html" id="checkboxstatus">
                        <input type='checkbox' name='status' lay-skin='switch' value="{{d.uid}}" lay-filter='status' lay-text='正常|禁止'  {{ d.status == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="barDemo">
                        <button type="button" class="layui-btn layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i>编辑</button>
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">

                            <li>
                                <a href="javascript:void(0);" lay-event="see">
                                    <i class="layui-icon layui-icon-edit"></i> 会员详情</a>
                            </li>

                            <li>
                                <a href="javascript:void(0);" lay-event="add_computer_order">
                                    <i class="layui-icon layui-icon-star-fill" aria-hidden="true"></i> 添加电脑记录</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event="add_printer_order">
                                    <i class="layui-icon layui-icon-star-fill" aria-hidden="true"></i> 添加打印机记录</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event="edit_address">
                                    <i class="layui-icon layui-icon-edit"></i> 添加地址</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event="edit_printer">
                                    <i class="layui-icon layui-icon-edit"></i> 添加打印机</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event="edit_computer">
                                    <i class="layui-icon layui-icon-edit"></i> 添加电脑</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event="count">
                                    <i class="layui-icon layui-icon-edit"></i> 具体详情</a>
                            </li>

                            {{# if(d.vip_name){ }}

                            {{# } }}
                        </ul>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
{/block}
{block name="script"}
<script>
    $('#province-div').hide();
    $('#city-div').hide();
    layList.select('country',function (odj,value,name) {
        var html = '';
        $.each(city,function (index,item) {
            html += '<option value="'+item.label+'">'+item.label+'</option>';
        })
        if(odj.value == 'domestic'){
            $('#province-div').show();
            $('#city-div').show();
            $('#province-top').siblings().remove();
            $('#province-top').after(html);
            $('#province').val('');
            layList.form.render('select');
        }else{
            $('#province-div').hide();
            $('#city-div').hide();
        }
        $('#province').val('');
        $('#city').val('');
    });
    layList.select('province',function (odj,value,name) {
        var html = '';
        $.each(city,function (index,item) {
            if(item.label == odj.value){
                $.each(item.children,function (indexe,iteme) {
                    html += '<option value="'+iteme.label+'">'+iteme.label+'</option>';
                })
                $('#city').val('');
                $('#city-top').siblings().remove();
                $('#city-top').after(html);
                layList.form.render('select');
            }
        })
    });
    layList.form.render();
    // console.log("{:Url('get_school_list')}");
    layList.tableList('schoolList',"{:Url('get_school_list')}",function () {
        return [
            {type:'checkbox'},
            {field: 'id', title: '编号',event:'id',width:'4%',align:'center'},
            {field: 'name', title: '校名',templet:'#name',align:'center'},
            // {field: 'phone', title: '手机号',align:'center',width:'8%'},
            {field: 'sort', title: '排序',align:'center',width:'5%'},
        ];
    });
    console.log(layList);
    // layList.date('last_time');
    // layList.date('add_time');
    // layList.date('user_time');
    // layList.date('time');
    // //监听并执行 uid 的排序
    // layList.sort(function (obj) {
    //     var layEvent = obj.field;
    //     var type = obj.type;
    //     switch (layEvent){
    //         case 'uid':
    //             layList.reload({order: layList.order(type,'u.uid')},true,null,obj);
    //             break;
    //         case 'now_money':
    //             layList.reload({order: layList.order(type,'u.now_money')},true,null,obj);
    //             break;
    //         case 'integral':
    //             layList.reload({order: layList.order(type,'u.integral')},true,null,obj);
    //             break;
    //     }
    // });
    // //监听并执行 uid 的排序
    // layList.tool(function (event,data,obj) {
    //     var layEvent = event;
    //     switch (layEvent){
    //         case 'edit':
    //             $eb.createModalFrame('编辑',layList.Url({a:'edit',p:{uid:data.uid}}));
    //             break;
    //         case 'see':
    //             $eb.createModalFrame(data.nickname+'-会员详情',layList.Url({a:'see',p:{uid:data.uid}}));
    //             break;
    //         case 'del_level':
    //             $eb.$swal('delete',function(){
    //                 $eb.axios.get(layList.U({a:'del_level',q:{uid:data.uid}})).then(function(res){
    //                     if(res.status == 200 && res.data.code == 200) {
    //                         $eb.$swal('success',res.data.msg);
    //                         obj.update({vip_name:false});
    //                         layList.reload();
    //                     }else
    //                         return Promise.reject(res.data.msg || '删除失败')
    //                 }).catch(function(err){
    //                     $eb.$swal('error',err);
    //                 });
    //             },{
    //                 title:'您确定要清除【'+data.nickname+'】的会员等级吗？',
    //                 text:'清除后无法恢复请谨慎操作',
    //                 confirm:'是的我要清除'
    //             })
    //             break;
    //         case 'give_level':
    //             $eb.createModalFrame(data.nickname+'-赠送会员',layList.Url({a:'give_level',p:{uid:data.uid}}),{w:500,h:200});
    //             break;
    //         case 'money':
    //             $eb.createModalFrame(data.nickname+'-积分余额修改',layList.Url({a:'edit_other',p:{uid:data.uid}}));
    //             break;
    //         case 'open_image':
    //             $eb.openImage(data.avatar);
    //             break;
    //         case 'add_computer_order':
    //             $eb.createModalFrame(data.nickname+'-添加电脑记录',layList.Url({a:'add_computer_order',p:{uid:data.uid}}));
    //             break;
    //         case 'add_printer_order':
    //             $eb.createModalFrame(data.nickname+'-添加打印机记录',layList.Url({a:'add_printer_order',p:{uid:data.uid}}));
    //             break;
    //         case 'count':
    //             $eb.createModalFrame(data.nickname+'-具体信息',layList.Url({a:'count',p:{uid:data.uid}}));
    //             break;
    //         case 'edit_address':
    //             $eb.createModalFrame(data.nickname+'-添加地址',layList.Url({a:'edit_address',p:{uid:data.uid}}));
    //             break;
    //         case 'edit_printer':
    //             $eb.createModalFrame(data.nickname+'-添加打印机',layList.Url({a:'edit_printer',p:{uid:data.uid}}));
    //             break;
    //         case 'edit_computer':
    //             $eb.createModalFrame(data.nickname+'-添加电脑',layList.Url({a:'edit_computer',p:{uid:data.uid}}));
    //             break;
    //     }
    // });
    // //layList.sort('uid');
    // //监听并执行 now_money 的排序
    // // layList.sort('now_money');
    // //监听 checkbox 的状态
    // layList.switch('status',function (odj,value,name) {
    //     if(odj.elem.checked==true){
    //         layList.baseGet(layList.Url({a:'set_status',p:{status:1,uid:value}}),function (res) {
    //             layList.msg(res.msg);
    //         });
    //     }else{
    //         layList.baseGet(layList.Url({a:'set_status',p:{status:0,uid:value}}),function (res) {
    //             layList.msg(res.msg);
    //         });
    //     }
    // });
    // layList.search('search',function(where){
    //     if(where['user_time_type'] != '' && where['user_time'] == '') return layList.msg('请选择选择时间');
    //     if(where['user_time_type'] == '' && where['user_time'] != '') return layList.msg('请选择访问情况');
    //     layList.reload(where,true);
    // });
    //
    // var action={
    //     set_status_f:function () {
    //        var ids=layList.getCheckData().getIds('uid');
    //        if(ids.length){
    //            layList.basePost(layList.Url({a:'set_status',p:{is_echo:1,status:0}}),{uids:ids},function (res) {
    //                layList.msg(res.msg);
    //                layList.reload();
    //            });
    //        }else{
    //            layList.msg('请选择要封禁的会员');
    //        }
    //     },
    //     set_status_j:function () {
    //         var ids=layList.getCheckData().getIds('uid');
    //         if(ids.length){
    //             layList.basePost(layList.Url({a:'set_status',p:{is_echo:1,status:1}}),{uids:ids},function (res) {
    //                 layList.msg(res.msg);
    //                 layList.reload();
    //             });
    //         }else{
    //             layList.msg('请选择要解封的会员');
    //         }
    //     },
    //     set_grant:function () {
    //         var ids=layList.getCheckData().getIds('uid');
    //         if(ids.length){
    //             var str = ids.join(',');
    //             $eb.createModalFrame('发送优惠券',layList.Url({c:'ump.store_coupon',a:'grant',p:{id:str}}),{'w':800});
    //         }else{
    //             layList.msg('请选择要发送优惠券的会员');
    //         }
    //     },
    //     set_template:function () {
    //         var ids=layList.getCheckData().getIds('uid');
    //         if(ids.length){
    //             var str = ids.join(',');
    //         }else{
    //             layList.msg('请选择要发送模板消息的会员');
    //         }
    //     },
    //     set_info:function () {
    //         var ids=layList.getCheckData().getIds('uid');
    //         if(ids.length){
    //             var str = ids.join(',');
    //             $eb.createModalFrame('发送站内信息',layList.Url({c:'user.user_notice',a:'notice',p:{id:str}}),{'w':1200});
    //         }else{
    //             layList.msg('请选择要发送站内信息的会员');
    //         }
    //     },
    //     set_custom:function () {
    //         var ids=layList.getCheckData().getIds('uid');
    //         if(ids.length){
    //             var str = ids.join(',');
    //             $eb.createModalFrame('发送客服图文消息',layList.Url({c:'wechat.wechat_news_category',a:'send_news',p:{id:str}}),{'w':1200});
    //         }else{
    //             layList.msg('请选择要发送客服图文消息的会员');
    //         }
    //     },
    //     refresh:function () {
    //         layList.reload();
    //     }
    // };
    // $('.conrelTable').find('button').each(function () {
    //     var type=$(this).data('type');
    //     $(this).on('click',function () {
    //         action[type] && action[type]();
    //     })
    // })
    // $(document).on('click',".open_image",function (e) {
    //     var image = $(this).data('image');
    //     $eb.openImage(image);
    // })
    // //下拉框
    // $(document).click(function (e) {
    //     $('.layui-nav-child').hide();
    // })
    // function dropdown(that){
    //     var oEvent = arguments.callee.caller.arguments[0] || event;
    //     oEvent.stopPropagation();
    //     var offset = $(that).offset();
    //     var top=offset.top-$(window).scrollTop();
    //     var index = $(that).parents('tr').data('index');
    //     $('.layui-nav-child').each(function (key) {
    //         if (key != index) {
    //             $(this).hide();
    //         }
    //     })
    //     if($(document).height() < top+$(that).next('ul').height()){
    //         $(that).next('ul').css({
    //             'padding': 10,
    //             'top': - ($(that).parent('td').height() / 2 + $(that).height() + $(that).next('ul').height()/2),
    //             'left':offset.left-$(that).parents('td').offset().left-20,
    //             'min-width': 'inherit',
    //             'position': 'absolute'
    //         }).toggle();
    //     }else{
    //         $(that).next('ul').css({
    //             'padding': 10,
    //             'top':$(that).parent('td').height() / 2 + $(that).height(),
    //             'left':offset.left-$(that).parents('td').offset().left-20,
    //             'min-width': 'inherit',
    //             'position': 'absolute'
    //         }).toggle();
    //     }
    // }

</script>
{/block}
