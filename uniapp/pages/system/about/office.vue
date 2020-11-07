<template>
	<view class="container">
    <view class="company bg-white">
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
    <u-loadmore bg-color="#F4F5F6" font-size="24" v-if="office.length>0" :status="loadStatus" />
	</view>
</template>

<script>
  import { HTTP_REQUEST_URL } from '@/config/app';
  import { getOfficeData } from '@/api/api.js';
	export default {
		data() {
			return {
        loadStatus: 'loadmore',
        page:1,
        limit: 10,
        office: [],
        baseUrl: HTTP_REQUEST_URL,
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
        title: '全国各地联络处-北京盛世国际拍卖',
        path: '/pages/system/about/office'
      }
    },
    onShareTimeline(){
      return {
        title: '全国各地联络处-北京盛世国际拍卖',
        imageUrl: ''
      }
    },
    methods:{
      getData(){
        var that = this;
        if(this.loadStatus == 'nomore'){
          return this.$util.Tips({
            title: "没有更多了"
          });
        }
        uni.showLoading();
        getOfficeData().then(res=>{
          let list = res.data.office;
          that.office = that.office.concat(list);
          let loadend = list.length < that.limit;
          if(loadend){
            that.loadStatus = 'nomore';
          }else{
            that.page++;
          }
          uni.hideLoading();
        }).catch(err=>{
          console.log(err)
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
    }
	}
</script>

<style lang="scss">
.container{
  background: $ss-bg-color;
  margin: 30rpx;
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
    .name{
      margin: 10rpx 0;
    }
  }
}
</style>
