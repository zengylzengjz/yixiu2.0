// pages/user_printer/index.js
import {getPrinterRecord} from '../../api/user.js';
import { setFormId } from '../../api/api.js';

var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    userPrinter: [],
    objectArray: ['综合楼', '学院楼', '弘德楼', "校友会堂", "田家炳", "其他"],
    index: 0,
    region: ['省', '市', '区'],
    cartId: '',//购物车id
    pinkId: 0,//拼团id
    couponId: 0,//优惠券id
    id: 0,//地址id
    printer_type: 0,//打印机详情
    userAddress: { is_default: false },//地址详情
  },

  /**
   * 授权回调
   *
  */
  onLoadFun: function () {
    // console.log(2);
    this.detailPrinter();
  },
  // bindRegionChange: function (e) {
  //   console.log('picker发送选择改变，携带值为', e.detail.value)
  //   this.setData({
  //     region: e.detail.value
  //   })
  // },
  /**
   * 生命周期函数--监听页面加载
   */

  onLoad: function (options) {
    console.log(options,121314);
    this.detailPrinter(options.id);
    this.setData({
      userPrinter: options.data,
    })
  //   var that = this;
  //   that.getPrinterRecord1(options.id);
  //   console.log(options.id);
  //   this.setData({
  //     cartId: options.cartId || '',
  //     pinkId: options.pinkId || 0,
  //     couponId: options.couponId || 0,
  //     id: options.id || 0,
  //     'parameter.title': options.id ? '修改地址' : '添加地址'

  //   });
    // var id = options.id;
    // this.getPrinterRecord(id);
    // this.getPrinterRecord1(id);
  },
  /**
   * 生命周期函数--监听页面加载
   */
  // onLoad: function (options) {
  //   console.log(options.cartId);
  //   this.setData({
  //     userPrinter: options.data,
  //   })
  // },
  // onShow: function () {
  //   var that = this;
  //   if (app.globalData.isLog) that.detailPrinter(true);
  // },
  // onLoadFun: function () {
  //   this.getPrinterList1();
  // },
  /**
   * 获取打印机型号信息
   */
  detailPrinter: function (printerId) {
    if (!printerId) return false;
    var that = this;
    getPrinterRecord({
      id:printerId
      //promise传回
    }).then((res)=>{
        //console.log(res.msg);
      that.setData({
        userPrinter: res.data,
        msg:res.msg,
        
      });
    }).catch(res =>{
        console.log(res);
    })
  },

  // getPrinterRecord1: function (zjz) {
  //   if (!zjz) return false;
  //   var that = this;
  //   getPrinterRecord(zjz).then(res => {
  //     that.setData({
  //       userPrinter: res.data,
  //     });
  //   });
  // },
  // var region = [res.data.province, res.data.city, res.data.district];
  // region: region,

  /**
   * 提交用户添加打印机
   *
  */
//   formSubmit: function (e) {
//     var that = this, value = e.detail.value, formId = e.detail.formId;
//     console.log(value.printer);
//     if (!value.printer_type) return app.Tips({ title: '请填写打印机型号' });
//     value.id = that.data.id;
//     value.is_default = that.data.userAddress.is_default ? 1 : 0;
//     setFormId(formId)
//     console.log(value);
//     editPrinter(value).then(res => {
//       if (that.data.id)
//         app.Tips({ title: '修改成功', icon: 'success' });
//       else
//         app.Tips({ title: '添加成功', icon: 'success' });
//       setTimeout(function () {
//         if (that.data.cartId) {
//           var cartId = that.data.cartId;
//           var pinkId = that.data.pinkId;
//           var couponId = that.data.couponId;
//           that.setData({ cartId: '', pinkId: '', couponId: '' })
//           wx.navigateTo({
//             url: '/pages/user_printer_list/index?cartId=' + cartId + '&addressId=' + (that.data.id ? that.data.id : res.data.id) + '&pinkId=' + pinkId + '&couponId=' + couponId
//           });
//         } else {
//           wx.navigateBack({ delta: 1 });
//         }
//       }, 1000);
//     }).catch(err => {
//       return app.Tips({ title: err });
//     })
//   },
//   ChangeIsDefault: function (e) {
//     this.setData({ 'userAddress.is_default': !this.data.userAddress.is_default });
//   },

})
