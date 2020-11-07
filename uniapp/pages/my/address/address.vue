<template>
	<view class="container">
		<view class="item" v-for="(item, index) in lists" :key="item.id" @click="chooseAddress(item.id)">
			<view class="top flex justify-between">
        <view class="flex">
          <view class="name">{{ item.name }}</view>
          <view class="phone">{{ item.phone }}</view>
          <view class="tag" v-if="item.is_default==1">
            <text class="red">默认</text>
          </view>
        </view>
				<u-icon v-if="!cartIds && !oid" @click="confirmDelete(item.id, index)" name="trash" :size="40" color="#cccccc"></u-icon>
			</view>
			<view class="bottom">
				<text class="line2">{{item.province}}{{item.city}}{{item.district}}{{item.detail}}</text>
				<u-icon v-if="!cartIds && !oid" @click="edit(item.id)" name="edit-pen" :size="40" color="#cccccc"></u-icon>
			</view>
		</view>
    
    <u-empty v-if="lists.length==0" margin-top="300" mode="address"></u-empty>
    
    <u-modal v-model="showModal" :show-cancel-button="true" content="确定要删除此地址信息吗？" @confirm="deleteAddress"></u-modal>
    
		<view class="addSite" @click="addAddress">
			<view class="add">
				<u-icon name="plus" color="#ffffff" class="icon" :size="30"></u-icon>新建收货地址
			</view>
		</view>
	</view>
</template>

<script>
import { getAddress, delAddress } from '@/api/user.js';
export default {
	data() {
		return {
			lists: [],
      cartIds: '', //购物车
      oid: '', //拍品id
      showModal: false,
      delId: 0,
      delIndex: 0
		};
	},
	onLoad(options) {
		this.getData();
    this.cartIds = options.cartIds || '';
    this.oid = options.uuid || '';
	},
	methods: {
		getData() {
			var that=this;
			getAddress().then(res=>{
			  that.lists = res.data.lists;
			});
		},
		addAddress(){
			uni.navigateTo({
			    url: '/pages/my/address/add?cartIds='+this.cartIds+"&oid="+this.oid
			});
		},
    edit(uuid){
      uni.navigateTo({
          url: '/pages/my/address/add?uuid='+uuid+"&cartIds="+this.cartIds+"&oid="+this.oid
      });
    },
    confirmDelete(uuid, index){
      this.showModal = true;
      this.delId = uuid;
      this.delIndex = index;
    },
    deleteAddress(){
      var that = this;
      delAddress(this.delId).then(res=>{
        that.$util.Tips({title: '操作成功'});
        that.lists.splice(this.delIndex, 1);
      }).catch(err => {
        return that.$util.Tips({
          title: err
        });
      });
    },
    chooseAddress(id) {
      let cartIds = '';
      if ((this.cartIds || this.oid) && id) {
        uni.redirectTo({
          url: '/pages/order/confirm?cartIds=' + this.cartIds + '&addressId=' + id + "&uuid="+this.oid
        })
      }
    }
	}
};
</script>

<style lang="scss" scoped>
.container{
  background: #ffffff;
  padding: 20rpx;
  min-height: 100vh;
}
.item {
	padding: 30rpx 20rpx;
  border-bottom: 1px solid #eeeeee;
	.top {
		display: flex;
		font-weight: bold;
		font-size: 34rpx;
		.phone {
			margin-left: 30rpx;
      font-weight: normal;
		}
		.tag {
			display: flex;
			font-weight: normal;
			align-items: center;
			text {
				display: block;
				width: 60rpx;
				height: 34rpx;
				line-height: 34rpx;
				color: #ffffff;
				font-size: 20rpx;
				border-radius: 6rpx;
				text-align: center;
				margin-left: 30rpx;
				background-color:rgb(49, 145, 253);
			}
			.red{
				background-color:red
			}
		}
	}
	.bottom {
		display: flex;
		margin-top: 20rpx;
		font-size: 28rpx;
		justify-content: space-between;
		color: #999999;
	}
}
.addSite {
	display: flex;
	justify-content: space-around;
	width: 600rpx;
	line-height: 80rpx;
	position: absolute;
	bottom: 30rpx;
	left: 80rpx;
	background-color: red;
	border-radius: 60rpx;
	font-size: 30rpx;
	.add{
		display: flex;
		align-items: center;
		color: #ffffff;
		.icon{
			margin-right: 10rpx;
		}
	}
}
</style>
