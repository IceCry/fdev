<template>
  
	<view class="wrap">
    
    <view class="top">
      <view class="avatar" @click="chooseAvatar">
        <image :src="userInfo.avatar" mode="widthFix"></image>
        <u-icon class="photo" name="camera" size="40"></u-icon>
      </view>
    </view>
    
    <view class="wrap">
    	<u-form :model="userInfo" :rules="rules" ref="uForm" :errorType="errorType">
    		<u-form-item label-width="120" label-position="top" label="昵称" prop="nickname">
    			<u-input :border="true" placeholder="请输入您的昵称" v-model="userInfo.nickname" type="text" maxlength="45"></u-input>
    		</u-form-item>
    		<u-form-item label-width="120" label-position="top" label="邮箱" prop="email">
    			<u-input :border="true" placeholder="(选填)请输入您的邮箱" v-model="userInfo.email" type="text" maxlength="45"></u-input>
    		</u-form-item>
    		<u-form-item label-width="120" label-position="top" label="微信号" prop="wechat">
    			<u-input :border="true" placeholder="(选填)请输入您的微信号" v-model="userInfo.wechat" type="text" maxlength="45"></u-input>
    		</u-form-item>
        <u-form-item label-position="top" label="性别" prop="gender">
        	<u-input :border="true" type="select" :select-open="sexSheetShow" v-model="sex_str" placeholder="请选择性别" @click="sexSheetShow = true"></u-input>
        </u-form-item>
    		<u-form-item label-position="top" label="所在地区" prop="region" label-width="150">
    			<u-input :border="true" type="select" v-model="userInfo.region" placeholder="请选择地区" @click="openAddress"></u-input>
    		</u-form-item>
    		<u-form-item label-position="top" label="详细地址" prop="address">
    			<u-input type="textarea" :border="true" placeholder="(选填)请填写详细地址" v-model="userInfo.address" maxlength="255" />
    		</u-form-item>
    	</u-form>
    	
    	<u-button v-if="showBtn" type="primary" @click="submit">确 定 修 改</u-button>
    	<u-action-sheet :list="sexList" v-model="sexSheetShow" @click="sexSheetCallback"></u-action-sheet>
    
      <simple-address ref="simpleAddress" :pickerValueDefault="cityPickerValueDefault" @onConfirm="onConfirm" themeColor="#007AFF"></simple-address>
    </view>
    
	</view>
</template>

<script>
import { getUserInfo, editUser } from '@/api/user.js';
import Util from '@/utils/util.js';
import { HTTP_REQUEST_URL } from '@/config/app.js';
  
import simpleAddress from '@/components/simple-address/simple-address.vue';
export default {
  components: {
    simpleAddress
  },
	data() {
		let that = this;
		return {
			userInfo:{
        nickname: '',
        gender: 0,
        region: '',
        address: '',
        area_code: '',
        email: '',
        wechat: ''
      },
      sex_str: '保密',
      sexList: [
      	{
          id: 1,
      		text: '男'
      	},
      	{
          id: 2,
      		text: '女'
      	},
      	{
          id: 0,
      		text: '保密'
      	}
      ],
      rules: {
        nickname: [
          { 
            required: true, 
            message: '请输入昵称', 
            trigger: 'blur' ,
          }, 
          { 
            min: 2, 
            max: 10, 
            message: '昵称长度在2到10个字符', 
            trigger: ['change','blur'],
          },
        ],
        region: [
          {
            required: true, 
            message: '请选择地区',
            trigger: 'change',
          }
        ],
      },
      check: false,
      sexSheetShow: false,
      pickerShow: false,
      radioCheckWidth: 'auto',
      radioCheckWrap: false,
      labelPosition: 'top',
      errorType: ['message'],
        
      t_address: [11, 1101, 110101],
      cityPickerValueDefault: [0, 0, 1],
      showBtn: true
    }
	},
  created() {
    var self = this;
    // 监听从裁剪页发布的事件，获得裁剪结果
    uni.$on('uAvatarCropper', path => {
      this.avatar = path;
      // 可以在此上传到服务端
      uni.uploadFile({
        url: HTTP_REQUEST_URL+'/api/avatar',
        filePath: path,
        name: 'avatar',
        header: {
          "Authori-zation": "Bearer " + self.$store.state.app.token
        },
        complete: (res) => {
          let data = JSON.parse(res.data);
          if(data.status==400){
            return self.$util.Tips({
            	title: data.msg
            });
          }else{
            self.userInfo.avatar = data.data.avatar;
          }
        }
      });
    })
  },
  onLoad() {
    this.getUser();
  },
  onReady() {
    this.$refs.uForm.setRules(this.rules);
  },
	methods: {
    openAddress() {
      // 根据 label 获取
      var index = this.$refs.simpleAddress.queryIndex(this.t_address, 'value');
      console.log(index);
      this.cityPickerValueDefault = index.index;
      this.$refs.simpleAddress.open();
    },
    onConfirm(e) {
      console.log(e);
      this.userInfo.region = e.label;
      this.userInfo.area_code = [e.provinceCode, e.cityCode, e.areaCode];
    },
    getUser(){
      var that=this;
      getUserInfo().then(res=>{
        console.log(res)
        that.userInfo = res.data.userInfo;
        that.sex_str = res.data.userInfo.sex_str;
        if(res.data.userInfo.area_code){
          that.t_address = res.data.userInfo.area_code;
        }
      });
    },
    chooseAvatar(){
      this.$u.route({
      	url: '/uview-ui/components/u-avatar-cropper/u-avatar-cropper',
      	params: {
      		// 输出图片宽度，高等于宽，单位px
      		destWidth: 300,
      		// 裁剪框宽度，高等于宽，单位px
      		rectWidth: 200,
      		// 输出的图片类型，如果'png'类型发现裁剪的图片太大，改成"jpg"即可
      		fileType: 'jpg',
      	}
      })
    },
    submit() {
      var that = this;
    	this.$refs.uForm.validate(valid => {
    		if (valid) {
          //保存数据
          that.showBtn = false;
          editUser(this.userInfo).then(res=>{
            Util.Tips({title:res.msg}, {tab:1,url:'/pages/my/my'});
          }).catch(err=>{
            that.showBtn = true;
            return that.$util.Tips({
            	title: err
            });
          })
    		} else {
    			console.log('验证失败');
    		}
    	});
    },
		// 点击actionSheet回调
		sexSheetCallback(index) {
			uni.hideKeyboard();
			this.userInfo.gender = this.sexList[index].id;
      this.sex_str = this.sexList[index].text;
		},
    // 选择地区回调
    regionConfirm(e) {
    	this.userInfo.region = e.province.label + '-' + e.city.label + '-' + e.area.label;
    }
	}
};
</script>

<style lang="scss" scoped>
  .wrap {
  	padding: 30rpx;
    background: #fff;
  }
  
  .agreement {
  	display: flex;
  	align-items: center;
  	margin: 40rpx 0;
  	
  	.agreement-text {
  		padding-left: 8rpx;
  		color: $u-tips-color;
  	}
  }
  
  
/deep/ .line {
	color: $u-light-color;
	font-size: 28rpx;
}
.wrap {
	.top {
		background-color: #ffffff;
		padding: 22rpx;
		.item {
			display: flex;
			font-size: 32rpx;
			line-height: 100rpx;
			align-items: center;
			border-bottom: solid 2rpx $u-border-color;
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
		margin-top: 20rpx;
		padding: 40rpx;
		padding-right: 0;
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
				.tags {
					width: 140rpx;
					padding: 16rpx 8rpx;
					border: solid 2rpx $u-border-color;
					text-align: center;
					border-radius: 50rpx;
					margin: 0 10rpx 20rpx;
					display: flex;
					font-size: 28rpx;
					align-items: center;
					justify-content: center;
					color: $u-content-color;
					line-height: 1;
				}
				.plus {
					//padding: 10rpx 0;
				}
			}
		}
		.default {
			margin-top: 50rpx;
			display: flex;
			justify-content: space-between;
			border-bottom: solid 2rpx $u-border-color;
			line-height: 64rpx;
			.tips {
				font-size: 24rpx;
			}
			.right {
			}
		}
	}
}
.avatar{
  width: 160rpx;
  height: 160rpx;
  margin: 20rpx auto;
  border: 3px solid #eee;
  border-radius: 50%;
  position: relative;
  overflow: hidden;
  image{
    width: 160rpx;
  }
  .photo{
    width: 40rpx;
    height: 40rpx;
    position: absolute;
    right: 20rpx;
    bottom: 10rpx;
  }
}
</style>
