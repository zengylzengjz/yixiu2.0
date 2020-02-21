import { getMachineList } from '../../api/user.js';
import { getComputerList, setComputerDefault, delComputer, editComputer } from '../../api/user.js';

var app = getApp();
Component({
    properties: {
        //跳转url链接
        pagesUrl: {
            type: String,
            value: '',
        },
        machine: {
            type: Object,
            value: {
                machine: true,
                machineId: 0,
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
        machineList: [],
        is_loading: true,
    },
    attached: function() {

    },
    methods: {
        tapmachine: function(e) {
            this.setData({ active: e.currentTarget.dataset.id });
            this.triggerEvent('OnChangeMachine', e.currentTarget.dataset.machineid);
        },
        close: function() {
            this.setData({ 'machine.machine': false });
            // this.triggerEvent('changeTextareaStatus');
        },
        goMachinePages: function() {
            this.setData({ 'machine.machine': false });
            // this.triggerEvent('changeTextareaStatus');
            wx.navigateTo({ url: this.data.pagesUrl });
        },
        getMachineList: function() {
            var that = this;
            getMachineList({ page: 1, limit: 5 }).then(res => {
                var machineList = res.data;
                //处理默认选中项
                for (var i = 0, leng = machineList.length; i < leng; i++) {
                    if (machineList[i].id == that.data.machine.machineId) that.setData({ active: i });
                }
                that.setData({ machineList: machineList, is_loading: false });
            })
        }
    }
})
