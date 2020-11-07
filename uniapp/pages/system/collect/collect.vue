<template>
	<view class="u-demo">
		<view class="u-demo-wrap">
      <u-section title="上传藏品信息" font-size="28" :show-line="false" :right="false"></u-section>
			<view class="u-demo-area">
				<u-toast ref="uToast"></u-toast>
        <u-top-tips ref="uTips"></u-top-tips>
        <view class="image-area">
          <u-upload :class="lists.length>0?'':'flex justify-center'" :header="{Authorization: 'Bearer '+token}" ref="uUpload" :show-upload-list="showUploadList" :action="action" :auto-upload="autoUpload"
           :show-progress="showProgress" :deletable="deletable" :max-count="maxCount" @on-list-change="onListChange" :max-size="10 * 1024 * 1024" @on-success="onSuccessUpload">
          </u-upload>
          <view class="desc">
            <text>说明：每件藏品图片不得少于3张，仅支持上传JPG/PNG格式的图片，图片尺寸（宽高）建议不低于2400px，大小不超过10M，最多允许上传9张。</text>
          </view>
        </view>
        
        <u-form :model="form" ref="uForm">
          <u-form-item><u-input class="input" :border="true" placeholder="藏品名称" v-model="form.title" /></u-form-item>
          <u-form-item><u-input :border="true" @click="showCategory = true" placeholder="藏品分类" v-model="cate_str" type="select" :select-open="showCategory" /></u-form-item>
          <u-form-item><u-input type="textarea" height="150" :border="true" placeholder="藏品简介" v-model="form.intro" /></u-form-item>
          <u-form-item><u-input :border="true" placeholder="请输入您的称呼" v-model="form.contact" /></u-form-item>
          <u-form-item><u-input type="number" :border="true" placeholder="请输入您的手机号" v-model="form.contact_tel" maxlength="11" /></u-form-item>
        </u-form>
        
        <view class="agree flex align-center">
          <u-checkbox-group @change="agreeGroupChange">
            <u-checkbox @change="agreeChange" v-model="agree" :checked="agree" name="agree">
              <text class="text-sm">我已阅读并同意</text>
            </u-checkbox>
          </u-checkbox-group>
          <view @click="showPop=true"><text class="u-font-12 text-red">《藏品征集服务规则》</text></view>
        </view>
        
				<u-button v-if="showBtn" class="submit" type="primary" @click="submit" size="medium">确定提交</u-button>
			</view>
		</view>
    
    <!-- 藏品征集规则 -->
    <u-popup v-model="showPop" mode="center" closeable custom-style="overflow:auto;" length="90%" zoom border-radius="10">
      <scroll-view class="pop-content" scroll-y="true">
        <jyf-parser :html="nowHtml" ref="article" :tag-style="tagStyle"></jyf-parser>
      </scroll-view>
    </u-popup>
    
    <!-- 客服 -->
    <view class="kefu" @click="goPage('/pages/system/about/contact')">
      <image :src="baseUrl+'/uploads/attach/2020/09/20200911/f7303b1758628fc4fd90274f7873ee83.png'" mode="widthFix"></image>
    </view>
    
    <u-action-sheet :list="category" v-model="showCategory" @click="showCategoryCallback"></u-action-sheet>
    
	</view>
</template>

<script>
  import { HTTP_REQUEST_URL } from '@/config/app';
  import { submitCollect, getCategory, getCollectRule } from '@/api/api.js';
  import parser from "@/components/jyf-parser/jyf-parser";
  import Util from '@/utils/util.js';
	import { mapGetters } from "vuex";
	export default {
		computed: mapGetters(['isLogin']),
    components: {
      "jyf-parser": parser
    },
		data() {
			return {
        baseUrl: HTTP_REQUEST_URL,
				action: HTTP_REQUEST_URL+'/api/attach',
        token: this.$store.state.app.token,
				// 预置上传列表
				fileList: [],
				showUploadList: true,
				autoUpload: true,
				showProgress: true,
				deletable: true,
				customStyle: false,
				maxCount: 9,
				lists: [], // 组件内部的文件列表
        
        showCategory: false,
        category:[],
        form:{
          title: '',
          cate_id: '',
          intro: '',
          contact: '',
          contact_tel: ''
        },
        agree: true,
        showBtn: true,
        cate_str: '',
        
        showPop:false,
        nowHtml: ''
      }
		},
		onLoad() {
			if (this.isLogin == false) {
        return this.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
      }else{
        this.getData();
        this.getRule();
      }
      wx.showShareMenu({
        menus: ["shareAppMessage", "shareTimeline"]
      });
		},
    onShareAppMessage(res) {
      return {
        title: '藏品征集-北京盛世国际拍卖',
        path: '/pages/system/collect/collect'
      }
    },
    onShareTimeline(){
      return {
        title: '藏品征集-北京盛世国际拍卖',
        imageUrl: ''
      }
    },
		methods: {
      getRule(){
        var that = this;
        getCollectRule().then(res=>{
          that.nowHtml = res.data.content;
        });
      },
      getData(){
        var that = this;
        getCategory().then(res=>{
          let data = res.data.data;
          let cate = [];
          data.forEach((item,index)=>{
            cate.push({text:item.name, id:item.id});
          })
          that.category = cate;
        });
      },
      submit() {
        var that = this;
        if(!this.agree){
          return this.$refs.uTips.show({
            title: '请同意《藏品征集服务规则》后提交',
            type: 'error',
            duration: '2300'
          })
        }
        if(!this.form.title || !this.form.intro || !this.form.cate_id || !this.form.contact || !this.form.contact_tel){
          return this.$refs.uTips.show({
            title: '请完整填写信息',
            type: 'error',
            duration: '2300'
          })
        }
        if(this.fileList.length==0){
          return this.$refs.uTips.show({
            title: '请上传藏品照片',
            type: 'error',
            duration: '2300'
          })
        }
        if(this.fileList.length<3){
          return this.$refs.uTips.show({
            title: '至少需上传3张藏品图',
            type: 'error',
            duration: '2300'
          })
        }
        that.showBtn = false;
        uni.showLoading({
          title:'提交中',
          mask: true
        });
        submitCollect({title:this.form.title, intro:this.form.intro, cate_id:this.form.cate_id, contact:this.form.contact, contact_tel:this.form.contact_tel, attach:this.fileList}).then(res=>{
          that.$util.Tips({title:res.msg}, {tab:4, url:'/pages/my/my'});
        }).catch(err=>{
          uni.hideLoading();
          that.showBtn = true;
          return that.$util.Tips({
            title: err
          });
        })
      },
      agreeGroupChange(e){
        //console.log('e',e)
      },
      agreeChange(e){
        this.agree = !e.value;
      },
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      },
      // 选择分类回调
      showCategoryCallback(index) {
      	uni.hideKeyboard();
      	this.form.cate_id = this.category[index].id;
        this.cate_str = this.category[index].text;
      },
			onSuccessUpload(res) {
        if(res.status!==200){
          return this.$refs.uTips.show({
            title: '上传失败，请重试',
            type: 'error',
            duration: '2000'
          });
        }else{
          this.$refs.uTips.show({
            title: '上传成功',
            type: 'success',
            duration: '2000'
          });
        }
        this.fileList.push(res.data.url);
			},
			onListChange(lists) {
        console.log(lists)
				// console.log('onListChange', lists);
				this.lists = lists;
			}
		}
	}
</script>

<style lang="scss">
  .u-demo{
    padding: 20rpx 30rpx;
  }
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
</style>
