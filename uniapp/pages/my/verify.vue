<template>
	<view class="u-demo">
		<view class="u-demo-wrap">
      <u-section title="个人实名认证" font-size="28" :show-line="false" :right="false"></u-section>
			<view class="u-demo-area">
				<u-toast ref="uToast"></u-toast>
        <u-top-tips ref="uTips"></u-top-tips>
        <view class="image-area">
          
          <view class="uploader flex justify-between">
            <u-upload :action="action" :header="{AuthoriZation: 'Bearer '+token}" :file-list="fileList" :auto-upload="true" max-count="1" width="310" height="200" :size-type="compressed" upload-text="证件正面" index="front" :form-data="{type:'cert_front'}" @on-success="onSuccessFront"></u-upload>
            <u-upload :action="action" :header="{AuthoriZation: 'Bearer '+token}" :file-list="fileList" :auto-upload="true" max-count="1" width="310" height="200" :size-type="compressed" upload-text="证件反面" index="front" :form-data="{type:'cert_end'}" @on-success="onSuccessEnd"></u-upload>
          </view>
          
          <view class="desc">
            <text>说明：请上传清晰身份证照片。</text>
          </view>
        </view>
        
        <u-form :model="form" ref="uForm">
          <u-form-item><u-input class="input" :border="true" placeholder="请输入您的真实姓名" v-model="form.contact" /></u-form-item>
          <u-form-item><u-input type="number" class="input" :border="true" placeholder="请输入您的手机号" v-model="form.phone" maxlength="11" /></u-form-item>
          <u-form-item>
            <!-- <u-input :border="true" @click="showCategory = true" placeholder="证件类型" v-model="form.cert_type" type="select" :select-open="showCategory" /> -->
            <u-input :border="true" type="select" :select-open="showCategory" v-model="cert_type_str" placeholder="请选择证件类型" @click="showCategory = true"></u-input>
          </u-form-item>
          <u-form-item><u-input class="input" :border="true" placeholder="请输入您的身份证号" v-model="form.cert_id" maxlength="18" /></u-form-item>
        </u-form>
        
        <view class="agree">
          <text class="text-sm">我们承诺保护您的个人隐私，您输入的信息将仅用于实名认证。</text>
        </view>
        
				<u-button class="submit" v-if="showBtn" type="primary" @click="submit" size="medium">确定提交</u-button>
			</view>
		</view>
    
		<u-select mode="single-column" :list="category" v-model="showCategory" @confirm="showCategoryCallback"></u-select>
    
	</view>
</template>

<script>
  import { HTTP_REQUEST_URL } from '@/config/app';
  import { openVerifySubscribe } from '@/utils/SubscribeMessage.js';
  import { doVerify } from '@/api/user.js';
  
	export default {
		data() {
			return {
				action: HTTP_REQUEST_URL+'/api/attach',
        token: this.$store.state.app.token,
        showCategory: false,
        showBtn: true,
        category:[{value:1, label:'大陆身份证'}, {value:2, label:'香港身份证'}, {value:3, label:'澳门身份证'}, {value:4, label:'台湾身份证'}],
        form:{
          contact: '',
          cert_type: 1,
          cert_id: '',
          phone: '',
          cert_front: '',
          cert_end: '',
        },
        cert_type_str: '大陆身份证',
      }
		},
    onReady() {
      
    },
		onLoad() {
			
		},
		methods: {
      submit() {
        var that = this;
        var cert_pic = this.form.cert_front+','+this.form.cert_end;
        if(!this.form.cert_front || !this.form.cert_end){
          return this.$refs.uTips.show({
            title: '请上传身份证正反面',
            type: 'error',
            duration: '2300'
          })
        }
        if(this.form.phone.length!=11){
          return this.$refs.uTips.show({
            title: '手机号码错误',
            type: 'error',
            duration: '2300'
          })
        }
        if(!this.form.contact || !this.form.cert_id){
          return this.$refs.uTips.show({
            title: '请填写完整信息',
            type: 'error',
            duration: '2300'
          })
        }
        
        //提交认证信息
        var data = {contact: that.form.contact, cert_type: that.form.cert_type, cert_id: that.form.cert_id, cert_pic: cert_pic, phone: that.form.phone};
        //订阅消息
        openVerifySubscribe().then(() => {
          that.doPost(data);
        });
      },
      doPost(data){
        var that = this;
        that.showBtn = false;
        uni.showLoading({
          title:'提交中',
          mask: true
        });
        doVerify(data).then(res=>{
          that.$util.Tips({title:res.msg}, {tab:4, url:'/pages/my/my'});
        }).catch(err=>{
          uni.hideLoading();
          that.showBtn = true;
          return that.$util.Tips({
            title: err
          });
        })
      },
      // 选择分类回调
      showCategoryCallback(e) {
        console.log(e)
      	this.form.cert_type = e[0].value;
        this.cert_type_str = e[0].label;
      },
			onSuccessFront(res) {
        this.$refs.uTips.show({
          title: '上传成功',
          type: 'success',
          duration: '2000'
        })
				this.form.cert_front = res.data.url;
        if(res.data.data.errcode==0){
          this.form.contact = res.data.data.name;
          this.form.cert_id = res.data.data.id;
        }
			},
			onSuccessEnd(res) {
        this.$refs.uTips.show({
          title: '上传成功',
          type: 'success',
          duration: '2000'
        })
				this.form.cert_end = res.data.url;
			},
		}
	}
</script>

<style lang="scss">
	.u-demo-wrap {
		background-color: #FFFFFF;
		padding: 20rpx;
		margin-left: -14rpx;
		margin-right: -14rpx;
	}
  .u-demo-area{
    padding: 20rpx 0;
  }
	
	/deep/ .slot-btn {
		width: 329rpx;
		height: 140rpx;
		display: flex;
		justify-content: center;
		align-items: center;
		background: rgb(244, 245, 246);
		border-radius: 10rpx;
	}

	.slot-btn__hover {
		background-color: rgb(235, 236, 238);
	}

	.pre-box {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
	}

	.pre-item {
		flex: 0 0 48.5%;
		border-radius: 10rpx;
		height: 140rpx;
		overflow: hidden;
		position: relative;
		margin-bottom: 20rpx;
	}

	.u-progress {
		position: absolute;
		bottom: 10rpx;
		left: 8rpx;
		right: 8rpx;
		z-index: 9;
		width: auto;
	}

	.pre-item-image {
		width: 100%;
		height: 140rpx;
	}

	.u-delete-icon {
		position: absolute;
		top: 10rpx;
		right: 10rpx;
		z-index: 10;
		background-color: $u-type-error;
		border-radius: 100rpx;
		width: 44rpx;
		height: 44rpx;
		display: flex;
		align-items: center;
		justify-content: center;
	}
  .image-area{
    background: $ss-bg-color;
    padding: 10rpx 0;
    .desc{
      width: 600rpx;
      margin: 20rpx auto;
      font-size: $uni-font-size-mini;
      color: $ss-font-dark;
    }
  }
  .input{
    background: $ss-bg-color;
  }
  .agree{
    margin: 30rpx 0;
  }
  .u-upload{
    background: #CCCCCC;
  }
  .u-upload .u-list-item{
    border: 1px dashed #cccccc;
  }
  .u-form-item{
    padding: 10rpx 0 !important;
  }
</style>
