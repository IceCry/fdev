<template>
	<view>
		<view class='payment-status'>
			<!--失败时： 用icon-iconfontguanbi fail替换icon-duihao2 bg-color-->
			<view class='iconfont icons bg-color' v-if="info.pay_time>0 || info.pay_type == 'offline'">
        <u-icon name="checkbox-mark" color="#ffffff" size="60"></u-icon>
      </view>
			<view class='iconfont icons bg-color' v-else>
        <u-icon name="close" color="#ffffff" size="60"></u-icon>
      </view>
			<!-- 失败时：订单支付失败 -->
			<view class='status' v-if="info.pay_type != 'offline'">{{info.pay_time>0 ? '订单支付成功':'订单支付失败'}}</view>
			<view class='status' v-else>订单创建成功</view>
			<view class='wrapper'>
				<view class='item flex justify-between'>
					<view>订单编号</view>
					<view class='itemCom'>{{info.order_sn}}</view>
				</view>
				<view class='item flex justify-between'>
					<view>下单时间</view>
					<view class='itemCom'>{{info.create_time}}</view>
				</view>
				<view class='item flex justify-between'>
					<view>支付方式</view>
					<view class='itemCom'>{{info.pay_type=='weixin'?'微信支付':'线下支付'}}</view>
				</view>
				<view class='item flex justify-between'>
					<view>支付金额</view>
					<view class='itemCom'>{{info.need_pay}}</view>
				</view>
				<!--失败时加上这个  -->
				<view class='item flex justify-between' v-if="info.pay_time==0 && info.pay_type != 'offline'">
					<view>失败原因</view>
					<view class='itemCom'>{{status==2 ? '取消支付':msg}}</view>
				</view>
			</view>
			<view @tap="goOrderDetails">
				<button formType="submit" class='returnBnt bg-color' hover-class='none'>查看订单</button>
			</view>
			<button @click="goIndex" class='returnBnt text-red' formType="submit" hover-class='none'>返回首页</button>
			
		</view>
	</view>
</template>

<script>
	import { orderInfo } from '@/api/order.js';
	import { mapGetters } from "vuex";
	export default {
		data() {
			return {
				orderId: '',
				info: {},
				status: 0,
				msg: '',
			};
		},
		computed: mapGetters(['isLogin']),
		onLoad: function(options) {
			if (!options.order_id) return this.$util.Tips({
				title: '缺少参数无法查看订单支付状态'
			}, {tab: 3, url: 1});
			this.orderId = options.order_id;
			this.status = options.status || 0;
			this.msg = options.msg || '';
			if (this.isLogin) {
				this.getOrderPayInfo();
			} else {
				return this.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
			}
		},
		methods: {
			onLoadFun: function() {
				this.getOrderPayInfo();
			},
			/**
			 * 支付完成查询支付状态
			 */
			getOrderPayInfo: function() {
				let that = this;
				uni.showLoading({
					title: '正在加载中'
				});
				orderInfo({uuid: that.orderId}).then(res => {
					uni.hideLoading();
					that.$set(that, 'info', res.data.info);
					uni.setNavigationBarTitle({
						title: res.data.info.pay_time>0 ? '支付成功' : '支付失败'
					});
				}).catch(err => {
          console.log(err)
					uni.hideLoading();
				});
			},
			/**
			 * 去首页关闭当前所有页面
			 */
			goIndex: function(e) {
				uni.switchTab({
					url: '/pages/index/index'
				});
			},
			/**
			 * 去订单详情页面
			 */
			goOrderDetails: function(e) {
				let that = this;
				uni.navigateTo({
					url: '/pages/order/detail?uuid=' + that.orderId
				})
			}
		}
	}
</script>

<style lang="scss">
	.coupons {
		.title {
			margin: 30rpx 0 25rpx 0;

			.line {
				width: 70rpx;
				height: 1px;
				background: #DCDCDC;
			}

			.name {
				font-size: 24rpx;
				color: #999;
				margin: 0 10rpx;
			}
		}

		.list {
			padding: 0 20rpx;

			.item {
				margin-bottom: 20rpx;
				box-shadow: 0px 2px 10px 0px rgba(0, 0, 0, 0.06);

				.price {
					width: 236rpx;
					height: 160rpx;
					font-size: 26rpx;
					color: #fff;
					font-weight: 800;

					text {
						font-size: 64rpx;
					}
				}

				.text {
					width: 385rpx;

					.name {
						font-size: #282828;
						font-size: 30rpx;
					}

					.priceMin {
						font-size: 24rpx;
						color: #999;
						margin-top: 10rpx;
					}

					.time {
						font-size: 24rpx;
						color: #999;
						margin-top: 15rpx;
					}
				}
			}

			.open {
				font-size: 24rpx;
				color: #999;
				margin-top: 30rpx;

				.iconfont {
					font-size: 25rpx;
					margin: 5rpx 0 0 10rpx;
				}
			}
		}
	}

	.payment-status {
		background-color: #fff;
		margin: 195rpx 30rpx 0 30rpx;
		border-radius: 10rpx;
		padding: 1rpx 0 28rpx 0;
	}

	.payment-status .icons {
		font-size: 70rpx;
		width: 140rpx;
		height: 140rpx;
		border-radius: 50%;
		color: #fff;
		text-align: center;
		line-height: 140rpx;
		text-shadow: 0px 4px 0px #df1e14;
		border: 6rpx solid #f5f5f5;
		margin: -76rpx auto 0 auto;
		background-color: #999;
	}

	.payment-status .iconfont.fail {
		text-shadow: 0px 4px 0px #7a7a7a;
	}

	.payment-status .status {
		font-size: 32rpx;
		font-weight: bold;
		text-align: center;
		margin: 25rpx 0 37rpx 0;
	}

	.payment-status .wrapper {
		border: 1rpx solid #eee;
		margin: 0 30rpx 47rpx 30rpx;
		padding: 35rpx 0;
		border-left: 0;
		border-right: 0;
	}

	.payment-status .wrapper .item {
		font-size: 28rpx;
		color: #282828;
	}

	.payment-status .wrapper .item~.item {
		margin-top: 20rpx;
	}

	.payment-status .wrapper .item .itemCom {
		color: #666;
	}

	.payment-status .returnBnt {
		width: 630rpx;
		height: 86rpx;
		border-radius: 50rpx;
		color: #fff;
		font-size: 30rpx;
		text-align: center;
		line-height: 86rpx;
		margin: 0 auto 20rpx auto;
	}
  .bg-color{
    background-color: #e93323!important
  }
</style>
