<template>
	<view class="container">
    <view class="content">
      <view class="rich">
        <jyf-parser :html="content" ref="contact" :tag-style="tagStyle"></jyf-parser>
      </view>
    </view>
    
    <view class="navH"></view>
    <view class="navigation">
    	<view class="left flex justify-around">
    		<view class="item flex flex-direction align-center">
    			<u-icon name="server-fill" :size="40" color="#18b566"></u-icon>
    			<view class="text u-line-1">小程序客服</view>
    	    <button class="share" :plain="true" type="default" open-type="contact"></button>
    		</view>
    		<view v-if="kefu_tel" class="item flex flex-direction align-center" @click="call">
    			<u-icon name="phone" :size="40" color="#fa3534"></u-icon>
    			<view class="text u-line-1">电话咨询</view>
    		</view>
    		<view class="item flex flex-direction align-center">
    			<u-icon name="share" :size="40" color="#2979ff"></u-icon>
    			<view class="text u-line-1">分享</view>
    	    <button class="share" :plain="true" type="default" open-type="share"></button>
    		</view>
    	</view>
    </view>
    
	</view>
</template>

<script>
  import { getKfData } from '@/api/api.js';
  import parser from "@/components/jyf-parser/jyf-parser";
	export default {
    components: {
      "jyf-parser": parser
    },
		data() {
			return {
        content: '',
        kefu_tel: '',
			};
		},
    onLoad(option) {
      this.getData();
      wx.showShareMenu({
        menus: ["shareAppMessage", "shareTimeline"]
      });
    },
    onShareAppMessage(res) {
      return {
        title: '联系我们-北京盛世国际拍卖',
        path: '/pages/system/about/contact'
      }
    },
    onShareTimeline(){
      return {
        title: '联系我们-北京盛世国际拍卖',
        imageUrl: ''
      }
    },
    methods:{
      call(){
        uni.makePhoneCall({
          phoneNumber: this.kefu_tel
        })
      },
      getData(){
      	var that = this;
        uni.showLoading();
      	getKfData().then(res=>{
          console.log(res)
      	  that.content = res.data.content;
      	  that.kefu_tel = res.data.kefu_tel;
      	  uni.hideLoading();
      	})
      },
    }
	}
</script>

<style lang="scss">
.container{
  background: #FFFFFF;
  min-height: 100vh;
}
.content{
  margin-bottom: 20rpx;
  padding: 20rpx;
  background: #FFFFFF;
  .rich{
    padding: 30rpx 0;
  }
}

.navigation {
  position: fixed;
  width: 100%;
  bottom: 0;
	display: flex;
	margin-top: 100rpx;
	border-top: solid 2rpx #EEEEEE;
	background-color: #ffffff;
	padding: 16rpx 0;
	.left {
    width: 100%;
		font-size: 20rpx;
		.item {
      position: relative;
			margin: 0 30rpx;
			&.car {
				text-align: center;
				position: relative;
				.car-num {
					position: absolute;
					top: -10rpx;
					right: -10rpx;
				}
			}
		}
    .price{
      font-size: $uni-font-size-lg;
      .num{
        color: $u-type-error;
        font-weight: 700;
        font-size: $uni-font-size-super;
      }
    }
	}
}
.share{
  width: 70rpx;
  height: 100rpx;
  border: 1px solid red;
  position: absolute;
  top: -10rpx;
  left: -10rpx;
  border: none !important;
  z-index: 99;
}
</style>
