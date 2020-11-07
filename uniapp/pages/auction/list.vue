<template>
	<view class="container">
		<view class="items flex flex-wrap justify-between">
      <view class="item" v-for="(item, index) in flowList" :key="index" @click="goPage('/pages/auction/detail?uuid='+item.uuid)">
        <view class="image flex align-center">
          <image :src="item.thumb" mode="widthFix"></image>
        </view>
        <view class="title line2">
          <text>{{item.title}}</text>
        </view>
        <view class="price">
          <text class="text-red text-bold">￥{{item.price}}元</text>
        </view>
        <view class="info flex justify-between">
          <view class="bid">出价次数：{{item.bid_times}}次</view>
          <view class="click">围观：{{item.click}}人</view>
        </view>
        <view class="countdown text-white" v-if="item.auction_status==1 && item.timestamp>0">距结束<u-count-down fontSize="24" separator-color="#ffffff" color="#ffffff" bg-color="transparent" :show-days="false" :timestamp="item.timestamp" @end="ended"></u-count-down></view>
          <view v-else-if="item.auction_status==0 && item.timestamp>0" class="countdown text-green">距开始<u-count-down fontSize="24" separator-color="#ffffff" color="#ffffff" bg-color="transparent" :timestamp="item.timestamp" :show-days="false" @end="started"></u-count-down></view>
        <view class="countdown ss-done text-white" v-else>已结拍</view>
      </view>
    </view>
		<u-loadmore v-if="flowList.length>0" bg-color="#F4F5F6" :status="loadStatus" @loadmore="getData"></u-loadmore>
    
    <u-empty margin-top="300" v-if="flowList.length==0" text="暂无拍品数据" mode="car"></u-empty>
	</view>
</template>

<script>
import Util from '../../utils/util.js';
import { getCollectionList } from '../../api/auction.js';
export default {
	data() {
		return {
			loadStatus: 'loadmore',
			flowList: [],
      page: 1,
      limit: 10,
      isEnd: false,
      cid: 0
		};
	},
	onLoad(option) {
    this.cid = option.cid || 0;
    let title = option.title || '';
    
    uni.setNavigationBarTitle({
      title: title+' 拍品列表'
    })
    
    this.getData();
    wx.showShareMenu({
      menus: ["shareAppMessage", "shareTimeline"]
    });
	},
	onReachBottom() {
    this.getData();
	},
  onShareAppMessage(res) {
    return {
      title: '拍品列表-北京盛世国际拍卖',
      path: '/pages/auction/list'
    }
  },
  onShareTimeline(){
    return {
      title: '拍品列表-北京盛世国际拍卖'
    }
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
      getCollectionList({cate_id:this.cid, page:this.page, limit:this.limit}).then(res=>{
        let list = res.data.data;
        that.flowList = that.flowList.concat(list);
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
		remove(id) {
			this.$refs.uWaterfall.remove(id);
		},
		clear() {
			this.$refs.uWaterfall.clear();
		},
    ended(){
      
    },
    started(){
      
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
  padding: 0 20rpx;
}
.item{
  width: 350rpx;
  padding: 15rpx;
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
  .text-green{
    background: rgba($color: #19be6b, $alpha: 0.8);
  }
  .ss-done{
    background: #666666;
  }
}
</style>
