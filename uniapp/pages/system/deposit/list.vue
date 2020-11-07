<template>
	<view class="container">
    <view class="content light bg-gray">
      <view class="title">
        <text>保证金余额</text>
      </view>
      <view class="price flex flex-direction align-center">
          <view class="sum">
            ￥
            <text class="num">{{balance|formatMoney}}</text>
          </view>
          <view class="type text-grey">当前可用保证金</view>
          <view class="ss-block text-grey text-sm">(当前冻结{{freeze|formatMoney}}元)</view>
          <view class="deposit text-red" @click="goPage('/pages/system/deposit/deposit?price='+min)">立即充值</view>
      </view>
    </view>
    
		<view class="item" v-for="(item, index) in lists" :key="item.id">
			<view class="top flex justify-between">
				<view class="title">
          <text>{{item.title}}</text>
					<!-- <text v-if="item.type==1" class="green">充值</text>
					<text v-else-if="item.type==-1" class="red">提现</text>
					<text v-else-if="item.type==-2" class="blue">抵扣</text>
					<text v-else-if="item.type==-3" class="black">扣除</text> -->
				</view>
				
				<view class="name flex justify-end align-center">{{ item.create_time }}</view>
			</view>
			<view class="bottom flex justify-between">
				<text class="line3">{{item.mark}}</text>
        <view class="phone text-bold" :class="item.pm==0?'text-red':'text-green'">{{item.pm==0?'-':'+'}}￥{{ item.number|formatMoney }}</view>
				<!-- <text class="line2">状态：{{item.order_status}}</text> -->
			</view>
		</view>
		<u-loadmore font-size="24" v-if="lists.length>0" :status="loadStatus" />
	</view>
</template>

<script>
import { getDepositList } from '@/api/auction.js';
import Util from '@/utils/util.js';
export default {
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
      balance: 0,
      freeze: 0,
			lists: [],
      loadStatus: 'loadmore',
      limit: 15,
      page: 1,
      min: 0, //最低充值金额
		};
	},
	onLoad() {
		this.getData();
	},
	methods: {
		getData() {
			var that=this;
      if(this.loadStatus == 'nomore'){
        return this.$util.Tips({
          title: "没有更多了"
        });
      }
      uni.showLoading();
			getDepositList({page:this.page, limit:this.limit}).then(res=>{
        let lists = res.data.lists;
        that.balance = res.data.balance;
        that.freeze = res.data.freeze;
        that.min = res.data.min;
        that.lists = that.lists.concat(lists);
        let loadend = lists.length < that.limit;
        if(loadend){
          that.loadStatus = 'nomore';
        }else{
          that.page++;
        }
        uni.hideLoading();
			});
		},
    goPage(url, type='navigate'){
      Util.goPage(url, type);
    }
	},
  onReachBottom() {
    if(this.loadStatus == 'nomore') return uni.showToast({
      icon: 'none',
      title: '没有更多了'
    });
    this.loadStatus = 'loading';
    this.getData();
  }
};
</script>

<style lang="scss" scoped>
.container{
  background: #ffffff;
  padding: 20rpx;
  min-height: 100vh;
}
.content {
	margin: 20rpx auto;
  // background: linear-gradient(45deg, #39b54a, #8dc63f);
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
    padding-top: 40rpx;
  }
	.price {
		padding: 10rpx 20rpx 30rpx;
		.sum {
			font-size: 32rpx;
			.num {
				font-size: 60rpx;
				font-weight: bold;
			}
		}
	}
}
.item {
	padding: 30rpx 20rpx;
  border-bottom: 1px solid #eeeeee;
  .title{
    font-size: $uni-font-size-base;
    
  }
	.top {
		display: flex;
		font-weight: bold;
		font-size: 34rpx;
    .name{
      font-weight: normal;
      color: $u-type-info;
      font-size: $uni-font-size-sm;
    }
		.phone {
			margin-left: 10rpx;
		}
		.tag {
			display: flex;
			font-weight: normal;
			align-items: center;
			text {
				display: block;
				width: 60rpx;
				height: 34rpx;
				line-height: 34rpx;
				color: #ffffff;
				font-size: 20rpx;
				border-radius: 6rpx;
				text-align: center;
				background-color:rgb(49, 145, 253);
			}
			.red{
				background-color:red;
			}
			.green{
				background-color:green;
			}
      .blue{
        background-color: blue;
      }
      .black{
        background-color: black;
        color: #FFFFFF;
      }
		}
	}
	.bottom {
		display: flex;
		margin-top: 20rpx;
		font-size: 28rpx;
		justify-content: space-between;
		color: #999999;
    .phone{
      width: 200rpx;
      text-align: right;
    }
	}
}
.addSite {
	display: flex;
	justify-content: space-around;
	width: 600rpx;
	line-height: 100rpx;
	position: absolute;
	bottom: 30rpx;
	left: 80rpx;
	background-color: red;
	border-radius: 60rpx;
	font-size: 30rpx;
	.add{
		display: flex;
		align-items: center;
		color: #ffffff;
		.icon{
			margin-right: 10rpx;
		}
	}
}
</style>
