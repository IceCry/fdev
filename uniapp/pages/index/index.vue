<template>
	<view>
		<!-- #ifdef H5 -->

		<!-- #endif -->
		<!-- #ifdef MP -->

		<!-- #endif -->
	</view>
</template>

<script>
import couponWindow from '@/components/couponWindow/index';
import { SUBSCRIBE_MESSAGE } from '@/config/cache';
import authorize from '@/components/Authorize.vue';
import { getTemlIds } from '@/api/api.js';
import { mapGetters } from 'vuex';
let app = getApp();
export default {
	computed: mapGetters(['isLogin', 'uid']),
	components: {
		couponWindow,
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
		uni.getLocation({
			type: 'wgs84',
			success: function(res) {
				try {
					uni.setStorageSync('user_latitude', res.latitude);
					uni.setStorageSync('user_longitude', res.longitude);
				} catch {}
			}
		});
		this.getIndexData();
		this.setOpenShare();
		// #ifdef H5
		window.addEventListener('message', this.handleMessageFromParent, false);
		if (app.globalData.isIframe) {
			uni.hideTabBar();
		}
		// #endif
		// #ifdef MP
		this.getTemlIds();
		// #endif
	},
	// #ifdef MP
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
	// #endif
	onShow() {
		if (!app.globalData.isIframe) {
			uni.showTabBar();
			if (this.isLogin) {
				this.getCoupon();
			}
		}
	},
	methods: {
		// 授权关闭
		authColse: function(e) {
			this.isShowAuth = e;
		},
		// #ifdef MP
		getTemlIds() {
			let messageTmplIds = wx.getStorageSync(SUBSCRIBE_MESSAGE);
			if (!messageTmplIds) {
				getTemlIds().then(res => {
					if (res.data) wx.setStorageSync(SUBSCRIBE_MESSAGE, JSON.stringify(res.data));
				});
			}
		},
		// #endif
		onLoadFun() {},
		// #ifdef H5
		handleMessageFromParent(event) {
			var data = event.data;
			// console.log(event.data,'handleMessageFromParent')
		},
		// #endif
		// 对象转数组
		objToArr(data) {
			for (let name in data) {
				if (name === "z_tabBar") {
					app.globalData.tabbarShow = data[name].isShow.val;
					break;
				}
			}
			let obj = Object.keys(data);

			let m = obj.map(function(key) {
				data[key].name = key;
				return data[key];
			});
			return m;
		},
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
