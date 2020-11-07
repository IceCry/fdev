<template>
	<view class="container">
    
    <!-- 广告位 -->
    <view class="banner" v-if="banner" @click="goPage('/pages/news/detail?id=17')">
      <image :src="banner" mode="widthFix"></image>
    </view>
    
    <view class="tabs">
      <u-tabs bg-color="#ffffff" :bold="true" active-color="#fa3534" :list="tabs"
      @change="change" :current="current" :is-scroll="false"></u-tabs>
    </view>
    
    <view class="content">
      <view class="item" v-for="(item, index) in lists" :key="index" @click="goPage('/pages/auction/special?uuid='+item.uuid)">
        <view class="tag">
          共 {{item.collection_num}} 件拍品
        </view>
        <view class="image flex align-center">
          <image :src="item.thumb" mode="widthFix"></image>
        </view>
        <view class="title">
          <text>{{item.title}}</text>
        </view>
        <view class="info">
          <text class="u-font-xs text-gray">开拍时间：{{item.start_time}}</text>
        </view>
        <view class="btm flex justify-between">
          <view class="num flex text-gray">
            <view class="click"><u-icon name="eye" :size="30"></u-icon><text>{{item.click}}围观</text></view>
            <view class="bid"><u-icon name="fachui" :size="30" custom-prefix="custom-icon"></u-icon><text>{{item.bid_times}}出价</text></view>
          </view>
          <view class="count">
            <!-- <text>拍品数量：{{item.collection_num}}</text> -->
            <view class="special-btn flex align-center justify-center"><image class="live-img" src="../../static/live.gif" mode="widthFix"></image> 进入拍场</view>
          </view>
        </view>
      </view>
    </view>
    
    <u-empty margin-top="300" v-if="lists.length==0" text="暂无专场信息" mode="car"></u-empty>
    
    <u-loadmore v-if="lists.length>0" bg-color="#F4F5F6" :status="loadStatus" @loadmore="getData"></u-loadmore>
    
    <view class="inner-box">
      <view class="ad-box" @click="goPage(ad1.url, ad1.url_type)">
        <image :src="ad1.image" mode="widthFix"></image>
      </view>
    </view>
	</view>
</template>

<script>
import Util from '@/utils/util.js';
import { getSyncList } from '@/api/auction.js';
export default {
	data() {
		return {
      current: 0,
      tabs: [{'id': 1, 'name':'拍卖中'}, {'id': -1, 'name':'预展中'}, {'id': 2, 'name':'已结拍'}],
      ad1: [],
      lists: [],
      special_status: 1,
      page: 1,
      limit: 10,
      loadStatus: 'loadmore',
      banner: ''
		};
	},
	onLoad(option) {
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
      if(this.loadStatus=='nomore'){
        return this.$util.Tips({
          title: "没有更多了"
        });
      }
      uni.showLoading();
      getSyncList({special_status:this.special_status, page: this.page, limit: this.limit}).then(res=>{
        that.ad1 = res.data.ad1;
        that.banner = res.data.banner;
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
    change(index) {
      this.current = index;
      this.special_status = this.tabs[index].id;
      this.initData();
    },
    initData(){
      this.loadStatus = 'loadmore';
      this.page = 1;
      this.lists = [];
      this.getData();
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
.content{
  width: 720rpx;
  margin: 15rpx auto;
  .item{
    width: 720rpx;
    padding: 20rpx;
    margin-bottom: 20rpx;
    border-radius: 14rpx;
    background: #FFFFFF;
    box-shadow: 0rpx 15px 10px -15px #ccc;
    position: relative;
    .image{
      width: 680rpx;
      height: auto;
      overflow: hidden;
      image{
        width: 680rpx;
      }
    }
    .title{
      font-size: $uni-font-size-lg;
      line-height: 50rpx;
    }
    .bid{
      margin-left: 14rpx;
    }
    .tag{
      background: rgba($color: $ss-bg-red, $alpha: 0.9);
      color: #ffffff;
      font-size: $uni-font-size-mini;
      height: 40rpx;
      line-height: 40rpx;
      border-radius: 0 20rpx 20rpx 0;
      position: absolute;
      left: 0;
      top: 10rpx;
      z-index: 99;
      padding: 0 20rpx 0 10rpx;
    }
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
.special-btn{
  width: 220rpx;
  height: 50rpx;
  line-height: 50rpx;
  color: #fff;
  border-radius: 25rpx;
  text-align: center;
  background: $u-type-error;
}
.live-img{
  max-height: 30rpx;
}
.banner image{
  width: 750rpx;
}
</style>
