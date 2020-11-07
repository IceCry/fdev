<template>
  <view>
    <view class='order-details'>
      <!-- 给header上与data上加on为退款订单-->
      <view class='header bg-color flex align-center'>
        <view class='pictrue'>
          <image :src="orderInfo.status_pic"></image>
        </view>
        <view class='data'>
          <view class='state'>{{orderInfo.status_msg}}</view>
          <view v-if="orderInfo.express_type=='express' && orderInfo.express_id" @click="goPage('/pages/order/express?uuid='+orderInfo.uuid)">查看物流信息</view>
        </view>
      </view>
      
      <view>
        <view class='address'>
          <view class='name'>{{orderInfo.user_name}}<text class='phone'>{{orderInfo.user_phone}}</text></view>
          <view>{{orderInfo.user_address}}</view>
        </view>
        <view class='line'>
          <image src='../../static/line.jpg'></image>
        </view>
      </view>

      <orderGoods :cartInfo="cartInfo" :jump="true"></orderGoods>

      <div class="goodCall" @click="goPage('/pages/system/about/contact')">
        <u-icon name="server-fill" color="#e93323" size="28"></u-icon>
        <span class="u-padding-left-10">联系客服</span>
      </div>
      <view class='wrapper'>
        <view class='item flex justify-between'>
          <view>订单编号：</view>
          <view class='conter align-center flex justify-end'>{{orderInfo.order_sn}}
            <text class='copy' @tap='copy'>复制</text>
          </view>
        </view>
        <view class='item flex justify-between'>
          <view>下单时间：</view>
          <view class='conter'>{{orderInfo.create_time}}</view>
        </view>
        <view class='item flex justify-between'>
          <view>支付状态：</view>
          <view class='conter' v-if="orderInfo.pay_time">已支付</view>
          <view class='conter' v-else>未支付</view>
        </view>
        <view class='item flex justify-between'>
          <view>支付方式：</view>
          <view class='conter'>{{orderInfo.pay_type == 'weixin'?'微信支付':'线下支付'}}</view>
        </view>
        <view class='item flex justify-between' v-if="orderInfo.mark">
          <view>买家留言：</view>
          <view class='conter'>{{orderInfo.mark}}</view>
        </view>
        <view class='item flex justify-between' v-if="orderInfo.cancel_time">
          <view>取消时间：</view>
          <view class='conter'>{{orderInfo.cancel_time}}</view>
        </view>
      </view>

      <view v-if="orderInfo.status!=0">
        <view class='wrapper' v-if='orderInfo.express_type=="express"'>
          <view class='item flex justify-between'>
            <view>配送方式：</view>
            <view class='conter'>{{orderInfo.express_type=='express'?'快递发货':'上门自提'}}</view>
          </view>
          <view class='item flex justify-between'>
            <view>快递公司：</view>
            <view class='conter'>{{orderInfo.express_name || ''}}</view>
          </view>
          <view class='item flex justify-between'>
            <view>快递号：</view>
            <view class='conter'>{{orderInfo.express_id || ''}}</view>
          </view>
        </view>
      </view>
      <view class='wrapper'>
        <view class='item flex justify-between'>
          <view>支付金额：</view>
          <view class='conter'>￥{{orderInfo.total_price}}</view>
        </view>
        <view class='item flex justify-between' v-if="orderInfo.postage_price > 0">
          <view>运费：</view>
          <view class='conter'>￥{{orderInfo.postage_price}}</view>
        </view>
        <view class='item flex justify-between' v-if="orderInfo.deduction_price > 0">
          <view>保证金抵扣：</view>
          <view class='conter'>-￥{{orderInfo.deduction_price}}</view>
        </view>
        <view class='actualPay flex row-right' v-if="orderInfo.pay_time>0">实付款：<text class='money font-color'>￥{{orderInfo.pay_price}}</text></view>
      </view>
      <view style='height:120rpx;'></view>
    </view>
  </view>
</template>
<style scoped lang="scss">
  .goodCall {
    color: #e93323;
    text-align: center;
    width: 100%;
    height: 86rpx;
    padding: 0 30rpx;
    border-bottom: 1rpx solid #eee;
    font-size: 30rpx;
    line-height: 86rpx;
    background: #fff;

    .icon-kefu {
      font-size: 36rpx;
      margin-right: 15rpx;
    }

    /* #ifdef MP */
    button {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 86rpx;
      font-size: 30rpx;
      color: #e93323;
    }

    /* #endif */
  }

  .order-details .header {
    padding: 0 30rpx;
    height: 150rpx;
  }

  .order-details .header.on {
    background-color: #666 !important;
  }

  .order-details .header .pictrue {
    width: 110rpx;
    height: 110rpx;
  }

  .order-details .header .pictrue image {
    width: 100%;
    height: 100%;
  }

  .order-details .header .data {
    color: rgba(255, 255, 255, 0.8);
    font-size: 24rpx;
    margin-left: 27rpx;
  }

  .order-details .header .data.on {
    margin-left: 0;
  }

  .order-details .header .data .state {
    font-size: 30rpx;
    font-weight: bold;
    color: #fff;
    margin-bottom: 7rpx;
  }

  .order-details .header .data .time {
    margin-left: 20rpx;
  }

  .order-details .nav {
    background-color: #fff;
    font-size: 26rpx;
    color: #282828;
    padding: 25rpx 0;
  }

  .order-details .nav .navCon {
    padding: 0 40rpx;
  }

  .order-details .nav .on {
    color: #e93323;
  }

  .order-details .nav .progress {
    padding: 0 65rpx;
    margin-top: 10rpx;
  }

  .order-details .nav .progress .line {
    width: 100rpx;
    height: 2rpx;
    background-color: #939390;
  }

  .order-details .nav .progress .iconfont {
    font-size: 25rpx;
    color: #939390;
    margin-top: -2rpx;
  }

  .order-details .address {
    font-size: 26rpx;
    color: #868686;
    background-color: #fff;
    margin-top: 13rpx;
    padding: 20rpx 30rpx;
  }

  .order-details .address .name {
    font-size: 30rpx;
    color: #282828;
    margin-bottom: 15rpx;
  }

  .order-details .address .name .phone {
    margin-left: 40rpx;
  }

  .order-details .line {
    width: 100%;
    height: 3rpx;
  }

  .order-details .line image {
    width: 100%;
    height: 100%;
    display: block;
  }

  .order-details .wrapper {
    background-color: #fff;
    margin-top: 12rpx;
    padding: 30rpx;
  }

  .order-details .wrapper .item {
    font-size: 28rpx;
    color: #282828;
  }

  .order-details .wrapper .item~.item {
    margin-top: 20rpx;
  }

  .order-details .wrapper .item .conter {
    color: #868686;
    width: 460rpx;
    text-align: right;
  }

  .order-details .wrapper .item .conter .copy {
    font-size: 20rpx;
    color: #333;
    border-radius: 3rpx;
    border: 1rpx solid #666;
    padding: 0rpx 15rpx;
    margin-left: 24rpx;
  }

  .order-details .wrapper .actualPay {
    border-top: 1rpx solid #eee;
    margin-top: 30rpx;
    padding-top: 30rpx;
  }

  .order-details .wrapper .actualPay .money {
    font-weight: bold;
    font-size: 30rpx;
  }

  .order-details .footer {
    width: 100%;
    height: 100rpx;
    position: fixed;
    bottom: 0;
    left: 0;
    background-color: #fff;
    padding: 0 30rpx;
    box-sizing: border-box;
  }

  .order-details .footer .bnt {
    width: 176rpx;
    height: 60rpx;
    text-align: center;
    line-height: 60rpx;
    border-radius: 50rpx;
    color: #fff;
    font-size: 27rpx;
  }

  .order-details .footer .bnt.cancel {
    color: #aaa;
    border: 1rpx solid #ddd;
  }

  .order-details .footer .bnt~.bnt {
    margin-left: 18rpx;
  }

  .order-details .writeOff {
    background-color: #fff;
    margin-top: 13rpx;
    padding-bottom: 30rpx;
  }

  .order-details .writeOff .title {
    font-size: 30rpx;
    color: #282828;
    height: 87rpx;
    border-bottom: 1px solid #f0f0f0;
    padding: 0 30rpx;
    line-height: 87rpx;
  }

  .order-details .writeOff .grayBg {
    background-color: #f2f5f7;
    width: 590rpx;
    height: 384rpx;
    border-radius: 20rpx 20rpx 0 0;
    margin: 50rpx auto 0 auto;
    padding-top: 55rpx;
    position: relative;
  }

  .order-details .writeOff .grayBg .written {
    position: absolute;
    top: 0;
    right: 0;
    width: 60rpx;
    height: 60rpx;
  }

  .order-details .writeOff .grayBg .written image {
    width: 100%;
    height: 100%;
  }

  .order-details .writeOff .grayBg .pictrue {
    width: 290rpx;
    height: 290rpx;
    margin: 0 auto;
  }

  .order-details .writeOff .grayBg .pictrue image {
    width: 100%;
    height: 100%;
    display: block;
  }

  .order-details .writeOff .gear {
    width: 590rpx;
    height: 30rpx;
    margin: 0 auto;
  }

  .order-details .writeOff .gear image {
    width: 100%;
    height: 100%;
    display: block;
  }

  .order-details .writeOff .num {
    background-color: #f0c34c;
    width: 590rpx;
    height: 84rpx;
    color: #282828;
    font-size: 48rpx;
    margin: 0 auto;
    border-radius: 0 0 20rpx 20rpx;
    text-align: center;
    padding-top: 4rpx;
  }

  .order-details .writeOff .rules {
    margin: 46rpx 30rpx 0 30rpx;
    border-top: 1px solid #f0f0f0;
    padding-top: 10rpx;
  }

  .order-details .writeOff .rules .item {
    margin-top: 20rpx;
  }

  .order-details .writeOff .rules .item .rulesTitle {
    font-size: 28rpx;
    color: #282828;
  }

  .order-details .writeOff .rules .item .rulesTitle .iconfont {
    font-size: 30rpx;
    color: #333;
    margin-right: 8rpx;
    margin-top: 5rpx;
  }

  .order-details .writeOff .rules .item .info {
    font-size: 28rpx;
    color: #999;
    margin-top: 7rpx;
  }

  .order-details .writeOff .rules .item .info .time {
    margin-left: 20rpx;
  }

  .order-details .map {
    height: 86rpx;
    font-size: 30rpx;
    color: #282828;
    line-height: 86rpx;
    border-bottom: 1px solid #f0f0f0;
    margin-top: 13rpx;
    background-color: #fff;
    padding: 0 30rpx;
  }

  .order-details .map .place {
    font-size: 26rpx;
    width: 176rpx;
    height: 50rpx;
    border-radius: 25rpx;
    line-height: 50rpx;
    text-align: center;
  }

  .order-details .map .place .iconfont {
    font-size: 27rpx;
    height: 27rpx;
    line-height: 27rpx;
    margin: 2rpx 3rpx 0 0;
  }

  .order-details .address .name .iconfont {
    font-size: 34rpx;
    margin-left: 10rpx;
  }

  .refund {
    padding: 0 30rpx 30rpx;
    margin-top: 24rpx;
    background-color: #fff;

    .title {
      display: flex;
      align-items: center;
      font-size: 30rpx;
      color: #333;
      height: 86rpx;
      border-bottom: 1px solid #f5f5f5;

      image {
        width: 32rpx;
        height: 32rpx;
        margin-right: 10rpx;
      }
    }

    .con {
      padding-top: 25rpx;
      font-size: 28rpx;
      color: #868686;
    }
  }
  .qs-btn {
    width: auto;
    height: 60rpx;
    text-align: center;
    line-height: 60rpx;
    border-radius: 50rpx;
    color: #fff;
    font-size: 27rpx;
    padding: 0 3%;
    color: #aaa;
    border: 1px solid #ddd;
    margin-right: 20rpx;
  }
  .bg-color{
    background: #e93323;
  }
</style>

<script>
  import {
    orderInfo
  } from '@/api/order.js';
  import Util from '@/utils/util.js';
  import orderGoods from "@/components/orderGoods";
  export default {
    components: {
      orderGoods
    },
    data() {
      return {
        uuid: '',
        evaluate: 0,
        cartInfo: [], //购物车产品
        orderInfo: {
          system_store: {},
          _status: {}
        }, //订单详情
        isGoodsReturn: false, //是否为退款订单
        status: {}, //订单底部按钮状态
        totalPrice: '0',
      };
    },
    onLoad: function(options) {
      this.uuid = options.uuid || '';
      this.getOrderInfo();
    },
    methods: {
      /**
       * 获取订单详细信息
       */
      getOrderInfo: function() {
        let that = this;
        uni.showLoading({
          title: "正在加载中"
        });
        orderInfo({
          uuid: this.uuid
        }).then(res => {
          uni.hideLoading();
          that.$set(that, 'orderInfo', res.data.info);
          that.cartInfo = res.data.info.snap.products;
        }).catch(err => {
          uni.hideLoading();
          that.$util.Tips({
            title: err
          }, '/pages/order/order?status=1');
        });
      },
      /**
       * 剪切订单号
       */
      copy: function() {
        let that = this;
        uni.setClipboardData({
          data: this.orderInfo.order_sn,
          success() {
            return that.$util.Tips({
              title: '已复制'
            });
          }
        });
      },
      goPage(url, type = 'navigate') {
        Util.goPage(url, type);
      }
    }
  }
</script>