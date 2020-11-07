<template>
	<view class="wrapper">
		<!-- 标题 -->
		<view :class="['grace-article-title', graceSkeleton == 'ing' ? 'grace-skeleton' : '']">{{info.title}}</view>
		<!-- 其他基本信息 -->
    <view class="grace-article-author-line">
      <view :class="['grace-article-info-line', graceSkeleton == 'ing' ? 'grace-skeleton' : '']">
        <view>查看：{{info.click || 0}}</view>
        <view class="u-p-l-30">时间：{{info.create_time || ''}}</view>
      </view>
      <view @click="favorite(0)" class="btn" v-if="info.is_favorite"> - 取消收藏 </view>
      <view @click="favorite(1)" class="btn" v-else><text class="text-blue"> + 收藏 </text></view>
    </view>
		<!-- 文章内容 -->
		<view class="grace-article-contents">
			<jyf-parser :html="info.content" ref="article" :tag-style="tagStyle"></jyf-parser>
		</view>
	</view>
</template>
<script>
import { doFavorite } from '@/api/api.js';
import { getArticleDetail } from '../../api/article.js';
import { mapGetters } from "vuex";
import parser from "@/components/jyf-parser/jyf-parser";
var _self;
export default {
  components: {
    "jyf-parser": parser
  },
  computed: mapGetters(['isLogin', 'uid']),
	data() {
		return {
			graceSkeleton: 'ing',
			info: [],
      id: 0
		}
	},
	onLoad(option) {
		_self = this;
		// 加载文章详情
		uni.showLoading();
    this.id = option.id;
    
    this.getArticle();
      
    wx.showShareMenu({
      menus: ["shareAppMessage", "shareTimeline"]
    });
	},
  onShareAppMessage(res) {
    return {
      title: this.info.title+'-北京盛世国际拍卖',
      path: '/pages/news/detail?id='+this.id
    }
  },
  onShareTimeline(){
    return {
      title: this.info.title+'-北京盛世国际拍卖',
      imageUrl: this.info.thumb
    }
  },
	methods: {
    getArticle(){
      var that = this;
      getArticleDetail({id: this.id, uid:this.uid || 0}).then(res=>{
        // 骨架屏规划后延长 500 毫秒进行数据替换
        setTimeout(function(){
        	_self.info = res.data.info;
        	_self.graceSkeleton = 'end';
        	uni.setNavigationBarTitle({title : _self.info.title});
        }, 500);
        uni.hideLoading();
      })
    },
    favorite(type){
      if (this.isLogin === false) {
        return this.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
      } else {
        let that = this;
        doFavorite({mid:this.info.id, mtable: 'article', type:type}).then(res => {
          that.$set(that.info, 'is_favorite', !that.info.is_favorite);
        })
      }
    }
	}
}
</script>
<style>
	.grace-padding{padding:2%; width:96%;}
	.grace-common-bg{background:#F8F8F8;}
	.grace-noborder{border:0 !important;}
	.grace-left-padding-wrap{width:96%; padding:12rpx 0; padding-left:4%; overflow:hidden; background:#FFFFFF;}
	.grace-left-padding-wrap .grace-list{border-bottom:1rpx solid #D1D1D1; padding-left:0;}
	.grace-left-padding-wrap .grace-list view text{color:#666; font-size:28rpx;}
	
	/* 栅格布局 */
	.grace-flex{display:flex !important;}
	.grace-rows{display:flex; flex-direction:row !important;}
	.grace-columns{display:flex; flex-direction:column !important;}
	.grace-wrap{display:flex; flex-wrap:wrap;}
	.grace-nowrap{display:flex; flex-wrap:nowrap !important;}
	.grace-space-between{display:flex; justify-content:space-between !important;}
	
	.grace-tab{padding:0;}
	.grace-tab-title{white-space:nowrap; text-align:center; background:#FFFFFF;}
	.grace-tab-title view{width:auto; padding:0 12px; margin:0 8px; line-height:42px; display: inline-block; text-align:center; border-bottom:2px solid #FFFFFF; font-size:30rpx;}
	.grace-tab-title view:first-child{margin-left: 0;}
	.grace-tab-title view:last-child{margin-right: 0;}
	.grace-tab-current{border-bottom:4rpx solid #FF0036 !important; color:#FF0036;}
	
	/* 新闻列表 */
	.grace-news-list{padding:12rpx 0;}
	.grace-news-list > navigator{display:block; width:100%; padding:12rpx 0; margin:12rpx 0;}
	.grace-news-list-items{width:100%; display:flex; flex-wrap:nowrap; position:relative;}
	.grace-news-list-img{width:200rpx; flex-shrink:0;}
	.grace-news-list-title{width:100%; overflow:hidden;}
	.grace-news-list-title-main{line-height:1.5em; font-size:32rpx; width:100%;}
	.grace-news-list-title-desc{font-size:24rpx; display:block; color:#666; margin-top:12rpx; height:40rpx; line-height:40rpx;}
	.grace-news-tips{width:auto; padding:0 12rpx; border-radius:5rpx; overflow:hidden; background:#FF4343; position:absolute; top:0; color:#FFFFFF; height:36rpx; line-height:36rpx; font-size:20rpx;}
	.grace-news-tips-l{left:0;}
	.grace-news-tips-r{right:0;}
	.grace-news-list-info{width:100%; margin-top:10rpx; line-height:36rpx; font-size:24rpx; color:#666; justify-content:space-between;}
	.grace-news-list-info view{font-size:24rpx; color:#666;}
	.grace-news-list-info text{font-size:24rpx; color:#666; margin-right:10rpx;}
	.grace-news-list-img-news{width:100%; flex-wrap:wrap;}
	.grace-news-list-imgs{justify-content:space-between; width:100%; display:flex; margin:18rpx 0; align-items:flex-start;}
	.grace-news-list-imgs image{width:31.3%; margin:0 1%;}
	.grace-news-list-img-big{width:100%; padding:18rpx 0;}
	.grace-news-list-img-big image{width:100%;}
	
	/* 文章骨架结构 */
	.grace-skeleton{padding:5px 0; background:#ffffff; border-radius:8px;}
	.grace-article-title{padding:8px 12px; font-size:40rpx; line-height:1.5em; font-weight:700;}
	.grace-article-author-line{margin:3px 0px; display:flex; flex-wrap:nowrap; justify-content:space-between;}
	.grace-article-author{display:flex; flex-wrap:nowrap;}
	.grace-article-author image{width:28px; height:28px; border-radius:100%;}
	.grace-article-author .author-name{line-height:28px; padding-left:5px;}
	.grace-article-author-line .btn{display:inline-block; height:28px; line-height:28px; border-radius:3px;background:transparent; color:#999999; margin-right: 20rpx;}
	.grace-article-info-line{margin:8px 12px; display:flex; flex-wrap:nowrap; justify-content:space-between;}
	.grace-article-info-line view{color:#888; line-height:20px; font-size:12px;}
	.grace-article-contents{margin:10px 0; padding: 10px;}
	.grace-article-contents .img-item{width:100%;}
	.grace-article-contents .img-item image{width:100%;}
	.grace-article-contents .text-item{margin:8px 12px; line-height:2.2em; font-size:16px; color:#2F2F2F;}
  
  .wrapper{
    background: #fff;
    min-height: 100vh;
  }
</style>