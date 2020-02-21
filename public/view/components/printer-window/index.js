import { getPrinterList } from '../../api/user.js';
import { getComputerList, setComputerDefault, delComputer, editComputer } from '../../api/user.js';

var app = getApp();
Component({
    properties: {
        //跳转url链接
        pagesUrl: {
            type: String,
            value: '',
        },
        printer: {
            type: Object,
            value: {
                printer: true,
                printerId: 0,
            }
        },
        isLog: {
            type: Boolean,
            value: false,
        },
    },
    data: {
        // addressList: [],
        // loading: false,
        // loadend: false,
        // loadTitle: '加载更多',
        // page: 1,
        // limit: 8,
        active: 0,
        //地址列表
        printerList: [],
        is_loading: true,
    },
    attached: function() {

    },
    methods: {
        tapprinter: function(e) {
            this.setData({ active: e.currentTarget.dataset.id });
            this.triggerEvent('OnChangePrinter', e.currentTarget.dataset.printerid);
        },
        close: function() {
            this.setData({ 'printer.printer': false });
            // this.triggerEvent('changeTextareaStatus');
        },
        goPrinterPages: function() {
            this.setData({ 'printer.printer': false });
            // this.triggerEvent('changeTextareaStatus');
            wx.navigateTo({ url: this.data.pagesUrl });
        },
        getPrinterList: function() {
            var that = this;
            getPrinterList({ page: 1, limit: 5 }).then(res => {
                var printerList = res.data;
                //处理默认选中项
                for (var i = 0, leng = printerList.length; i < leng; i++) {
                    if (printerList[i].id == that.data.printer.printerId) that.setData({ active: i });
                }
                that.setData({ printerList: printerList, is_loading: false });
            })
        }
    }
})
