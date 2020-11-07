<template>
	<view class="container">
    <view class="partner bg-white">
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
    <u-loadmore bg-color="#F4F5F6" font-size="24" v-if="office.length>0" :status="loadStatus" />
	</view>
</template>

<script>
  import { HTTP_REQUEST_URL } from '@/config/app';
  import { getPartnerData } from '@/api/api.js';
	export default {
		data() {
			return {
        loadStatus: 'loadmore',
        page:1,
        limit: 10,
        partner: [],
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
        title: '合作单位-北京盛世国际拍卖',
        path: '/pages/system/about/partner'
      }
    },
    onShareTimeline(){
      return {
        title: '合作单位-北京盛世国际拍卖',
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
        getPartnerData().then(res=>{
          let list = res.data.partner;
          that.partner = that.partner.concat(list);
          let loadend = list.length < that.limit;
          if(loadend){
            that.loadStatus = 'nomore';
          }else{
            that.page++;
          }
          uni.hideLoading();
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
