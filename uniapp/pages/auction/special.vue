<template>
	<view class="container">
    <view class="top-title">
      <text>{{info.title}}</text>
    </view>
    
    <view class="jingdong">
      <view class="top">
        <view class="left flex justify-center align-center">
          <image :src="info.thumb" mode="widthFix"></image>
        </view>
        <view class="right">
          <view class="bottom flex flex-direction">
            <view class="date u-line-1">预展：{{info.show_time}}</view>
            <view class="date u-line-1 flex justify-between">
              <view>开拍：{{info.start_time}}</view>
              <!-- <view><u-tag text="开拍通知" size="mini" type="success" shape="circle" mode="plain" /></view> -->
            </view>
            <view class="date u-line-1">LOT号：{{info.lot}}</view>
            <view class="date u-line-1">拍品：{{info.collection_num}}件</view>
            <view class="date u-line-1">专场保证金：{{info.deposit_price|formatMoney}}元</view>
            <view class="date u-line-1">围观：{{info.click}}人</view>
            <view class="date u-line-2">地点：{{info.address}}<text class="text-blue u-padding-left-10" v-if="info.lat" @click="goMap">查看地图</text></view>
          </view>
        </view>
      </view>
      <view class="btm u-font-sm">
        <!-- <u-section title="北京盛世国际拍卖有限公司拍卖规则" :bold="false" fontSize="24" :showLine="false" sub-title=" " sub-color="#999999"></u-section> -->
        <text class="text-blue" @click="pop = true">《北京盛世国际拍卖有限公司拍卖规则》</text>
        <u-checkbox class="checkbox" @change="changeAgree" size="26" label-size="24" v-model="agree">我已阅读并同意</u-checkbox>
      </view>
    </view>
    
    <view class="video" v-if="info.live_url">
      <video :src="info.live_url" :poster="info.thumb" :enable-progress-gesture="false" :controls="false" picture-in-picture-mode="push"></video>
    </view>
    
    <view class="content">
      <u-read-more color="#999999" font-size="24" show-height="300" close-text="查看更多">
      <jyf-parser :html="info.content" ref="special" :tag-style="tagStyle"></jyf-parser>
      </u-read-more>
    </view>
		
		<view class="taobao">
			<view class="title">
				<view class="left">
					<view class="store">拍品列表</view>
				</view>
			</view>
      
			<view class="ticket" v-for="(item, index) in lists" :key="index" @click="goAuction(item.uuid)">
        <view v-if="item.auction_status==0" class="status status-blue">未开拍</view>
        <view v-else-if="item.auction_status==1" class="status status-orange">拍卖中</view>
        <view v-else-if="item.auction_status==2" class="status status-green">已结拍</view>
        <view v-else-if="item.auction_status==-1" class="status status-black">流拍</view>
        <view v-else-if="item.auction_status==-2" class="status status-gray">撤拍</view>
        <view v-else-if="item.auction_status==-3" class="status status-gray">终止拍卖</view>
				<view class="left">
					<view class="thumb flex align-center">
            <image class="picture" :src="item.thumb" mode="widthFix"></image>
          </view>
					<view class="introduce">
						<view class="top">
							￥
              <text class="big">{{item.start_price|formatMoney}}</text>
              <text class="text-gray">起拍价</text>
						</view>
						<view class="type line2">{{item.title}}</view>
						<!-- <view class="date u-line-1">[图录号：1] 包邮</view> -->
					</view>
				</view>
				<view class="right flex flex-direction">
					<!-- <view class="use immediate-use" :round="true">图录号：{{item.number}}</view> -->
					<view class="text-gray">图录号</view>
          <view class="u-font-lg text-bold">{{item.number}}</view>
				</view>
			</view>
      
		</view>
    
    <u-loadmore v-if="lists.length>0" bg-color="#F4F5F6" :status="loadStatus" @loadmore="getData"></u-loadmore>
    
    <view class="navH"></view>
    <view class="navigation">
    	<view class="left flex align-center">
    		<view class="item price">
    			<view class="text line2"><text class="text-sm">为保证正常参与竞拍，请及时缴纳保证金。</text></view>
    		</view>
    	</view>
    	<view class="right flex">
        <!-- 判断是否已缴纳 -->
        <template v-if="info.special_status==0 || info.special_status==1">
          <view v-if="paid" class="done btn line1">保证金已缴纳</view>
          <view v-else class="buy btn line1" @click="payDeposit">缴纳保证金</view>
        </template>
        <template v-else>
          <view class="done btn line1">已 结 拍</view>
        </template>
    	</view>
    </view>
    
    <!-- 参拍须知 -->
    <u-popup v-model="pop" mode="center" closeable custom-style="overflow:auto;" length="90%" zoom border-radius="10">
      <scroll-view class="pop-content" scroll-y="true">
        <jyf-parser :html="nowHtml" ref="article" :tag-style="tagStyle"></jyf-parser>
      </scroll-view>
    </u-popup>
    
    <!-- 余额支付 -->
    <u-modal v-model="showModal" :show-cancel-button="true" confirm-text="确定缴纳" title="提示" content="确定使用余额缴纳保证金吗？" @confirm="apply"></u-modal>
    
	</view>
</template>

<script>
import Util from '@/utils/util.js';
import { getSpecialInfo, getSpecialList, hasDeposit, postDeposit, getCollectionStatus } from '@/api/auction.js';
import { getUserInfo } from '@/api/user.js';
import parser from "@/components/jyf-parser/jyf-parser";
import { mapGetters } from "vuex";
export default {
  components: {
    "jyf-parser": parser
  },
  computed: mapGetters(['isLogin', 'uid']),
  filters:{
    formatMoney: function(s, type) {
      if (/[^0-9\.]/.test(s))
        return "0";
      if (s == null || s == "")
        return "0";
      s = s.toString().replace(/^(\d*)$/, "$1.");
      s = (s + "00").replace(/(\d*\.\d\d)\d*/, "$1");
      s = s.replace(".", ",");
      var re = /(\d)(\d{3},)/;
      while (re.test(s))
        s = s.replace(re, "$1,$2");
      s = s.replace(/,(\d\d)$/, ".$1");
      if (true || type == 0) {// 不带小数位
        var a = s.split(".");
        if (a[1] == "00") {
          s = a[0];
        }
      }
      return s;
    },
  },
	data() {
		return {
      uuid: '',
      info: [],
      lists: [],
      page: 1,
      limit: 10,
      loadStatus: 'loadmore',
      pop: false,
      nowHtml: '',
      paid: false, //是否已缴纳专场保证金
      showModal: false,
      agree: false,
      timer: null, //定时获取拍品最新状态
		};
	},
	onLoad(option) {
    this.uuid = option.uuid || '';
    this.getInfo();
    this.getData();
    wx.showShareMenu({
      menus: ["shareAppMessage", "shareTimeline"]
    });
	},
  onShow() {
    var that = this;
    clearInterval(this.timer);
    this.timer = setInterval(function() {
      that.updateCollectionStatus();
    }, 5000);
  },
  onHide() {
    clearInterval(this.timer);
  },
  onUnload() {
    clearInterval(this.timer);
  },
	onReachBottom() {
		this.getData();
	},
  onShareAppMessage(res) {
    return {
      title: this.info.title + '-北京盛世国际拍卖',
      path: '/pages/auction/special?uuid='+this.uuid
    }
  },
  onShareTimeline(){
    return {
      title: this.info.title + '-北京盛世国际拍卖',
      imageUrl: this.info.thumb
    }
  },
	methods: {
    //跳转拍品详情页
    goAuction(uuid){
      var that = this;
      //判断是否阅读规则
      uni.getStorage({
        key: 'agree_'+this.info.id,
        success(res) {
          console.log(res)
          if(res.data){
            that.goPage('/pages/auction/detail?uuid='+uuid);
          }else{
            return that.$util.Tips({
              title: "请先阅读并同意拍卖规则"
            });
          }
        },
        fail(){
          return that.$util.Tips({
            title: "请先阅读并同意拍卖规则"
          });
        }
      })
    },
    getInfo(){
      var that = this;
      getSpecialInfo(this.uuid).then(res=>{
        that.info = res.data.info;
        that.nowHtml = res.data.notice;
        uni.setNavigationBarTitle({
          title:res.data.info.title
        })
        
        uni.getStorage({
          key: 'agree_'+res.data.info.id,
          success(res) {
            if(res.data){
              that.agree = true;
            }
          }
        });
        //如果已登录，判断是否缴纳保证金
        if(that.isLogin){
          that.specialDeposit();
        }
      })
    },
    getData(){
      var that = this;
      if(this.loadStatus=='nomore'){
        return this.$util.Tips({
          title: "没有更多了"
        });
      }
      uni.showLoading();
      getSpecialList({uuid:this.uuid, page: this.page, limit: this.limit}).then(res=>{
        that.ad1 = res.data.ad1;
        let list = res.data.lists;
        that.lists = that.lists.concat(list);
        let loadend = list.length < that.limit;
        if(loadend){
          that.loadStatus = 'nomore';
        }else{
          that.page++;
        }
        
        clearInterval(that.timer);
        that.timer = setInterval(function() {
          that.updateCollectionStatus();
        }, 5000);
        uni.hideLoading();
      })
    },
    //实时获取当前已加载藏品状态信息
    updateCollectionStatus(){
      var that = this;
      var lists = this.lists;
      if(lists.length==0) return;
      var ids = new Array();
      lists.forEach(item=>{
      	ids.push(item.id);
      });
      //获取状态
      getCollectionStatus({ids:ids}).then(res=>{
        //更新
        let data = res.data.arr;
        console.log(data)
        data.forEach(item=>{
          lists.forEach((item2, index2)=>{
            if(item.id == item2.id){
              that.lists[index2].auction_status = item.status;
            }
          })
        });
      })
    },
    goPage(url, type='navigate'){
      Util.goPage(url, type);
    },
    goMap(){
      var that = this;
      uni.openLocation({
        latitude: parseFloat(that.info.lat),
        longitude: parseFloat(that.info.lng),
        name: that.info.title,
        address: that.info.address,
        success: function () {
          console.log('success');
        }
      })
    },
    specialDeposit(){
      console.log(this.info.id)
      var that = this;
      hasDeposit({collection_id: 0, special_id: this.info.id}).then(res=>{
        that.paid = true;
      }).catch(err => {
        //显示保证金弹窗
        console.log(err)
      });
    },
    payDeposit(){
      var that = this;
      if(that.isLogin === false){
        return this.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
      }
      //判断余额是否充足，不足则跳转充值
      getUserInfo().then(res=>{
        let depositPrice = res.data.userInfo.deposit_price;
        if(parseFloat(depositPrice)>=parseFloat(that.info.deposit_price)){
          //直接支付
          that.showModal = true;
        }else{
          let url = '/pages/system/deposit/deposit?uuid='+that.uuid+'&price='+that.info.deposit_price+'&special_id='+that.info.id;
          return that.$util.Tips({title:'保证金可用余额不足，请充值'}, url);
        }
      }).catch(err => {
        //显示保证金弹窗
        console.log(err)
      });
    },
    //余额抵扣保证金
    apply(){
      var that = this;
      postDeposit({special_id: this.info.id, balance: 1}).then(res=>{
        that.getInfo();
        return that.$util.Tips({title:'保证金已缴纳'});
      }).catch(err => {
        console.log(err)
        return that.$util.Tips({title:err});
      });
    },
    //同意规则
    changeAgree(e){
      //写入localstorage
      console.log(e.value)
      if(!e.value){
        uni.setStorage({
          key: 'agree_'+this.info.id,
          data: Date.parse(new Date())
        })
      }else{
        uni.removeStorage({
          key: 'agree_'+this.info.id
        })
      }
    }
	}
};
</script>

<style lang="scss" scoped>
page {
	height: 100%;
}
.container {
}
.top-title{
  background: #FFFFFF;
  padding: 20rpx;
  line-height: 40rpx;
  font-size: 32rpx;
  font-weight: 700;
}
.jingdong {
	width: 750rpx;
  padding: 20rpx 30rpx;
	height: auto;
	background-color: #ffffff;
  .top {
  	border-bottom: 2rpx dashed $u-border-color;
  }
  .btm{
    width: 100%;
    padding-top: 20rpx;
  }
	.left {
		text-align: center;
		font-size: 28rpx;
		color: #ffffff;
    width: 100%;
    overflow: hidden;
    image{
      width: 100%;
    }
	}
	.right {
		padding: 16rpx 0;
    width: 100%;
    .bottom {
    	.date {
    		font-size: 24rpx;
    	}
    }
		.tips {
			width: 100%;
			line-height: 36rpx;
      margin-top: 10rpx;
			display: flex;
			align-items: center;
			justify-content: space-between;
			font-size: 24rpx;
			.transpond {
				margin-right: 10rpx;
			}
			.explain {
				display: flex;
			}
		}
	}
}
.content{
  margin: 20rpx 0;
  padding: 20rpx;
  background: #FFFFFF;
}
.taobao {
	margin-top: 20rpx;
	width: 750rpx;
	background-color: white;
	padding: 20rpx;
	.title {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 20rpx;
		font-size: 30rpx;
		.left {
			display: flex;
			align-items: center;
		}
	}
	.ticket {
		display: flex;
    position: relative;
    overflow: hidden;
    margin-bottom: 15rpx;
    .status{
      position: absolute;
      left: -80rpx;
      top: 18rpx;
      height: 40rpx;
      z-index: 99;
      line-height: 40rpx;
      padding: 0 80rpx;
      font-size: $uni-font-size-mini;
      margin-right: -28rpx;
      transform:rotate(-45deg);
    }
		.left {
			width: 600rpx;
			padding: 20rpx 10rpx;
			background-color: rgb(255, 245, 244);
			border-radius: 20rpx;
			border-right: dashed 2rpx rgb(224, 215, 211);
			display: flex;
			.picture {
				width: 180rpx;
				border-radius: 20rpx;
			}
			.introduce {
				margin-left: 10rpx;
				.top{
					color:$u-type-error;
					font-size: 28rpx;
					.big{
						font-size: 50rpx;
						font-weight: bold;
						margin-right: 10rpx;
					}
				}
				.type{
					font-size: 24rpx;
          line-height: 34rpx;
					color: $u-type-info-dark;
				}
				.date{
					font-size: 20rpx;
					color: $u-type-info-dark;
				}
			}
		}
		.right {
			width: 200rpx;
			padding: 40rpx 10rpx;
			background-color: rgb(255, 245, 244);
			border-radius: 20rpx;
			display: flex;
			align-items: center;
			.use{
				height: auto;
				padding: 0 20rpx;
				font-size: 24rpx;
				border-radius: 40rpx;
				color: #ffffff!important;
				background-color: $u-type-error!important;
				line-height: 40rpx;
				color: rgb(117, 142, 165);
				margin-left: 20rpx;
			}
		}
	}
}
.video{
  padding: 20rpx 30rpx;
  background: #FFFFFF;
  margin-top: 20rpx;
  video{
    width: 100%;
  }
}
.navigation {
  width: 100%;
  position: fixed;
  bottom: 0;
	display: flex;
	margin-top: 100rpx;
	border-top: solid 2rpx #EEEEEE;
	background-color: #ffffff;
  z-index: 9999;
	padding: 10rpx 20rpx;
	.left {
    width: 550rpx;
		font-size: 20rpx;
		.item {
      position: relative;
		}
	}
	.right {
		font-size: 28rpx;
		align-items: center;
		.btn {
			line-height: 66rpx;
			padding: 0 30rpx;
			border-radius: 36rpx;
			color: #ffffff;
		}
		.buy {
      width: 220rpx;
			background-color: $u-type-error;
      text-align: center;
		}
    .done{
      width: 240rpx;
			background-color: $u-type-info-dark;
      text-align: center;
    }
	}
}
.checkbox{
  text-align: right;
}
</style>
