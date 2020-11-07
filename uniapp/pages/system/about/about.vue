<template>
	<view class="container">
		<view class="about bg-white">
      <u-section title="公司简介" :right="false" :showLine="false"></u-section>
      <view class="content">
          <u-parse :html="about"></u-parse>
          <u-divider class="more" @click="showMore">查看更多</u-divider>
      </view>
    </view>
    
    <view class="company bg-white">
      <u-section title="全国各地联络处" @click="goPage('/pages/system/about/office')" :showLine="false"></u-section>
      <view class="items">
        <view class="item" v-for="(item, index) in office" :key="index">
          <text class="ss-block name text-red">{{item.type}}：{{item.name}}</text>
          <text class="ss-block" @click="goMap(item.lat, item.lng, item.title, item.address)">地址：{{item.province}}{{item.city}}{{item.district}}{{item.address}}<text class="text-blue u-padding-left-10" v-if="item.lat">查看地图</text></text>
          <text class="ss-block" @click="makeCall(item.contact_tel)">电话：{{item.contact_tel}}</text>
          <text class="ss-block" @click="copy(item.email)">邮箱：{{item.email}}</text>
          <u-line v-if="index+1<office.length" margin="30rpx 0" color="#eeeeee" />
        </view>
      </view>
    </view>
    
    <view class="partner bg-white">
      <u-section title="合作单位" @click="goPage('/pages/system/about/partner')" :showLine="false"></u-section>
      <view class="items">
        <u-grid :col="3" :border="false">
        		<u-grid-item v-for="(item, index) in partner" :key="index">
              <view class="image flex align-center">
                <image :src="item.logo" mode="widthFix"></image>
              </view>
        			<view class="grid-text">{{item.name}}</view>
        		</u-grid-item>
        	</u-grid>
      </view>
    </view>
    
    <!-- 客服 -->
    <view class="kefu" @click="goPage('/pages/system/about/contact')">
      <image :src="baseUrl+'/uploads/attach/2020/09/20200911/f7303b1758628fc4fd90274f7873ee83.png'" mode="widthFix"></image>
    </view>
    
    <!-- 弹窗内容 -->
    <u-popup v-model="pop" mode="center" closeable custom-style="overflow:auto;" length="90%" zoom border-radius="10">
      <scroll-view class="pop-content" scroll-y="true">
        <jyf-parser :html="nowHtml" ref="about" :tag-style="tagStyle"></jyf-parser>
      </scroll-view>
    </u-popup>
    
	</view>
</template>

<script>
  import { HTTP_REQUEST_URL } from '@/config/app';
  import { getAboutData } from '@/api/api.js';
  import Util from '@/utils/util.js';
	export default {
		data() {
			return {
        office: [],
        partner: [],
				about: "",
        baseUrl: HTTP_REQUEST_URL,
        nowHtml: '',
        pop: false
			};
		},
    onLoad() {
      this.getData();
      wx.showShareMenu({
        menus: ["shareAppMessage", "shareTimeline"]
      });
    },
    onShareAppMessage(res) {
      return {
        title: '关于我们-北京盛世国际拍卖',
        path: '/pages/system/about/about'
      }
    },
    onShareTimeline(){
      return {
        title: '关于我们-北京盛世国际拍卖',
        imageUrl: ''
      }
    },
    methods:{
      getData(){
        var that = this;
        uni.showLoading();
        getAboutData().then(res=>{
          that.about = res.data.about;
          that.office = res.data.office;
          that.partner = res.data.partner;
          that.nowHtml = res.data.content;
          uni.hideLoading();
        })
      },
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      },
      showMore(){
        this.pop = true;
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
    }
	}
</script>

<style lang="scss">
.container{
  background: $ss-bg-color;
  margin: 30rpx;
}
.about{
  padding: 20rpx;
  border-radius: 14rpx;
  .content{
    margin-top: 20rpx;
  }
}
.company{
  margin-top: 30rpx;
  padding: 20rpx;
  border-radius: 14rpx;
  .items{
    margin-top: 20rpx;
    .item{
      line-height: 38rpx;
    }
    .item:last-child{
      border: none;
    }
    .name{
      margin: 10rpx 0;
    }
  }
}
.partner{
  margin-top: 30rpx;
  padding: 20rpx;
  border-radius: 14rpx;
  .items{
    .image{
      width: 200rpx;
      height: 100rpx;
      overflow: hidden;
    }
    image{
      width: 200rpx;
    }
  }
}
</style>
