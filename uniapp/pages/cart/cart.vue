<template>
	<view class="container">
		<!-- 空白页 -->
		<view v-if="!isLogin || empty===true" class="empty">
      <u-empty text="空空如也" mode="car"></u-empty>
			<view v-if="isLogin" class="empty-tips">
				<navigator class="navigator" v-if="isLogin" url="/pages/goods/list" open-type="navigate">随便逛逛></navigator>
			</view>
			<view v-else class="empty-tips">
				<view class="navigator" @click="navToLogin">去登陆></view>
			</view>
		</view>
		<view v-else>
			<!-- 列表 -->
			<view class="cart-list">
				<block v-for="(item, index) in cartList" :key="item.id">
					<view class="cart-item" :class="{'b-b': index!==cartList.length-1}">
						<view class="image-wrapper">
              <view class="image flex align-center" @click="goPage('/pages/goods/detail?uuid='+item.goods.uuid)">
                <image :src="item.goods.thumb" :class="[item.loaded]" mode="widthFix" lazy-load @load="onImageLoad('cartList', index)" @error="onImageError('cartList', index)"></image>
              </view>
							<view class="checkbox" @click="check('item', index, item.id)">
                <u-icon name="checkmark-circle" v-if="item.checked" color="#fa3534" size="40"></u-icon>
                <u-icon name="checkmark-circle" v-else color="#cccccc" size="40"></u-icon>
              </view>
						</view>
						<view class="item-right">
							<text class="line1 title" @click="goPage('/pages/goods/detail?uuid='+item.goods.uuid)">{{item.goods.title}}</text>
							<text class="attr line-through text-sm">市场价：¥{{item.goods.market_price}}</text>
							<text class="price text-red">¥{{item.goods.sale_price}}</text>
							<uni-number-box class="step" :min="1" :max="item.goods.stock" :value="(item.num>item.goods.stock)?item.goods.stock:item.num" :isMax="(item.num>=item.goods.stock)?true:false" :isMin="item.num===1" :index="index" @eventChange="numberChange"></uni-number-box>
						</view>
            <u-icon name="close" color="#999999" size="30" @click="deleteCartItem(index)"></u-icon>
					</view>
				</block>
			</view>
			<!-- 底部菜单栏 -->
			<view class="action-section">
				<view class="checkbox">
					<image 
						:src="allChecked?'/static/selected.png':'/static/select.png'" 
						mode="aspectFit"
						@click="check('all')"
					></image>
					<view class="clear-btn" :class="{show: allChecked}" @click="clearCart">
						清空
					</view>
				</view>
				<view class="total-box">
					<text class="price text-red">¥{{total}}</text>
					<!-- <text class="coupon">
						已优惠
						<text></text>
						元
					</text> -->
				</view>
				<button type="primary" class="no-border confirm-btn" @click="createOrder">去 结 算</button>
			</view>
      
      <u-loadmore font-size="24" v-if="cartList.length>0" :status="loadStatus" />
		</view>
	</view>
</template>

<script>
  import { mapGetters } from "vuex";
  import Util from '@/utils/util.js';
	import uniNumberBox from '@/components/uni-number-box.vue';
  import { getCartData, updateCartNum, cartDelete } from '@/api/order.js';
	export default {
		components: {
			uniNumberBox
		},
		computed: mapGetters(['isLogin', 'uid']),
		data() {
			return {
				total: 0, //总价格
				allChecked: false, //全选状态  true|false
				empty: false, //空白页现实  true|false
				cartList: [],
        checked: [], //当前选中
        cids: [], //购物车所有id
				loadStatus: 'loadmore',
				limit: 15,
				page: 1
			};
		},
    onShow() {
      if(this.isLogin){
        this.page = 1;
        this.loadStatus = 'loadmore';
        this.cartList = [];
        this.getData();
      }
    },
		onLoad(){
			this.checked =  uni.getStorageSync('checkedCart') || [];
		},
		watch:{
			//显示空白页
			cartList(e){
				let empty = e.length === 0 ? true: false;
				if(this.empty !== empty){
					this.empty = empty;
				}
			}
		},
		methods: {
			//请求数据
			async getData(){
        var that = this;
				var cartIds = uni.getStorageSync('checkedCart') || [];
        uni.showLoading();
        getCartData({page: this.page, limit: this.limit, type: 'goods'}).then(res=>{
          uni.hideLoading();
          let list = res.data.list;
          let loadend = list.length < that.limit;
          //判断是否已选中
          list.forEach(item=>{
            if(cartIds.includes(item.id)){
              item.checked=true;
              that.cids.push(item.id);
            }
          });
          that.cartList = list;
          if(loadend){
            that.loadStatus = 'nomore';
          }else{
            that.page++;
          }
          // that.$set(that, 'cartList', list);
          that.calcTotal();  //计算总价
        })
			},
			//监听image加载完成
			onImageLoad(key, index) {
				this.$set(this[key][index], 'loaded', 'loaded');
			},
			//监听image加载失败
			onImageError(key, index) {
				this[key][index].image = '/static/errorImage.jpg';
			},
			navToLogin(){
				uni.switchTab({
					url: '/pages/my/my'
				})
			},
			 //选中状态处理
			check(type, index, id){
        console.log(type)
				if(type === 'item'){
					var ids = uni.getStorageSync('checkedCart') || [];
					this.cartList[index].checked = !this.cartList[index].checked;
					//仅记录选中的商品
					if(this.cartList[index].checked){
						if(!ids.includes(id)){
							ids.push(id);
						}
					}else{
						if(ids.includes(id)){
							ids.splice(ids.findIndex(item => item === id), 1);
						}
					}
					uni.setStorageSync('checkedCart', ids);
				}else{
					var ids = [];
					this.cids=[];
					const checked = !this.allChecked
					const list = this.cartList;
					list.forEach(item=>{
						item.checked = checked;
						this.cids.push(item.goods_id);
						if(checked){
							ids.push(item.id);
						}
					})
					this.allChecked = checked;
					if(!checked){
						ids = [];
					}
					uni.setStorageSync('checkedCart', ids);
				}
				this.calcTotal(type);
			},
			//数量
			numberChange(data){
        var that = this;
        updateCartNum({id: this.cartList[data.index].id, num: data.number}).then(res=>{
          that.cartList[data.index].num = data.number;
          that.calcTotal();
        }).catch(err => {
          that.show = false;
          return that.$util.Tips({
            title: err
          });
        });
			},
			//删除
			deleteCartItem(index){
				let list = this.cartList;
				let row = list[index];
				let id = row.id;
        console.log(index)
        
        var that = this;
        cartDelete({ids: id}).then(res=>{
          that.cartList.splice(index, 1);
          that.calcTotal();
        }).catch(err => {
          that.show = false;
          return that.$util.Tips({
            title: err
          });
        });
			},
			//清空
			clearCart(){
        var that = this;
				let ids = this.cids.join(',');
				uni.showModal({
					content: '清空购物车？',
					success: (e)=>{
						if(e.confirm){
              cartDelete({ids: ids}).then(res=>{
                that.cartList= [];
                uni.setStorageSync('checkedCart', []);
              }).catch(err => {
                that.show = false;
                return that.$util.Tips({
                  title: err
                });
              });
						}
					}
				})
			},
			//计算总价
			calcTotal(){
				let list = this.cartList;
				if(list.length === 0){
					this.empty = true;
					return;
				}
				let total = 0;
				let checked = true;
				list.forEach(item=>{
					if(item.checked === true){
						total += item.goods.sale_price * item.num;
					}else if(checked === true){
						checked = false;
					}
				})
				this.allChecked = checked;
				this.total = Number(total.toFixed(2));
			},
			//创建订单
			createOrder(){
        var that = this;
				let list = this.cartList;
        let str = '';
				let goodsData = [];
				list.forEach(item=>{
					if(item.checked){
						if(!str){
              str = item.id;
            }else{
              str += ','+item.id;
            }
					}
				})
        
        if(!str){
          return that.$util.Tips({
            title: '请选择结算商品'
          });
        }

				uni.navigateTo({
					url: '/pages/order/confirm?cartIds='+str
				})
			},
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      }
		}
	}
</script>

<style lang='scss'>
	.container{
    background: #FFFFFF;
    min-height: 100vh;
		padding-bottom: 134rpx;
		/* 空白页 */
		.empty{
			position:fixed;
			left: 0;
			top:0;
			width: 100%;
			height: 100vh;
			padding-bottom:100rpx;
			display:flex;
			justify-content: center;
			flex-direction: column;
			align-items:center;
			background: #fff;
			image{
				width: 240rpx;
				height: 160rpx;
				margin-bottom:30rpx;
			}
			.empty-tips{
				display:flex;
				font-size: $uni-font-size-sm;
				color: $uni-text-color-disable;
				.navigator{
					color: $uni-color-primary;
					margin-left: 16rpx;
				}
			}
		}
	}
	/* 购物车列表项 */
	.cart-item{
		display:flex;
		position:relative;
		padding:20rpx 30rpx;
		.image-wrapper{
			width: 230rpx;
			height: 230rpx;
			flex-shrink: 0;
			position:relative;
      border-radius:8rpx;
		}
    .image{
			width: 230rpx;
			height: 230rpx;
      overflow: hidden;
    }
		.checkbox{
			position:absolute;
			left:-16rpx;
			top: -16rpx;
			z-index: 8;
			font-size: 44rpx;
			line-height: 1;
			padding: 4rpx;
			color: $uni-text-color-disable;
			background:#fff;
			border-radius: 50px;
		}
		.item-right{
			display:flex;
			flex-direction: column;
			flex: 1;
			overflow: hidden;
			position:relative;
			padding-left: 30rpx;
			.title,.price{
				font-size:$uni-font-size-lg;
				height: 40rpx;
				line-height: 40rpx;
			}
			.attr{
				font-size: $uni-font-size-sm;
				color: $uni-text-color-grey;
				height: 50rpx;
				line-height: 50rpx;
			}
			.price{
				height: 50rpx;
				line-height:50rpx;
			}
		}
	}
	/* 底部栏 */
	.action-section{
		/* #ifdef H5 */
		margin-bottom:100rpx;
		/* #endif */
		position:fixed;
		left: 30rpx;
		bottom:30rpx;
		z-index: 95;
		display: flex;
		align-items: center;
		width: 690rpx;
		height: 100rpx;
		padding: 0 30rpx;
		background: rgba(255,255,255,.9);
		box-shadow: 0 0 20rpx 0 rgba(0,0,0,.5);
		border-radius: 16rpx;
		.checkbox{
			height:52rpx;
			position:relative;
			image{
				width: 52rpx;
				height: 100%;
				position:relative;
				z-index: 5;
			}
		}
		.clear-btn{
			position:absolute;
			left: 26rpx;
			top: 0;
			z-index: 4;
			width: 0;
			height: 52rpx;
			line-height: 52rpx;
			padding-left: 38rpx;
			font-size: $uni-font-size-base;
			color: #fff;
			background: $uni-text-color-disable;
			border-radius:0 50px 50px 0;
			opacity: 0;
			transition: .2s;
			&.show{
				opacity: 1;
				width: 120rpx;
			}
		}
		.total-box{
			flex: 1;
			display:flex;
			flex-direction: column;
			text-align:right;
			padding-right: 40rpx;
			.price{
				font-size: 40rpx;
			}
			.coupon{
				font-size: $uni-font-size-sm;
				color: $uni-text-color-grey;
				text{
					color: $uni-text-color-grey;
				}
			}
		}
		.confirm-btn{
			padding: 0 38rpx;
			margin: 0;
			border-radius: 100px;
			height: 76rpx;
			line-height: 76rpx;
			font-size: $uni-font-size-base;
			background: $u-type-error;
			box-shadow: 1px 2px 5px rgba(217, 60, 93, 0.72)
		}
	}
	/* 复选框选中状态 */
	.action-section .checkbox.checked,
	.cart-item .checkbox.checked{
		color: $uni-color-primary;
	}
</style>
