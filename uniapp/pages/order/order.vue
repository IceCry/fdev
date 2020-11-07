<template>
	<view>
		<view class="wrap">
			<view class="u-tabs-box">
				<u-tabs-swiper activeColor="#f45300" ref="tabs" :list="tabs" :current="current" @change="change" :is-scroll="false" swiperWidth="750"></u-tabs-swiper>
			</view>
      
			<swiper class="swiper-box" :current="swiperCurrent" @transition="transition" @animationfinish="animationfinish">
        <!-- 待支付 -->
				<swiper-item class="swiper-item">
					<scroll-view scroll-y style="height: 100%;width: 100%;" @scrolltolower="reachBottom">
						<view class="page-box">
							<view class="order" v-for="(res, index) in orderList[0]" :key="res.id">
								<view class="top">
                  <view class="left">
                    <u-tag text="拍品" size="mini" v-if="res.from_type==1" type="success" />
                    <u-tag text="藏品" size="mini" v-else type="primary" />
                  </view>
									<view class="right">请于{{res.last_pay_time}}前支付</view>
								</view>
								<view class="item" v-for="(item, index) in res.goodsList" :key="index">
									<view class="left"><image :src="item.thumb" mode="aspectFill"></image></view>
									<view class="content">
										<view class="title line1">{{ item.title }}</view>
										<view class="type line1">{{ item.intro }}</view>
										<view class="delivery-time">数量 x{{ item.number }}</view>
									</view>
									<view class="right">
										
									</view>
								</view>
								<view class="total">
									<!--共{{ res.number }}件 -->总价:￥{{ res.total_price }}，保证金抵扣￥{{ res.deduction_price }}，合计
									<text class="total-price text-red">
										￥{{ res.need_pay }}
									</text>
								</view>
								<view class="bottom">
                  <template v-if="res.from_type==1">
                    <view class="more"></view>
                    <template v-if="res.user_phone">
                      <view class="exchange btn" @click="goPage('/pages/order/detail?uuid='+res.uuid)">订单详情</view>
                      <view @click="pay(res.uuid, index)" v-if="res.pay_time==0" class="evaluate btn">立即支付</view>
                    </template>
                    <template v-else>
                    <view class="evaluate btn" @click="goPage('/pages/order/confirm?uuid='+res.uuid)">确认订单并支付</view>
                    </template>
                    
                  </template>
                  <template v-else>
                    <view @click="goPage('/pages/order/detail?uuid='+res.uuid)" class="exchange btn">订单详情</view>
                    <view @click="showCancel(res.uuid, index)" v-if="res.pay_time==0" class="logistics btn">取消订单</view>
                    <view @click="pay(res.uuid, index)" v-if="res.pay_time==0" class="evaluate btn">立即支付</view>
                  </template>
								</view>
							</view>
							<u-loadmore v-if="orderList[0].length>0" :status="loadStatus[0]" bgColor="#f2f2f2"></u-loadmore>
              <!-- 空数据 -->
              <view class="centre flex align-center flex-direction" v-if="orderList[0].length==0">
              	<image :src="baseUrl+'/default/no_order.png'" mode=""></image>
              	<view class="explain">
              		您还没有相关的订单
              		<view class="tips">可以去看看有那些想买的</view>
              	</view>
              	<view @click="goPage('/pages/goods/category')" class="btn">随便逛逛</view>
              </view>
						</view>
					</scroll-view>
				</swiper-item>
        
        <!-- 待发货 -->
				<swiper-item class="swiper-item">
					<scroll-view scroll-y style="height: 100%;width: 100%;" @scrolltolower="reachBottom">
						<view class="page-box">
							<view class="order" v-for="(res, index) in orderList[1]" :key="res.id">
								<view class="top">
						      <view class="left">
						        <u-tag text="拍品" size="mini" v-if="res.from_type==1" type="success" />
						        <u-tag text="藏品" size="mini" v-else type="primary" />
						      </view>
									<view class="right"></view>
								</view>
								<view class="item" v-for="(item, index) in res.goodsList" :key="index">
									<view class="left"><image :src="item.thumb" mode="aspectFill"></image></view>
									<view class="content">
										<view class="title line1">{{ item.title }}</view>
										<view class="type line1">{{ item.intro }}</view>
										<view class="delivery-time">数量 x{{ item.number }}</view>
									</view>
									<view class="right">
										
									</view>
								</view>
								<view class="total">
									共{{ res.number }}件商品 合计:
									<text class="total-price text-red">
										￥{{ res.total_price }}
									</text>
								</view>
								<view class="bottom">
                  <view @click="goPage('/pages/order/detail?uuid='+res.uuid)" class="exchange btn">订单详情</view>
								</view>
							</view>
							<u-loadmore v-if="orderList[1].length>0" :status="loadStatus[1]" bgColor="#f2f2f2"></u-loadmore>
						  <!-- 空数据 -->
						  <view class="centre flex align-center flex-direction" v-if="orderList[1].length==0">
						  	<image :src="baseUrl+'/default/no_order.png'" mode=""></image>
						  	<view class="explain">
						  		您还没有相关的订单
						  		<view class="tips">可以去看看有那些想买的</view>
						  	</view>
						  	<view @click="goPage('/pages/goods/category')" class="btn">随便逛逛</view>
						  </view>
						  
						</view>
					</scroll-view>
				</swiper-item>
        
        <!-- 待收货 -->
				<swiper-item class="swiper-item">
					<scroll-view scroll-y style="height: 100%;width: 100%;">
						<view class="page-box">
							<view class="order" v-for="(res, index) in orderList[2]" :key="res.id">
								<view class="top">
						      <view class="left">
						        <u-tag text="拍品" size="mini" v-if="res.from_type==1" type="success" />
						        <u-tag text="藏品" size="mini" v-else type="primary" />
						      </view>
									<view class="right"></view>
								</view>
								<view class="item" v-for="(item, index) in res.goodsList" :key="index">
									<view class="left"><image :src="item.thumb" mode="aspectFill"></image></view>
									<view class="content">
										<view class="title line1">{{ item.title }}</view>
										<view class="type line1">{{ item.intro }}</view>
										<view class="delivery-time">数量 x{{ item.number }}</view>
									</view>
									<view class="right">
										
									</view>
								</view>
								<view class="total">
									共{{ res.number }}件商品 合计:
									<text class="total-price text-red">
										￥{{ res.total_price }}
									</text>
								</view>
								<view class="bottom">
                  <view @click="goPage('/pages/order/detail?uuid='+res.uuid)" class="exchange btn">订单详情</view>
                  <view @click="goPage('/pages/order/express?uuid='+res.uuid)" v-if="res.express_type=='express' && res.express_id" class="logistics btn">查看物流</view>
                  <view @click="showReceive(res.uuid, index)" v-if="res.order_status==3" class="evaluate btn">确认收货</view>
								</view>
							</view>
							<u-loadmore v-if="orderList[2].length>0" :status="loadStatus[2]" bgColor="#f2f2f2"></u-loadmore>
						  <!-- 空数据 -->
						  <view class="centre flex align-center flex-direction" v-if="orderList[2].length==0">
						  	<image :src="baseUrl+'/default/no_order.png'" mode=""></image>
						  	<view class="explain">
						  		您还没有相关的订单
						  		<view class="tips">可以去看看有那些想买的</view>
						  	</view>
						  	<view @click="goPage('/pages/goods/category')" class="btn">随便逛逛</view>
						  </view>
						</view>
					</scroll-view>
				</swiper-item>
        
        <!-- 已完成 -->
				<swiper-item class="swiper-item">
					<scroll-view scroll-y style="height: 100%;width: 100%;">
						<view class="page-box">
							<view class="order" v-for="(res, index) in orderList[3]" :key="res.id">
								<view class="top">
						      <view class="left">
						        <u-tag text="拍品" size="mini" v-if="res.from_type==1" type="success" />
						        <u-tag text="藏品" size="mini" v-else type="primary" />
						      </view>
									<view class="right"></view>
								</view>
								<view class="item" v-for="(item, index) in res.goodsList" :key="index">
									<view class="left"><image :src="item.thumb" mode="aspectFill"></image></view>
									<view class="content">
										<view class="title line1">{{ item.title }}</view>
										<view class="type line1">{{ item.intro }}</view>
										<view class="delivery-time">数量 x{{ item.number }}</view>
									</view>
									<view class="right">
										
									</view>
								</view>
								<view class="total">
									共{{ res.number }}件商品 合计:
									<text class="total-price text-red">
										￥{{ res.total_price }}
									</text>
								</view>
								<view class="bottom">
                  <view @click="goPage('/pages/order/detail?uuid='+res.uuid)" class="exchange btn">订单详情</view>
                  <view @click="goPage('/pages/order/express?uuid='+res.uuid)" v-if="res.express_type=='express' && res.express_id" class="logistics btn">查看物流</view>
								</view>
							</view>
							<u-loadmore v-if="orderList[3].length>0" :status="loadStatus[3]" bgColor="#f2f2f2"></u-loadmore>
						  <!-- 空数据 -->
						  <view class="centre flex align-center flex-direction" v-if="orderList[3].length==0">
						  	<image :src="baseUrl+'/default/no_order.png'" mode=""></image>
						  	<view class="explain">
						  		您还没有相关的订单
						  		<view class="tips">可以去看看有那些想买的</view>
						  	</view>
						  	<view @click="goPage('/pages/goods/category')" class="btn">随便逛逛</view>
						  </view>
						</view>
					</scroll-view>
				</swiper-item>
        
        <!-- 已取消 -->
        <swiper-item class="swiper-item">
        	<scroll-view scroll-y style="height: 100%;width: 100%;">
        		<view class="page-box">
        			<view class="order" v-for="(res, index) in orderList[4]" :key="res.id">
        				<view class="top">
        		      <view class="left">
        		        <u-tag text="拍品" size="mini" v-if="res.from_type==1" type="success" />
        		        <u-tag text="藏品" size="mini" v-else type="primary" />
        		      </view>
        					<view class="right">取消时间 {{res.cancel_time}}</view>
        				</view>
        				<view class="item" v-for="(item, index) in res.goodsList" :key="index">
        					<view class="left"><image :src="item.thumb" mode="aspectFill"></image></view>
        					<view class="content">
        						<view class="title line1">{{ item.title }}</view>
        						<view class="type line1">{{ item.intro }}</view>
        						<view class="delivery-time">数量 x{{ item.number }}</view>
        					</view>
        					<view class="right"></view>
        				</view>
        				<view class="total">
        					共{{ res.number }}件商品 合计:
        					<text class="total-price text-red">
        						￥{{ res.total_price }}
        					</text>
        				</view>
        				<view class="bottom">
                  <view @click="goPage('/pages/order/detail?uuid='+res.uuid)" class="exchange btn">订单详情</view>
        				</view>
        			</view>
        			<u-loadmore v-if="orderList[4].length>0" :status="loadStatus[4]" bgColor="#f2f2f2"></u-loadmore>
        		  <!-- 空数据 -->
        		  <view class="centre flex align-center flex-direction" v-if="orderList[4].length==0">
        		  	<image :src="baseUrl+'/default/no_order.png'" mode=""></image>
        		  	<view class="explain">
        		  		您还没有相关的订单
        		  		<view class="tips">可以去看看有那些想买的</view>
        		  	</view>
        		  	<view @click="goPage('/pages/goods/category')" class="btn">随便逛逛</view>
        		  </view>
        		</view>
        	</scroll-view>
        </swiper-item>
        
			</swiper>
		</view>
    
    <!-- 取消订单确认 -->
    <u-modal v-model="cancelModal" :show-cancel-button="true" cancel-text="暂不取消" confirm-text="确定取消" content="确定要取消此订单吗？" @confirm="cancel"></u-modal>
    <u-modal v-model="receivedModal" :show-cancel-button="true" cancel-text="取消" confirm-text="确定收货" content="确定要标记为已收货吗？" @confirm="received"></u-modal>
    
	</view>
</template>

<script>
import { getOrderData, orderCancel, orderReceived, orderPay } from '@/api/order.js';
import { HTTP_REQUEST_URL } from '@/config/app.js';
import Util from '@/utils/util.js';
import { mapGetters } from "vuex";
export default {
  computed: mapGetters(['isLogin', 'uid']),
	data() {
		return {
      baseUrl: HTTP_REQUEST_URL,
      status: 1,//1待付款 2待发货 3待收货 4已完成
      
			orderList: [[], [], [], [], []],
			tabs: [
				{
          status: 1,
					name: '待付款',
          page: 1,
          limit: 10,
          loadStatus: 'loadmore'
				},
				{
          status: 2,
					name: '待发货',
          page: 1,
          limit: 10,
          loadStatus: 'loadmore'
				},
				{
          status: 3,
					name: '待收货',
          page: 1,
          limit: 10,
          loadStatus: 'loadmore'
				},
				{
          status: 4,
					name: '已完成',
          page: 1,
          limit: 10,
          loadStatus: 'loadmore'
				},
				{
          status: -1,
					name: '已取消',
          page: 1,
          limit: 10,
          loadStatus: 'loadmore'
				}
			],
			current: 0, //当前tab
			swiperCurrent: 0,
			tabsHeight: 0,
			dx: 0,
      cancelModal: false,
      cancelUuid: '',
      cancelIndex: '',
      receivedModal: false,
      receivedUuid: '',
      receivedIndex: ''
		};
	},
	onLoad(option) {
    //判断当前tab
    var status = option.status || 1;
    this.status = status;
    this.tabs.forEach((val, index)=>{
      if(val.status==status){
        this.current = index;
      }
    });
    
    if(this.isLogin === false){
      return this.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
    }
    
    //获取当前对应的数据
    //this.getData();
    this.change(this.current);
	},
	computed: {
		// 价格小数
		priceDecimal() {
			return val => {
				if (val !== parseInt(val)) return val.slice(-2);
				else return '00';
			};
		},
		// 价格整数
		priceInt() {
			return val => {
				if (val !== parseInt(val)) return val.split('.')[0];
				else return val;
			};
		}
	},
	methods: {
    showCancel(uuid, index){
      this.cancelModal = true;
      this.cancelUuid = uuid;
      this.cancelIndex = index;
    },
    showReceive(uuid, index){
      this.receivedModal = true;
      this.receivedUuid = uuid;
      this.receivedIndex = index+'';
    },
    //确认收货
    received(){
      var that = this;
      orderReceived({uuid: this.receivedUuid}).then(res=>{
        //已收货列表追加
        that.tabs[that.current].page = 1;
        that.tabs[that.current].loadStatus='loadmore';
        that.$set(that.orderList, that.current, []);
        that.getData();
        /* //删除列表数据
        that.orderList[that.current].splice(0, 1);
        //重新渲染
        that.$set(that.orderList, that.current, that.orderList[that.current]); */
        return that.$util.Tips({ title: res.msg});
      }).catch(err => {
        return that.$util.Tips({title: err});
      });
    },
    //取消订单
    cancel(){
      var that = this;
      orderCancel({uuid: this.cancelUuid}).then(res=>{
        //删除列表数据
        that.orderList[that.current].splice(that.cancelIndex, 1);
        //重新渲染
        that.$set(that.orderList, that.current, that.orderList[that.current]);
        return that.$util.Tips({ title: res.msg});
      }).catch(err => {
        return that.$util.Tips({title: err});
      });
    },
    //重新支付订单
    pay(uuid, index){
      let that = this;
      uni.showLoading({
        title: '订单支付中'
      });
      orderPay({uuid: uuid}).then(res => {
        console.log('order_pay', res)
        let status = res.data.status,
          orderId = res.data.result.uuid,
          jsConfig = res.data.result.jsConfig,
          goPages = '/pages/order/pay_result?order_id=' + orderId + '&msg=' + res.msg;
        switch (status) {
          case 'ORDER_EXIST':
          case 'PAID_ORDER':
          case 'PAY_ERROR':
            uni.hideLoading();
            return that.$util.Tips({
              title: res.msg
            }, {
              tab: 5,
              url: goPages
            });
            break;
          case 'SUCCESS':
            uni.hideLoading();
            return that.$util.Tips({
              title: res.msg,
              icon: 'success'
            }, {
              tab: 5,
              url: goPages
            });
            break;
          case 'WECHAT_PAY':
            uni.requestPayment({
              timeStamp: jsConfig.timeStamp,
              nonceStr: jsConfig.nonceStr,
              package: jsConfig.package,
              signType: jsConfig.signType,
              paySign: jsConfig.paySign,
              success: function(res) {
                uni.hideLoading();
                //将此订单标记为已支付
                that.orderList[that.current][index].order_status = 2;
                return that.$util.Tips({
                  title: '支付成功',
                  icon: 'success'
                }, {
                  tab: 5,
                  url: goPages
                });
              },
              fail: function(e) {
                console.log(e)
                uni.hideLoading();
                return that.$util.Tips({
                  title: '取消支付'
                }, {
                  tab: 5,
                  url: goPages + '&status=0'
                });
              },
              complete: function(e) {
                uni.hideLoading();
                //关闭当前页面跳转至订单状态
                if (res.errMsg == 'requestPayment:cancel') return that.$util.Tips({
                  title: '取消支付'
                }, {
                  tab: 5,
                  url: goPages + '&status=0'
                });
              },
            })
            break;
        }
      }).catch(err => {
        uni.hideLoading();
        return that.$util.Tips({
          title: err
        });
      });
    },
    getData(){
      var that = this;
      if(that.tabs[that.current].loadStatus=='nomore'){
        return this.$util.Tips({
          title: "没有更多了"
        });
      }
      uni.showLoading();
      getOrderData({order_status: this.status, page: this.tabs[this.current].page, limit: this.tabs[this.current].limit}).then(res=>{
        let list = res.data.lists;
        that.orderList[that.current] = that.orderList[that.current].concat(list);
        let loadend = list.length < that.tabs[that.current].limit;
        if(loadend){
          that.tabs[that.current].loadStatus = 'nomore';
        }else{
          that.tabs[that.current].page++;
        }
        uni.hideLoading();
      }).catch(err => {
        if(err.msg == '未登陆'){
          return that.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
        }else{
          return that.$util.Tips({title: err});
        }
      });
    },
    goPage(url, type='navigate'){
      Util.goPage(url, type);
    },
		reachBottom() {
      this.getData();
		},
		// tab栏切换
		change(index) {
			this.swiperCurrent = index;
      this.current = index;
      this.status = this.tabs[index].status;
			this.getData();
		},
		transition({ detail: { dx } }) {
			this.$refs.tabs.setDx(dx);
		},
		animationfinish({ detail: { current } }) {
			this.$refs.tabs.setFinishCurrent(current);
			this.swiperCurrent = current;
			this.current = current;
		}
	}
};
</script>

<style>
/* #ifndef H5 */
page {
	height: 100%;
	background-color: #f2f2f2;
}
/* #endif */
</style>

<style lang="scss" scoped>
.order {
	width: 710rpx;
	background-color: #ffffff;
	margin: 20rpx auto;
	border-radius: 20rpx;
	box-sizing: border-box;
	padding: 20rpx;
	font-size: 28rpx;
	.top {
		display: flex;
		justify-content: space-between;
		.left {
			display: flex;
			align-items: center;
			.store {
				margin: 0 10rpx;
				font-size: 32rpx;
				font-weight: bold;
			}
		}
		.right {
			color: $u-type-warning-dark;
		}
	}
	.item {
		display: flex;
		margin: 20rpx 0 0;
		.left {
			margin-right: 20rpx;
			image {
				width: 160rpx;
				height: 160rpx;
				border-radius: 10rpx;
			}
		}
		.content {
      width: 490rpx;
			.title {
				font-size: 28rpx;
				line-height: 50rpx;
			}
			.type {
				margin: 10rpx 0;
				font-size: 24rpx;
				color: $u-tips-color;
			}
			.delivery-time {
				color: $u-tips-color;
				font-size: 24rpx;
			}
		}
		.right {
			margin-left: 10rpx;
			padding-top: 20rpx;
			text-align: right;
			.decimal {
				font-size: 24rpx;
				margin-top: 4rpx;
			}
			.number {
				color: $u-tips-color;
				font-size: 24rpx;
			}
		}
	}
	.total {
		margin-top: 20rpx;
		text-align: right;
		font-size: 24rpx;
		.total-price {
			font-size: 32rpx;
		}
	}
	.bottom {
		display: flex;
		margin-top: 40rpx;
		padding: 0 10rpx;
		justify-content: space-between;
		align-items: center;
		.btn {
			line-height: 52rpx;
			width: 160rpx;
			border-radius: 26rpx;
			border: 2rpx solid $u-border-color;
			font-size: 26rpx;
			text-align: center;
			color: $u-type-info-dark;
		}
		.evaluate {
			color: $u-type-error;
			border-color: $u-type-error;
		}
    .cancel{
			color: $u-type-error;
			border-color: $u-type-error;
    }
	}
}
.centre {
	text-align: center;
	margin: 200rpx auto;
	font-size: 32rpx;
	image {
		width: 164rpx;
		height: 164rpx;
		border-radius: 50%;
		margin-bottom: 20rpx;
	}
	.tips {
		font-size: 24rpx;
		color: #999999;
		margin-top: 20rpx;
	}
	.btn {
		margin: 80rpx auto;
		width: 200rpx;
		border-radius: 32rpx;
		line-height: 64rpx;
		color: #ffffff;
		font-size: 26rpx;
		background: linear-gradient(270deg, rgba(249, 116, 90, 1) 0%, rgba(255, 158, 1, 1) 100%);
	}
}
.wrap {
	display: flex;
	flex-direction: column;
	height: calc(100vh - var(--window-top));
	width: 100%;
}
.swiper-box {
	flex: 1;
}
.swiper-item {
	height: 100%;
}
.order .bottom .btn{
  width: auto !important;
  padding: 0 20rpx;
  margin-left: 16rpx;
}
.order .bottom{
  justify-content: flex-end;
}
</style>
