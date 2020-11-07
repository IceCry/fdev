<template>
	<view class="container">
    <!-- 广告位 -->
    <view class="banner" v-if="banner" @click="goPage('/pages/news/detail?id=20')">
      <image :src="banner" mode="widthFix"></image>
    </view>
    
    <view class="tabs">
      <view class="condition">
        <text class="title">资产类型：</text>
        <text class="name text-gray" @click="chooseAsset(index)" :class="assetIndex==index?'cur':''" v-for="(item, index) in assetType" :key="index">{{item.name}}</text>
      </view>
      <view class="condition">
        <text class="title">标的物类型：</text>
        <text class="name text-gray" @click="chooseType(index)" :class="typeIndex==index?'cur':''" v-for="(item, index) in resType" :key="index">{{item.name}}</text>
      </view>
      <!-- <u-read-more ref="uReadMore" closeText="查看更多" color="#c8c9cc" :shadow-style="shadowStyle" :show-height="100"> -->
      <view class="condition">
        <text class="title">标的物所在地：</text>
        <text class="name text-gray" @click="chooseProvince(index)" :class="provinceIndex==index?'cur':''" v-for="(item, index) in province" :key="index">{{item.name}}</text>
      </view>
      <!-- </u-read-more> -->
    </view>
    
    <!-- 首页模式 -->
    <view class="content" v-if="mode==1">
      <view v-for="(cate, cindex) in lists" :key="cindex">
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
              <text class="text-red text-bold">￥{{item.price|formatMoney}}元</text>
            </view>
            <view class="info flex justify-between">
              <view class="bid">出价次数：{{item.bid_times}}次</view>
              <view class="click">围观：{{item.click}}人</view>
            </view>
            <view v-if="item.auction_status==1 && item.timestamp>0" class="countdown text-white">距结束<u-count-down fontSize="24" separator-color="#ffffff" color="#ffffff" bg-color="transparent" :show-days="false" :timestamp="item.timestamp" @end="ended"></u-count-down></view>
          <view v-else-if="item.auction_status==0 && item.timestamp>0" class="countdown text-green">距开始<u-count-down fontSize="24" separator-color="#ffffff" color="#ffffff" bg-color="transparent" :timestamp="item.timestamp" :show-days="false" @end="started"></u-count-down></view>
            <view v-else class="countdown text-gray">
              已结拍
            </view>
          </view>
        </view>
      </view>
      <u-empty margin-top="300" v-if="lists.length==0" text="暂无资产信息" mode="car"></u-empty>
    </view>
    <!-- 列表模式 -->
    <view class="content" v-else>
      <view class="items flex flex-wrap justify-between">
        <view class="item" v-for="(item, index) in collections" :key="index" @click="goPage('/pages/auction/detail?uuid='+item.uuid)">
          <view class="image flex align-center">
            <image :src="item.thumb" mode="widthFix"></image>
          </view>
          <view class="title line2">
            <text>{{item.title}}</text>
          </view>
          <view class="price">
            <text class="text-red text-bold">￥{{item.price|formatMoney}}元</text>
          </view>
          <view class="info flex justify-between">
            <view class="bid">出价次数：{{item.bid_times}}次</view>
            <view class="click">围观：{{item.click}}人</view>
          </view>
          <view v-if="item.auction_status==1 && item.timestamp>0" class="countdown text-white">距结束<u-count-down fontSize="24" separator-color="#ffffff" color="#ffffff" bg-color="transparent" :show-days="false" :timestamp="item.timestamp" @end="ended"></u-count-down></view>
          <view v-else-if="item.auction_status==0 && item.timestamp>0" class="countdown text-green">距开始<u-count-down fontSize="24" separator-color="#ffffff" color="#ffffff" bg-color="transparent" :timestamp="item.timestamp" :show-days="false" @end="started"></u-count-down></view>
          <view v-else class="countdown text-gray">
            已结拍
          </view>
        </view>
      </view>
      
      <u-empty margin-top="200" v-if="collections.length==0" text="未查询到相关资产信息" mode="car"></u-empty>
      <u-loadmore v-if="collections.length>0" bg-color="#F4F5F6" :status="loadStatus" @loadmore="getList"></u-loadmore>
    </view>
    
	</view>
</template>

<script>
import Util from '@/utils/util.js';
import { getAssetIndex, getAssetList } from '@/api/auction.js';
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
      mode: 1, //1为首页模式 2为列表模式
      
      resType: [],
      typeIndex: 0,
      typeId: 0,
      assetType: [],
      assetIndex: 0,
      assetId: 0,
      province: [],
      provinceIndex: 0,
      provinceId: '',
      lists: [],
      
      collections: [],
      loadStatus: 'loadmore',
      page: 1,
      limit: 10,
      
      shadowStyle: {
        backgroundImage: "none",
        paddingTop: "0",
        marginTop: "20rpx"
      },
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
		if(this.mode==2){
      this.getList();
    }
	},
  onShareAppMessage(res) {
    return {
      title: '资产拍卖-北京盛世国际拍卖',
      path: '/pages/auction/asset'
    }
  },
  onShareTimeline(){
    return {
      title: '资产拍卖-北京盛世国际拍卖'
    }
  },
	methods: {
    getData(){
      var that = this;
      uni.showLoading();
      getAssetIndex().then(res=>{
        let asset = res.data.assetType;
        asset.unshift({id:0, name:'不限'});
        that.assetType = asset;
        
        let type = res.data.resType;
        type.unshift({id:0, name:'不限'});
        that.resType = type;
        
        let pro = res.data.province;
        pro.unshift({id:0, name:'不限', city_id:0});
        that.province = pro;
        
        that.lists = res.data.lists;
        that.banner = res.data.banner;
        uni.hideLoading();
      })
    },
    getList(){
      var that = this;
      uni.showLoading();
      getAssetList({page:this.page, limit:this.limit, res_type:this.typeId, assets_type:this.assetId, province: this.provinceId}).then(res=>{
        let list = res.data.lists;
        that.collections = that.collections.concat(list);
        let loadend = list.length < that.limit;
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
    },
    chooseAsset(index){
      this.initData();
      this.assetId = this.assetType[index].id;
      this.assetIndex = index;
      if(this.assetId==0 && this.typeId==0 && this.provinceId==0){
        this.mode = 1;
      }else{
        this.mode = 2;
      }
      this.getList();
    },
    chooseType(index){
      this.initData();
      this.typeId = this.resType[index].id;
      this.typeIndex = index;
      if(this.assetId==0 && this.typeId==0 && this.provinceId==0){
        this.mode = 1;
      }else{
        this.mode = 2;
      }
      this.getList();
    },
    chooseProvince(index){
      this.initData();
      this.provinceId = this.province[index].city_id;
      this.provinceIndex = index;
      if(this.assetId==0 && this.typeId==0 && this.provinceId==0){
        this.mode = 1;
      }else{
        this.mode = 2;
      }
      this.getList();
    },
    initData(){
      this.page = 1;
      this.loadStatus = 'loadmore';
      this.collections = [];
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
  .text-green{
    background: rgba($color: #19be6b, $alpha: 0.8);
  }
  .text-gray{
    background: rgba($color: $u-type-info-dark, $alpha: 0.8);
  }
}
.tabs{
  background: #FFFFFF;
  padding: 20rpx;
  .title{
    font-weight: 700;
  }
  .name{
    margin-right: 10rpx;
  }
  .cur{
    background: $u-type-error;
    color: #FFFFFF;
  }
}
.banner image{
  width: 750rpx;
}
</style>
