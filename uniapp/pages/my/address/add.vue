<template>
	<view class="wrap">
		<view class="top">
			<view class="item">
				<view class="left">收货人</view>
				<input type="text" placeholder-class="line" v-model="formData.name" placeholder="请填写收货人姓名" />
			</view>
			<view class="item">
				<view class="left">手机号码</view>
				<input type="number" placeholder-class="line" v-model="formData.phone" placeholder="请填写收货人手机号" maxlength="11" />
			</view>
			<view class="item" @click="openAddress">
				<view class="left">所在地区</view>
				<input disabled type="text" placeholder-class="line" v-model="region" placeholder="省市区县、乡镇等" />
			</view>
			<view class="item address">
				<view class="left">详细地址</view>
				<textarea type="text" placeholder-class="line" v-model="formData.detail" placeholder="街道、楼牌等" />
			</view>
		</view>
		<view class="bottom">
			<view class="default">
				<view class="left">
					<view class="set">设为默认地址</view>
					<view class="tips">提醒：每次下单会默认推荐该地址</view>
				</view>
				<view class="right">
          <u-switch v-model="checked" active-color="#fa3534" inactive-color="#cccccc" @change="setDefault" active-value="1" inactive-value="0"></u-switch>
        </view>
			</view>
		</view>
    
    <view class="btn">
      <u-button v-if="showBtn" type="error" shape="circle" @click="submit">确定保存</u-button>
    </view>
    
    <simple-address ref="simpleAddress" :pickerValueDefault="cityPickerValueDefault" @onConfirm="onConfirm" themeColor="#fa3534"></simple-address>
	</view>
</template>

<script>
import simpleAddress from '@/components/simple-address/simple-address.vue';
import { saveAddress, getAddressDetail } from '@/api/user.js';
export default {
  components: {
    simpleAddress
  },
	data() {
		return {
      uuid: '',
      oid: '',//拍品uuid
      cartIds: '',//商品购物车
      formData:{
        uuid: '',
        name: '',
        phone: '',
        province: '',
        city: '',
        district: '',
        city_id: 0,
        detail: '',
        is_default: 0,
        area_code: [0, 0, 0]
      },
      //地址选择器
      t_address: [11, 1101, 110101],
      cityPickerValueDefault: [0, 0, 1],
      region: '',
      showBtn: true,
      checked: false
		};
	},
  onLoad(option) {
    var uuid = option.uuid || '';
    this.cartIds = option.cartIds || '';
    this.oid = option.oid || '';
    if(uuid){
      this.getAddressInfo(uuid);
    }
  },
	methods: {
    getAddressInfo(uuid){
      var that = this;
      getAddressDetail(uuid).then(res=>{
        let info = res.data.info;
			  that.formData = info;
        that.t_address = info.area_code;
        that.region = info.province + '-' + info.city + '-' + info.district;
        that.checked = info.is_default?true:false;
        that.formData.city_id = info.area_code[1];
			});
    },
    submit(){
      var that = this;
      if(!this.formData.name || !this.formData.phone || !this.formData.district || !this.formData.detail){
        return this.$util.Tips({
          title: "请填写完整信息"
        });
      }
      if(this.formData.phone.length!=11){
        return this.$util.Tips({
          title: "手机号码输入有误"
        });
      }
      uni.showLoading();
      that.showBtn = false;
      saveAddress(this.formData).then(res=>{
        let url = '/pages/order/confirm?uuid='+that.oid+'&addressId='+res.data.id+"&cartIds="+this.cartIds;
        console.log(url)
        if(that.cartIds || that.oid){
          that.$util.Tips(url);
        }else{
          that.$util.Tips({title:res.msg}, {tab:4, url:'/pages/my/address/address'});
        }
      }).catch(err => {
        uni.hideLoading();
        that.showBtn = true;
        return that.$util.Tips({
          title: err
        });
      });
    },
		setDefault(e) {
      this.formData.is_default = e;
      this.checked = e>0?true:false;
    },
    openAddress() {
      // 根据 label 获取
      var index = this.$refs.simpleAddress.queryIndex(this.t_address, 'value');
      console.log(index);
      this.cityPickerValueDefault = index.index;
      this.$refs.simpleAddress.open();
    },
    onConfirm(e) {
      console.log(e);
      this.region = e.label;
      this.formData.area_code = [e.provinceCode, e.cityCode, e.areaCode];
      this.formData.province = e.labelArr[0];
      this.formData.city = e.labelArr[1];
      this.formData.district = e.labelArr[2];
      this.formData.city_id = e.cityCode;
    },
    // 选择地区回调
    /* regionConfirm(e) {
    	console.log(e.province.label + '-' + e.city.label + '-' + e.area.label);
    } */
	}
};
</script>

<style lang="scss" scoped>
/deep/ .line {
	color: $u-light-color;
	font-size: 28rpx;
}
.wrap {
	background-color: #f2f2f2;
	.top {
		background-color: #ffffff;
		padding: 22rpx;
		.item {
			display: flex;
			font-size: 32rpx;
			line-height: 100rpx;
			align-items: center;
			border-bottom: solid 1rpx $ss-bg-grey;
      :last-child{
        border-bottom: none;
      }
			.left {
				width: 180rpx;
			}
			input {
				text-align: left;
			}
		}
		
		.address {
			padding: 20rpx 0;
			textarea {
				// width: 100%;
				height: 150rpx;
				background-color: #f7f7f7;
				line-height: 60rpx;
				margin: 40rpx auto;
				padding: 20rpx;
			}
		}
		.site-clipboard {
			padding-right: 40rpx;
			textarea {
				// width: 100%;
				height: 150rpx;
				background-color: #f7f7f7;
				line-height: 60rpx;
				margin: 40rpx auto;
				padding: 20rpx;
			}
			.clipboard {
				display: flex;
				justify-content: center;
				align-items: center;
				font-size: 26rpx;
				color: $u-tips-color;
				height: 80rpx;
				.icon {
					margin-top: 6rpx;
					margin-left: 10rpx;
				}
			}
		}
	}
	.bottom {
		padding: 0 20rpx;
		background-color: #ffffff;
		font-size: 28rpx;
		.tag {
			display: flex;
			.left {
				width: 160rpx;
			}
			.right {
				display: flex;
				flex-wrap: wrap;
			}
		}
		.default {
			display: flex;
			justify-content: space-between;
			line-height: 64rpx;
			.tips {
				font-size: 24rpx;
			}
			.right {
			}
		}
	}
}
.btn{
  padding: 30rpx;
  margin-top: 30rpx;
}
</style>
