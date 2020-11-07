<template>
	<view class="container">
		<u-toast ref="uToast"></u-toast>
    <!-- 广告位 -->
    <view class="banner" v-if="banner" @click="goPage('/pages/news/detail?id=19')">
      <image :src="banner" mode="widthFix"></image>
    </view>
    
    <view class="tabs">
      <u-tabs bg-color="#ffffff" :bold="true" :active-color="activeColor" :list="tabs"
      @change="change" :current="current" :is-scroll="true"></u-tabs>
    </view>
		<view class="content">
			<view class="items flex flex-wrap justify-between">
        <!-- <view class="item one">
          <u-tag class="tag" text="预告" type="error" mode="dark" shape="circleRight" />
          <image src="http://iph.href.lu/690x280?text=推荐位" mode="aspectFill"></image>
        </view>
        <view class="item two">
          <u-tag class="tag" text="直播中" type="success" mode="dark" shape="circleRight" />
          <image src="http://iph.href.lu/690x280?text=推荐位" mode="aspectFill"></image>
          <view class="info">
            <view class="name">佩奇</view>
            <u-tag class="cate" text="瓷器" size="mini" bg-color="#bc9f77" mode="dark" shape="circle " />
            <text class="click">840观看</text>
          </view>
        </view> -->
        
        <view class="item two" v-for="(item, index) in lists" :key="index" @click="goPage('/pages/auction/detail?uuid='+item.uuid)">
          <view class="tag" v-if="item.auction_status==1 && item.timestamp>0">
            距离结束<text space="nbsp"></text><u-count-down fontSize="20" separator-color="#ffffff" color="#ffffff" bg-color="transparent" :show-days="false" :timestamp="item.timestamp" @end="ended"></u-count-down>
          </view>
          <view class="tag ss-unstart" v-else-if="item.auction_status==0 && item.timestamp>0">
            距开拍<text space="nbsp"></text><u-count-down fontSize="20" separator-color="#ffffff" color="#ffffff" bg-color="transparent" :show-days="false" :timestamp="item.timestamp" @end="started"></u-count-down>
          </view>
          <view class="tag ss-done" v-else-if="item.auction_status==-1">
            <text>已流拍</text>
          </view>
          <view class="tag ss-done" v-else-if="item.auction_status==-2">
            <text>已撤拍</text>
          </view>
          <view class="tag ss-done" v-else-if="item.auction_status==-3">
            <text>终止拍卖</text>
          </view>
          <view class="tag ss-done" v-else-if="item.auction_status==2">
            <text>已结拍</text>
          </view>
          <!-- <u-tag class="tag" text="距离结束:" type="error" mode="dark" shape="circleRight" /> -->
          <view class="image flex align-center">
            <image :src="item.thumb" mode="aspectFill"></image>
          </view>
          <view class="info">
            <view class="name line1">{{item.title}}</view>
            <view class="price text-red">￥{{item.price}}</view>
            <view class="btm flex justify-between">
              <view><u-icon name="eye" :size="24"></u-icon><text class="u-padding-left-10">{{item.click}} 围观</text></view>
              <view><u-icon name="fachui" :size="24" custom-prefix="custom-icon"></u-icon><text class="u-padding-left-10">{{item.bid_times}} 次出价</text></view>
            </view>
          </view>
        </view>
        
      </view>
		</view>
    
    <u-empty margin-top="300" v-if="lists.length==0" text="暂无拍品信息" mode="car"></u-empty>
    <u-loadmore v-if="lists.length>0" bg-color="#F4F5F6" :status="loadStatus" @loadmore="getData"></u-loadmore>
    
	</view>
</template>

<script>
  import Util from '@/utils/util.js';
  import { getCategory } from '@/api/api.js';
  import { getCollectionList } from '@/api/auction.js';
	export default {
		data() {
			return {
				tabs: [],
				lists: [],
				current: 0,
				sectionCurrent: 0,
				tabCountIndex: 0,
				activeColor: this.$u.color['error'],
        
        cate_id: 0,
        
        page: 1,
        limit: 10,
        loadStatus: 'loadmore',
        banner: ''
			}
		},
		onLoad() {
      this.getCate();
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
        title: '限时拍-北京盛世国际拍卖',
        path: '/pages/auction/seckill'
      }
    },
    onShareTimeline(){
      return {
        title: '限时拍-北京盛世国际拍卖'
      }
    },
		methods: {
      getCate(){
        var that = this;
        getCategory().then(res=>{
          let cates = res.data.data;
          cates.unshift({id:0, name: '全部'});
          that.tabs = cates;
        })
      },
      getData(){
        var that = this;
        if(this.loadStatus=='nomore'){
          return this.$util.Tips({
            title: "没有更多了"
          });
        }
        uni.showLoading();
        getCollectionList({type:1, auction_type:2, cate_id:this.cate_id, page: this.page, limit: this.limit}).then(res=>{
          let list = res.data.data;
          that.lists = that.lists.concat(list);
          that.banner = res.data.banner;
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
        this.cate_id = this.tabs[index].id;
        this.initData();
			},
      initData(){
        this.loadStatus = 'loadmore';
        this.page = 1;
        this.lists = [];
        this.getData();
      },
      ended(){
        
      },
      started(){
        
      }
		}
	}
</script>

<style lang="scss" scoped>
	.content{
		margin: 30rpx;
    .items{
      .item{
        position: relative;
        overflow: hidden;
        padding: 10rpx;
        background: #FFFFFF;
        border-radius: 8rpx;
        margin-bottom: 20rpx;
        .tag{
          background: rgba($color: $ss-bg-red, $alpha: 0.8);
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
        .ss-done{
          background: rgba($color: $u-main-color, $alpha: 0.8);
        }
        .ss-unstart{
          background: rgba($color: $u-type-success, $alpha: 0.8);
        }
        .info{
          .name{
            width: 310rpx;
            font-size: $uni-font-size-base;
            line-height: 60rpx;
            font-weight: 700;
          }
          .price{
            color: $ss-bg-red;
            font-size: $uni-font-size-lg;
            line-height: 40rpx;
            font-weight: 700;
          }
          .btm{
            padding: 10rpx 0;
            color: $u-type-info;
            border-top: 1px solid $ss-bg-grey;
            font-size: $uni-font-size-mini;
          }
        }
      }
      .one{
        width: 690rpx;
        height: 280rpx;
        border-radius: 14rpx;
        image{
          width: 100%;
        }
      }
      .two{
        width: 335rpx;
        .image{
          width: 315rpx;
          height: 315rpx;
          overflow: hidden;
        }
        image{
          width: 100%;
        }
      }
    }
	}
  .banner image{
    width: 750rpx;
  }
</style>
