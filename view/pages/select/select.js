// pages/select/select.js
const app = getApp();
import { selectSet } from '../../api/user.js';
import { setFormId } from '../../api/api.js';

Page({
    data: {
        searchValue: "",
      objectArray: ['计算机与信息科学学院', '文学院', '历史与社会学院', '地理与旅游学院', '数学科学学院', '经济与管理学院', '物理与电子工程学院', '音乐学院', '继续教育学院', '生命科学学院', '马克思主义学院', '外国语学院', '国际汉语文化学院', '职教师资学院', '初等教育学院', '美术学院', '教育科学学院', '新闻与传媒学院', '体育与健康科学学院','化学学院','其他'],
        index: 0,
      imagesArray: "/images/student.png",
      imgArray: "/images/teacher_select.png",
        flag: 1,
        teacher: "teacther",
        student: "student",
        StudentNum: '123',
        StudentSchool: '456',
    },

    formSubmit: function (e) {
        var that = this;
        if (e.detail.value.school_num.length == 0) {
            wx.showToast({
                title: '学号不能为空!',
                icon: 'loading',
                duration: 1000
            })
            setTimeout(function () {
                wx.hideToast()
            }, 1000)
        } else if (e.detail.value.school_num.length < 13) {
          wx.showToast({
            title: '学号不符合标准!',
            icon: 'loading',
            duration: 1000
          })
          setTimeout(function () {
            wx.hideToast()
          }, 1000)
        }else {
            selectSet({
                school_num: e.detail.value.school_num,
                college: e.detail.value.college,
                flag: e.detail.value.flag,
            }).then(res => {
                // console.log(233, res.data.school_num);
                // console.log(234, res.data.college);
                // wx.setStorageSync({ key: 'studentNum', data: res.data.school_num });
                // wx.setStorageSync({ key: 'college', data: res.data.college });
                // var a = wx.setStorageSync('studentNum', res.data.school_num);
                // var b = wx.setStorageSync('college', res.data.college);
                var datatest = res.data;
            }).catch((e) => { console.log(e) });
            var selectflag = app.globalData.selectflag;
            var datatest = wx.getStorageInfoSync();
            wx.showLoading({
                title: '正在缓存中',
            })
            setTimeout(function () {
                wx.navigateBack({
                    delta: 1, // 回退前 delta(默认为1) 页面
                })
            }, 2000);
        }
    },
  teacher: function (e) {
    // var s = app.globalData.StudentNum
    this.setData({
      imgArray: "/images/teacher_select.png",
      imagesArray: "/images/student.png",
      flag: 1,
    })
  },
  student: function (e) {
    this.setData({
      imgArray: "/images/teacher.png",
      imagesArray: "/images/student_select.png",
      flag: 0
    })
  },

    bindKeyInput: function(e) {
        this.setData({
            inputValue: e.detail.value
        })
    },
    bindReplaceInput: function(e) {
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
    bindHideKeyboard: function(e) {
        if (e.detail.value === '123') {
            // 收起键盘
            wx.hideKeyboard()
        }
    },
    // //提交返回
    // buttonsub: function(e) {
    //     console.log('提交')
    //     wx.navigateBack({
    //         delta: 1, // 回退前 delta(默认为1) 页面
    //         success: function(res) {
    //             // success
    //         },
    //         fail: function() {
    //             // fail
    //         },
    //         complete: function() {
    //             // complete
    //         }
    //     })
    // },

    //  点击分类组件确定事件
    bindPickerChange: function(e) {
        // console.log(e.detail.value)
        this.setData({
            index: e.detail.value
        })
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {

    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function() {
        // var userInfo = app.globalData.userInfo
        // if (1 > 0) {
        //     wx.navigateTo({
        //         url: '/pages/select/select',
        //     })
        // }
    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function() {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function() {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function() {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function() {

    }
})
