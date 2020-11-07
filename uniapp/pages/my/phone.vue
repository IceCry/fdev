<template>
	<view class="forget ss-login">
		
		<view class="content">
			<!-- 主体 -->
			<view class="main">
				<view class="tips text-red" v-if="userInfo.phone">当前绑定手机：{{userInfo.phone}}，如需更换手机请重新绑定</view>
				<view class="tips" v-else>绑定手机号获取更好的服务</view>
				<wInput
					v-model="phoneData"
					type="text"
					maxlength="11"
					placeholder="请输入手机号码"
				></wInput>
				<wInput
					v-model="verCode"
					type="number"
					maxlength="6"
					placeholder="验证码"
					
					isShowCode
					codeText="获取验证码"
					setTime="60"
					ref="runCode"
					@setCode="getVerCode()"
				></wInput>
			</view>
			
			<wButton 
				text="确 定 绑 定"
				:rotate="isRotate" 
        bgColor="#3856f4"
				@click.native="doBind()"
				class="wbutton"
			></wButton>

		</view>
	</view>
</template>

<script>
	var _this;
  import { getCodeKey, getSmsCode } from '@/api/public.js';
  import { getUserInfo, bindPhone } from '@/api/user.js';
	import wInput from '@/components/uni-login/watch-input.vue' //input
	import wButton from '@/components/uni-login/watch-button.vue' //button
  import Util from '@/utils/util.js';
  import { mapGetters } from "vuex";
	export default {
		data() {
			return {
				phoneData: "", //电话
				verCode:"", //验证码
				isRotate: false, //是否加载旋转
        userInfo: {},
        key: '',//短信发送key
			}
		},
		computed: mapGetters(['isLogin', 'uid']),
		components:{
			wInput,
			wButton
		},
		mounted() {
			_this= this;
      this.CodeKey()
      this.UserInfo();
		},
		methods: {
      CodeKey(){
        var that=this;
        getCodeKey().then(res=>{
          that.key = res.data.key;
        });
      },
      UserInfo(){
        var that=this;
        getUserInfo().then(res=>{
          that.userInfo = res.data.userInfo
        });
      },
			getVerCode(){
        var that = this;
				//获取验证码
				if (_this.phoneData.length != 11) {
				    return false;
            return that.$util.Tips({
            	title: '手机号格式错误'
            });
				}
				_this.$refs.runCode.$emit('runCode'); //触发倒计时（一般用于请求成功验证码后调用）
				
        //todo 暂不添加验证码
        getSmsCode({phone: _this.phoneData, type: 'bind', key: _this.key, uid: this.uid||0}).then(res=>{
          Util.Tips({title:res.msg});
        }).catch(err => {
        	return that.$util.Tips({
        		title: err
        	});
        });
        setTimeout(function(){
          _this.$refs.runCode.$emit('runCode',0);
        }, 60000);
			},
			doBind() {
				//重置密码
				if(this.isRotate){
					//判断是否加载中，避免重复点击请求
					return false;
				}
				if (this.phoneData.length != 11) {
				    uni.showToast({
				        icon: 'none',
				        title: '手机号不正确'
				    });
				    return false;
				}
				if (this.verCode.length != 6) {
				    uni.showToast({
				        icon: 'none',
				        title: '验证码不正确'
				    });
				    return false;
				}
        
        if(this.phoneData == this.userInfo.phone){
          uni.showToast({
              icon: 'none',
              title: '此手机号已绑定'
          });
          return false;
        }
        
				_this.isRotate=true
        //执行
        var that = this;
        bindPhone({phone: this.phoneData, code: this.verCode}).then(res=>{
          //剔除取关数据
          Util.Tips({title:res.msg}, {tab:4, url:'/pages/my/my'});
        }).catch(err => {
          that.isRotate = false;
          return uni.showToast({
            icon: 'none',
            title: err
          });
        });
			}
		}
	}
</script>

<style>
	@import url("../../components/uni-login/css/icon.css");
  .main .text-red{
    color: #555;
    font-weight: 700;
  }
</style>

