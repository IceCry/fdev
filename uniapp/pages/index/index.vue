<template>
  <view class="container">
		<!-- 搜索框 -->
    <view class="search-box" @click="goPage('/pages/search/search')">
      <view class="bg"></view>
      <view class="text">
        <u-icon name="search"></u-icon>
        <text class="tip">请输入关键词搜索拍品</text>
      </view>
    </view>
    <!-- 轮播图 -->
    <!-- <video src="http://live.r1989.com/test/test.m3u8?auth_key=1601342661-0-0-5328c1da395a4581a5ef516ac17d3679" controls="false"></video> -->
    <view class="slider-box">
      <view class="swiper">
        <swiper class="swiper" :indicator-dots="true" indicator-active-color="#ffffff" autoplay circular :interval="interval" :duration="duration">
						<swiper-item v-for="(item, index) in sliders" :key="index">
								<image :src="item.image" mode="aspectFill" @click="goPage(item.url, item.url_type)"></image>
						</swiper-item>
				</swiper>
      </view>
    </view>
    <!-- 导航图标 -->
		<view class="nav-box">
			<view class="nav-item" v-for="(item, index) in navs" :key="index" @click="goPage(item.url, item.type)">
				<image :src="item.icon" mode="widthFix" v-if="item.is_web == 1"></image>
        <u-icon :name="item.icon" custom-prefix="custom-icon" :color="item.color" v-else></u-icon>
				<text class="ss-block">{{item.title}}</text>
			</view>
		</view>
    
    <view class="inner-box">
      <view class="ad-box" @click="goPage(ad1.url, ad1.url_type)">
        <image :src="ad1.image" mode="widthFix"></image>
      </view>
		</view>
    
    <view class="news">
      <u-section title="公司动态" :showLine="false" font-size="28" line-color="#999999" sub-title="更多" @click="goPage('/pages/news/news?cid=1')"></u-section>
      <view class="items">
        <view class="item flex" v-for="(item, index) in news" :key="index" @click="goPage('/pages/news/detail?id='+item.id)">
          <view class="image flex align-center">
            <image :src="item.thumb?item.thumb:baseUrl+'/default/article_thumb.jpg'" mode="widthFix"></image>
          </view>
          <view class="info u-flex-1">
            <view class="top flex justify-between">
              <view class="title text-black line2"><text>{{item.title}}</text></view>
              <!-- <text class="date text-gray text-sm">{{$u.timeFormat(item.create_time, 'yyyy/mm/dd')}}</text> -->
            </view>
            <view class="intro line1">{{$u.timeFormat(item.create_time, 'yyyy/mm/dd')}}</view>
          </view>
        </view>
      </view>
    </view>
    <!-- 联系方式 -->
    <view class="news contact">
      <u-section title="联系我们" :right="false" :showLine="false" font-size="28" line-color="#999999"></u-section>
      <view class="items">
        <view class="item">
          <text class="ss-block">名称：{{company.company_name || ''}}</text>
          <text class="ss-block" @click="goMap(company.company_lat, company.company_lng, company.company_name, company.company_address)">地址：{{company.company_address || ''}}<text class="text-blue u-padding-left-10" v-if="company.company_lat">查看地图</text></text>
          <text class="ss-block" @click="makeCall(company.company_tel)">电话：{{company.company_tel || ''}}</text>
          <text class="ss-block" @click="copy(company.company_email)">邮箱：{{company.company_email || ''}}</text>
          <text class="ss-block" @click="copy(company.company_url)">网址：{{company.company_url || ''}}</text>
        </view>
      </view>
    </view>
    
    <view class="qrcode" @longpress="saveImg">
      <image :src="baseUrl+'/default/index_qrcode.jpg'" mode="widthFix"></image>
    </view>
    
  </view>
</template>

<script>
  let app = getApp();
  import { getIndexData, getTplIds } from '@/api/api.js';
  import Util from '@/utils/util.js';
  import { HTTP_REQUEST_URL } from '@/config/app.js';
  
  //订阅消息
  import { SUBSCRIBE_MESSAGE, TIPS_KEY } from '@/config/cache';
  
  // import mSearch from '@/components/uni-search/uni-search.vue';
  export default {
		components: {
			// mSearch
		},
    data() {
      return {
        baseUrl: '',
        interval: 3000,
        duration: 500,
				sliders:[],
				navs:[],
				ad1: {},
        news: [],
				status: 'loadmore',
				limit: 15,
				page: 1,
        keyword: "",
        cachePath: '',
        company: []
			}
    },
    onShareAppMessage(res) {
      return {
        title: '北京盛世国际拍卖',
        path: '/pages/index/index'
      }
    },
    onShareTimeline(){
      return {
        title: '北京盛世国际拍卖',
        query: {},
        imageUrl: ''
      }
    },
    onShow() {
      this.current = 0;
    },
    onLoad() {
      this.baseUrl = HTTP_REQUEST_URL;
			uni.showLoading({
				title: '加载中'
			})
			this.getIndex();
      
      //获取订阅消息
      this.getSubscribeTpl();
      
      this.cachePath = `${wx.env.USER_DATA_PATH}`;
      uni.downloadFile({
        url: this.baseUrl+'/default/index_qrcode.jpg',
        filePath: this.cachePath + '/index_qrcode.jpg',
        success(res){
          console.log(res)
        },
        fail(err){
          console.log(err)
        }
      })
      
      wx.showShareMenu({
        menus: ["shareAppMessage", "shareTimeline"]
      });
    },
    methods: {
			getIndex(){
				var that = this;
				getIndexData().then(res=>{
          uni.hideLoading();
				  that.sliders=res.data.sliders;
          that.navs = res.data.navs;
          that.ad1 = res.data.ad1;
          that.news = res.data.news;
          that.company = res.data.company;
				})
			},
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      },
      getSubscribeTpl(){
        let tplIds = uni.getStorageSync(SUBSCRIBE_MESSAGE);
        if (!tplIds || tplIds=='[]'){
          getTplIds().then(res=>{
            console.log(res)
            if (res.data){
              uni.setStorageSync(SUBSCRIBE_MESSAGE, JSON.stringify(res.data));
            }
          })
        }
      },
      saveImg(e){
        console.log(e)
        uni.saveImageToPhotosAlbum({
          filePath: this.cachePath + '/index_qrcode.jpg',
          success(res) {
            uni.showToast({
              title:'保存成功',
              icon: 'none'
            });
          },
          fail(res){
            uni.showToast({
              title:'保存失败！请重试',
              icon: 'none'
            });
          }
        })
      },
      goMap(lat, lng, title, address){
        if(!lat) return;
        var that = this;
        uni.openLocation({
          latitude: parseFloat(lat),
          longitude: parseFloat(lng),
          name: title,
          address: address,
          success: function () {
            console.log('success');
          }
        })
      },
      makeCall(tel){
        uni.makePhoneCall({
          phoneNumber:tel
        })
      },
      copy(val){
        uni.setClipboardData({
          data:val
        })
      }
    },
    onPullDownRefresh(){
      
    },
		onReachBottom() {
			
		}
  }
</script>

<style lang="scss" scoped>
  .container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
		background: $ss-bg-color;
  }
	.search-box{
		height: 95rpx;
		width: 100%;
    color: #fff;
    background: #FFFFFF;
		input{
			width: 685rpx;
			height: 60rpx;
			background: #f5f5f5;
			opacity: 0.3;
			text-align: center;
			margin: 17rpx auto;
			padding: 0 20rpx;
			border-radius: 30rpx;
		}
	}
  .search-box{
    display: flex;
    justify-content: center;
    align-items: center;
    .bg{
      width: 685rpx;
      height: 60rpx;
      background: #f5f5f5;
      text-align: center;
      margin: 17rpx auto;
      padding: 0 20rpx;
      border-radius: 30rpx;
    }
    .text{
      position: absolute;
      color: #CCCCCC;
      left: 55rpx;
    }
    .text .tip{
      margin-left: 10rpx;
    }
  }
	.slider-box{
		width: 100%;
		height: 300rpx;
		.swiper{
			width: 100%;
			height: 300rpx;
		}
		image{
			width: 750rpx;
			height: 300rpx;
		}
	}
	.nav-box{
		width: 750rpx;
    padding: 25rpx 10rpx;
		background: #fff;
		display: flex;
		flex-flow: row wrap;
		justify-content: space-around;
		z-index: 999;
		.nav-item{
			width: 150rpx;
			height: 150rpx;
      display: flex;
      flex-flow: column;
      justify-content: center;
      align-items: center;
      u-icon{
        font-size: 56rpx;
      }
		}
		text{
			font-size: 24rpx;
		}
		image{
			width: 64rpx;
			height: 64rpx;
      margin-bottom: 10rpx;
		}
	}
	.ad-box{
		width: 690rpx;
    height: 255rpx;
		margin: 30rpx auto 0;
		overflow: hidden;
		image{
			width: 100%;
			height: 163rpx;
			border-radius: 15rpx;
		}
	}
  .swiper{
    height: 70rpx;
    // line-height: 70rpx;
    overflow: hidden;
  }
  .search-box{
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .serach{
    
  }
  .serach .content{
    
  }
  .inner-box{
    width: 750rpx;
    padding: 0 30rpx;
  }
  
  .news{
    width: 690rpx;
    margin: 30rpx 30rpx 0 30rpx;
    padding: 20rpx;
    background: #FFFFFF;
    border-radius: 14rpx;
    .title{
      
    }
    .items{
      padding-top: 20rpx;
      .item{
        margin-bottom: 30rpx;
      }
      .image{
        width: 140rpx;
        height: 120rpx;
        overflow: hidden;
        border-radius: 8rpx;
        image{
        }
      }
      .info{
        margin-left: 15rpx;
        .title{
          width: 480rpx;
          line-height: 50rpx;
          font-size: $uni-font-size-lg;
        }
        .intro{
          line-height: 34rpx;
          font-size: $uni-font-size-sm;
          color: $u-type-info-dark;
        }
      }
    }
  }
  
  .qrcode{
    width: 690rpx;
    margin: 30rpx;
    border-radius: 14rpx;
    image{
      width: 100%;
    }
  }
</style>
