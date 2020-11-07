<script>
  import { checkLogin } from "./libs/login";
  import Server from '@/utils/server.js';
  import { HTTP_REQUEST_URL } from './config/app';
  
	export default {
		onLaunch: function() {
			
			// 网路监听（用户目前断网，切换wifi）
			//this.util.NetWork.On();
			// #ifdef APP-PLUS
			// 更新检测
			//this.util.Update();
			// #endif
			
			/* uni.request({
				url: 'https://uniapp.dcloud.io/update', //检查更新的服务器地址
				data: {
					appid: plus.runtime.appid,
					version: plus.runtime.version,
					imei: plus.device.imei
				},
				success: (res) => {
					if (res.statusCode == 200 && res.data.isUpdate) {
						let openUrl = plus.os.name === 'iOS' ? res.data.iOS : res.data.Android;
						// 提醒用户更新
						uni.showModal({
							title: '更新提示',
							content: res.data.note ? res.data.note : '是否选择更新',
							success: (showResult) => {
								if (showResult.confirm) {
									plus.runtime.openURL(openUrl);
								}
							}
						})
					}
				}
			}) */
      
      //实例化聊天服务
      //this.$chat = new Server(this);
		},
    //$chat:null,
		onShow: function() {
			console.log('App Show')
		},
		onHide: function() {
			console.log('App Hide')
		},
		globalData: {
			spid: 0,
			code:0,
			isLogin:false,
			userInfo:{},
      //默认基础菜单 未登录菜单
			MyMenus:[]
		},
    /**
     * 聊天事件快捷注册
    */
    /* $on: function (name, action){
      this.$chat.$on(name,action);
    } */
	}
</script>

<style lang="scss">
	/* #ifndef APP-PLUS-NVUE */
	/* uni.css - 通用组件、模板样式库，可以当作一套ui库应用 */
	
	/* 引入uview-ui */
	@import "./uview-ui/index.scss";
	
	@import './common/app.css';
  
  // 引入自定义图标
  @import "./static/iconfont.css";

	/* 以下样式用于 hello uni-app 演示所需 */
	page {
		background-color: #F4F5F6;
		height: 100%;
		font-size: 28rpx;
		line-height: 1.8;
	}

	.uni-header-logo {
		padding: 30rpx;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		margin-top: 10rpx;
	}

	.uni-header-image {
		width: 100px;
		height: 100px;
	}

	.uni-hello-text {
		color: #7A7E83;
	}

	.uni-hello-addfile {
		text-align: center;
		line-height: 300rpx;
		background: #FFF;
		padding: 50rpx;
		margin-top: 10px;
		font-size: 38rpx;
		color: #808080;
	}

	/* #endif*/
  
  
  .noLogin{
    width: 700rpx;
    height: 300rpx;
    margin: 0 auto;
    margin-top: 200rpx;
    display: flex;
    flex-flow: column;
    justify-content: space-between;
  }
  
  .no-data{
    color: #ccc;
    text-align: center;
    line-height: 40rpx;
  }
  
  .navH{
    width: 100%;
    height: 120rpx;
  }
  .text-link{
    color: $u-type-primary-dark;
  }
  
  //专家讲座
  .lecture{
  	margin: 30rpx;
    .items{
      .item{
        position: relative;
        overflow: hidden;
        margin-bottom: 20rpx;
        .tag{
          position: absolute;
          left: 0;
          top: 4rpx;
          z-index: 99;
        }
        .info{
          position: absolute;
          left: 10rpx;
          bottom: 10rpx;
          z-index: 99;
          .name{
            font-size: $uni-font-size-base;
            color: #FFFFFF;
            line-height: 40rpx;
            font-weight: 700;
          }
          .click{
            color: #FFFFFF;
            font-size: $uni-font-size-mini;
            line-height: 34rpx;
            margin-left: 15rpx;
          }
        }
      }
      .one{
        width: 690rpx;
        height: 280rpx;
        border-radius: 14rpx;
        image{
          width: 100%;
        }
      }
      .two{
        width: 340rpx;
        height: 250rpx;
        border-radius: 14rpx;
        image{
          width: 100%;
        }
      }
    }
  }
  
  .mask {
  	position: fixed;
  	top: 0;
  	left: 0;
  	right: 0;
  	bottom: 0;
  	background-color: #000;
  	opacity: .5;
  	z-index: 5
  }
  .line-through{
    text-decoration:line-through;
  }
  .ss-block{
    display: block;
    clear: both;
  }
  .kefu{
    position: fixed;
    right: 20rpx;
    bottom: 120rpx;
    z-index: 99;
    image{
      width: 80rpx;
      height: 80rpx;
    }
  }
  .status-blue{
    background: #1E9FFF!important;
    color: #FFFFFF;
  }
  .status-orange{
    background: #FF5722!important;
    color: #FFFFFF;
  }
  .status-green{
    background: #009688!important;
    color: #FFFFFF;
  }
  .status-black{
    background: #393D49!important;
    color: #FFFFFF;
  }
  .status-gray{
    background: #eeeeee!important;
    color: #000000;
  }
</style>
