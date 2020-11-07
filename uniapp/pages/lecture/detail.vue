<template>
	<view class="content">
		<view class="banner" v-if="info.type==1">
			<image :src="info.thumb" mode="aspectFill"></image>
			<view class="share">
				<u-icon name="share" color="#b9ac8c" size="40"></u-icon>
				<text>推荐</text>
        <button class="share-btn" plain type="default" open-type="share"></button>
			</view>
		</view>
    <view class="video-box" v-else>
      <video :src="info.video_url" id="play" :autoplay="false" :poster="info.thumb" controls @click="startPlay"></video>
    </view>
    
		<view class="intro">
			<view class="title line2">{{info.title}}</view>
			<view class="desc line2">{{info.intro}}</view>
			<view class="status flex justify-between" v-if="info.type==1">
        <view v-if="info.start_time>info.now_time"><text class="label bg-red">{{info.start_live}}点开播</text></view>
        <view v-if="info.start_time<info.now_time && info.end_time>info.now_time"><text class="label bg-blue">直播中</text><text class="click">{{info.click}}人在看</text></view>
        <view v-if="info.end_time<info.now_time"><text class="label bg-grey">已结束</text></view>
        
        <view class="bg-gradual-green action flex justify-center" v-if="info.end_time<info.now_time" @click="goLive">
          <u-icon name="play-circle" color="#fff" size="28"></u-icon>
          <text>查看回放</text>
        </view>
        <!-- <view class="bg-red action flex justify-center" v-else-if="info.start_time>info.now_time">
          <u-icon name="play-circle" color="#fff" size="28"></u-icon>
          <text>即将开播</text>
        </view> -->
        <!-- ="info.start_time<info.now_time && info.end_time>info.now_time" -->
        <view class="bg-gradual-green action flex justify-center" v-else @click="goLive">
          <u-icon name="play-circle" color="#fff" size="28"></u-icon>
          <text>进入直播</text>
        </view>
			</view>
      <!-- 视频 -->
      <view class="status flex justify-between" v-else>
        <view><text class="click">{{info.click}}人在看</text></view>
        <view><text class="click">主讲专家：{{info.expert_name}}</text></view>
      </view>
		</view>
		<!-- 即将开始 -->
		<view class="detail">
			<view class="title"><text>详 情</text></view>
			<view class="content">
        <jyf-parser :html="info.content" ref="article" :tag-style="tagStyle"></jyf-parser>
			</view>
		</view>
		
	</view>
</template>

<script>
  import { getLectureInfo } from '@/api/expert.js';
	import parser from "@/components/jyf-parser/jyf-parser";
  import Util from '@/utils/util.js';
	export default {
		components: {
			"jyf-parser": parser
		},
		data() {
			return {
        uuid: '',
        info: [],
        playing: false
			};
		},
		onLoad(option) {
      this.uuid = option.uuid || '';
      if(!this.uuid){
        uni.navigateBack();
      }
      this.getData();
      
      wx.showShareMenu({
        menus: ["shareAppMessage", "shareTimeline"]
      });
		},
    onShareAppMessage(res) {
      return {
        title: '【专家讲座】'+this.info.title+'-北京盛世国际拍卖',
        path: '/pages/lecture/detail?uuid='+this.uuid
      }
    },
    onShareTimeline(){
      return {
        title: '【专家讲座】'+this.info.title+'-北京盛世国际拍卖',
        imageUrl: this.info.thumb
      }
    },
		methods:{
			getData(){
			  var that = this;
        uni.showLoading();
			  getLectureInfo({uuid: this.uuid}).then(res=>{
			    that.info = res.data.info;
          uni.setNavigationBarTitle({
            title: res.data.info.title
          });
			    uni.hideLoading();
			  })
			},
      goLive(){
        let roomId = this.info.room_id
        console.log(roomId)
        let customParams = encodeURIComponent(JSON.stringify({ path: 'pages/index/index', pid: 1 }));
        uni.navigateTo({
            url: `plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin?room_id=${roomId}&custom_params=${customParams}`
        })
      },
      startPlay() {
        this.videoContext = uni.createVideoContext('play')
        if (!this.playing) {
          this.videoContext.play();
          this.playing = true;
        } else {
          this.videoContext.pause();
          //this.videoContext.exitFullScreen();
          this.playing = false;
        }
      },
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      }
		}
	}
</script>

<style lang="scss" scoped>
	.banner{
		width: 750rpx;
		// height: 425rpx;
    height: auto;
		overflow: hidden;
		position: relative;
		image{
			width: 750rpx;
		}
		.share{
			width: 100rpx;
			height: 100rpx;
			position: absolute;
			right: 20rpx;
			bottom: 20rpx;
			z-index: 999;
			display: flex;
			flex-flow: column wrap;
			align-items: center;
			justify-content: center;
			text{
				text-align: center;
				color: #b9ac8c;
				font-size: 28rpx;
			}
		}
    .share-btn{
      width: 100rpx;
      height: 100rpx;
      position: absolute;
			right: 20rpx;
			bottom: 20rpx;
      border: none !important;
    }
	}
	.intro{
		overflow: hidden;
		background: #fff;
		padding: 20rpx 30rpx;
		.title{
			font-size: $uni-font-size-lg;
			line-height: 40rpx;
		}
		.desc{
			max-height: 60rpx;
			line-height: 30rpx;
			margin: 20rpx 0;
			font-size: $uni-font-size-base;
			color: $ss-font-dark;
		}
		.status{
			.label{
				display: inline-block;
				padding: 0 10rpx;
				color: #fff;
				font-size: $uni-font-size-sm;
				border-radius: 5rpx;
				margin-right: 10rpx;
			}
			.click{
				color: $ss-font-dark;
				font-size: $uni-font-size-sm;
			}
		}
	}
	.detail{
		margin-top: 20rpx;
		background: #fff;
		.title{
			height: 80rpx;
			padding-bottom: 8rpx;
			line-height: 80rpx;
			border-bottom: 1px solid $ss-border-color;
			text{
				display: block;
				width: 100rpx;
				height: 80rpx;
				text-align: center;
				margin: 0 auto;
				font-size: $uni-font-size-lg;
				border-bottom: 8rpx solid $ss-base-color;
			}
		}
		.content{
			padding: 35rpx;
			overflow: hidden;
		}
	}
  .action{
    color: #fff;
    padding: 0 20rpx;
    border-radius: 10rpx;
    text{
      padding-left: 10rpx;
    }
  }
  .video-box{
    width: 100%;
    height: auto;
    video{
      width: 750rpx;
      height: 420rpx;
    }
  }
</style>
