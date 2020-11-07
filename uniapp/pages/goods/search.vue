<template>
	<view class="container">
    <view class="search-box">
        <mSearch class="mSearch-input-box" mode="1" button="inside" placeholder="输入藏品关键词搜索" @search="doSearch" @input="inputChange" :isFocus="true" @confirm="doSearch" v-model="keyword"></mSearch>
    </view>
		<view class="items flex flex-wrap justify-between">
      <view class="item" v-for="(item, index) in flowList" :key="index" @click="goPage('/pages/goods/detail?uuid='+item.uuid)">
        <view class="image flex align-center">
          <image :src="item.thumb" mode="widthFix"></image>
        </view>
        <view class="title line2">
          <text>{{item.title}}</text>
        </view>
        <view class="price">
          <text class="text-red text-bold">￥{{item.sale_price || ''}}元</text>
          <text v-if="item.market_price>0" class="text-gray text-xs u-padding-left-10 line-through">市场价 ￥{{item.market_price}}</text>
        </view>
        <view class="info flex justify-between">
          <view class="bid">销量：{{item.sale_total || 0}}</view>
          <view class="click">查看：{{item.click}}</view>
        </view>
        <view class="countdown text-white">{{item.cate_str}}</view>
      </view>
    </view>
		<u-loadmore v-if="flowList.length>0" :status="loadStatus" @loadmore="getData"></u-loadmore>
    
    <u-empty margin-top="300" v-if="flowList.length==0" text="暂无藏品数据" mode="car"></u-empty>
	</view>
</template>

<script>
import mSearch from '@/components/mh-search/mh-search.vue';
import Util from '@/utils/util.js';
import { getGoodsList } from '@/api/goods.js';
export default {
  components: {
    mSearch
  },
	data() {
		return {
			loadStatus: 'loadmore',
			flowList: [],
      page: 1,
      limit: 10,
      cid: 0,
      keyword: ''
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
      title: '藏品搜索-北京盛世国际拍卖',
      path: '/pages/goods/search'
    }
  },
  onShareTimeline(){
    return {
      title: '藏品搜索-北京盛世国际拍卖',
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
      getGoodsList({cate_id:this.cid, keyword: this.keyword, page:this.page, limit:this.limit}).then(res=>{
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
    doSearch() {
      this.initData();
    },
    inputChange(){
      if(this.keyword==''){
        this.initData();
      }
    },
    initData(){
      this.cid = 0;
      this.page = 1;
      this.loadStatus = 'loadmore';
      this.flowList = [];
      this.getData();
    }
	}
};
</script>

<style lang="scss" scoped>
.container{
  min-height: 100vh;
  background: #FFFFFF;
}
.search-box{
  height: 100rpx;
  width: 100%;
  padding: 20rpx 10rpx;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f5f5f5;
  .input{
    background: #FFFFFF;
  }
  u-icon{
    display: inline;
    margin-right: 5rpx;
  }
}
.items{
  padding: 0 20rpx;
}
.item{
  width: 340rpx;
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
.mSearch-input-box{
  width: 700rpx;
}
</style>
