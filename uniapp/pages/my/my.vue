<template>
	<view>
		<view class="u-flex user-box u-p-l-30 u-p-r-20 u-p-b-30">
			<view class="u-m-r-10">
				<u-avatar :src="userInfo.avatar" size="120"></u-avatar>
			</view>
			<view class="u-flex-1">
				<view class="u-font-18">
          <text class="name" v-if="!userInfo.id" @tap="openAuto">请点击授权</text>
          <text v-else>{{userInfo.nickname}}</text>
        </view>
        <template v-if="userInfo.id">
          <view v-if="userInfo.is_verify==1" @click="goPage('/pages/my/phone')" class="u-font-14 u-tips-color"><text v-if="userInfo.phone">手机号:{{userInfo.phone}}</text><text v-else>点击绑定手机号</text></view>
          <view v-else-if="userInfo.is_verify==-1" class="u-font-14 text-blue">审核中</view>
          <view v-else class="u-font-14 text-blue" @click="goPage('/pages/my/verify')">立即实名认证</view>
        </template>
			</view>
			<!-- <view @click="goPage('/pages/my/edit')" class="u-m-l-10 u-p-10">
				<u-icon name="setting" color="#969799" size="28"></u-icon>
			</view> -->
			<view class="u-p-10 edit-btn" @click="goPage('/pages/my/edit')">
				<u-icon name="arrow-right" color="#969799" size="28"></u-icon>
			</view>
		</view>
    
    <view class="order">
      <u-grid :col="4" :border="false">
      		<u-grid-item @click="goPage('/pages/order/order?status=1')">
            <u-badge :count="orderNum['unPay'] || 0" :offset="[30, 30]"></u-badge>
      			<u-icon name="daizhifu" custom-prefix="custom-icon" :size="46"></u-icon>
      			<view class="grid-text">待付款</view>
      		</u-grid-item>
      		<u-grid-item @click="goPage('/pages/order/order?status=2')">
            <u-badge :count="orderNum['unExpress'] || 0" :offset="[30, 30]"></u-badge>
      			<u-icon name="daifahuo" custom-prefix="custom-icon" :size="46"></u-icon>
      			<view class="grid-text">待发货</view>
      		</u-grid-item>
      		<u-grid-item @click="goPage('/pages/order/order?status=3')">
            <u-badge :count="orderNum['unReceive'] || 0" :offset="[30, 30]"></u-badge>
      			<u-icon name="shouhuo" custom-prefix="custom-icon" :size="46"></u-icon>
      			<view class="grid-text">待收货</view>
      		</u-grid-item>
      		<u-grid-item @click="goPage('/pages/order/order?status=4')">
            <u-badge :count="orderNum['done'] || 0" :offset="[30, 30]"></u-badge>
      			<u-icon name="wancheng" custom-prefix="custom-icon" :size="46"></u-icon>
      			<view class="grid-text">已完成</view>
      		</u-grid-item>
      	</u-grid>
    </view>
		<view class="u-m-t-20">
			<u-cell-group>
				<u-cell-item @click="goPage('/pages/my/bid')" title="出价记录">
          <u-icon slot="icon" size="32" custom-prefix="custom-icon" name="chujia"></u-icon>
        </u-cell-item>
				<u-cell-item @click="goPage('/pages/my/favorite')" title="我的收藏">
          <u-icon slot="icon" size="32" custom-prefix="custom-icon" name="shoucang"></u-icon>
        </u-cell-item>
				<u-cell-item @click="goPage('/pages/system/deposit/list')" title="保证金明细">
          <u-icon slot="icon" size="32" custom-prefix="custom-icon" name="baozhengjin"></u-icon>
        </u-cell-item>
				<u-cell-item @click="goPage('/pages/my/address/address')" title="收货地址">
          <u-icon slot="icon" size="32" custom-prefix="custom-icon" name="dizhi"></u-icon>
        </u-cell-item>
				<u-cell-item @click="goPage('/pages/system/help/help')" title="新手指南">
          <u-icon slot="icon" size="32" custom-prefix="custom-icon" name="bangzhu"></u-icon>
        </u-cell-item>
				<u-cell-item @click="goPage('/pages/system/about/about')" title="关于我们">
          <u-icon slot="icon" size="32" custom-prefix="custom-icon" name="guanyuwomen"></u-icon>
        </u-cell-item>
			</u-cell-group>
		</view>
		
		<view class="u-m-t-20 contact">
			<u-cell-group>
				<u-cell-item @click="goPage('/pages/system/about/contact')" title="联系客服">
          <u-icon slot="icon" size="32" custom-prefix="custom-icon" name="kefu"></u-icon>
        </u-cell-item>
			</u-cell-group>
		</view>
    
		<authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize>
	</view>
</template>

<script>
  import Util from '@/utils/util.js';
  import { getUserInfo } from '@/api/user.js';
  import { getOrderNum } from '@/api/order.js';
	import { toLogin } from '@/libs/login.js';
  import { mapGetters } from "vuex";
  import authorize from '@/components/Authorize';
  import Cache from '@/utils/cache';
	export default {
    components:{
      authorize
    },
		computed: mapGetters(['isLogin']),
		data() {
			return {
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				userInfo: {},
        orderNum: [{unPay:0, unExpress:0, unReceive:0, done:0}], //订单数量
			}
		},
		onLoad() {
			var that = this;
      if (that.isLogin == false) {
        toLogin();
      }
		},
		onShow: function() {
			if (this.isLogin) {
				this.user();
        this.order();
			}
		},
		methods: {
      goPage(url, type='navigate'){
        //判断是否登录，如未登录则弹窗
        if(this.isLogin == false){
          this.openAuto();
        }else{
          Util.goPage(url, type);
        }
      },
      user(){
        var that=this;
        getUserInfo().then(res=>{
          that.userInfo = res.data.userInfo;
          //防止后台审核通过后仍无权限问题
          Cache.set('USER_INFO', res.data.userInfo);
          that.$store.commit("SETUID", res.data.userInfo.id);
        });
      },
      //获取订单基础信息 如个数
      order(){
        var that=this;
        getOrderNum().then(res=>{
          that.orderNum = res.data;
        });
      },
			// 打开授权
			openAuto() {
				this.isAuto = true;
				this.isShowAuth = true
			},
			// 授权回调
			onLoadFun() {
				this.user();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
		}
	}
</script>

<style lang="scss">
page{
	background-color: #ededed;
}

.camera{
	width: 54px;
	height: 44px;
	
	&:active{
		background-color: #ededed;
	}
}
.user-box{
	background-color: #fff;
}
.u-m-t-20 .u-cell_title{
  margin-left: 16rpx;
}
.edit-btn{
  padding: 10rpx 10rpx 10rpx 50rpx !important;
}
.contact{
  margin-bottom: 20rpx;
}
</style>
