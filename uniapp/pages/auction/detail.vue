<template>
	<view class="container">
		<view class="slider">
      <u-swiper :list="info.slider_image" mode="rect" height="750" borderRadius="0"></u-swiper>
    </view>
    <view class="title-box">
      <view class="title">
        <text>{{info.title}}</text>
      </view>
      <view class="info-box flex justify-between">
        <view class="tags">
          <u-tag text="包邮" v-if="info.postage_price == 0" size="mini" type="success" />
          <text class="text-gray" v-else>运费：￥{{info.postage_price || ''}}</text>
          <text class="text-gray u-p-l-30 text-sm">围观数：{{info.click || 0}}</text>
        </view>
        
        <template>
          <template v-if="info.auction_type==2">
            <view class="time text-red" v-if="info.auction_status==1 && info.timestamp>0">距结束<u-count-down fontSize="24" separator-color="#fa3534" color="#fa3534" bg-color="transparent" :timestamp="info.timestamp" :show-days="false" @end="ended"></u-count-down></view>
            <view class="time text-green" v-else-if="info.auction_status==0 && info.timestamp>0">距开始<u-count-down fontSize="24" separator-color="#19be6b" color="#19be6b" bg-color="transparent" :timestamp="info.timestamp" :show-days="false" @end="started"></u-count-down></view>
          </template>
          <u-tag text="已结拍" v-else-if="info.auction_status==2" size="mini" type="error" />
          <u-tag text="流拍" v-else-if="info.auction_status==-1" size="mini" type="error" />
          <u-tag text="已撤拍" v-else-if="info.auction_status==-2" size="mini" type="error" />
          <u-tag text="终止拍卖" v-else-if="info.auction_status==-3" size="mini" type="error" />
        </template>
      </view>
    </view>
    
    <view class="price-box flex justify-around">
      <view class="item flex flex-direction align-center">
        <text class="price">￥{{info.start_price | formatMoney}}</text>
        <text class="title">起拍价</text>
      </view>
      <view class="item flex flex-direction align-center" @click="showPriceRule=true">
        <text class="price" v-if="info.step_type==1">￥{{info.step_price|formatMoney}}</text>
        <text class="price" v-else-if="info.step_type==2">阶梯258</text>
        <text class="price" v-else-if="info.step_type==3">阶梯250</text>
        <view>
          <text class="title">加价幅度</text><u-icon name="bangzhu" custom-prefix="custom-icon" color="#adadad" size="24"></u-icon>
        </view>
      </view>
      <view class="item flex flex-direction align-center">
        <text class="price" v-if="info.special_id>0">￥{{info.special.deposit_price|formatMoney}}</text>
        <text class="price" v-else>￥{{info.deposit_price|formatMoney}}</text>
        <text class="title">保证金 <text v-if="info.special_id>0">（专场）</text></text>
      </view>
    </view>
    
    <view class="params" v-if="info.param.length>0">
      <!-- <view class="item flex">
        <text class="name">保证金</text>
        <view class="u-flex-1">
          <text class="value" v-if="info.special_id>0">￥{{info.special.deposit_price|formatMoney}}（专场）</text>
          <text class="value" v-else>￥{{info.deposit_price|formatMoney}}</text>
        </view>
      </view> -->
      <view class="item flex" v-for="(item, index) in info.params" :key="index">
        <text class="name">{{item.var}}</text>
        <text class="value u-flex-1">{{item.val}}</text>
      </view>
      <template v-if="info.type==2">
        <view class="item flex">
          <text class="name">资产类型</text>
          <view class="u-flex-1">
            <text class="value">资产类型</text>
          </view>
        </view>
        <view class="item flex">
          <text class="name">标的物类型</text>
          <view class="u-flex-1">
            <text class="value">资产类型</text>
          </view>
        </view>
        <view class="item flex">
          <text class="name">地址</text>
          <view class="u-flex-1">
            <text class="value">{{info.province}}/{{info.city}}/{{info.district}} {{info.address}}</text>
          </view>
        </view>
      </template>
      <view class="item flex" v-if="info.intro">
        <text class="name">简介</text>
        <view class="u-flex-1">
          <u-read-more color="#999999" font-size="24" show-height="300" close-text="查看更多">
            <text class="value">{{info.intro}}</text>
          </u-read-more>
        </view>
      </view>
    </view>
    
    <!-- 查看同场拍卖产品 -->
    <view class="special" v-if="info.special" @click="goPage('/pages/auction/special?uuid='+info.special.uuid)">
      <view class="item flex">
        <text class="name">拍卖专场</text>
        <view class="value u-flex-1 flex justify-between align-center">
          <u-section class="section" :title="info.special.title" color="#444444" font-size="24" :bold="false" :showLine="false" subTitle=" "></u-section>
        </view>
      </view>
      <view class="item flex">
        <text class="name">拍卖时间</text>
        <view class="value u-flex-1 flex justify-between align-center">
          <u-section class="section" :title="info.special.start_time" color="#444444" font-size="24" :bold="false" :showLine="false" subTitle=" " :right="false"></u-section>
        </view>
      </view>
      <view class="item flex">
        <text class="name">拍卖地点</text>
        <view class="value u-flex-1 flex justify-between align-center">
          <u-section class="section" :title="info.special.address" color="#444444" font-size="24" :bold="false" :showLine="false" subTitle=" " :right="false"></u-section>
        </view>
      </view>
    </view>
    
    <!-- info.auction_type == 2 &&  -->
    <view class="bid" v-if="info.auction_status!=0">
      <view class="">
        <u-section title="出价记录" lineColor="#cccccc" :sub-title="info.bid_times+'次出价'" :arrow ="false"></u-section>
      </view>
      <view class="items">
        <u-read-more color="#999999" font-size="24" show-height="600" close-text="查看更多">
          <view class="item flex" v-for="(item, index) in info.bids" :key="index">
            <view class="image flex align-center">
              <image :src="item.avatar" mode="widthFix"></image>
            </view>
            <view class="info flex u-flex-1 align-center">
              <view class="name flex flex-direction">
                <view class="text-black" v-if="item.is_offline==1">[现场] {{item.user_name}}</view>
                <view class="text-black" v-else>{{item.user_name_en}}</view>
                <view class="time text-sm">{{item.create_time}}</view>
              </view>
               <!-- v-if="item.shout && item.shout>0" -->
              <view class="price">
                
                <text v-if="item.shout>0" class="text-red text-bold">
                  <text class="ss-block">￥{{item.price}}元</text>
                  <text>第{{item.shout}}次喊价</text>
                </text>
                <text v-else-if="item.shout==-1" class="text-red text-bold">
                  <text class="ss-block">终止</text>
                  <text v-if="item.remark">说明：{{item.remark}}</text>
                </text>
                <view v-else>
                  <text class="text-red text-bold ss-block">￥{{item.price}}</text>
                  <u-tag text="领先" v-if="item.is_max==1" size="mini" type="error" />
                  <u-tag text="出局" v-else mode="plain" size="mini" type="info" />
                </view>
              </view>
            </view>
          </view>
        </u-read-more>
        <u-empty v-if="info.bids.length==0" text="暂无出价记录" mode="history"></u-empty>
      </view>
    </view>
    
    <view class="content">
      <u-section title="拍品详情" lineColor="#cccccc" :right="false"></u-section>
      <view class="rich">
        <jyf-parser :html="info.content" ref="article" :tag-style="tagStyle"></jyf-parser>
      </view>
    </view>
    
    <!-- 使用组件 -->
    <!-- <view class="likes">
      <u-section title="推荐拍品" :right="false"></u-section>
      <view class="items">
        
      </view>
    </view> -->
    
    <!-- 底部菜单 -->
    <view class="navH"></view>
    <view class="navigation">
    	<view class="left flex align-center">
    		<view class="item" @click="goPage('/pages/system/about/contact')">
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
    		<!-- <view class="item">
    			<u-icon name="share" :size="40" :color="$u.color['contentColor']"></u-icon>
    			<view class="text u-line-1">分享</view>
          <button class="share" :plain="true" type="default" open-type="share"></button>
    		</view> -->
    		<view class="item price">
    			<view class="text u-line-1">当前价: <text class="num">￥{{info.max_price|formatMoney}}</text></view>
    		</view>
    	</view>
    	<view class="right flex">
        <!--  v-if="info.auction_type==2" -->
        <template>
          <view v-if="info.auction_status==1" class="buy btn u-line-1" @click="showPop(true)">出  价</view>
          <view v-else-if="info.auction_status==0" class="unstart btn u-line-1">未开拍</view>
          <view v-else-if="info.auction_status==2" class="done btn u-line-1">已结拍</view>
          <view v-else-if="info.auction_status==-1" class="done btn u-line-1">流拍</view>
          <view v-else-if="info.auction_status==-2" class="done btn u-line-1">撤拍</view>
          <view v-else-if="info.auction_status==-3" class="done btn u-line-1">终止拍卖</view>
        </template>
    		<!-- <view v-else class="special-btn btn u-line-1" @click="goPage('/pages/auction/special?uuid='+info.special.uuid)"><image class="live-img" src="../../static/live.gif" mode="widthFix"></image> 进入拍场</view> -->
    	</view>
    </view>
    
    <!-- 弹窗 -->
    <view class="ss-pop" v-show="show">
      <view class="text-center u-padding-20 money">
        <text><text class="u-font-34">￥</text>{{info.max_price|formatMoney}}</text>
        <text class="u-font-24 u-padding-left-10">当前价</text>
        <view class="u-padding-10 close" data-flag="false" @click="showPop(false)">
          <u-icon name="close" color="#333333" size="28"></u-icon>
        </view>
      </view>
      <view class="flex justify-center">
        <u-number-box v-model="bid_price" :step="info.step_price?info.step_price:100" :min="info.max_price>0?info.max_price:info.start_price" max="9999999999" @change="valChange" :input-width="400" :input-height="80"></u-number-box>
      </view>
      <view @click="bid" class="text-center tips">确定出价</view>
    </view>
    <u-mask :show="showMask" @click="showPop(false)"></u-mask>
    
    <!-- <u-keyboard
    	default=""
    	ref="uKeyboard" 
    	mode="number" 
    	:mask="true" 
    	:mask-close-able="false"
    	:dot-enabled="false" 
    	v-model="show"
    	:safe-area-inset-bottom="true"
    	:tooltip="false"
    	@change="onChange"
    	@backspace="onBackspace">
    	<view>
    		<view class="u-text-center u-padding-20 money">
    			<text><text class="u-font-34">￥</text>{{info.max_price|formatMoney}}</text>
    			<text class="u-font-24 u-padding-left-10">当前价</text>
    			<view class="u-padding-10 close" data-flag="false" @click="showPop(false)">
    				<u-icon name="close" color="#333333" size="28"></u-icon>
    			</view>
    		</view>
    		<view class="u-flex u-row-center">
          <u-number-box v-model="bid_price" :step="info.step_price?info.step_price:100" :min="info.max_price>0?info.max_price:info.start_price" max="9999999999" @change="valChange" :input-width="400" :input-height="80"></u-number-box>
    		</view>
    		<view @click="bid" class="u-text-center tips">确定出价</view>
    	</view>
    </u-keyboard> -->
    <!-- 保证金弹窗 -->
    <u-modal v-model="showDeposit" :show-title="false" :confirm-style="{background:'#18b566'}" confirm-color="#ffffff" confirm-text="去缴纳" :content="'您的保证金不足。拍品出价需缴纳￥'+info.deposit_price+'元保证金，保证金随时可退'" :zoom="false" :show-cancel-button="true" @confirm="payDeposit"></u-modal>
    <!-- 加价幅度规则 -->
    <u-popup v-model="showPriceRule" mode="center" closeable custom-style="overflow:auto;" length="90%" zoom border-radius="10">
      <scroll-view class="pop-content" scroll-y="true">
        <jyf-parser :html="priceRule" ref="article" :tag-style="tagStyle"></jyf-parser>
      </scroll-view>
    </u-popup>
    
    <!-- 出价确认框 -->
    <u-modal v-model="showConfirm" @confirm="confirmed" @cancel="showMask = false" :show-cancel-button="true">
      <view class="slot-content">
        <rich-text :nodes="confirmContent"></rich-text>
      </view>
    </u-modal>
    
	</view>
</template>

<script>
  import { getCollectionInfo, getBidPrice, hasDeposit, createBid, getValidPrice } from "@/api/auction.js";
  import Util from '@/utils/util.js';
  import { doFavorite } from '@/api/api.js';
  import parser from "@/components/jyf-parser/jyf-parser";
  import { mapGetters } from "vuex";
  import {WSS_SERVER_URL, HEADER} from "@/config/socket.js";
  import { openBidSubscribe } from '@/utils/SubscribeMessage.js';
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
        show: false,
        bid_price: '',
				info: [],
        showDeposit: false,
        
        timer: null,
        timer2: null, //轮询模式
        showPriceRule: false, //加价幅度规则
        priceRule: '',
        
        showConfirm: false, //出价确认
        confirmContent: '',
        
        showMask: false
			};
		},
    onLoad(option) {
      this.uuid = option.uuid || '';
      this.getData();
      wx.showShareMenu({
        menus: ["shareAppMessage", "shareTimeline"]
      });
    },
    onUnload() {
      uni.closeSocket();
      clearInterval(this.timer);
      clearInterval(this.timer2);
    },
    onShareAppMessage(res) {
      return {
        title: this.info.title+'-北京盛世国际拍卖',
        path: '/pages/auction/detail?uuid='+this.uuid
      }
    },
    onShareTimeline(){
      return {
        title: this.info.title+'-北京盛世国际拍卖',
        imageUrl: this.info.thumb
      }
    },
    methods:{
      startWs(){
        //打开时的动作
        uni.onSocketOpen(() => {
          var that = this;
          console.log('WebSocket 已连接');
          this.timer = setInterval(function() {
            that.sendWsMsg("{\"type\":\"bid\", \"id\":\""+that.uuid+"\"}");
            // that.sendWsMsg("{\"type\":\"ping\"}");
          }, 3000);
        });
        //报错时的动作
        uni.onSocketError(error => {
          console.error('socket error:', error)
        })
        //断开时的动作
        uni.onSocketClose(() => {
          clearInterval(this.timer);
          console.log('WebSocket 已断开')
        })
        // 监听服务器推送的消息
        uni.onSocketMessage(message => {
          //把JSONStr转为JSON
          message = message.data.replace(" ", "");
          if (typeof message != 'object') {
            message = message.replace(/\ufeff/g, ""); //重点
            var jj = JSON.parse(message);
            message = jj;
          }
          console.log(message);
          //分配数据
          if(parseInt(message.next_price)>parseInt(this.bid_price)){
            this.bid_price = message.next_price+'';
          }
          this.info.max_price = message.max_price;
          this.info.end_time = message.end_time;
          this.info.bid_times = message.bid_times;
          this.info.bids = message.bids;
          this.info.next_price = message.next_price;
          this.info.timestamp = message.timestamp;
          this.info.auction_status = message.auction_status;
        })
        //建立websocket
        uni.connectSocket({
          url: WSS_SERVER_URL,
          header: HEADER
        });
        //发送消息
      },
      sendWsMsg(data){
        uni.sendSocketMessage({
          data: data
        })
      },
      bid(){
        var that = this;
        //判断出价是否高于下次价格
        if(this.bid_price<this.info.next_price || this.bid_price==0 || this.bid_price<=this.info.max_price){
          return this.$util.Tips({
            title: "出价不符合要求"
          });
        }
        //弹窗提示获取合理的出价规则 获取合法价格
        this.show = false;
        getValidPrice({id: this.info.id, price: this.bid_price, api: 1, user_id: this.uid}).then(res=>{
          /* if(res.data.price>that.info.bid_price){
            return that.$util.Tips({
              title: "下次出价最低为 "+res.data.price+"元 请重新出价"
            });
          } */
          let showPrice = res.data.price;
          if(that.info.max_price==0 && that.bid_price>res.data.price){
            showPrice = that.bid_price;
          }else{
            that.bid_price = res.data.price;
          }
          
          let tip = "";
          //todo 获取当前用户可用保证金 1:3，不满足则提示充值保证金
          //todo 错误，应计算当前一共保证金
          let nowDeposit = res.data.deposit;
          //保证金共计，如为专场则获取专场保证金额
          //let totalDeposit = parseInt(nowDeposit) + parseInt(that.info.deposit_price);
          //if(totalDeposit*3 < that.bid_price){
          if(nowDeposit<0){
            tip += "保证金金额不足，";
          }
          
          that.showConfirm = true;
          tip += "确认要出价 <strong style='color:red;'>￥"+showPrice+"</strong> 元吗？";
          that.confirmContent = tip;
        }).catch(err => {
          //获取失败则直接出价
          that.deposit();
        });
      },
      confirmed(){
        //确定出价
        this.deposit();
      },
      makeBid(){
        var that = this;
        //订阅消息
        openBidSubscribe().then(() => {
          that.doPost();
        });
      },
      doPost(){
        var that = this;
        createBid({id: this.info.id, price: this.bid_price}).then(res=>{
          that.showPop(false);
          that.showMask = false;
          that.$util.Tips({
            title: res.data.msg
          });
        }).catch(err => {
          that.show = false;
          that.showMask = false;
          return that.$util.Tips({
            title: err,
            endtime: 3000
          });
        });
      },
      deposit(){
        var that = this;
        hasDeposit({collection_id: this.info.id, special_id: this.info.special_id}).then(res=>{
          //出价
          that.makeBid();
        }).catch(err => {
          //显示保证金弹窗
          console.log(err)
          if(err.msg == '未登陆'){
            return that.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
          }else{
            //专场需通过列表缴纳
            if(that.info.special_id>0){
              return that.$util.Tips({title:'请先缴纳专场保证金'}, {tab:4, url:'/pages/auction/special?uuid='+that.info.special.uuid});
            }else{
              that.showDeposit = true;
            }
            that.show = false;
            that.showMask = false;
          }
        });
      },
      payDeposit() {
        //缴纳保证金
        return this.$util.Tips('/pages/system/deposit/deposit?uuid='+this.uuid+'&price='+this.info.deposit_price+'&special_id='+this.info.special_id);
      },
      valChange(e){
        console.log(e.value)
        //this.bid_price = ''+e.value;
      },
			onChange(val){
        console.log(this.bid_price, val)
				let now = this.bid_price += ''+val;
        this.$set(this, 'bid_price', now);
			},
			onBackspace(e){
        let price = this.bid_price+'';
        if(this.bid_price<=this.info.next_price) return;
				if(price.length>0){
					this.bid_price = price.substring(0, price.length-1);
				}
			},
      showPop(flag = true){
        //获取最新出价信息
        var that = this;
        getBidPrice(this.uuid).then(res=>{
          that.info.max_price = res.data.info.max_price;
          that.info.end_time = res.data.info.end_time;
          that.info.bid_times = res.data.info.bid_times;
          that.info.bids = res.data.info.bids;
          that.info.next_price = res.data.info.next_price;
          that.info.timestamp = res.data.info.timestamp;
          that.info.auction_status = res.data.info.auction_status;
        });
      	//this.bid_price = 0;
      	this.show = flag;
        this.showMask = flag;
      },
      finish(){
      	console.log(11111)
      },
      started(){
        this.info.auction_status=1;
      },
      ended(){
        this.info.auction_status=2;
      },
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      },
      getData(show=1){
      	var that = this;
        if(show==1){
          uni.showLoading();
        }
      	getCollectionInfo({uuid:this.uuid, uid:this.uid || 0}).then(res=>{
          uni.hideLoading();
          let info = res.data.info;
          that.info = info;
          that.bid_price = info.next_price;
          that.priceRule = info.price_rule;
          //todo 仅进行中的拍卖获取数据(暂仅限制结拍不获取，拍卖前进入页面则同样获取)
          if(info.auction_status==0 || info.auction_status==1){
            if(info.start_poll==1){
              clearInterval(that.timer2);
              that.timer2 = setInterval(function() {
                that.getData(0);
              }, 3000);
            }else{
              that.startWs();
            }
          }
          uni.setNavigationBarTitle({
            title: info.title
          });
      	})
      },
      favorite(type){
        if (this.isLogin === false) {
          return this.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
        } else {
          let that = this;
          doFavorite({mid:this.info.id, mtable: 'collection', type:type}).then(res => {
            that.$set(that.info, 'is_favorite', !that.info.is_favorite);
          }).catch(err => {
            return that.$util.Tips({
              title: err
            });
          });
        }
      },
      fMoney: function(s, type=0) {
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
.bid{
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
      width: 400rpx;
    }
    .price{
      width: 200rpx;
      font-size: $uni-font-size-sm;
      color: $ss-font-normal;
      text-align: right;
    }
  }
}

.content{
  width: 100%;
  margin-bottom: 20rpx;
  padding: 20rpx;
  background: #FFFFFF;
  .rich{
    width: 100%;
    padding: 30rpx 0;
  }
  image{
    width: 100%;
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
  bottom: 0;
	display: flex;
	margin-top: 100rpx;
	border-top: solid 2rpx #EEEEEE;
	background-color: #ffffff;
	padding: 6rpx 0;
	.left {
    width: 550rpx;
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
      margin-left: 0;
      .num{
        color: $u-type-error;
        font-weight: 700;
        font-size: $uni-font-size-super;
      }
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
      width: 180rpx;
			background-color: #19be6b;
      text-align: center;
		}
    .unstart{
      width: 180rpx;
			background-color: $u-type-info-dark;
      text-align: center;
    }
    .done{
      width: 180rpx;
			background-color: $u-type-info-dark;
      text-align: center;
    }
    .special-btn{
      width: 220rpx;
      text-align: center;
      margin-right: 20rpx;
      background: $u-type-error;
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
.live-img{
  max-height: 80rpx;
}

.slot-content {
  text-align: center;
}

.ss-pop{
  position: fixed;
  bottom: 0;
  left: 0;
  background: #fff;
  width: 100%;
  height: 600rpx;
  padding: 10rpx 0;
  z-index: 20000;
  
  .tips{
    margin-top: 60rpx;
  }
}
</style>
