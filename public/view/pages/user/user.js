const app = getApp();
import { getMenuList, getUserInfo, StudentAndStudent} from '../../api/user.js';
import { switchH5Login } from '../../api/api.js';
import authLogin from '../../utils/autuLogin.js';
import util from '../../utils/util.js';
import {getOrderData} from '../../api/order.js';
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '0',
      'title': '个人中心',
      'color': true,
      'class': '0'
    },
    userInfo:{},
    MyMenus:[],
    isGoIndex:false,
    iShidden:true,
    isAuto:false,
    switchActive:false,
    loginType: app.globalData.loginType,
    orderStatusNum:{},
  },

  close:function(){
    this.setData({ switchActive:false});
  },
  /**
   * 授权回调
  */
  onLoadFun:function(e){
    this.getUserInfo();
    this.getMyMenus();
    this.getOrderData1();
    this.setData({ MyMenus: app.globalData.MyMenus });

  },
  
  // /**
  //  * 生命周期函数--监听页面加载
  //  */

  // onLoad: function (options) {
  //   var that = this;
  //   that.getPrinterOrderData1(options.id);
  //   // console.log(options.id);
  //   this.setData({
  //   });
  // },
  // /**
  //  * 获取打印机统计数据
  //  * 
  // */
  // getPrinterOrderData1: function () {
  //   var that = this;
  //   getPrinterOrderData().then(res => {
  //     //console.log(res);
  //     that.setData({ PrinterOrderData: res.data });
  //   })
  // },
  /**
   * 生命周期函数--监听页面加载
   */

  onLoad: function (options) {
    var that = this;
    that.getOrderData1(options.id);
    console.log(options.id);
    this.setData({
    });
    
  },
  /**
   * 获取统计数据
   * 
  */
  getOrderData1: function () {
    var that = this;
    getOrderData().then(res => {
      // console.log(res);
      that.setData({ OrderData: res.data });
    })
  },
  /**
   * 
   * 获取个人中心图标
  */
  getMyMenus: function () {
    var that = this;
    var MyMenus='';
    if (this.data.MyMenus.length) return;
    getMenuList().then(res=>{
      res.data.routine_my_menus[0].pic = "http://zengyl.dev.dxdc.net/address.png" ;
      // + res.data.routine_my_menus[0].pic;
      console.log(res.data.routine_my_menus[0].pic);
      that.setData({ MyMenus: res.data.routine_my_menus });
      // console.log(res.data.routine_my_menus);
    });
  },
  /**
   * 获取个人用户信息
  */
  getUserInfo:function(){
    var that=this;
    getUserInfo().then(res=>{
      that.setData({ userInfo: res.data, loginType: res.data.login_type, orderStatusNum: res.data.orderStatusNum});
    });
    StudentAndStudent().then(res => {
      console.log('预警')
      console.log(this.data) 
      this.setData({ school_num: res.data.stu_num, college: res.data.college });
    });
    //   var studentNum = '';
    // //   console.log(12, studentNum)
    //   try {
    //       var college = wx.getStorageSync('college');
    //       var studentNum = wx.getStorageSync('studentNum');
    //       console.log(12, studentNum)
    //       if (college && studentNum) {
    //           that.setData({ StudentSchool: college, StudentNum: studentNum });
    //       } else {
    //           wx.navigateTo({
    //               url: '/pages/select/select',
    //           })
    //       }
    //   } catch (e) {
    //       // wx.navigateTo({
    //       //     url: '/pages/select/select',
    //       // }) 
    //   }
  },
  /**
   * 页面跳转
  */
  goPages:function(e){
    if(app.globalData.isLog){
      if (e.currentTarget.dataset.url == '/pages/user_spread_user/index' && this.data.userInfo.statu==1) {
        if (!this.data.userInfo.is_promoter) return app.Tips({ title: '您还没有推广权限！！' });
      }
      if (e.currentTarget.dataset.url == '/pages/logon/index') return this.setData({ switchActive:true});
      wx.navigateTo({
        url: e.currentTarget.dataset.url
      })
    }else{
      this.setData({ iShidden:false});
    }
  },
  toComputer: function () {
    wx.navigateTo({
      url: '/pages/user_computer_list/index',
    })
  },
  toPrinter: function () {
    wx.navigateTo({
      url: '/pages/user_printer_list/index',
    })
  },
  toComputerOrderList: function () {
    wx.navigateTo({
      url: '/pages/order_list2/index',
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
//  onLoad:function (options) {
//   },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    this.setData({ switchActive: false });
  },
  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
    
  },
  onShow:function(){
    let that = this;
    if (app.globalData.isLog) this.getUserInfo();
  },

  /**
  * 生命周期函数--监听页面卸载
  */
  onUnload: function () {

  },
})