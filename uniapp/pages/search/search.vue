<template>
	<view class="content">
		<view class="search-box">
			<!-- mSearch组件 如果使用原样式，删除组件元素-->
			<mSearch class="mSearch-input-box" :mode="2" button="inside" :placeholder="defaultKeyword" @search="doSearch(false)" @input="inputChange" @confirm="doSearch(false)" v-model="keyword"></mSearch>
			<!-- 原样式 如果使用原样式，恢复下方注销代码 -->
			<!-- 						
			<view class="input-box">
				<input type="text" :adjust-position="true" :placeholder="defaultKeyword" @input="inputChange" v-model="keyword" @confirm="doSearch(false)"
				 placeholder-class="placeholder-class" confirm-type="search">
			</view>
			<view class="search-btn" @tap="doSearch(false)">搜索</view> 
			 -->
			<!-- 原样式 end -->
		</view>
		<view class="search-keyword" >
			<scroll-view class="keyword-list-box" v-show="isShowKeywordList" scroll-y>
				<block v-for="(row,index) in keywordList" :key="index">
					<view class="keyword-entry" hover-class="keyword-entry-tap" >
						<view class="keyword-text" @tap.stop="goDetail(keywordList[index].uuid, keywordList[index].type)">
							<rich-text :nodes="row.htmlStr"></rich-text>
						</view>
						<!-- <view class="keyword-img" @tap.stop="setKeyword(keywordList[index].keyword)">
							<image src="/static/HM-search/back.png"></image>
						</view> -->
					</view>
				</block>
				
			</scroll-view>
			<scroll-view class="keyword-box" v-show="!isShowKeywordList" scroll-y>
				<view class="keyword-block" v-if="oldKeywordList.length>0">
					<view class="keyword-list-header">
						<view>历史搜索</view>
						<view>
							<image @tap="oldDelete" src="/static/HM-search/delete.png"></image>
						</view>
					</view>
					<view class="keyword">
						<view v-for="(keyword,index) in oldKeywordList" @tap="doSearch(keyword)" :key="index">{{keyword}}</view>
					</view>
				</view>
				<view class="keyword-block">
					<view class="keyword-list-header">
						<view>热门搜索</view>
						<!-- <view>
							<image @tap="hotToggle" :src="'/static/HM-search/attention'+forbid+'.png'"></image>
						</view> -->
					</view>
					<view class="keyword" v-if="hotKeywordList.length>0">
						<view v-for="(keyword,index) in hotKeywordList" @tap="doSearch(keyword.keyword)" :key="index">{{keyword.keyword}}</view>
					</view>
					<view class="hide-hot-tis" v-else>
						<view>暂无热门搜索</view>
					</view>
				</view>
        
        <view class="cu-list menu-avatar" v-if="results.length>0">
        	<view class="cu-item" @click="goDetail(item.uuid)" v-for="(item, index) in results" :key="index">
        		<view class="cu-avatar radius lg" :style="'background-image:url('+item.thumb+');'"></view>
        		<view class="content">
        			<view class=""><view class="text-cut"><rich-text :nodes="item.title"></rich-text></view></view>
        			<view class="text-gray text-sm flex line2"><rich-text :nodes="item.intro"></rich-text></view>
        		</view>
        		<view class="action">
        			<!-- <view class="text-grey text-xs">22:20</view>
        			<view class="cu-tag round bg-red sm">5</view> -->
        		</view>
        	</view>
          <u-divider v-if="results.length>=30">最大显示30条结果，请输入精确关键词查询</u-divider>
        </view>
        <view v-else>
          <u-empty v-if="showResult" marginTop="200" mode="search"></u-empty>
        </view>
        
			</scroll-view>
		</view>
	</view>
</template>

<script>
	//引用mSearch组件，如不需要删除即可
	import mSearch from '@/components/mh-search/mh-search.vue';
  import { getHotKeyword, getSearchResult } from '@/api/api.js';
  import Util from '@/utils/util.js';
	export default {
		data() {
			return {
				defaultKeyword: "",
				keyword: "",
				oldKeywordList: [],
				hotKeywordList: [],
				keywordList: [],
				forbid: '',
				isShowKeywordList: false,
        results: [],
        showResult: false,
        current: 1
			}
		},
		onLoad() {
			this.init();
      wx.showShareMenu({
        menus: ["shareAppMessage", "shareTimeline"]
      });
		},
		components: {
			//引用mSearch组件，如不需要删除即可
			mSearch
		},
    onShareAppMessage(res) {
      return {
        title: '搜索-北京盛世国际拍卖',
        path: '/pages/search/search'
      }
    },
    onShareTimeline(){
      return {
        title: '拍品搜索-北京盛世国际拍卖',
        imageUrl: ''
      }
    },
		methods: {
      goDetail(uuid, type){
        uni.redirectTo({
          url:'/pages/auction/detail?uuid='+uuid
        })
      },
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      },
			init() {
				this.loadDefaultKeyword();
				this.loadOldKeyword();
				this.loadHotKeyword();
			},
			blur(){
				uni.hideKeyboard()
			},
			//加载默认搜索关键字
			loadDefaultKeyword() {
				this.defaultKeyword = "请输入藏品关键词搜索";
			},
			//加载历史搜索,自动读取本地Storage
			loadOldKeyword() {
				uni.getStorage({
					key: 'OldKeys',
					success: (res) => {
						var OldKeys = JSON.parse(res.data);
						this.oldKeywordList = OldKeys;
					}
				});
			},
			//加载热门搜索
			loadHotKeyword() {
        var that = this;
        getHotKeyword().then(res=>{
          this.hotKeywordList = res.data.lists;
        }).catch(err=>{
          console.log(err)
        })
			}, 
			//监听输入
			inputChange(event) {
        /* var that = this;
				//兼容引入组件时传入参数情况
				var keyword = event.detail?event.detail.value:event;
				if (!keyword) {
					this.keywordList = [];
					this.isShowKeywordList = false;
          this.showResult = false;
					return;
				}
				this.isShowKeywordList = true;
				//以下示例截取淘宝的关键字，请替换成你的接口
        //直接显示搜索结果，直接跳转，限制显示20条
        getSearchResult({keyword: this.keyword}).then(res=>{
          this.keywordList = [];
          this.keywordList = this.drawCorrelativeKeyword(res.data.lists, keyword);
        }); */
			},
			//高亮关键字
			drawCorrelativeKeyword(keywords, keyword) {
				var len = keywords.length,
					keywordArr = [];
				for (var i = 0; i < len; i++) {
					var row = keywords[i];
					//定义高亮#9f9f9f
          var title = row['title'].replace(keyword, "<span style='color: #aa0000;'>" + keyword + "</span>");
          var intro = "<span style='font-size:12px; color: #cccccc;'>"+row['intro']+"</span>";
					
					title = '<div>' + title + '</div>';
					var tmpObj = {
            title: title,
            uuid: row['uuid'],
            thumb: row['thumb'],
            id: row['id'],
            intro: intro
					};
					keywordArr.push(tmpObj)
				}
				return keywordArr;
			},
			//顶置关键字
			setKeyword(index) {
				this.keyword = this.keywordList[index].keyword;
			},
			//清除历史搜索
			oldDelete() {
				uni.showModal({
					content: '确定清除历史搜索记录？',
					success: (res) => {
						if (res.confirm) {
							console.log('用户点击确定');
							this.oldKeywordList = [];
							uni.removeStorage({
								key: 'OldKeys'
							});
						} else if (res.cancel) {
							console.log('用户点击取消');
						}
					}
				});
			},
			//热门搜索开关
			hotToggle() {
				this.forbid = this.forbid ? '' : '_forbid';
			},
			//执行搜索
			doSearch(keyword) {
				keyword = keyword===false?this.keyword:keyword;
				this.keyword = keyword;
        
        if(!keyword){
          return that.$util.Tips({
          	title: '请输入搜索的内容'
          });
        }
				this.saveKeyword(keyword); //保存为历史 
        this.isShowKeywordList = false;
        this.showResult = true;
        
        //展示搜索结果
        getSearchResult({keyword: this.keyword}).then(res=>{
          this.results = [];
          console.log(this.drawCorrelativeKeyword(res.data.lists, keyword))
          this.results = this.drawCorrelativeKeyword(res.data.lists, keyword);
        });
        
				//以下是示例跳转淘宝搜索，可自己实现搜索逻辑
				/*
				//#ifdef APP-PLUS
				plus.runtime.openURL(encodeURI('taobao://s.taobao.com/search?q=' + keyword));
				//#endif
				//#ifdef H5
				window.location.href = 'taobao://s.taobao.com/search?q=' + keyword
				//#endif
				*/
			},
			//保存关键字到历史记录
			saveKeyword(keyword) {
				uni.getStorage({
					key: 'OldKeys',
					success: (res) => {
						var OldKeys = JSON.parse(res.data);
						var findIndex = OldKeys.indexOf(keyword);
						if (findIndex == -1) {
							OldKeys.unshift(keyword);
						} else {
							OldKeys.splice(findIndex, 1);
							OldKeys.unshift(keyword);
						}
						//最多10个纪录
						OldKeys.length > 10 && OldKeys.pop();
						uni.setStorage({
							key: 'OldKeys',
							data: JSON.stringify(OldKeys)
						});
						this.oldKeywordList = OldKeys; //更新历史搜索
					},
					fail: (e) => {
						var OldKeys = [keyword];
						uni.setStorage({
							key: 'OldKeys',
							data: JSON.stringify(OldKeys)
						});
						this.oldKeywordList = OldKeys; //更新历史搜索
					}
				});
			}
		}
	}
</script>
<style>
	view{display:block;}
	.search-box {width:100%;background-color:rgb(242,242,242);padding:15upx 2.5%;display:flex;justify-content:space-between;position:sticky;top: 0; z-index: 999;}
	.search-box .mSearch-input-box{width: 100%;}
	.search-box .input-box {width:85%;flex-shrink:1;display:flex;justify-content:center;align-items:center;}
	.search-box .search-btn {width:15%;margin:0 0 0 2%;display:flex;justify-content:center;align-items:center;flex-shrink:0;font-size:28upx;color:#fff;background:linear-gradient(to right,#ff9801,#f45300);border-radius:60upx;}
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
  .cu-list.menu-avatar>.cu-item .content{
    width: 570rpx;
  }
  .serach .content .content-box .input.center{
    width: 300rpx !important;
  }
</style>
