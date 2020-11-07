<template>
	<view class="container">
		<view class="items flex flex-wrap justify-between">
      <view class="item" v-for="(item, index) in flowList" :key="index" @click="goPage('/pages/goods/detail?uuid='+item.uuid)">
        <view class="image flex align-center">
          <image :src="item.thumb" mode="widthFix"></image>
        </view>
        <view class="title line2">
          <text>{{item.title}}</text>
        </view>
        <view class="price">
          <text class="text-red text-bold">￥{{item.sale_price}}元</text>
          <text v-if="item.market_price>0" class="text-gray text-xs u-padding-left-10 line-through">市场价 ￥{{item.market_price}}</text>
        </view>
        <view class="info flex justify-between">
          <view class="bid">销量：{{item.sale_total}}</view>
          <view class="click">查看：{{item.click}}</view>
        </view>
        <view class="countdown text-white">{{item.cate_str}}</view>
      </view>
    </view>
		<u-loadmore v-if="flowList.length>0" bg-color="#F4F5F6" :status="loadStatus" @loadmore="getData"></u-loadmore>
    
    <u-empty margin-top="300" v-if="flowList.length==0" text="暂无藏品数据" mode="car"></u-empty>
	</view>
</template>

<script>
import Util from '@/utils/util.js';
import { getGoodsList } from '@/api/goods.js';
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
      title: '藏品列表-北京盛世国际拍卖',
      path: '/pages/goods/list'
    }
  },
  onShareTimeline(){
    return {
      title: '藏品列表-北京盛世国际拍卖',
      imageUrl: ''
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
      getGoodsList({cate_id:this.cid, page:this.page, limit:this.limit}).then(res=>{
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
}
</style>
