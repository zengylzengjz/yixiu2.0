// pages/user_computer/index.js
import {editComputer,getComputerDetail} from '../../api/user.js';
import { setFormId } from '../../api/api.js';

var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
  	objectArray: ['综合楼', '学院楼', '弘德楼', "校友会堂", "田家炳", "其他"],
    index: 0,
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '添加地址'
    },
    region: ['省', '市', '区'],
    cartId: '',//购物车id
    pinkId: 0,//拼团id
    couponId: 0,//优惠券id
    id: 0,//地址id
    userComputer:{is_default:false},//电脑默认
  },
  /**
   * 授权回调
   *
  */
  onLoadFun: function () {
    this.getUserComputer();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
  	var that = this;
  	that.getUserComputer(options.id);
  	console.log(options.id);
    this.setData({
      cartId: options.cartId || '',
      pinkId: options.pinkId || 0,
      couponId: options.couponId || 0,
      id: options.id || 0,
      'parameter.title': options.id ? '修改地址' : '添加地址'
    });
  },
  bindRegionChange: function (e) {
    // console.log('picker发送选择改变，携带值为', e.detail.value)
    this.setData({
      region: e.detail.value
    })
  },
  getUserComputer: function (zjz) {
    if (!zjz) return false;
    var that = this;
    // console.log(zjz,123);
    getComputerDetail(zjz).then(res => {
      //console.log(1,2);
      // var region = [res.data.province, res.data.city, res.data.district];
      that.setData({
        userComputer: res.data,
        // region: region,
      });
    });
  },

  /**
   * 提交用户添加地址
   *
  */
  formSubmit: function (e) {
    var that = this, value = e.detail.value, formId = e.detail.formId;
    console.log(value.computer);
    if (!value.com_type) return app.Tips({ title: '请填写电脑型号' });
    value.id = that.data.id;
    value.is_default = that.data.userComputer.is_default ? 1 : 0;
    setFormId(formId)
    console.log(value);
    wx.showModal({
      title: '提示',
      content: '您确定要添加这个电脑信息吗',
      success: res => {
        if (res.confirm) {
          editComputer(value).then(res => {
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
    editComputer(value).then(res => {
      if (value) {
        wx.showModal({
          title: 'WARNING',
          content: '您确定要添加这个电脑信息吗',
          success: res => {
            if (res.confirm) {
              editComputer(value).then(res => {
                app.Tips({ title: '添加成功', icon: 'success' }, function () {
                  wx.navigateBack({
                    delta: 0
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



      .catch(err => {
        return app.Tips({ title: err });
      })
  },
  
  ChangeIsDefault: function (e) {
    this.setData({ 'userComputer.is_default': !this.data.userComputer.is_default });
  },

})
