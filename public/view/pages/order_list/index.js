import { getPrinterOrderList, getComputerOrderList,getOrderData} from '../../api/order.js';

var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '打印机管理'
    },
    addressList: [],
    cartId: '',
    pinkId: 0,
    couponId: 0,
    loading: false,
    loadend: false,
    loadTitle: '加载更多',
    page: 1,
    limit: 8,
  },

  onShow: function () {
    var that = this;
    if (app.globalData.isLog) that.getPrinterOrderList1(true);
  },
  onLoadFun: function () {
    this.getPrinterOrderList1();
    this.getPrinterOrderData1();
  },
  /**
   * 生命周期函数--监听页面加载
   */

  onLoad: function (options) {
    var that = this;
    that.getPrinterOrderData1(options.id);
    //console.log(options.id);
    this.setData({
    });
  },
  /**
   * 获取打印机统计数据
   * 
  */
  getPrinterOrderData1: function () {
    var that = this;
    getOrderData().then(res => {
      //console.log(res);
      that.setData({ PrinterOrderData: res.data });
    })
  },

  /**
   * 获取打印机列表
   * 
  */
  getPrinterOrderList1: function (isPage) {
    var that = this;
    if (isPage) that.setData({ loadend: false, page: 1, addressList: [] });
    that.setData({ loading: true, loadTitle: '' });
    getPrinterOrderList({ page: that.data.page, limit: that.data.limit }).then(res => {
      // console.log(1);
      var list = res.data;
      var loadend = list.length < 4;
      //console.log(list);
      that.setData({
        addressList: list,
        loadend: loadend,
        loadTitle: loadend ? '我也是有底线的' : '加载更多',
        page: that.data.page + 1,
        loading: false,
      });
//    console.log(21314,this.data);
    }).catch(err => {
      that.setData({ loading: false, loadTitle: '加载更多' });
    });
    
  },
  /**
   * 设置默认地址
  */
  radioChange: function (e) {
    var index = parseInt(e.detail.value), that = this;;
    var address = this.data.addressList[index];
    if (address == undefined) return app.Tips({ title: '您设置的默认打印机不存在!' });
    setPrinterDefault(address.id).then(res => {
      for (var i = 0, len = that.data.addressList.length; i < len; i++) {
        if (i == index) that.data.addressList[i].is_default = true;
        else that.data.addressList[i].is_default = false;
      }
      app.Tips({ title: '设置成功', icon: 'success' }, function () {
        that.setData({ addressList: that.data.addressList });
      });
    }).catch(err => {
      return app.Tips({ title: err });
    });
  },
  /**
   * 编辑地址
  */
  editPrinter: function (e) {
    var cartId = this.data.cartId, pinkId = this.data.pinkId, couponId = this.data.couponId;
    this.setData({ cartId: '', pinkId: '', couponId: '' })
    wx.navigateTo({
      url: '/pages/user_printer/index?id=' + e.currentTarget.dataset.id + '&cartId=' + cartId + '&pinkId=' + pinkId + '&couponId=' + couponId
    })
  },
  /**
   * 删除地址
  */
  delPrinter: function (e) {
    var index = e.currentTarget.dataset.index, that = this, address = this.data.addressList[index];
    if (address == undefined) return app.Tips({ title: '您删除的打印机不存在!' });
    delPrinter(address.id).then(res => {
      app.Tips({ title: '删除成功', icon: 'success' }, function () {
        that.data.addressList.splice(index, 1);
        that.setData({ addressList: that.data.addressList });
      });
    }).catch(err => {
      return app.Tips({ title: err });
    });
  },
  /**
   * 新增地址
  */
  addPrinter: function () {
    var cartId = this.data.cartId, pinkId = this.data.pinkId, couponId = this.data.couponId;
    this.setData({ cartId: '', pinkId: '', couponId: '' })
    wx.navigateTo({
      url: '/pages/user_printer/index?cartId=' + cartId + '&pinkId=' + pinkId + '&couponId=' + couponId
    })
  },
  toComputer: function () {
    wx.navigateTo({
      url: '/pages/order_list2/index',
    })
  }, 
  toPrinter: function () {
    wx.navigateTo({
      url: '/pages/order_list/index',
    })
  },







  // goOrder: function (e) {
  //   var id = e.currentTarget.dataset.id;
  //   var cartId = '';
  //   var pinkId = '';
  //   var couponId = '';
  //   if (this.data.cartId && id) {
  //     cartId = this.data.cartId;
  //     pinkId = this.data.pinkId;
  //     couponId = this.data.couponId;
  //     this.setData({
  //       cartId: '',
  //       pinkId: '',
  //       couponId: '',
  //     })
  //     wx.redirectTo({
  //       url: '/pages/order_confirm/index?is_address=1&cartId=' + cartId + '&addressId=' + id + '&pinkId=' + pinkId + '&couponId=' + couponId
  //     })
  //   }
  // },
  // /**
  //  * 页面上拉触底事件的处理函数
  //  */
  // onReachBottom: function () {
  //   this.getAddressList();
  // }
})