
import { getAddressList, setAddressDefault, delAddress, editAddress} from '../../api/user.js';

var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    parameter: {
      'navbar': '1',
      'return': '1',
      'title': '地址管理'
    },
    addressList:[],
    cartId:'',
    pinkId:0,
    couponId:0,
    loading:false,
    loadend:false,
    loadTitle:'加载更多',
    page:1,
    limit:8,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      cartId: options.cartId || '',
      pinkId: options.pinkId || 0,
      couponId: options.couponId || 0,
    })
  },
  onShow:function(){
    var that = this;
    if (app.globalData.isLog) that.getAddressList(true);
  },
  onLoadFun:function(){
    this.getAddressList();
  },
  
  /**
   * 获取地址列表
   * 
  */
  getAddressList: function (isPage){
    var that=this;
    if (isPage) that.setData({ loadend: false, page: 1, addressList:[]});
    if (that.data.loading) return;
    if (that.data.loadend) return;
    that.setData({ loading:true,loadTitle:''});
    getAddressList({ page: that.data.page, limit: that.data.limit }).then(res=>{
      var list = res.data;
      var loadend = list.length < that.data.limit;
      that.data.addressList = app.SplitArray(list, that.data.addressList);
      //console.log(that.data.addressList);
      that.setData({
        addressList: that.data.addressList,
        loadend: loadend,
        loadTitle: loadend ? '我也是有底线的' : '加载更多',
        page: that.data.page + 1,
        loading: false,
      });
    }).catch(err=>{
      that.setData({ loading: false, loadTitle: '加载更多' });
    });
  },
  /**
   * 设置默认地址
  */
  radioChange:function(e){
    var index = parseInt(e.detail.value),that=this;;
    var address = this.data.addressList[index];
    if (address==undefined) return app.Tips({title:'您设置的默认地址不存在!'});
    setAddressDefault(address.id).then(res=>{
      for (var i = 0, len = that.data.addressList.length; i < len; i++) {
        if (i == index) that.data.addressList[i].is_default = true;
        else that.data.addressList[i].is_default = false;
      }
      app.Tips({ title: '设置成功', icon: 'success' }, function () {
        that.setData({ addressList: that.data.addressList });
      });
    }).catch(err=>{
      return app.Tips({title:err});
    });
  },
  /**
   * 编辑地址
  */
  editAddress:function(e){
    // console.log('a');
    var cartId = this.data.cartId,pinkId = this.data.pinkId,couponId = this.data.couponId;
    this.setData({cartId: '',pinkId: '',couponId: ''})
    wx.navigateTo({ 
       url: '/pages/user_address/index?id=' + e.currentTarget.dataset.id + '&cartId=' + cartId + '&pinkId=' + pinkId + '&couponId=' + couponId
    })
  },
  /**
   * 新增地址
  */
  addAddress: function () {
    var cartId = this.data.cartId, pinkId = this.data.pinkId, couponId = this.data.couponId;
    this.setData({ cartId: '', pinkId: '', couponId: '' })
    wx.navigateTo({
      url: '/pages/user_address/index?cartId=' + cartId + '&pinkId=' + pinkId + '&couponId=' + couponId
    })
  },
  /**
   * 删除地址
  */


  delAddress: function (e) {
    var index = e.currentTarget.dataset.index,
      that = this,
      address = this.data.addressList[index];
    if (address == undefined) return app.Tips({
      title: '您删除的地址不存在!'
    });
    wx.showModal({
      title: 'WARNING',
      content: '您确定要删除这个地址信息吗',
      success: res => {
        if (res.confirm) {
          delAddress(address.id).then(res => {
            app.Tips({ title: '删除成功', icon: 'success' }, function () {
              that.data.addressList.splice(index, 1);
              that.setData({ addressList: that.data.addressList });
            });
          }).catch(err => {
            return app.Tips({ title: err });
          });
        } else if (res.cancel) {
          //
        }
      }
    })
    return;
    getPrinterRecord({
      id: address.id
    }).then(res => {
      if (res.msg == "该设备没有维修记录") {
        wx.showModal({
          title: 'WARNING',
          content: '您确定要删除这个打印机信息吗',
          success: res => {
            if (res.confirm) {
              delPrinter(address.id).then(res => {
                app.Tips({ title: '删除成功', icon: 'success' }, function () {
                  that.data.addressList.splice(index, 1);
                  that.setData({ addressList: that.data.addressList });
                });
              }).catch(err => {
                return app.Tips({ title: err });
              });
            } else if (res.cancel) {
              //
            }
          }
        })
      } else {
        wx.showToast({
          title: '您不能删除该打印机信息',
          icon: 'none',
        });
      }
    })
  },

  
  goOrder:function(e){
    var id = e.currentTarget.dataset.id;
    var cartId = '';
    var pinkId = '';
    var couponId = '';
    if (this.data.cartId && id) {
      cartId = this.data.cartId;
      pinkId = this.data.pinkId;
      couponId = this.data.couponId;
      this.setData({
        cartId: '',
        pinkId: '',
        couponId: '',
      })
      wx.redirectTo({ 
        url: '/pages/order_confirm/index?is_address=1&cartId=' + cartId + '&addressId=' + id + '&pinkId=' + pinkId + '&couponId=' + couponId
      })
    }
  },
  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    this.getAddressList();
  }
})