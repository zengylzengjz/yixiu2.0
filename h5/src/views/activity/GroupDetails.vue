<template>
  <div
    :class="[posterImageStatus ? 'noscroll product-con' : 'product-con']"
    v-show="domStatus"
  >
    <ProductConSwiper :imgUrls="imgUrls"></ProductConSwiper>
    <div class="wrapper">
      <div class="share acea-row row-between row-bottom">
        <div class="money font-color-red">
          ￥<span class="num" v-text="storeInfo.price"></span
          ><span class="y-money" v-text="'￥' + storeInfo.product_price"></span>
        </div>
        <div class="iconfont icon-fenxiang" @click="setPosterImageStatus"></div>
      </div>
      <div class="introduce" v-text="storeInfo.title"></div>
      <div class="label acea-row row-between-wrapper">
        <div v-text="'类型:' + storeInfo.people + '人团'"></div>
        <div v-text="'库存:' + storeInfo.stock + storeInfo.unit_name"></div>
        <div v-text="'已拼:' + storeInfo.sales + storeInfo.unit_name"></div>
      </div>
    </div>
    <div class="notice acea-row row-middle">
      <div class="num font-color-red">
        <span class="iconfont icon-laba"></span>已拼{{ storeInfo.sales
        }}{{ storeInfo.unit_name }}<span class="line">|</span>
      </div>
      <div class="swiper-no-swiping swiper">
        <swiper class="swiper-wrapper" :options="swiperTip">
          <swiperSlide
            class="swiper-slide"
            v-for="(item, index) in itemNew"
            :key="index"
          >
            <div class="line1">{{ item }}</div>
          </swiperSlide>
        </swiper>
      </div>
    </div>
    <div class="assemble">
      <div v-for="(item, index) in groupList" :key="index">
        <div
          class="item acea-row row-between-wrapper"
          v-if="index < groupListCount"
        >
          <div class="pictxt acea-row row-between-wrapper">
            <div class="pictrue"><img :src="item.avatar" class="image" /></div>
            <div class="text line1" v-text="item.nickname"></div>
          </div>
          <div class="right acea-row row-middle">
            <div>
              <div class="lack">
                还差<span class="font-color-red" v-text="item.count"></span
                >人成团
              </div>
              <CountDown
                :is-day="false"
                :tip-text="'剩余 '"
                :day-text="''"
                :hour-text="':'"
                :minute-text="':'"
                :second-text="''"
                :datatime="item.stop_time"
              ></CountDown>
            </div>
            <div class="spellBnt" @click="groupRule(item.id)">
              去拼单<span class="iconfont icon-jiantou"></span>
            </div>
          </div>
        </div>
      </div>
      <div
        class="more"
        v-if="groupList.length > groupListCount"
        @click="setGroupListCount"
      >
        查看更多<span class="iconfont icon-xiangxia"></span>
      </div>
    </div>
    <div class="playWay">
      <div class="title acea-row row-between-wrapper">
        <div>拼团玩法</div>
      </div>
      <div class="way acea-row row-middle">
        <div class="item"><span class="num">①</span>开团/参团</div>
        <div class="iconfont icon-arrow"></div>
        <div class="item"><span class="num">②</span>邀请好友</div>
        <div class="iconfont icon-arrow"></div>
        <div class="item">
          <div><span class="num">③</span>满员发货</div>
        </div>
      </div>
    </div>
    <div class="userEvaluation">
      <div class="title acea-row row-between-wrapper">
        <div v-text="'用户评价(' + replyCount + ')'"></div>
        <div class="praise" @click="goReply">
          <span class="font-color-red" v-text="replyChance + '%'"></span
          >好评率<span class="iconfont icon-jiantou"></span>
        </div>
      </div>
      <UserEvaluation :reply="reply"></UserEvaluation>
    </div>
    <div class="product-intro">
      <div class="title">产品介绍</div>
      <div class="conter" v-html="storeInfo.description"></div>
    </div>
    <div style="height:1.2rem;"></div>
    <div class="footer-group acea-row row-between-wrapper">
      <div
        class="customerSer acea-row row-center-wrapper row-column"
        @click="$router.push({ path: '/customer/list' })"
      >
        <div class="iconfont icon-kefu"></div>
        <div>客服</div>
      </div>
      <div class="bnt bg-color-violet" @click="openAlone">单独购买</div>
      <div class="bnt bg-color-red" @click="openTeam">立即开团</div>
    </div>
    <ProductWindow v-on:changeFun="changeFun" :attr="attr"></ProductWindow>
    <StorePoster
      v-on:setPosterImageStatus="setPosterImageStatus"
      :posterImageStatus="posterImageStatus"
      :posterData="posterData"
    ></StorePoster>
  </div>
</template>
<style scoped>
.noscroll {
  height: 100%;
  overflow: hidden;
}
.product-con .footer-group .bnt {
  width: 43%;
}
.product-con .footer-group .bnt.bg-color-violet {
  background-color: #fa8013;
}
</style>

<script>
import { swiper, swiperSlide } from "vue-awesome-swiper";
import "@assets/css/swiper.min.css";
import ProductConSwiper from "@components/ProductConSwiper";
import CountDown from "@components/CountDown";
import UserEvaluation from "@components/UserEvaluation";
import ProductWindow from "@components/ProductWindow";
import StorePoster from "@components/StorePoster";
import { getCombinationDetail } from "@api/activity";
import { postCartAdd } from "@api/store";
import { imageBase64 } from "@api/public";
const NAME = "GroupDetails";

export default {
  name: "GroupDetails",
  components: {
    ProductConSwiper,
    CountDown,
    UserEvaluation,
    swiper,
    swiperSlide,
    ProductWindow,
    StorePoster
  },
  props: {},
  data: function() {
    return {
      domStatus: false,
      posterData: {
        image: "",
        title: "",
        price: "",
        code: ""
      },
      posterImageStatus: false,
      reply: [],
      replyCount: 0,
      replyChance: 0,
      imgUrls: [],
      storeInfo: {},
      itemNew: {},
      groupListCount: 2,
      groupList: {},
      swiperTip: {
        direction: "vertical",
        autoplay: {
          disableOnInteraction: false,
          delay: 2000
        },
        loop: true,
        speed: 1000,
        observer: true,
        observeParents: true
      },
      attr: {
        cartAttr: false,
        productSelect: {
          image: "",
          store_name: "",
          price: "",
          stock: "",
          unique: "",
          cart_num: 1
        }
      }
    };
  },
  watch: {
    $route: function(n) {
      var that = this;
      console.log(n);
      if (n.name === NAME) {
        that.mountedStart();
      }
    }
  },
  mounted: function() {
    this.mountedStart();
  },
  methods: {
    openAlone: function() {
      this.$router.replace({ path: "/detail/" + this.storeInfo.product_id });
    },
    mountedStart: function() {
      var that = this;
      let id = that.$route.params.id;
      getCombinationDetail(id).then(res => {
        that.$set(that, "storeInfo", res.data.storeInfo);
        that.$set(that, "imgUrls", res.data.storeInfo.images);
        that.$set(that, "itemNew", res.data.pink_ok_list);
        that.$set(that, "groupList", res.data.pink);
        that.$set(that, "reply", [res.data.reply]);
        that.$set(that, "replyCount", res.data.replyCount);
        that.$set(that, "replyChance", res.data.replyChance);
        that.setProductSelect();
        that.posterData.image = that.storeInfo.image_base;
        if (that.storeInfo.title.length > 30) {
          that.posterData.title = that.storeInfo.title.substring(0, 30) + "...";
        } else {
          that.posterData.title = that.storeInfo.title;
        }
        that.posterData.price = that.storeInfo.price;
        that.posterData.code = that.storeInfo.code_base;
        that.domStatus = true;
        that.getImageBase64();
      });
    },
    getImageBase64: function() {
      let that = this;
      imageBase64(this.posterData.image, that.posterData.code)
        .then(res => {
          that.posterData.image = res.data.image;
          that.posterData.code = res.data.code;
        })
        .catch(res => {
          that.$dialog.error(res.msg);
        });
    },
    setPosterImageStatus: function() {
      var sTop = document.body || document.documentElement;
      sTop.scrollTop = 0;
      this.posterImageStatus = !this.posterImageStatus;
    },
    groupRule: function(id) {
      var that = this;
      that.$router.push({
        path: "/activity/group_rule/" + id
      });
    },
    goReply: function() {
      var that = this;
      that.$router.push({
        path: "/evaluate_list/" + that.storeInfo.product_id
      });
    },
    setGroupListCount: function() {
      this.groupListCount = this.groupListCount + 2;
    },
    //将父级向子集多次传送的函数合二为一；
    changeFun: function(opt) {
      if (typeof opt !== "object") opt = {};
      let action = opt.action || "";
      let value = opt.value === undefined ? "" : opt.value;
      this[action] && this[action](value);
    },
    changeattr: function(res) {
      var that = this;
      that.attr.cartAttr = res;
    },
    ChangeCartNum: function(res) {
      var that = this;
      console.log(res);
      that.attr.productSelect.cart_num = 1;
      that.$dialog.message("每人每次限购1" + that.storeInfo.unit_name);
    },
    setProductSelect: function() {
      var that = this;
      var attr = that.attr;
      attr.productSelect.image = that.storeInfo.image;
      attr.productSelect.store_name = that.storeInfo.title;
      attr.productSelect.price = that.storeInfo.price;
      attr.productSelect.stock = that.storeInfo.stock;
      attr.cartAttr = false;
      that.$set(that, "attr", attr);
    },
    openTeam: function() {
      var that = this;
      if (that.attr.cartAttr == false) {
        that.attr.cartAttr = !this.attr.cartAttr;
      } else {
        var data = {};
        data.productId = that.storeInfo.product_id;
        data.cartNum = that.attr.productSelect.cart_num;
        data.uniqueId = that.attr.productSelect.unique;
        data.combinationId = that.storeInfo.id;
        data.new = 1;
        postCartAdd(data)
          .then(res => {
            that.$router.push({
              path: "/order/submit/" + res.data.cartId
            });
          })
          .catch(res => {
            this.$dialog.error(res.msg);
          });
      }
    }
  }
};
</script>
<style scoped>
.product-con .wrapper {
  padding-bottom: 0.26rem;
}
</style>
