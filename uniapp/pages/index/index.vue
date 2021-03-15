<template>
	<view>
		<view class="u-flex u-row-center u-margin-60">
			<image src="/static/images/support.png" mode="widthFix"></image>
		</view>
		
		<view class="u-text-center">
			<text>仅供演示，请删除pages自行创建文件。完整项目地址：https://github.com/IceCry/fdev</text>
		</view>
	</view>
</template>

<script>
import { SUBSCRIBE_MESSAGE } from '@/config/cache';
import authorize from '@/components/Authorize.vue';
import { getTemlIds } from '@/api/api.js';
import { getShare } from '@/api/public.js';
import { mapGetters } from 'vuex';
let app = getApp();
export default {
	computed: mapGetters(['isLogin', 'uid']),
	components: {
		authorize,
	},
	data() {
		return {
			loading: false,
			loadend: false,
			loadTitle: '下拉加载更多', //提示语
			page: 1,
			limit: this.$config.LIMIT,
			isAuto: false, //没有授权的不会自动授权
			isShowAuth: false, //是否隐藏授权,
			shareInfo: {}
		};
	},
	onLoad(options) {
		/* uni.getLocation({
			type: 'wgs84',
			success: function(res) {
				try {
					uni.setStorageSync('user_latitude', res.latitude);
					uni.setStorageSync('user_longitude', res.longitude);
				} catch {}
			}
		}); */
		//this.getIndexData();
		//this.setOpenShare();
		//this.getTemlIds();
	},
	//发送给朋友
	onShareAppMessage: function() {
		// 此处的distSource为分享者的部分信息，需要传递给其他人
		let that = this;
		return {
			title: this.shareInfo.title,
			path: '/pages/index/index',
			imageUrl: this.storeInfo.img
		};
	},
	//分享到朋友圈
	onShareTimeline: function() {
		return {
			title: this.shareInfo.title,
			imageUrl: this.storeInfo.img
		};
	},
	onShow() {
		
	},
	methods: {
		// 授权关闭
		authColse: function(e) {
			this.isShowAuth = e;
		},
		getTemlIds() {
			let messageTmplIds = wx.getStorageSync(SUBSCRIBE_MESSAGE);
			if (!messageTmplIds) {
				getTemlIds().then(res => {
					if (res.data) wx.setStorageSync(SUBSCRIBE_MESSAGE, JSON.stringify(res.data));
				});
			}
		},
		onLoadFun() {},
		getIndexData() {
			let self = this;
			getIndexData().then(res => {
				uni.setNavigationBarTitle({
					title: res.data.site_name
				});
				self.$store.commit('indexData/setIndexData', res.data);
			});
		},
		// 微信分享；
		setOpenShare: function() {
			let that = this;
			getShare().then(res => {
				let data = res.data.data;
				this.shareInfo = data;
				// #ifdef H5
				if (that.$wechat.isWeixin()) {
					let configAppMessage = {
						desc: data.synopsis,
						title: data.title,
						link: location.href,
						imgUrl: data.img
					};
					that.$wechat.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData'], configAppMessage);
				}
				// #endif
			});
		}
	},
	onReachBottom: function() {
		// this.getGroomList();
	}
};
</script>

<style>
page {
	background: #fff;
}
</style>
