Page({
  data: {
    focus: false,
    inputValue: ''
  },
  bindKeyInput: function (e) {
    this.setData({
      inputValue: e.detail.value
    })
  },
  bindReplaceInput: function (e) {
    var value = e.detail.value
    var pos = e.detail.cursor
    var left
    if (pos !== -1) {
      // 光标在中间
      left = e.detail.value.slice(0, pos)
      // 计算光标的位置
      pos = left.replace(/11/g, '2').length
    }

    // 直接返回对象，可以对输入进行过滤处理，同时可以控制光标的位置
    return {
      value: value.replace(/11/g, '2'),
      cursor: pos
    }

    // 或者直接返回字符串,光标在最后边
    // return value.replace(/11/g,'2'),
  },
  bindHideKeyboard: function (e) {
    if (e.detail.value === '123') {
      // 收起键盘
      wx.hideKeyboard()
    }
  },
  data: {
    img_arr: [],
    formdata: '',
  },
  formSubmit: function (e) {
    if (e.detail.value.building.length == 0) {

      wx.showToast({

        title: '楼栋不能为空!',

        icon: 'loading',

        duration: 1000

      })

      setTimeout(function () {

        wx.hideToast()

      }, 1000)

    } else if (e.detail.value.roomid.length == 0) {

      wx.showToast({

        title: '房间号不能为空!',

        icon: 'loading',

        duration: 1000

      })

      setTimeout(function () {

        wx.hideToast()

      }, 1000)

    } else if (e.detail.value.printer.length == 0) {

      wx.showToast({

        title: '打印机型号不能为空!',

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
        url: 'http://www.wx.com/api/order/create/', 
        data: {

          building: e.detail.value.building,
          roomid: e.detail.value.roomid,
          printer: e.detail.value.printer,
          description: e.detail.value.description,
        },
        method: 'POST',
        header: {
          'content-type':
            'application/x-www-form-urlencoded' // 默认值
        },
        success: function (res) {
          // console.log(res.data);
          //   console.log(e.detail.value.post_type)
          console.log(e.detail.value.time);
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

  data: {
    
    objectArray: ['综合楼', '学院楼', '弘德楼', "校友会堂", "田家炳", "其他"],
    index: 0,
  },
  //  点击分类组件确定事件  
  bindPickerChange: function (e) {
    // console.log(e.detail.value)
    this.setData({
      index: e.detail.value
    })
  }
})