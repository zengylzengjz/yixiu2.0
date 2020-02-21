// pages/index/repair.js
Page({

  formSubmit: function (e) {
    if (e.detail.value.title.length == 0) {

      wx.showToast({

        title: '标题不能为空!',

        icon: 'loading',

        duration: 1000

      })

      setTimeout(function () {

        wx.hideToast()

      }, 1000)

    } else if (e.detail.value.content.length == 0) {

      wx.showToast({

        title: '内容不能为空!',

        icon: 'loading',

        duration: 1000

      })

      setTimeout(function () {

        wx.hideToast()

      }, 1000)

    } else if (e.detail.value.address.length == 0) {

      wx.showToast({

        title: '地址不能为空!',

        icon: 'loading',

        duration: 1000

      })

      setTimeout(function () {

        wx.hideToast()

      }, 1000)

    } else {
      wx.getUserInfo({
        success: function (res) {
          var userInfo = res.userInfo
        }
      })

      wx.request({
        url: '',
        data: {
          title: e.detail.value.title,
          content: e.detail.value.content,
          post_type: e.detail.value.post_type,
          date: e.detail.value.date,
          time: e.detail.value.time,
          address: e.detail.value.address
        },
        method: 'POST',
        header: {
          'content-type':
            'application/x-www-form-urlencoded' // 默认值
        },
        success: function (res) {
          // console.log(res.data);
          //   console.log(e.detail.value.post_type)
          // console.log(e.detail.value.time);
          if (res.data.status == 0) {
            wx.showToast({
              title: '提交失败！！！',
              icon: 'loading',
              duration: 1500
            })
          } else {
            wx.showToast({
              title: '提交成功！！！',//这里打印出登录成功
              icon: 'success',
              duration: 1000
            })
          }
        }
      })
    }
  },

  /**
   * 页面的初始数据
   */
  data: {

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})
