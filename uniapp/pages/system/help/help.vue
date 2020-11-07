<template>
	<view class="container">
    <u-sticky>
      <view class="tabs">
        <u-tabs v-if="control" bg-color="#ffffff" :bold="true" active-color="#f45300" :list="list"
        @change="change" :current="current" :is-scroll="true"></u-tabs>
      </view>
    </u-sticky>
    
    <view class="content">
      <jyf-parser :html="info.content" ref="content" :tag-style="tagStyle"></jyf-parser>
    </view>
    
	</view>
</template>

<script>
  import parser from "@/components/jyf-parser/jyf-parser";
  import { getArticleDetail } from '@/api/article.js';
	export default {
    components: {
      "jyf-parser": parser
    },
		data() {
			return {
				list: [{'id': 4, 'name':'买家服务'}, {'id': 5, 'name':'卖家服务'}, {'id': 6, 'name':'售后服务'}],
        info: [],
				current: 0,
				sectionCurrent: 0,
				tabCountIndex: 0,
				control: true
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
        title: '新手指南-北京盛世国际拍卖',
        path: '/pages/system/help/help'
      }
    },
    onShareTimeline(){
      return {
        title: '新手指南-北京盛世国际拍卖',
        imageUrl: ''
      }
    },
    methods:{
      getData(){
        var id = this.list[this.current].id;
        var that = this;
        uni.showLoading();
        getArticleDetail({id: id}).then(res=>{
          that.info = res.data.info;
          uni.hideLoading();
        })
      },
			change(index) {
				this.current = index;
        this.getData();
			}
    }
	}
</script>

<style lang="scss">
.content{
  margin: 30rpx;
}
.container{
  background: #FFFFFF;
  min-height: 100vh;
}
</style>
