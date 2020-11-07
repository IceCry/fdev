<template>
	<view class="container">
		<view class="search-box">
				<mSearch class="mSearch-input-box" :mode="2" button="inside" :placeholder="defaultKeyword" @search="doSearch(false)" @input="inputChange" :isFocus="false" @confirm="doSearch(false)" v-model="keyword"></mSearch>
		</view>
    <view class="search-keyword">
    	<scroll-view class="keyword-list-box" v-if="keywordList.length>0" scroll-y>
    		<block v-for="(row,index) in keywordList" :key="index">
    			<view class="keyword-entry" hover-class="keyword-entry-tap" >
    				<view class="keyword-text line2" @tap.stop="goPage('/pages/news/detail?id='+keywordList[index].id)">
    					<rich-text :nodes="row.htmlStr"></rich-text>
    				</view>
    				<!-- <view class="keyword-img" @tap.stop="setKeyword(keywordList[index].keyword)">
    					<image src="/static/HM-search/back.png"></image>
    				</view> -->
    			</view>
    		</block>
    	</scroll-view>
      <scroll-view scroll-y="true" v-else>
        <u-empty v-if="showResult" mode="search"></u-empty>
      </scroll-view>
    </view>
    
		<view class="plan">
      
      <swiper v-if="sliders.length>0" class="swiper" :indicator-dots="true" circular autoplay interval="3000" duration="1000">
        <swiper-item v-for="(item, index) in sliders" :key="index">
          <view class="cu-card case" @click="goDetail(item)">
          	<view class="shadow">
          		<view class="image flex align-center">
          			<image :src="item.thumb" mode="aspectFill"></image>
          			<view v-if="item.tag" class="cu-tag bg-blue">{{item.tag}}</view>
          			<view class="cu-bar bg-shadeBottom"> <text class="line1">{{item.title}}</text></view>
          		</view>
          	</view>
          </view>
        </swiper-item>
      </swiper>
      
			<view class="article-box margin-top-sm" v-if="articles.length>0">
				<block v-for="(item, index) in articles" :key="index">
			    <view class="media-item view" v-if="item.title" @click="goDetail(item)">
			        <view class="view flex" :style="{flexDirection: (item.article_type === 1 || item.article_type === 2)?(item.article_type === 2 ?'row':'row-reverse'):'column' }">
			            <text class="media-title" :class="{'media-title2': item.article_type === 1 || item.article_type === 2}">{{item.title}}</text>
			            <view v-if="item.thumb" class="image-section flex-row" :class="{'image-section-right': item.article_type === 2, 'image-section-left': item.article_type === 1}">
			                <image class="image-list1" :class="{'image-list2': item.article_type === 1 || item.article_type === 2}" v-if="item.thumb" :src="item.thumb" mode="widthFix"></image>
			                <!-- <image class="image-list3" v-if="item.image_list" :src="source.url" v-for="(source, i) in item.image_list"
			                    :key="i" /> -->
			            </view>
			        </view>
			        <view class="media-foot flex-row">
			            <view class="media-info flex-row">
			                <!-- <text class="info-text">{{item.author}}</text> -->
			                <text class="info-text">{{item.click}}次浏览</text>
			                <text class="info-text">{{item.create_time}}</text>
			            </view>
			            <!-- <view class="close-view" @click.stop="close">
			                <view class="close-l close-h"></view>
			                <view class="close-l close-v"></view>
			            </view> -->
			        </view>
			        <!-- <view class="media-item-line" v-if="articles.length!=(index+1)" style="position: absolute;"></view> -->
			    </view>
			  </block>
			</view>
      
		</view>
		
		<u-empty margin-top="300" v-if="articles.length==0" mode="news"></u-empty>
		<u-loadmore font-size="24" v-if="articles.length>0" :status="loadStatus" />
	</view>
</template>

<script>
	import mSearch from '@/components/mh-search/mh-search.vue';
  import { getArticleList, searchArticle } from '@/api/article.js';
  import Util from '@/utils/util.js';
  import { HTTP_REQUEST_URL } from '@/config/app.js';
  
	export default {
		components: {
			mSearch
		},
		data() {
			return {
        baseUrl: '',
        defaultKeyword: "请输入您想查找的文章",
        keyword: "",
        keywordList: [],
        isShowKeywordList: false,
        
        current: 0,
        cid: 1,
        articles: [],
        sliders: [],
				loadStatus: 'loadmore',
				limit: 15,
				page: 1
			};
		},
    onLoad() {
      this.baseUrl = HTTP_REQUEST_URL;
      this.getData();
      
      wx.showShareMenu({
        menus: ["shareAppMessage", "shareTimeline"]
      });
    },
    onShareAppMessage(res) {
      return {
        title: '公司动态-北京盛世国际拍卖',
        path: '/pages/news/news'
      }
    },
    onShareTimeline(){
      return {
        title: '公司动态-北京盛世国际拍卖',
        imageUrl: ''
      }
    },
		methods:{
      doSearch(keyword) {
        
      },
      //监听输入
			inputChange(event) {
        var that = this;
				//兼容引入组件时传入参数情况
				var keyword = event.detail?event.detail.value:event;
				if (!keyword) {
					this.keywordList = [];
					this.isShowKeywordList = false;
          this.showResult = false;
					return;
				}
				this.isShowKeywordList = true;
        this.showResult = true;
				//以下示例截取淘宝的关键字，请替换成你的接口
        //直接显示搜索结果，直接跳转，限制显示20条
        searchArticle({keyword: this.keyword}).then(res=>{
          this.keywordList = [];
          this.keywordList = this.drawCorrelativeKeyword(res.data.lists, keyword);
        });
			},
			//高亮关键字
			drawCorrelativeKeyword(keywords, keyword) {
				var len = keywords.length,
					keywordArr = [];
				for (var i = 0; i < len; i++) {
					var row = keywords[i];
					//定义高亮#9f9f9f
          var html = row['title'].replace(keyword, "<span style='color: #aa0000;'>" + keyword + "</span>");
					
					html = '<div>' + html + '</div>';
					var tmpObj = {
            name: row['title'],
						keyword: row['title'],
            uuid: row['uuid'],
            id: row['id'],
						htmlStr: html
					};
					keywordArr.push(tmpObj)
				}
				return keywordArr;
			},
			getData(){
        var that = this;
        if(this.loadStatus=='nomore'){
          return this.$util.Tips({
            title: "没有更多了"
          });
        }
        uni.showLoading();
        getArticleList({cate_id: this.cid, page: this.page, limit: this.limit}).then(res=>{
          let list = res.data;
          that.articles = that.articles.concat(list.lists);
          let loadend = list.lists.length < that.limit;
          if(loadend){
            that.loadStatus = 'nomore';
          }else{
            that.page++;
          }
          uni.hideLoading();
        })
      },
      change(e){
        this.cid = this.cates[e].id;
        this.current = e;
        this.page =1;
        this.articles = [];
        this.getData();
      },
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      },
      goDetail(detail) {
        //区分文章内容类型
        if(detail.is_mp){
          uni.navigateTo({
            url: '/pages/system/web-view?src='+encodeURIComponent(detail.mp_url)
          })
        }else{
          uni.navigateTo({
          	url: '/pages/news/detail?id=' + detail.id
          });
        }
      },
		},
		onReachBottom() {
      if(this.loadStatus == 'nomore') return uni.showToast({
        icon: 'none',
        title: '没有更多了'
      });
      this.loadStatus = 'loading';
      this.getData();
		}
	}
</script>

<style lang="scss">
  .container{
    background: #FFFFFF;
    min-height: 100vh;
  }
  .search-box{
  	height: 100rpx;
  	width: 100%;
  	padding: 20rpx 0;
  	display: flex;
    justify-content: center;
  	align-items: center;
  	background: #f5f5f5;
  	.input{
      background: #FFFFFF;
  		/* width: 685rpx;
  		height: 60rpx;
  		line-height: 60rpx;
  		background: #fff;
  		opacity: 0.5;
  		text-align: center;
  		margin: 0 auto;
  		padding: 0 20rpx;
  		border-radius: 30rpx;
  		color: $ss-blue; */
  	}
  	u-icon{
  		display: inline;
  		margin-right: 5rpx;
  	}
  }
  /* m-search{
  	width: 685rpx;
  	height: 60rpx;
  	line-height: 60rpx;
  	opacity: 0.6;
  	text-align: center;
  	margin: 0 auto;
  	padding: 0 20rpx;
  	border-radius: 30rpx;
  	color: $ss-blue;
  } */
  
  .serach .content .content-box .input.center{
    width: 300rpx !important;
  }
  
	.tab-box{
		border-bottom: 1px solid $ss-border-color;
	}
  
  .article-box{
  	// padding: 25rpx 30rpx;
  	background: #fff;
  }
  .view {
      flex-direction: column;
  }
  
  .flex-row {
      flex-direction: row;
  }
  
  .flex-col {
      flex-direction: column;
  }
  
  .list-cell {
      padding: 0 30rpx;
  }
  
  .uni-list-cell-hover {
      background-color: #eeeeee;
  }
  
  .media-item {
      position: relative;
      flex: 1;
      flex-direction: column;
      padding: 20rpx 30rpx 21rpx 30rpx;
      border-bottom: 1px solid #ebebeb;
  }
  .media-item:last-child{
    border: none;
  }
  
  .media-item-line {
      position: absolute;
      left: 30rpx;
      right: 30rpx;
      bottom: 0;
      height: 1rpx;
      background-color: #ebebeb;
  }
  
  .media-image-right {
      flex-direction: row;
  }
  
  .media-image-left {
      flex-direction: row-reverse;
  }
  
  .media-title {
      flex: 1;
  }
  
  .media-title {
      lines: 3;
      text-overflow: ellipsis;
      font-size: 30rpx;
      color: #555555;
  }
  
  .media-title2 {
      flex: 1;
      margin-top: 6rpx;
      line-height: 40rpx;
  }
  
  .image-section {
      margin-top: 20rpx;
      flex-direction: row;
      justify-content: space-between;
  }
  
  .image-section-right {
      margin-top: 0rpx;
      margin-left: 10rpx;
      width: 225rpx;
      height: 146rpx;
  }
  
  .image-section-left {
      margin-top: 0rpx;
      margin-right: 10rpx;
      width: 225rpx;
      height: 146rpx;
  }
  
  .image-list1 {
      width: 690rpx;
      height: 481rpx;
  }
  .image-section{
    overflow: hidden;
  }
  .image-list2 {
      width: 225rpx;
      height: 146rpx;
      min-height: 146rpx;
  }
  
  .image-list3 {
      width: 225rpx;
      height: 146rpx;
  }
  
  .media-info {
      flex-direction: row;
      align-items: center;
  }
  
  .info-text {
      margin-right: 20rpx;
      color: #999999;
      font-size: 24rpx;
  }
  
  .media-foot {
      margin-top: 25rpx;
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
  }
  
  .close-view {
      position: relative;
      align-items: center;
      flex-direction: row;
      width: 40rpx;
      height: 30rpx;
      line-height: 30rpx;
      border-width: 1rpx;
      border-style: solid;
      border-color: #aaaaaa;
      border-radius: 4px;
      justify-content: center;
      text-align: center;
  }
  
  .close-l {
      position: absolute;
      width: 18rpx;
      height: 1rpx;
      background-color: #aaaaaa;
  }
  
  .close-h {
      transform: rotate(45deg);
  }
  
  .close-v {
      transform: rotate(-45deg);
  }
  
  .swiper{
    width: 100%;
    height: 400rpx;
  }
  .swiper .image{
    width: 750rpx;
    height: 400rpx;
    overflow: hidden;
  }
  
  
  /* .search-box {width:100%;background-color:$ss-blue;padding:15upx 2.5%;display:flex;justify-content:space-between;position:sticky;top: 0; z-index: 999;} */
  .search-box .mSearch-input-box{width: 685rpx;}
  .search-box .input-box {width:85%;flex-shrink:1;display:flex;justify-content:center;align-items:center;}
  .search-box .search-btn {width:15%;margin:0 0 0 2%;display:flex;justify-content:center;align-items:center;flex-shrink:0;font-size:28upx;color:#fff;background:linear-gradient(to right,#ff9801,#ff570a);border-radius:60upx;}
  .search-box .input-box>input {width:100%;height:60upx;font-size:32upx;border:0;border-radius:60upx;-webkit-appearance:none;-moz-appearance:none;appearance:none;padding:0 3%;margin:0;background-color:#ffffff;}
  .placeholder-class {color:#9e9e9e;}
  .search-keyword {width:100%;background-color:rgb(242,242,242);}
  .keyword-list-box {height:calc(100vh - 110upx);padding-top:10upx;border-radius:20upx 20upx 0 0;background-color:#fff;}
  .keyword-entry-tap {background-color:#eee;}
  .keyword-entry {width:94%;margin:0 3%;font-size:30upx;color:#333;display:flex;justify-content:space-between;align-items:center;border-bottom:solid 1upx #e7e7e7;line-height: 36rpx;padding: 6rpx 0;}
  .keyword-entry image {width:60upx;height:60upx;}
  .keyword-entry .keyword-text,.keyword-entry .keyword-img {display:flex;align-items:center;}
  .keyword-entry .keyword-text {width:100%;}
  .keyword-entry .keyword-img {width:10%;justify-content:center;}
  .keyword-box {height:calc(100vh - 110upx);border-radius:20upx 20upx 0 0;background-color:#fff;}
  .keyword-box .keyword-block {padding:10upx 0;}
  .keyword-box .keyword-block .keyword-list-header {width:94%;padding:10upx 3%;font-size:27upx;color:#333;display:flex;justify-content:space-between;}
  .keyword-box .keyword-block .keyword-list-header image {width:40upx;height:40upx;}
  .keyword-box .keyword-block .keyword {width:94%;padding:3px 3%;display:flex;flex-flow:wrap;justify-content:flex-start;}
  .keyword-box .keyword-block .hide-hot-tis {display:flex;justify-content:center;font-size:28upx;color:#6b6b6b;}
  .keyword-box .keyword-block .keyword>view {display:flex;justify-content:center;align-items:center;border-radius:60upx;padding:0 20upx;margin:10upx 20upx 10upx 0;height:60upx;font-size:28upx;background-color:rgb(242,242,242);color:#6b6b6b;}
  .serachBtn{
    display: none;
  }
</style>
