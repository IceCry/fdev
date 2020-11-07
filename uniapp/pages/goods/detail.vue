<template>
	<view class="container">
		<view class="slider">
      <u-swiper :list="info.slider_image" mode="rect" height="750" borderRadius="0"></u-swiper>
    </view>
    <view class="title-box">
      <view class="title">
        <text>{{info.title}}</text>
      </view>
      <view class="price">
        <text class="sale_price text-red">￥{{info.sale_price || ''}}</text>
        <text class="text-gray line-through u-padding-left-10" v-if="info.market_price>0">￥{{info.market_price}}</text>
      </view>
      <view class="intro">
        <view class="u-flex-1">
          <text class="value text-gray line3">{{info.intro}}</text>
        </view>
      </view>
      <view class="info-box flex justify-between">
        <view class="tags">
          <u-tag text="包邮" v-if="info.postage_price == 0" size="mini" type="success" />
          <text class="text-gray" v-else>运费：￥{{info.postage_price}}</text>
        </view>
        <view class="tags">
          <text class="price text-gray">销量：{{info.sale_total}}</text>
        </view>
      </view>
    </view>
    
    <view class="params">
      <view class="item flex" v-for="(item, index) in info.params" :key="index">
        <text class="name">{{item.var}}</text>
        <text class="value u-flex-1">{{item.val}}</text>
      </view>
    </view>
    
    <view class="content">
      <u-section title="商品详情" lineColor="#cccccc" :right="false"></u-section>
      <view class="rich">
        <jyf-parser :html="info.content" ref="goods" :tag-style="tagStyle"></jyf-parser>
      </view>
    </view>
    
    <!-- 使用组件 -->
    <!-- <view class="likes">
      <u-section title="推荐商品" :right="false"></u-section>
      <view class="items">
        
      </view>
    </view> -->
    
    <!-- 底部菜单 -->
    <view class="navH"></view>
    <view class="navigation">
    	<view class="left">
    		<view class="item text-center" @click="goPage('/pages/system/about/contact')">
    			<u-icon name="server-fill" :size="40" :color="$u.color['contentColor']"></u-icon>
    			<view class="text u-line-1">客服</view>
    		</view>
    		<view class="item text-center" @click="favorite(0)" v-if="info.is_favorite>0">
    			<u-icon name="heart-fill" :size="40" color="#fa3534"></u-icon>
    			<view class="text u-line-1 text-red">已收藏</view>
    		</view>
        <view class="item text-center" @click="favorite(1)" v-else>
        	<u-icon name="heart" :size="40" :color="$u.color['contentColor']"></u-icon>
        	<view class="text u-line-1">收藏</view>
        </view>
    		<view class="item car text-center" @click="goPage('/pages/cart/cart', 'switch')">
    			<u-badge class="car-num" :count="info.cart_num || 0" type="error" :offset="[-3, -6]"></u-badge>
    			<u-icon name="shopping-cart" :size="40" :color="$u.color['contentColor']"></u-icon>
    			<view class="text u-line-1">购物车</view>
    		</view>
    	</view>
    	<view class="right">
    		<view class="cart btn u-line-1" @click="goCart(0)">加入购物车</view>
    		<view class="buy btn u-line-1" @click="goCart(1)">立即购买</view>
    	</view>
    </view>
	</view>
</template>

<script>
  import { doFavorite } from '@/api/api.js';
  import { getGoodsInfo } from "@/api/goods.js";
  import { cartAdd, orderConfirm } from "@/api/order.js";
  import Util from '@/utils/util.js';
  import parser from "@/components/jyf-parser/jyf-parser";
  import { mapGetters } from "vuex";
	export default {
    components: {
      "jyf-parser": parser
    },
		computed: mapGetters(['isLogin', 'uid']),
		data() {
			return {
        uuid: '',
				info: [],
			};
		},
    onLoad(option) {
      this.uuid = option.uuid || '';
      this.getData();
      wx.showShareMenu({
        menus: ["shareAppMessage", "shareTimeline"]
      });
    },
    onShareAppMessage(res) {
      return {
        title: this.info.title+'-北京盛世国际拍卖',
        path: '/pages/goods/detail?uuid='+this.uuid
      }
    },
    onShareTimeline(){
      return {
        title: this.info.title+'-北京盛世国际拍卖',
        imageUrl: this.info.thumb
      }
    },
    methods:{
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      },
      getData(){
      	var that = this;
        uni.showLoading();
      	getGoodsInfo({uuid:this.uuid, uid:this.uid || 0}).then(res=>{
          uni.hideLoading();
          that.info = res.data.info;
          uni.setNavigationBarTitle({
            title: res.data.info.title
          });
      	})
      },
      goCart(type){
        //0加入购物车 1立即购买
        var that = this;
        let cart_num = this.info.cart_num;
        let one = 0;
        let num = 1; //购买/加入购物车商品数量
        if(type==1){
          one = 1;
        }
        
        //判断是否有库存
        if(this.info.stock < num){
          return that.$util.Tips({
            title: '库存不足'
          });
        }
        
        if(this.isLogin === false){
          return this.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
        }
        
        //均需加入购物车
        cartAdd({id: this.info.id, num: num, one: one}).then(res=>{
          let cartId = res.data.cartId;
          
          if(type==0){
            that.$util.Tips({
              title: res.msg
            });
            that.info.cart_num = cart_num + 1;
          }
          
          //默认勾选此id
          var ids = uni.getStorageSync('checkedCart') || [];
          //仅记录选中的商品
          if(!ids.includes(cartId)){
            ids.push(Number(cartId));
          }
          uni.setStorageSync('checkedCart', ids);
          //立即购买则跳转
          if(type==1){
            uni.navigateTo({
            	url: '/pages/order/confirm?cartIds='+cartId
            })
          }
        }).catch(err => {
          return that.$util.Tips({
            title: err
          });
        });
        
        
      },
      favorite(type){
        if (this.isLogin === false) {
          return this.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
        } else {
          let that = this;
          doFavorite({mid:this.info.id, mtable: 'goods', type:type}).then(res => {
            that.$set(that.info, 'is_favorite', !that.info.is_favorite);
          })
        }
      }
    }
	}
</script>

<style lang="scss">
.container{
  background: $ss-bg-color;
}
.title-box{
  background: #FFFFFF;
  padding: 30rpx 20rpx;
}
.title{
  line-height: 36rpx;
  font-size: $uni-font-size-lg;
  font-weight: 700;
}
.intro{
  line-height: 34rpx;
}
.info-box{
  margin-top: 10rpx;
  .time{
    font-size: $uni-font-size-sm;
  }
}
.price-box{
  margin: 20rpx 0;
  padding: 20rpx;
  background: #FFFFFF;
  .price{
    font-size: $uni-font-size-lg;
    font-weight: 700;
  }
  .title{
    font-size: $uni-font-size-mini;
    color: $ss-font-dark;
  }
}
.params{
  margin: 20rpx 0;
  padding: 20rpx;
  background: #FFFFFF;
  .item{
    margin-bottom: 16rpx;
    font-size: $uni-font-size-sm;
    line-height: 30rpx;
    .name{
      color: $ss-font-dark;
      width: 110rpx;
      padding-right: 10rpx;
    }
    .value{
      font-size: $uni-font-size-sm;
      color: $ss-font-normal;
    }
  }
}
.special{
  margin: 20rpx 0;
  padding: 20rpx;
  background: #FFFFFF;
  .item{
    margin: 20rpx 0;
    font-size: $uni-font-size-sm;
    line-height: 30rpx;
    .name{
      color: $ss-font-dark;
      width: 110rpx;
      padding-right: 10rpx;
    }
    .value{
      font-size: $uni-font-size-sm;
      color: $ss-font-normal;
    }
    .section{
      width: 100%;
    }
  }
}
.bit{
  margin-bottom: 20rpx;
  padding: 20rpx;
  background: #FFFFFF;
  .item{
    margin-top: 30rpx;
    font-size: $uni-font-size-sm;
    line-height: 30rpx;
    .info{
      padding-left: 16rpx;
    }
    .image{
      width: 80rpx;
      height: 80rpx;
      overflow: hidden;
      border-radius: 50%;
    }
    .name{
      color: $ss-font-dark;
      width: 110rpx;
      padding-right: 10rpx;
      width: 500rpx;
    }
    .price{
      width: 100rpx;
      font-size: $uni-font-size-sm;
      color: $ss-font-normal;
    }
  }
}

.content{
  margin-bottom: 20rpx;
  padding: 20rpx;
  background: #FFFFFF;
  .rich{
    padding: 30rpx 0;
  }
}
.likes{
  margin-bottom: 20rpx;
  padding: 20rpx;
  background: #FFFFFF;
}

.navigation {
  width: 100%;
  position: fixed;
  left: 0;
  bottom: 0;
	display: flex;
	margin-top: 100rpx;
	border: solid 2rpx #f2f2f2;
	background-color: #ffffff;
	padding: 6rpx 0;
	.left {
		display: flex;
		font-size: 20rpx;
		.item {
			margin: 0 30rpx;
			&.car {
				text-align: center;
				position: relative;
				.car-num {
					position: absolute;
					top: 0;
					right: -10rpx;
				}
			}
		}
	}
	.right {
		display: flex;
		font-size: 28rpx;
		align-items: center;
		.btn {
			line-height: 66rpx;
			padding: 0 30rpx;
			border-radius: 36rpx;
			color: #ffffff;
		}
		.cart {
			background-color: #ed3f14;
			margin-right: 30rpx;
		}
		.buy {
			background-color: #ff7900;
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
.money{
  font-size: 60rpx;
  color: $u-type-warning;
  position: relative;
  
  .close{
    position: absolute;
    top: 20rpx;
    right: 20rpx;
    line-height: 28rpx;
    font-size: 28rpx;
  }
}
.tips{
  width: 600rpx;
  height: 80rpx !important;
  line-height: 80rpx !important;
  margin: 20rpx auto;
  background: $u-type-error;
  color: #FFFFFF;
}
.u-content{
  line-height: 1 !important;
}
.sale_price{
  font-size: $uni-img-size-sm;
}
</style>
