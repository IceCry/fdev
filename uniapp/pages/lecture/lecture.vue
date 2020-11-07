<template>
	<view class="container">
		<u-toast ref="uToast"></u-toast>
    <view class="tabs">
      <u-tabs bg-color="#ffffff" :bold="true" :active-color="activeColor" :list="tabs"
      @change="change" :current="current" :is-scroll="true"></u-tabs>
    </view>
    
    <!-- 直播预告 轮播 -->
    <view class="slider-box">
      <view class="swiper">
        <swiper class="swiper" indicator-active-color="rgba(255, 255, 255, .8)" :indicator-dots="true" autoplay circular interval="2500">
      			<swiper-item class="image" v-for="(item, index) in lives" :key="index">
      					<image :src="item.image" mode="aspectFill" @click="goPage('/pages/lecture/detail?uuid='+item.uuid)"></image>
                <u-tag class="tag" v-if="item.live_status==0" text="直播预告" type="success" mode="dark" shape="circleRight" />
                <u-tag class="tag" v-else-if="item.live_status==1" text="直播中" type="error" mode="dark" shape="circleRight" />
                <u-tag class="tag" v-else-if="item.live_status==2" text="已结束" type="info" mode="dark" shape="circleRight" />
      			</swiper-item>
      	</swiper>
      </view>
    </view>
    
		<view class="lecture">
			<view class="items flex flex-wrap justify-between">
        
        <view class="item" :class="item.flag=='t' ? 'two':'two'" v-for="(item, index) in lists" :key="index" @click="goPage('/pages/lecture/detail?uuid='+item.uuid)">
          <u-tag class="tag" v-if="item.type==1 && item.live_status==0" text="预告" type="error" mode="dark" shape="circleRight" />
          <u-tag class="tag" v-else-if="item.type==1 && item.live_status==1" text="直播中" type="success" mode="dark" shape="circleRight" />
          <u-tag class="tag" v-else text="查看回放" type="info" mode="dark" shape="circleRight" />
          <view class="image flex align-center">
            <image :src="item.thumb" mode="widthFix"></image>
          </view>
          <view class="info">
            <view class="name line2">{{item.title}}</view>
            <u-tag v-if="item.type==2" class="cate" :text="item.cate_name" size="mini" bg-color="#bc9f77" mode="dark" shape="circle " />
            <text class="click"><text class="u-p-r-20">{{item.expert_name}}</text> {{item.click}}观看</text>
          </view>
        </view>
        
      </view>
		</view>
    
    <u-empty margin-top="300" text="暂无讲座信息" v-if="lists.length==0" mode="news"></u-empty>
    <u-loadmore bg-color="#F4F5F6" font-size="24" v-if="lists.length>0" :status="loadStatus" />
	</view>
</template>

<script>
  import { getLectureData, getLectureIndex } from '@/api/expert.js';
  import Util from '@/utils/util.js';
	export default {
		data() {
			return {
				lists: [],
				tabs: [],
        lives: [],
        
				current: 0,
				sectionCurrent: 0,
				tabCountIndex: 0,
				activeColor: this.$u.color['error'],
        
				loadStatus: 'loadmore',
				limit: 16,
				page: 1
			}
		},
		onLoad() {
      this.getData();
      wx.showShareMenu({
        menus: ["shareAppMessage", "shareTimeline"]
      });
		},
    onShareAppMessage(res) {
      return {
        title: '专家讲座-北京盛世国际拍卖',
        path: '/pages/lecture/lecture'
      }
    },
    onShareTimeline(){
      return {
        title: '专家讲座-北京盛世国际拍卖',
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
        getLectureData({page: this.page, limit: this.limit}).then(res=>{
          let list = res.data.lists;
          that.lists = that.lists.concat(list);
          let loadend = list.length < that.limit;
          if(loadend){
            that.loadStatus = 'nomore';
          }else{
            that.page++;
          }
          uni.hideLoading();
        });
        getLectureIndex().then(res=>{
          let experts = res.data.experts;
          //追加全部
          experts.unshift({id:0, name:'全部'});
          that.tabs = experts;
          that.lives = res.data.lives;
        })
      },
			change(index) {
				// this.current = index;
        if(index==0) return;
        // 获取专家uuid
        let uuid = this.tabs[index].uuid;
        this.goPage('/pages/expert/expert?uuid='+uuid);
			},
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      },
      goLive(index){
        let uuid = this.lives[index].uuid;
        this.goPage('/pages/lecture/detail?uuid='+uuid);
      }
		}
	}
</script>

<style lang="scss" scoped>
	.slider-box{
    margin: 20rpx 30rpx 0;
    .image{
      border-radius: 10rpx;
      overflow: hidden;
      position: relative;
    }
    image{
      width: 100%;
    }
    .tag{
      position: absolute;
      top: 0;
      left: 0;
    }
  }
</style>
