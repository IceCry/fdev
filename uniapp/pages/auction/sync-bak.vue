<template>
	<view class="container">
    <view class="top-title">
      <u-section title="拍品图录" :showLine="false" fontSize="30" :right="false"></u-section>
    </view>
    
    <view class="content">
      <view v-for="(cate, cindex) in cates" :key="cindex">
        <view class="title1">
          <u-section :title="cate.name" :showLine="false" fontSize="28" @click="goPage('/pages/auction/list?cid='+cate.id)" sub-title="更多"></u-section>
        </view>
        <view class="items flex flex-wrap justify-between">
          <view class="item" v-for="(item, index) in cate.collection" :key="index" @click="goPage('/pages/auction/detail?uuid='+item.uuid)">
            <view class="image flex align-center">
              <image :src="item.thumb" mode="widthFix"></image>
            </view>
            <view class="title line2">
              <text>{{item.title}}</text>
            </view>
            <view class="price">
              <text class="text-red text-bold">￥{{item.max_price}}元</text>
            </view>
            <view class="info flex justify-between">
              <view class="bid">出价次数：{{item.bid_times}}次</view>
              <view class="click">围观：{{item.click}}人</view>
            </view>
            <view class="countdown text-red">距结束<u-count-down fontSize="24" separator-color="#ffffff" color="#ffffff" bg-color="transparent" :show-days="false" :timestamp="item.timestamp" @end="ended"></u-count-down></view>
          </view>
        </view>
      </view>
    
    </view>
    
    <u-empty margin-top="300" v-if="cates.length==0" text="暂无拍品数据" mode="car"></u-empty>
    
    <view class="inner-box">
      <view class="ad-box" @click="goPage(ad1.url, ad1.url_type)">
        <image :src="ad1.image" mode="widthFix"></image>
      </view>
    </view>
	</view>
</template>

<script>
import Util from '../../utils/util.js';
import { getSyncList } from '../../api/auction.js';
export default {
	data() {
		return {
			cates: [1,2,3],
      ad1: []
		};
	},
	onLoad(option) {
    this.getData();
    wx.showShareMenu({
      menus: ["shareAppMessage", "shareTimeline"]
    });
	},
	onReachBottom() {
		
	},
  onShareAppMessage(res) {
    return {
      title: '直播拍卖-北京盛世国际拍卖',
      path: '/pages/auction/sync'
    }
  },
  onShareTimeline(){
    return {
      title: '直播拍卖-北京盛世国际拍卖',
      imageUrl: ''
    }
  },
	methods: {
    getData(){
      var that = this;
      uni.showLoading();
      getSyncList().then(res=>{
        that.cates = res.data.cates;
        that.ad1 = res.data.ad1;
        uni.hideLoading();
      })
    },
    goPage(url, type='navigate'){
      Util.goPage(url, type);
    }
	}
};
</script>


<style lang="scss" scoped>
/* page不能写带scope的style标签中，否则无效 */
page {
  background-color: rgb(240, 240, 240);
}
.container{
}
.top-title{
  background: #FFFFFF;
  height: 80rpx;
  line-height: 80rpx;
  padding: 30rpx 0 0 20rpx;
}
.content{
  width: 720rpx;
  margin: 15rpx auto;
  .title1{
    height: 60rpx;
    line-height: 60rpx;
    margin-top: 30rpx;
  }
}
.item{
  padding: 15rpx;
  width: 350rpx;
  border-radius: 10rpx;
  background: #FFFFFF;
  margin-top: 15rpx;
  position: relative;
  .image{
    width: 320rpx;
    height: 280rpx;
    overflow: hidden;
    border-radius: 8rpx;
  }
  .title{
    font-size: $uni-font-size-base;
    color: #333333;
    height: 80rpx;
    line-height: 40rpx;
  }
  .info{
    font-size: $uni-font-size-sm;
    color: $u-type-info-dark;
    padding-top: 6rpx;
    border-top: 1px dashed $ss-bg-grey;
  }
  .countdown{
    position: absolute;
    color: #FFFFFF;
    font-size: $uni-font-size-sm;
    left: 0;
    padding: 0 10rpx;
    top: 10rpx;
    height: 36rpx;
    line-height: 36rpx;
    border-radius: 0 18rpx 18rpx 0;
    background: rgba($color: $u-type-error, $alpha: 0.8);
  }
}
.inner-box{
  width: 710rpx;
  margin: 40rpx 20rpx;
  .ad-box{
    width: 710rpx;
    height: 235rpx;
    border-radius: 14rpx;
    overflow: hidden;
  }
  image{
    width: 750rpx;
  }
}
</style>
