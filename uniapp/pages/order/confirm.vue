<template>
	<view>
		<view class='order-submission'>
			<view class="allAddress" :style="'padding-top:10rpx'">
				<view class='address flex align-center justify-between' @click='onAddress'>
					<view class='addressCon' v-if="addressInfo.name">
						<view class='name'>{{addressInfo.name}}
							<text class='phone'>{{addressInfo.phone}}</text>
						</view>
						<view>
            <text class='default text-red' v-if="addressInfo.is_default">[默认]</text>{{addressInfo.province}}{{addressInfo.city}}{{addressInfo.district}}{{addressInfo.detail}}</view>
					</view>
					<view class='addressCon' v-else>
						<view class='setaddress'>设置收货地址</view>
					</view>
          <u-icon name="arrow-right"></u-icon>
				</view>
				
				<view class='line'>
					<image src='/static/line.jpg'></image>
				</view>
			</view>
      
			<orderGoods :cartInfo="orderInfo.snap.products"></orderGoods>
      
			<view class='wrapper'>
				
				<view class='item flex justify-between'>
					<view>保证金抵扣</view>
					<view class='discount flex align-center text-sm'>
						<view> {{useDeposit ? "剩余保证金":"当前保证金"}}
							<text class='num text-red'>{{ priceGroup.left_deposit }}</text>
						</view>
            
            <u-checkbox-group>
              <u-checkbox class="u-checkbox" @change="changeDeposit" shape="circle" active-color="red" v-model="useDeposit"></u-checkbox>
            </u-checkbox-group>
            
					</view>
				</view>
				<view class='item flex justify-between'>
					<view>快递费用</view>
					<view class='discount' v-if="priceGroup.postage_price>0">+￥{{priceGroup.postage_price}}</view>
					<view class='discount' v-else>免运费</view>
				</view>
				<view class='item' v-if="textareaStatus">
					<view>备注信息</view>
					<textarea placeholder-class='placeholder' @input='bindHideKeyboard' value="" name="mark"
					 placeholder='请添加备注（150字以内）' maxlength="150"></textarea>
				</view>
			</view>
			<view class='wrapper'>
				<view class='item'>
					<view>支付方式</view>
					<view class='list'>
						<view class='payItem flex align-center' :class='active==index ?"on":""' @click='payItem(index)' v-for="(item,index) in cartArr" :key='index' v-if="item.payStatus==1">
							<view class='name flex justify-center align-center'>
                <u-icon :name="item.icon" :color="item.color" size="36" custom-prefix="custom-icon"></u-icon> <text class="u-padding-left-10">{{item.name}}</text>
							</view>
							<view class='tip'>{{item.title}}</view>
						</view>
					</view>
				</view>
			</view>
			<view class='moneyList'>
				<view class='item flex justify-between'>
					<view>商品总价：</view>
					<view class='money'>￥{{priceGroup.product_price}}</view>
				</view>
				<view class='item flex justify-between' v-if="orderInfo.from_type==1">
					<view>买家佣金：</view>
					<view class='money'>￥{{priceGroup.commission_price}}</view>
				</view>
				<view class='item flex justify-between' v-if="priceGroup.postage_price>0">
					<view>运费：</view>
					<view class='money'>+￥{{priceGroup.postage_price}}</view>
				</view>
				<view class='item flex justify-between' v-if="priceGroup.deduction_price>0">
					<view>保证金抵扣：</view>
					<view class='money'>-￥{{priceGroup.deduction_price}}</view>
				</view>
			</view>
			<view style='height:120rpx;'></view>
			<view class='footer flex justify-between align-center'>
				<view>合计:
					<text class='text-red text-bold'>￥{{priceGroup.need_price}}</text>
				</view>
				<view class='settlement' style='z-index:100' @click="subOrder">立即结算</view>
			</view>
		</view>
		<addressWindow ref="addressWindow" @changeTextareaStatus="changeTextareaStatus" :address='address' :pagesUrl="pagesUrl" @OnChangeAddress="OnChangeAddress" @changeClose="changeClose"></addressWindow>
	</view>
</template>
<script>
  import { mapGetters } from "vuex";
	import addressWindow from '@/components/addressWindow';
	import orderGoods from '@/components/orderGoods';
  import Util from '@/utils/util.js';
  import { orderConfirm, orderComputed, orderCreate } from '@/api/order.js';
  import { getDefaultAddress, getAddressDetail } from '@/api/user.js';
  import { openPaySubscribe } from '@/utils/SubscribeMessage.js';
	export default {
		components: {
			orderGoods,
			addressWindow
		},
		computed: mapGetters(['isLogin']),
		data() {
			return {
        //通过uuid或cartId区分订单类型
        uuid: '', //拍品订单uuid
        cartIds: '', //普通商品购物车id
				textareaStatus: true,
        //支付方式
				cartArr: [{
						"name": "微信支付",
						"icon": "weixinzhifu",
						value: 'weixin',
						title: '微信快捷支付',
						payStatus: 1,
            color: '#04BE02'
					}
					/* ,{
						"name": "线下支付",
						"icon": "xianxiazhifu",
						value: 'offline',
						title: '线下支付',
						payStatus: 1,
            color: '#999999'
					} */
				],
				payType: 'weixin', //支付方式
				active: 0, //支付方式切换
				address: {
					address: false
				}, //地址组件
				addressInfo: {}, //地址信息
				addressId: 0, //地址id
				userInfo: {}, //用户信息
				mark: '', //备注信息
				useDeposit: false, //是否使用积分
				orderInfo: [],
				priceGroup: {},
        pagesUrl: '', //返回页
			};
		},
    onLoad(options) {
      if(!this.isLogin){
        //跳转到授权
        return this.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
      }else{
        this.uuid = options.uuid || '';
        this.cartIds = options.cartIds || '';
        this.addressId = options.addressId || 0;
        this.pagesUrl = '/pages/my/address/address?uuid='+this.uuid+"&cartIds="+this.cartIds
        this.getAddressInfo();
        this.getCartInfo();
				//调用子页面方法授权后执行获取地址列表
				this.$nextTick(function() {
					this.$refs.addressWindow.getAddressList();
				});
      }
    },
		methods: {
      //计算价格
      computedPrice(){
        var that = this;
        orderComputed({
          uuid: this.uuid,
          cartIds: this.cartIds,
          addressId: this.addressId,
          useDeposit: this.useDeposit ? 1 : 0,
          payType: this.payType
        }).then(res => {
          that.priceGroup = res.data.priceGroup;
        })
      },
      //确定保证金
      changeDeposit(){
        this.computedPrice();
      },
      //切换支付方式
      payItem(e){
        let that = this;
        let active = e;
        that.active = active;
        that.payType = that.cartArr[active].value;
        that.computedPrice();
      },
      //获取订单信息
      getCartInfo(){
        let that = this;
        orderConfirm({uuid: this.uuid, cartIds: this.cartIds}).then(res => {
          console.log(res,'res')
          that.$set(that, 'userInfo', res.data.userInfo);
          that.$set(that, 'orderInfo', res.data.orderInfo);
          that.$set(that, 'priceGroup', res.data.priceGroup);
        }).catch(err => {
          console.log(err,'err')
        });
      },
			// 关闭地址弹窗
			changeClose: function() {
				this.$set(this.address, 'address', false);
			},
			/**
			 * 选择地址后改变事件
			 * @param object e
			 */
			OnChangeAddress: function(e) {
				this.textareaStatus = true;
				this.addressId = e;
				this.address.address = false;
				this.getAddressInfo();
				//this.computedPrice();
			},
			onAddress() {
				let that = this;
				that.textareaStatus = false;
				that.address.address = true;
			},
			bindHideKeyboard: function(e) {
				this.mark = e.detail.value;
			},
      //显示隐藏备注信息
			changeTextareaStatus() {
				this.textareaStatus = true;
				this.status = 0;
			},
			/*
			 * 获取默认收货地址
			 */
			getAddressInfo() {
				let that = this;
        if (that.addressId) {
          getAddressDetail(that.addressId).then(res => {
            let info = res.data.info;
          	info.is_default = parseInt(info.is_default);
          	that.addressInfo = info || {};
          	that.addressId = info.id || 0;
          	that.address.addressId = info.id || 0;
          })
        }else{
          getDefaultAddress().then(res => {
            let info = res.data.info;
            console.log(info)
          	info.is_default = parseInt(info.is_default);
          	that.addressInfo = info || {};
          	that.addressId = info.id || 0;
          	that.address.addressId = info.id || 0;
          })
        }
			},
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      },
      //提交订单信息
      subOrder(){
        var that = this, data = {};
        if (!that.payType) return that.$util.Tips({
          title: '请选择支付方式'
        });
        if (!that.addressId) return that.$util.Tips({
          title: '请选择收货地址'
        });
        data = {
          uuid: that.uuid,
          cartIds: that.cartIds,
          addressId: that.addressId,
          payType: that.payType,
          useDeposit: that.useDeposit,
          mark: that.mark
        };
        //todo 开启通知
        openPaySubscribe().then(() => {
          that.payment(data);
        });
      },
      //支付
      payment(data){
        let that = this;
        uni.showLoading({
          title: '订单支付中'
        });
        orderCreate(data).then(res => {
          console.log('order_create', res)
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
                tab: 4,
                url: goPages
              });
              break;
            case 'SUCCESS':
              uni.hideLoading();
              return that.$util.Tips({
                title: res.msg,
                icon: 'success'
              }, {
                tab: 4,
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
                  return that.$util.Tips({
                    title: '支付成功',
                    icon: 'success'
                  }, {
                    tab: 4,
                    url: goPages
                  });
                },
                fail: function(e) {
                  console.log(e)
                  uni.hideLoading();
                  return that.$util.Tips({
                    title: '取消支付'
                  }, {
                    tab: 4,
                    url: goPages + '&status=0'
                  });
                },
                complete: function(e) {
                  uni.hideLoading();
                  //关闭当前页面跳转至订单状态
                  if (res.errMsg == 'requestPayment:cancel') return that.$util.Tips({
                    title: '取消支付'
                  }, {
                    tab: 4,
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
      }
		}
	}
</script>

<style lang="scss" scoped>
	.order-submission .line {
		width: 100%;
		height: 3rpx;
	}
	.order-submission .line image {
		width: 100%;
		height: 100%;
		display: block;
	}
	.order-submission .address {
		padding: 28rpx 30rpx;
		background-color: #fff;
		box-sizing: border-box;
	}
	.order-submission .address .addressCon {
		width: 610rpx;
		font-size: 26rpx;
		color: #666;
	}
	.order-submission .address .addressCon .name {
		font-size: 30rpx;
		color: #282828;
		font-weight: bold;
		margin-bottom: 10rpx;
	}
	.order-submission .address .addressCon .name .phone {
		margin-left: 50rpx;
	}
	.order-submission .address .addressCon .default {
		margin-right: 12rpx;
	}
	.order-submission .address .addressCon .setaddress {
		color: #333;
		font-size: 28rpx;
	}
	.order-submission .address .iconfont {
		font-size: 35rpx;
		color: #707070;
	}
	.order-submission .allAddress {
		width: 100%;
		background: linear-gradient(to bottom, #e93323 0%,#f5f5f5 100%);
		// background-image: linear-gradient(to bottom, #e93323 0%, #f5f5f5 100%);
		// background-image: -webkit-linear-gradient(to bottom, #e93323 0%, #f5f5f5 100%);
		// background-image: -moz-linear-gradient(to bottom, #e93323 0%, #f5f5f5 100%);
		padding-top: 100rpx;
	}
	.order-submission .allAddress .nav {
		width: 710rpx;
		margin: 0 auto;
	}
	.order-submission .allAddress .nav .item {
		width: 355rpx;
	}

	.order-submission .allAddress .nav .item.on {
		position: relative;
		width: 250rpx;
	}

	.order-submission .allAddress .nav .item.on::before {
		position: absolute;
		bottom: 0;
		content: "快递配送";
		font-size: 28rpx;
		display: block;
		height: 0;
		width: 336rpx;
		border-width: 0 20rpx 80rpx 0;
		border-style: none solid solid;
		border-color: transparent transparent #fff;
		z-index: 2;
		border-radius: 7rpx 30rpx 0 0;
		text-align: center;
		line-height: 80rpx;
	}

	.order-submission .allAddress .nav .item:nth-of-type(2).on::before {
		content: "到店自提";
		border-width: 0 0 80rpx 20rpx;
		border-radius: 30rpx 7rpx 0 0;
	}

	.order-submission .allAddress .nav .item.on2 {
		position: relative;
	}

	.order-submission .allAddress .nav .item.on2::before {
		position: absolute;
		bottom: 0;
		content: "到店自提";
		font-size: 28rpx;
		display: block;
		height: 0;
		width: 400rpx;
		border-width: 0 0 60rpx 60rpx;
		border-style: none solid solid;
		border-color: transparent transparent #f7c1bd;
		border-radius: 40rpx 6rpx 0 0;
		text-align: center;
		line-height: 60rpx;
	}

	.order-submission .allAddress .nav .item:nth-of-type(1).on2::before {
		content: "快递配送";
		border-width: 0 60rpx 60rpx 0;
		border-radius: 6rpx 40rpx 0 0;
	}

	.order-submission .allAddress .address {
		width: 710rpx;
		height: 150rpx;
		margin: 0 auto;
	}

	.order-submission .allAddress .line {
		width: 710rpx;
		margin: 0 auto;
	}

	.order-submission .wrapper .item .discount .placeholder {
		color: #ccc;
	}

	.order-submission .wrapper {
		background-color: #fff;
		margin-top: 13rpx;
	}

	.order-submission .wrapper .item {
		padding: 27rpx 30rpx;
		font-size: 30rpx;
		color: #282828;
		border-bottom: 1px solid #f0f0f0;
	}

	.order-submission .wrapper .item .discount {
		font-size: 30rpx;
		color: #999;
	}

	.order-submission .wrapper .item .discount .iconfont {
		color: #515151;
		font-size: 30rpx;
		margin-left: 15rpx;
	}

	.order-submission .wrapper .item .discount .num {
		font-size: 32rpx;
		margin-right: 20rpx;
	}

	.order-submission .wrapper .item .shipping {
		font-size: 30rpx;
		color: #999;
		position: relative;
		padding-right: 58rpx;
	}

	.order-submission .wrapper .item .shipping .iconfont {
		font-size: 35rpx;
		color: #707070;
		position: absolute;
		right: 0;
		top: 50%;
		transform: translateY(-50%);
		margin-left: 30rpx;
	}

	.order-submission .wrapper .item textarea {
		background-color: #f9f9f9;
		width: 690rpx;
		height: 140rpx;
		border-radius: 3rpx;
		margin-top: 30rpx;
		padding: 25rpx 28rpx;
		box-sizing: border-box;
	}

	.order-submission .wrapper .item .placeholder {
		color: #ccc;
	}

	.order-submission .wrapper .item .list {
		margin-top: 35rpx;
	}

	.order-submission .wrapper .item .list .payItem {
		border: 1px solid #eee;
		border-radius: 6rpx;
		height: 86rpx;
		width: 100%;
		box-sizing: border-box;
		margin-top: 20rpx;
		font-size: 28rpx;
		color: #282828;
	}

	.order-submission .wrapper .item .list .payItem.on {
		border-color: #fc5445;
		color: #e93323;
	}

	.order-submission .wrapper .item .list .payItem .name {
		width: 50%;
		text-align: center;
		border-right: 1px solid #eee;
	}

	.order-submission .wrapper .item .list .payItem .name .iconfont {
		width: 44rpx;
		height: 44rpx;
		border-radius: 50%;
		text-align: center;
		line-height: 44rpx;
		background-color: #fe960f;
		color: #fff;
		font-size: 30rpx;
		margin-right: 15rpx;
	}

	.order-submission .wrapper .item .list .payItem .name .iconfont.icon-weixin2 {
		background-color: #41b035;
	}

	.order-submission .wrapper .item .list .payItem .tip {
		width: 49%;
		text-align: center;
		font-size: 26rpx;
		color: #aaa;
	}

	.order-submission .moneyList {
		margin-top: 12rpx;
		background-color: #fff;
		padding: 30rpx;
	}

	.order-submission .moneyList .item {
		font-size: 28rpx;
		color: #282828;
	}

	.order-submission .moneyList .item~.item {
		margin-top: 20rpx;
	}

	.order-submission .moneyList .item .money {
		color: #868686;
	}

	.order-submission .footer {
		width: 100%;
		height: 100rpx;
		background-color: #fff;
		padding: 0 30rpx;
		font-size: 28rpx;
		color: #333;
		box-sizing: border-box;
		position: fixed;
		bottom: 0;
		left: 0;
	}

	.order-submission .footer .settlement {
		font-size: 30rpx;
		color: #fff;
		width: 240rpx;
		height: 70rpx;
		background-color: #e93323;
		border-radius: 50rpx;
		text-align: center;
		line-height: 70rpx;
	}

	.footer .transparent {
		opacity: 0
	}
</style>
