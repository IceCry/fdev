<template>
	<view class="container">
    
    <view class="content">
      <view class="title">
        <text>买家保证金缴纳</text>
      </view>
      <view class="price flex flex-direction align-center">
          <view class="sum">
            ￥
            <text class="num">{{info.deposit|formatMoney}}</text>
          </view>
          <view class="type">当前可用保证金</view>
      </view>
      <view class="tips text-center">
        <view class="circle-left"></view>
        <view class="circle-right"></view>
        <view>为保证拍卖正常进行请缴纳保证金</view>
      </view>
    </view>
    
    <!-- 默认可选金额 -->
    <view class="choose flex flex-wrap justify-around">
      <view v-if="!special_id" class="price-item flex align-center justify-center" :class="(nowType=='seckill' && nowIndex==index)?'on':''" v-for="(item, index) in info.seckill_group" :key="index" @click="choosePrice(item.value, index, 'seckill')">
        <view class="grid-text">{{item.name}}</view>
      </view>
      <view v-if="special_id || (!uuid && !special_id)" class="price-item flex align-center justify-center" :class="(nowType=='sync' && nowIndex==index)?'on':''" v-for="(item, index) in info.sync_group" :key="index" @click="choosePrice(item.value, index, 'sync')">
        <view class="grid-text">{{item.name}}</view>
      </view>
    </view>
      
    <view class="pay-box">
      <view class="rule u-line-1" @click="showRule=true">
        <text class="text-gray">我已阅读并同意<text class="text-red">《保证金规则》</text></text>
      </view>
      
      <view class="pay-type">
        <view class="pay-item flex align-center justify-between" :class="payType=='weixin'?'on':''" @click="chPayType('weixin')">
          <view>微信支付</view>
          <view><text class="mr10 text-gray text-sm">小额支付即时到账</text><u-icon slot="right-icon" size="34" color="#04BE02" custom-prefix="custom-icon" name="weixinzhifu"></u-icon></view>
        </view>
        <view class="pay-item flex align-center justify-between" :class="payType=='offline'?'on':''" @click="chPayType('offline')">
          <view>线下转账</view>
          <view><text class="mr10 text-gray text-sm">大额需提交转账信息,审核后到账</text><u-icon slot="right-icon" size="34" color="#666666" custom-prefix="custom-icon" name="yinhang"></u-icon></view>
        </view>
      </view>
      
      <!-- <u-cell-group>
      	<u-cell-item :border-bottom="false" title="微信支付" value="小额支付即时到账" :arrow="false">
      		<u-icon slot="right-icon" size="34" color="#04BE02" custom-prefix="custom-icon" name="weixinzhifu"></u-icon>
      		<u-field slot="value"></u-field>
      	</u-cell-item>
        <u-cell-item :border-bottom="false" title="线下转账" value="大额需提交转账信息,审核后到账" :arrow="false">
        	<u-icon slot="right-icon" size="34" color="#666666" custom-prefix="custom-icon" name="yinhang"></u-icon>
        	<u-field slot="value"></u-field>
        </u-cell-item>
      </u-cell-group> -->
      
      <view class="pay">
        <u-button v-if="payType=='weixin'" @click="doPay" type="success">立即缴纳<text v-if="deposit_price>0">{{deposit_price|formatMoney}}元</text></u-button>
        <u-button v-if="payType=='offline'" @click="doPay" type="success">线下银行卡转账</u-button>
      </view>
      
      <view class="rollback text-gray" v-if="withdrawBtn" @click="rollback">申请提现</view>
    </view>
    
    <!-- 申请退款 -->
    <u-modal v-model="showModal" :show-cancel-button="true" confirm-text="确认申请" title="重要提示" content="确定要提取当前可用保证金吗？" @confirm="apply"></u-modal>
    
    <!-- <u-keyboard
    	default=""
    	ref="uKeyboard" 
    	mode="number" 
    	:mask="true" 
    	:mask-close-able="false"
    	:dot-enabled="false" 
    	v-model="show"
    	:safe-area-inset-bottom="true"
    	:tooltip="false"
    	@change="onChange"
    	@backspace="onBackspace">
    	<view>
    		<view class="u-text-center u-padding-20 money">
    			<text><text class="u-font-34">￥</text>{{deposit_price || 0}}</text>
    			<view class="u-padding-10 close" data-flag="false" @click="showPop(false)">
    				<u-icon name="close" color="#333333" size="28"></u-icon>
    			</view>
    		</view>
    		<view @click="doPay" class="u-text-center tips">确定缴纳</view>
    	</view>
    </u-keyboard> -->
    
    <!-- 参拍须知 -->
    <u-popup v-model="showRule" mode="center" closeable custom-style="overflow:auto;" length="90%" zoom border-radius="10">
      <scroll-view class="pop-content" scroll-y="true">
        <jyf-parser :html="info.content" ref="rule" :tag-style="tagStyle"></jyf-parser>
      </scroll-view>
    </u-popup>
	</view>
</template>

<script>
  import { getDepositInfo, postDeposit, withdrawDeposit } from "@/api/auction.js";
  import Util from '@/utils/util.js';
  import parser from "@/components/jyf-parser/jyf-parser";
  import { openWithdrawSubscribe } from '@/utils/SubscribeMessage.js';
	export default {
    components: {
      "jyf-parser": parser
    },
    filters:{
      formatMoney: function(s, type) {
      	if (/[^0-9\.]/.test(s))
      		return "0";
      	if (s == null || s == "")
      		return "0";
      	s = s.toString().replace(/^(\d*)$/, "$1.");
      	s = (s + "00").replace(/(\d*\.\d\d)\d*/, "$1");
      	s = s.replace(".", ",");
      	var re = /(\d)(\d{3},)/;
      	while (re.test(s))
      		s = s.replace(re, "$1,$2");
      	s = s.replace(/,(\d\d)$/, ".$1");
      	if (true || type == 0) {// 不带小数位
      		var a = s.split(".");
      		if (a[1] == "00") {
      			s = a[0];
      		}
      	}
      	return s;
      },
    },
		data() {
			return {
        uuid: '', //拍品uuid
        price: 0, //默认充值金额
        show: false,
        deposit_price: '',
				info: [],
        showModal: false,
        showDeposit: false,
        payment: [],
        showRule: false,
        special_id: 0,
        withdrawBtn: true,
        
        nowIndex: -1, //当前序号
        nowType: 'seckill', //当前类型
        payType: 'weixin', //当前支付方式
			};
		},
    onShow() {
      this.getData();
    },
    onLoad(option) {
      this.uuid = option.uuid || '';
      this.price = option.price || 0;
      //this.deposit_price = this.price;
      this.special_id = option.special_id || 0;
      //this.getData();
    },
    methods:{
      doPay(){
        var that = this;
        if(!this.deposit_price){
          return that.$util.Tips({title: "请选择要充值的金额"});
        }
        if(parseInt(this.deposit_price) < parseInt(this.info.min)){
          return that.$util.Tips({title: "最低充值金额为￥"+this.info.min});
        }
        
        //判断是否为线下转账
        if(this.payType=='offline'){
          uni.showLoading({
            title: 'none'
          })
          return that.$util.Tips({title:'跳转中'}, '/pages/system/deposit/offline?price='+this.deposit_price);
        }
        
        postDeposit({price:this.deposit_price, special_id: this.special_id, balance: 0}).then(res=>{
          console.log(res)
          let payment = res.data.payment;
          
          uni.requestPayment({
            provider: 'wxpay',
            timeStamp: payment.timeStamp,
            nonceStr: payment.nonceStr,
            package: payment.package,
            signType: 'MD5',
            paySign: payment.paySign,
            success: function (res) {
              //如果存在uuid则跳回拍品详情页
              console.log('success:' + JSON.stringify(res));
              if(that.uuid){
                if(that.special_id>0){
                  return that.$util.Tips({title:'充值成功'}, '/pages/auction/special?uuid='+that.uuid);
                }else{
                  return that.$util.Tips({title:'充值成功'}, '/pages/auction/detail?uuid='+that.uuid);
                }
              }else{
                return that.$util.Tips({title:'充值成功'}, {tab:4, url:'/pages/system/deposit/list'});
              }
            },
            fail: function (err) {
              console.log('fail:' + JSON.stringify(err));
              return that.$util.Tips({title:'支付失败'});
            }
          });
        }).catch(err => {
          that.show = false;
          if(err == '未登陆'){
            return that.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
          }else if(err == '请先完成实名认证'){
            return that.$util.Tips({title:'请先完成实名认证'}, '/pages/my/verify');
          }else{
            return that.$util.Tips({title: err});
          }
        });
      },
      rollback(){
        this.showModal = true;
      },
      valChange(e){
        console.log(e.value)
      },
			onChange(val){
        if(this.special_id>0){
          return this.$util.Tips({title: '专场保证金金额禁止修改'});;
        }
        if(this.deposit_price!=0){
          this.deposit_price += ''+val;
        }else{
          this.deposit_price += val;
        }
			},
			onBackspace(e){
        if(this.special_id>0){
          return this.$util.Tips({title: '专场保证金金额禁止修改'});;
        }
        let price = this.deposit_price+'';
        if(this.deposit_price<=this.info.next_price) return;
				if(price.length>0){
					this.deposit_price = price.substring(0, price.length-1);
				}
			},
      showPop(flag = true){
        this.show = flag;
      },
      finish(){
      	console.log(11111)
      },
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      },
      //申请提现
      apply(){
        var that = this;
        if(that.info.deposit<=0){
          return that.$util.Tips({title:'可用保证金为0，无法提现'});
        }
        openWithdrawSubscribe().then(() => {
          that.doPost();
        });
      },
      doPost(){
        var that = this;
        that.withdrawBtn = false;
        withdrawDeposit().then(res=>{
          //跳转到保证金列表页
          return that.$util.Tips({title:'已提交，请等待审核'}, '/pages/system/deposit/list');
        }).catch(err => {
          if(err.msg == '未登陆'){
            return that.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
          }else{
            that.withdrawBtn = true;
            return that.$util.Tips({title:err});
          }
        });
      },
      getData(){
      	var that = this;
      	getDepositInfo().then(res=>{
          that.info = res.data.info;
      	}).catch(err => {
      	  if(err.msg == '未登陆'){
      	    return that.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
      	  }
      	});
      },
      choosePrice(price, index, type){
        this.nowType = type;
        this.deposit_price = price;
        this.nowIndex = index;
      },
      chPayType(type){
        this.payType = type;
      }
    }
	}
</script>

<style lang="scss">
.container{
  background: $ss-bg-color;
}
.content {
	margin: 20rpx auto;
	background-color: #ffffff;
	width: 700rpx;
	color: $u-type-warning;
  border-radius: 20rpx;
	font-size: 28rpx;
  box-shadow: 0rpx 15px 10px -15px #999999;
  .title{
    font-size: 36rpx;
    color: #24292E;
    text-align: center;
    font-weight: 700;
    padding-top: 30rpx;
  }
	.price {
		.sum {
			font-size: 32rpx;
			.num {
				font-size: 60rpx;
				font-weight: bold;
			}
		}
	}
	.tips {
		padding: 0 20rpx;
		border: 10rpx;
		position: relative;
		color: #a75c00;
		line-height: 60rpx;
		font-size: 24rpx;
    text-align: center;
    background: #f7dbb3;
		.circle-left,
		.circle-right {
			position: absolute;
			height: 36rpx;
			width: 18rpx;
			background-color: #ffffff;
		}
		.circle-right {
			border-radius: 40rpx 0 0 40rpx;
			right: 0;
			top: -18rpx;
		}
		.circle-left {
			border-radius: 0 40rpx 40rpx 0;
			left: 0;
			top: -18rpx;
		}
		.rule {
			font-size: 24rpx;
			display: flex;
			align-items: center;
			text {
				margin-right: 10rpx;
				flex: 1;
			}
		}
	}
}
.money{
  font-size: 60rpx;
  color: $u-type-warning;
  position: relative;
  
  .close{
    position: absolute;
    top: 20rpx;
    right: 20rpx;
    line-height: 28rpx;
    font-size: 28rpx;
  }
}
.tips{
  width: 600rpx;
  height: 80rpx !important;
  line-height: 80rpx !important;
  margin: 20rpx auto;
  background: $u-type-error;
  color: #FFFFFF;
}
.pay-box{
  width: 700rpx;
  margin: 40rpx auto;
  .rule{
    margin-bottom: 20rpx;
  }
  .pay{
    margin: 20rpx 0;
  }
}
.rollback{
  text-align: center;
  color: u-type-info-dark;
  line-height: 80rpx;
}
.choose{
  width: 690rpx;
  margin: 0 30rpx;
  background: #FFFFFF;
}
.price-item{
  width: 220rpx;
  height: 100rpx;
  font-size: $uni-font-size-lg;
  background: #eeeeee;
  margin-top: 10rpx;
  font-weight: 700;
  color: #666666;
}
.choose .on{
  color: #FFFFFF;
  background: $ss-base-color;
}
.mr10{
  margin-right: 10rpx;
}
.pay-type{
  margin: 20rpx 0;
  .pay-item{
    font-size: $uni-font-size-base;
    padding: 16rpx 20rpx;
    border: 1px solid #ccc;
    margin-bottom: 10rpx;
    background: #FFFFFF;
  }
  .on{
    border: 1px solid #ff9900;
  }
}
</style>
