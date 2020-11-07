<template>
	<view class="container">
    <u-sticky>
      <view class="tabs">
        <u-tabs v-if="control" bg-color="#ffffff" :bold="true" active-color="#f45300" :list="list"
        @change="change" :current="current" :is-scroll="true"></u-tabs>
      </view>
    </u-sticky>
    
    <view class="content" v-if="!showForm">
      <jyf-parser :html="info.content" ref="content" :tag-style="tagStyle"></jyf-parser>
    </view>
    
    <view class="form" v-else>
      <view class="u-demo-area">
      	<u-toast ref="uToast"></u-toast>
        <u-top-tips ref="uTips"></u-top-tips>
        <view class="image-area">
          <view class="uploader flex justify-between">
            <u-upload :action="action" :header="{AuthoriZation: 'Bearer '+token}" :file-list="fileList" :auto-upload="true" max-count="1" width="310" height="200" :size-type="compressed" upload-text="身份证件正面照" index="front" :form-data="{type:'cert_front'}" @on-success="onSuccessFront"></u-upload>
            <u-upload :action="action" :header="{AuthoriZation: 'Bearer '+token}" :file-list="fileList" :auto-upload="true" max-count="1" width="310" height="200" :size-type="compressed" upload-text="身份证件反面照" index="front" :form-data="{type:'cert_end'}" @on-success="onSuccessEnd"></u-upload>
          </view>
          <view class="desc">
            <text>说明：请上传清晰身份证照片。</text>
          </view>
        </view>
        
        <u-form :model="form" ref="uForm">
          <u-form-item><u-input class="input" :border="true" placeholder="请输入您的真实姓名" v-model="form.name" /></u-form-item>
          <u-form-item label="性别" prop="gender" label-width="100">
          	<u-radio-group v-model="form.gender" @change="sexChange">
          		<u-radio shape="circle" name="1">男</u-radio>
          		<u-radio shape="circle" name="2">女</u-radio>
          	</u-radio-group>
          </u-form-item>
          
          <u-form-item><u-input type="text" @click="showBirth=true" class="input" :border="true" placeholder="请选择您的出生年月" v-model="form.birth" :disabled="true" /></u-form-item>
          <u-picker v-model="showBirth" mode="time" @confirm="chooseBirth" :start-year="1900" :params="timeParams"></u-picker>
            
          <u-form-item><u-input type="number" class="input" :border="true" placeholder="请输入您的手机号" v-model="form.phone" maxlength="11" /></u-form-item>
          <u-form-item><u-input type="textarea" height="150" :border="true" placeholder="个人简介" v-model="form.intro" /></u-form-item>
          <u-form-item><u-input type="textarea" height="150" :border="true" placeholder="藏品说明" v-model="form.collect_remark" /></u-form-item>
          <u-form-item>
            <u-input :border="true" type="select" v-model="form.region" placeholder="请选择地区" @click="openAddress"></u-input>
          </u-form-item>
          <u-form-item><u-input class="input" :border="true" placeholder="请输入您的联系地址" v-model="form.address" /></u-form-item>
        </u-form>
        
        <!-- 上传藏品 -->
        <u-section title="上传藏品" :arrow="false" sub-title="不低于10件，每件需上传3-6张照片"></u-section>
        <view  v-for="(item, index) in collects" :key="index" @click="uploadIndex(index)">
          <u-form-item>
          	<u-upload width="200" height="200" max-count="6" :action="action" :header="{AuthoriZation: 'Bearer '+token}" :file-list="fileList" :auto-upload="true" :size-type="compressed" upload-text="藏品照片" :max-size="10 * 1024 * 1024" index="collect" :form-data="{type: 'collect'}" @on-success="onSuccessCollect"></u-upload>
          </u-form-item>
        </view>
        <u-divider class="more" @click="addCollect" color="#007AFF" margin-top="20">点击继续添加藏品</u-divider>
        
        <view class="agree text-center">
          <text class="text-sm text-gray">提交信息则默认您已了解艺术社章程及入会条件</text>
        </view>
      	<u-button v-if="showBtn" class="submit" type="primary" @click="submit" size="medium">确定提交</u-button>
      </view>
    </view>
    
    <simple-address ref="simpleAddress" :pickerValueDefault="cityPickerValueDefault" @onConfirm="onConfirm" themeColor="#007AFF"></simple-address>
	</view>
</template>

<script>
  import { HTTP_REQUEST_URL } from '@/config/app';
  import parser from "@/components/jyf-parser/jyf-parser";
  import { getArticleDetail } from '@/api/article.js';
  import simpleAddress from '@/components/simple-address/simple-address.vue';
	import { mapGetters } from "vuex";
  import { joinVip } from '@/api/user.js';
	export default {
		computed: mapGetters(['isLogin']),
    components: {
      "jyf-parser": parser,
      "simpleAddress": simpleAddress
    },
		data() {
			return {
				action: HTTP_REQUEST_URL+'/api/attach',
        token: this.$store.state.app.token,
				list: [{'id': 7, 'name':'艺术社章程'}, {'id': 18, 'name':'入会条件'}, {'id': 0, 'name':'我要入会'}],
        info: [],
				current: 0,
				sectionCurrent: 0,
				tabCountIndex: 0,
				control: true,
        showForm: false,
        showBtn: true,
        gender: 1,
        showBirth: false,
        
        form:{
          name: '',
          birth: '',
          phone: '',
          intro: '',
          collect_remark: '',
          gender: 1,
          cert_front: '',
          cert_end: '',
          address: '',
          area_code: '',
          collects: [],
          region: '',
          card_id: ''
        },
        
        collects: [[]],
        nowUploadIndex: 0,
        
        timeParams:{
          year: true,
          month: true,
          day: true,
          hour: false,
          minute: false,
          second: false
        },
        t_address: [11, 1101, 110101],
        cityPickerValueDefault: [0, 0, 1],
			};
		},
    onLoad() {
      if (this.isLogin == false) {
        return this.$util.Tips({title:'请先授权登录'}, {tab:1, url:'/pages/my/my'});
      }else{
        this.getData();
      }
      wx.showShareMenu({
        menus: ["shareAppMessage", "shareTimeline"]
      });
    },
    onShareAppMessage(res) {
      return {
        title: '盛世雅聚艺术社-北京盛世国际拍卖',
        path: '/pages/system/association/association'
      }
    },
    onShareTimeline(){
      return {
        title: '盛世雅聚艺术社-北京盛世国际拍卖',
        imageUrl: ''
      }
    },
    methods:{
      getData(){
        var id = this.list[this.current].id;
        var that = this;
        uni.showLoading();
        getArticleDetail({id: id}).then(res=>{
          that.info = res.data.info;
          uni.hideLoading();
        })
      },
			change(index) {
				this.current = index;
        if(index==2){
          this.showForm = true;
        }else{
          this.showForm = false;
          this.getData();
        }
			},
      sexChange(e) {
        this.gender = e;
      },
      chooseBirth(e){
        console.log(e)
        this.form.birth = e.year+'-'+e.month+'-'+e.day;
      },
      openAddress() {
        // 根据 label 获取
        var index = this.$refs.simpleAddress.queryIndex(this.t_address, 'value');
        this.cityPickerValueDefault = index.index;
        this.$refs.simpleAddress.open();
      },
      onConfirm(e) {
        console.log(e);
        this.form.region = e.label;
        this.form.area_code = [e.provinceCode, e.cityCode, e.areaCode];
      },
      uploadIndex(index){
        console.log(index)
        this.nowUploadIndex = index;
      },
			onSuccessFront(res) {
        this.$refs.uTips.show({
          title: '上传成功',
          type: 'success',
          duration: '2000'
        })
				this.form.cert_front = res.data.url;
        if(res.data.data.errcode==0){
          this.form.name = res.data.data.name;
          this.form.card_id = res.data.data.id;
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
      //添加藏品
      addCollect(){
        let collects = this.collects;
        //最多上传20件
        if(collects.length>20){
          return this.$refs.uTips.show({
            title: '最大支持20个藏品上传',
            type: 'error',
            duration: '2000'
          });
        }
        collects.push([]);
        this.collects = collects;
      },
			onSuccessCollect(res) {
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
        let arr = this.collects[this.nowUploadIndex];
        arr.push(res.data.url);
        this.collects[this.nowUploadIndex] = arr;
        console.log(this.collects[this.nowUploadIndex]);
			},
			onListChange(lists) {
        console.log(lists)
				// console.log('onListChange', lists);
				this.lists = lists;
			},
      submit() {
        var that = this;
        if(!this.form.name || !this.form.birth || !this.form.phone || !this.form.intro || !this.form.collect_remark || !this.form.gender || !this.form.cert_front || !this.form.cert_end || !this.form.address || !this.form.area_code){
          return this.$refs.uTips.show({
            title: '请完整填写信息',
            type: 'error',
            duration: '2300'
          })
        }
        //todo 调整为10件
        if(this.collects.length<2){
          return this.$refs.uTips.show({
            title: '上传藏品数量需不低于10件',
            type: 'error',
            duration: '2300'
          })
        }
        //遍历每个藏品图片数量
        this.collects.forEach((val, index)=>{
          if(val.length<3){
            this.$refs.uTips.show({
              title: '每件藏品至少需上传3张图',
              type: 'error',
              duration: '2300'
            })
            throw new Error("每件藏品至少需上传3张图");
          }
        });
        
        this.form.collects = this.collects;
        
        that.showBtn = false;
        uni.showLoading({
          title:'提交中',
          mask: true
        });
        joinVip(this.form).then(res=>{
          that.$util.Tips({title:res.msg, endtime:3000}, {tab:4, url:'/pages/my/my'});
        }).catch(err=>{
          uni.hideLoading();
          that.showBtn = true;
          return that.$util.Tips({
            title: err
          });
        })
      },
    }
	}
</script>

<style lang="scss">
.content{
  margin: 30rpx;
}
.container{
  background: #FFFFFF;
  min-height: 100vh;
}
.form{
  padding: 20rpx;
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
.u-upload{
  background: #CCCCCC;
}
.agree{
  margin: 20rpx 0;
}
.u-form-item{
  padding: 10rpx 0 !important;
}
.submit{
  margin-bottom: 20rpx;
}
</style>
