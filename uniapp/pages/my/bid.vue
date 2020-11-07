<template>
	<view class="container">
		<view class="wrap">
      <block v-if="lists.length > 0">
        <view class="page-box">
          <view class="order" v-for="(item, index) in lists" :key="index">
            <view class="item" @click="goPage('/pages/auction/detail?uuid='+item.uuid)">
              <view class="left"><image :src="item.thumb" mode="aspectFill"></image></view>
              <view class="content">
                <view class="title u-line-2">{{item.title}}</view>
                <view class="type line2">{{item.intro}}</view>
                <view class="price text-red text-bold" v-if="item.auction_status==2">成交价：{{ item.max_price }}</view>
              </view>
            </view>
            <view class="total">
              您共出价{{item.bid_count}}次 
              <template v-if="item.auction_status==2">
                <text class="total-price text-green u-padding-left-10" v-if="item.success==1">中拍</text>
                <text class="total-price text-red u-padding-left-10" v-else>未中拍</text>
              </template>
              <template v-else-if="item.auction_status==1">
                <text class="total-price text-red u-padding-left-10">竞拍中</text>
              </template>
              <template v-else-if="item.auction_status==-1">
                <text class="total-price text-red u-padding-left-10">流拍</text>
              </template>
            </view>
            <view class="bottom flex flex-direction">
              <u-read-more ref="uReadMore" closeText="更多出价记录" color="#c8c9cc" :shadow-style="shadowStyle" :show-height="200" :toggle="true">
                <view class="bid flex justify-between text-gray" v-for="(item2, index2) in item.my_bid" :key="index2">
                  <text class="text-sm">出价：<text class="text-red">￥{{ item2.price | formatMoney }}</text></text>
                  <text class="text-sm">{{item2.create_time}}</text>
                </view>
              </u-read-more>
            </view>
          </view>
          <u-loadmore v-if="lists.length>0" :status="loadStatus" bgColor="#f2f2f2"></u-loadmore>
        </view>
      </block>
		</view>
    
    <u-empty margin-top="300" v-if="lists.length==0" text="暂无出价记录" mode="order"></u-empty>
    
	</view>
</template>

<script>
  import Util from '@/utils/util.js';
  import { getBidHistory } from '@/api/auction.js';
  export default {
    filters:{
      formatMoney: function(s) {
        console.log(s)
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
      	if (true) {// 不带小数位
      		var a = s.split(".");
      		if (a[1] == "00") {
      			s = a[0];
      		}
      	}
      	return s;
      }
    },
    data() {
      return {
        lists: [],
        loadStatus: 'loadmore',
        page: 1,
        limit: 10
      };
    },
    onLoad() {
      this.getData();
    },
    onReachBottom() {
      this.getData();
    },
    methods: {
      getData(){
        var that = this;
        //判断是否结束
        if(this.loadStatus == 'nomore'){
          return this.$util.Tips({
            title: "没有更多了"
          });
        }
        uni.showLoading();
        getBidHistory({page: this.page, limit:this.limit}).then(res=>{
          let list = res.data.lists;
          that.lists = that.lists.concat(list);
          let loadend = list.length < that.limit;
          if(loadend){
            that.loadStatus = 'nomore';
          }else{
            that.page++;
          }
          uni.hideLoading();
        })
      },
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      },
      fMoney(s, type) {
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
		.left {
			margin-right: 20rpx;
			image {
				width: 200rpx;
				height: 200rpx;
				border-radius: 10rpx;
			}
		}
		.content {
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
		text-align: right;
		font-size: 24rpx;
		.total-price {
			font-size: 32rpx;
		}
	}
	.bottom {
		margin-top: 10rpx;
		padding: 10rpx 10rpx 0 10rpx;
    border-top: 1px dashed $u-type-info-disabled;
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
</style>
