<template>
	<view class="content">
    
    <view class="tab bg-gray ">
    	<u-tabs-swiper class='u-skeleton-fillet' active-color="#fa3534" bg-color='#f1f1f1' :isScroll="false" ref="uTabs" :list="tabs" :current="current" @change="tabsChange" :isScrool="false"></u-tabs-swiper>
    </view>
    <swiper class="swiper bg-white radius-bbg" style="height: 100%;width: 100%;" :current="swiperCurrent" @transition="transition" @animationfinish="animationfinish">
    	<swiper-item v-for="(item, index) in tabs" :key="index" >
    		<scroll-view class="scroll-box" scroll-y :scroll-top="scrollTop" style="height: 100%;width: 100%;" @scroll="scroll" @scrolltolower="onreachBottom(index)">
    			<view v-for="(item1,index1) in swiperData" :key='index1' class="item flex">
            <!-- 商品 -->
            <block class="ask-box" v-if="current==0">
              <view class="avatar flex align-center">
                <image :src="item1.goods.thumb" mode="widthFix"></image>
              </view>
              <view class="info flex-sub" @click="goDetail(item1.goods.uuid, index)">
                <view class="name line1">
                  <text class="text-bold line1">{{item1.goods.title}}</text>
                </view>
                <view>
                  <text class="text-red">￥{{item1.goods.sale_price}}</text>
                </view>
                <view class="con line1">
                  <text class="text-gray text-sm">收藏时间：{{item1.create_time}}</text>
                </view>
              </view>
            </block>
            <!-- 拍品 -->
            <block class="ask-box" v-else-if="current==1">
              <view class="avatar flex align-center">
                <image :src="item1.collection.thumb" mode="widthFix"></image>
              </view>
              <view class="info flex-sub" @click="goDetail(item1.collection.uuid, index)">
                <view class="name line1">
                  <text class="text-bold line1">{{item1.collection.title}}</text>
                </view>
                <view>
                  <text class="text-red">最高出价：￥{{item1.collection.max_price}}</text>
                </view>
                <view class="con line1">
                  <text class="text-gray text-sm">收藏时间：{{item1.create_time}}</text>
                </view>
              </view>
            </block>
            <block v-else-if="current==2">
              <view class="cu-item" @click="goDetail(item1.article.id, index)">
              	<view class="padding-tb-sm">
                  <view class="name line1">
                    <text class="text-bold line1">{{item1.article.title}}</text>
                  </view>
              		<view class="text-gray text-sm line2">{{item1.article.intro}}</view>
                  <view class="text-gray text-sm">收藏时间：{{item1.create_time}}</view>
              	</view>
              </view>
            </block>
    			</view>
          <u-loadmore bg-color="#ffffff" font-size="24" v-if="swiperData.length>0" :status="status" />
          <u-empty v-if="swiperData.length==0" text="暂无数据" mode="list"></u-empty>
    		</scroll-view>
    	</swiper-item>
    </swiper>
	</view>
</template>

<script>
  import { getMyFavorite } from '@/api/user.js';
  import Util from '@/utils/util.js';
	export default {
		data() {
			return {
				tabs: [{name: '商品'}, {name: '拍品'}, {name: '文章'}],
				current: 0,
				swiperCurrent: 0,
        dx: 0,
        swiperHeight:'800rpx',
        scrollTop: 0,
        old: {
        	scrollTop: 0
        },
        loading:true,
        swiperData:[],
				status: 'loadmore',
				limit: 15,
				page: 1,
			};
		},
		onReady() {
			//this.getSwiperHeight();
		},
    onLoad() {
      //默认商品数据
      this.getData(0);
    },
    methods:{
      async getSwiperHeight(){
      	let windowHeight = this.$until.height(1)
        console.log(windowHeight)
      	let tab = await this.$u.getRect('.tab');
      	this.swiperHeight = (windowHeight-tab.height)+'px'
      },
			tabsChange(index) {
				this.swiperCurrent = index;
        this.swiperData = [];
				this.scrollTop = this.old.scrollTop
				this.$nextTick(()=>{
					this.scrollTop = 0
				});
			},
			// swiper-item左右移动，通知tabs的滑块跟随移动
			transition(e) {
				let dx = e.detail.dx;
				this.$refs.uTabs.setDx(dx);
			},
			// 由于swiper的内部机制问题，快速切换swiper不会触发dx的连续变化，需要在结束时重置状态
			// swiper滑动结束，分别设置tabs和swiper的状态
			animationfinish(e) {
				let current = e.detail.current;
        this.swiperData = [];
        //暂定数据重新加载
        this.page = 1;
        this.status = 'loadmore';
        this.getData(current);
				this.$refs.uTabs.setFinishCurrent(current);
				this.swiperCurrent = current;
				this.current = current;
				this.scrollTop = this.old.scrollTop
				this.$nextTick(()=>{
					this.scrollTop = 0
				});
			},
			// scroll-view到底部加载更多
			onreachBottom(index) {
        if(this.status == 'nomore') return that.$util.Tips({
          title: '没有更多了'
        });
        this.status = 'loading';
        this.getData(this.current);
			},
			scroll(e) {
				this.old.scrollTop = e.detail.scrollTop
			},
      getData(index){
        var that = this;
        var mtable = 'goods';
        if(index==1){
          mtable = 'collection';
        }else if(index==2){
          mtable = 'article';
        }
        uni.showLoading();
        getMyFavorite({mtable: mtable, page:this.page, limit: this.limit}).then(res=>{
          let list = res.data.lists;
          that.swiperData = that.swiperData.concat(list);
          let loadend = list.length < that.limit;
          if(loadend){
            that.status = 'nomore';
          }else{
            that.page++;
          }
          uni.hideLoading();
        })
      },
      goDetail(id, index){
        //根据index决定跳转位置
        if(index==0){
          uni.navigateTo({
            url: '/pages/goods/detail?uuid='+id
          })
        }else if(index==1){
          uni.navigateTo({
            url: '/pages/auction/detail?uuid='+id
          })
        }else if(index==2){
          uni.navigateTo({
            url: '/pages/news/detail?id='+id
          })
        }
      }
    }
	}
</script>


<style>
/* #ifndef H5 */
page {
	height: 100%;
	background-color: #f2f2f2;
}
/* #endif */
</style>

<style lang="scss">
.content {
	display: flex;
	flex-direction: column;
	height: calc(100vh - var(--window-top));
	width: 100%;
}
.swiper-box {
	flex: 1;
}
.swiper-item {
	height: 100%;
}
.swiper{
  padding: 0 30rpx;
}
.item{
  width: 100%;
  padding: 20rpx 0;
  border-bottom: 1px solid $ss-border-color;
  .avatar{
    width: 120rpx;
    height: 120rpx;
    image{
      width: 120rpx;
      border-radius: 6rpx;
    }
  }
  .info{
    margin-left: 15rpx;
    .name{
      display: flex;
      width: 100%;
    }
  }
}
.scroll-box .item:last-child{
  border: none;
}

.ask-box .info, .padding-tb-sm{
  width: 690rpx;
}
</style>
