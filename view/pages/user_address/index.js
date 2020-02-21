 import { editAddress, getAddressDetail} from '../../api/user.js';
import { setFormId } from '../../api/api.js';

var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    // array: ['综合楼', '学院楼', '弘德楼', "校友会堂", "田家炳", "其他"],
    // objectArray: [
    //   {
    //     id: 0,
    //     name: '综合楼'
    //   },
    //   {
    //     id: 1,
    //     name: '学院楼'
    //   },
    //   {
    //     id: 2,
    //     name: '宏德楼'
    //   },
    //   {
    //     id: 3,
    //     name: '校友会堂'
    //   },
    //   {
    //     id:4,
    //     name:'田家炳'
    //   },
    //   {
    //     id:5,
    //     name:'其他'
    //   }
    // ],
    index: 0,
    objectArray: ['博雅楼', '校友会堂', '行政楼', '弘德楼', '集贤楼', '田家炳', '知行楼', '励志楼', '汇贤楼', '图书馆', '美术学院', '音乐学院', '物电学院', '畅风苑', '雅风苑', '惠风苑', '清风苑', '嘉风苑', '和风苑', '清风苑', '其他'],
    // index: 0,
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '添加地址'
    },
    region: ['省', '市', '区'],
    cartId:'',//购物车id
    pinkId:0,//拼团id
    couponId:0,//优惠券id
    id:0,//地址id
    userAddress: { is_default:false},//地址详情
  },
  /**
   * 授权回调
   *
  */
  onLoadFun:function(){
    // console.log(1);
    this.getUserAddress();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      cartId: options.cartId || '',
      pinkId: options.pinkId || 0,
      couponId: options.couponId || 0,
      id: options.id || 0,
      'parameter.title': options.id ? '修改地址' : '添加地址'
    });
  },
  bindPickerChange: function (e) {
    // console.log('picker发送选择改变，携带值为', e.detail.value)
    this.setData({
      index: e.detail.value
    })
  },
  getUserAddress:function(){
    if(!this.data.id) return false;
    var that=this;
    getAddressDetail(this.data.id).then(res=>{
      // console.log(res);
      var region = res.data.province;
      that.setData({
        userAddress: res.data,
        region: region,
      });
    });
  },

  /**
   * 提交用户添加地址
   *
  */
  formSubmit:function(e){
    var that = this, value = e.detail.value, formId=e.detail.formId;
    if (!value.real_name) return app.Tips({title:'请填写姓名'});
    if (!value.phone) return app.Tips({title:'请填写联系电话'});
    if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(value.phone)) return app.Tips({title:'请输入正确的手机号码'});
    // if (that.data.region[0] =='省') return app.Tips({title:'请选择所在地区'});
    if (!value.city) return app.Tips({title:'请填写房间号'});
    value.id=that.data.id;
    value.address={
      province:that.data.province,
      city: that.data.city,
      // district: that.data.region[2],
    };
    value.is_default = that.data.userAddress.is_default ? 1 : 0;
    setFormId(formId)
    console.log(value);
    wx.showModal({
      title: 'WARNING',
      content: '您确定要添加这个地址信息吗',
      success: res => {
        if (res.confirm) {
          editAddress(value).then(res => {
            app.Tips({ title: '添加成功', icon: 'success' }, function () {
              wx.navigateBack({
                delta: 2
              });
            });
          }).catch(err => {
            return app.Tips({ title: err });
          });
        } else if (res.cancel) {
          //
        }
      }
    });
    return;
    editAddress(value).then(res=>{
      if (value)
        {
        wx.showModal({
          title: '提示',
          content: '您确定要添加这个地址信息吗',
          success: res => {
            if (res.confirm) {
              editAddress(value).then(res => {
                app.Tips({ title: '添加成功', icon: 'success' }, function () {
                  wx.navigateBack({
                    delta:0
                  });
                });
              }).catch(err => {
                return app.Tips({ title: err });
              });
            } else if (res.cancel) {
              //
            }
          }
        })
      }
    })
    
    
    
    .catch(err=>{
      return app.Tips({title:err});
    })
  },
  ChangeIsDefault:function(e){
    this.setData({ 'userAddress.is_default': !this.data.userAddress.is_default});
  },





})
