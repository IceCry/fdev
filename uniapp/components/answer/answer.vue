<template>
	<view class="answer">
		<view class="item" v-for="(item, index) in list" :key="index">
      <view class="tags" v-if="item.is_best == 1">
        <text class="done">已采纳</text>
      </view>
      <view @click="choose(item.id)" class="tags2" v-if="ismy && status==0">
        <text class="done">采纳此解答</text>
      </view>
		  <view class="avatar" @click="goPage('/pages/lawyer/lawyer?uuid='+item.lawyer.uuid)">
		    <image :src="item.lawyer.avatar" mode="widthFix"></image>
		  </view>
		  <view class="info">
		    <view class="name" @click="goPage('/pages/lawyer/lawyer?uuid='+item.lawyer.uuid)">
		      <view class="t"><text>{{item.lawyer.name}} 律师</text></view>
		      <view class="d"><text>{{item.lawyer.lawfirm_name}}</text></view>
		    </view>
		    <view class="desc">
		      <text>{{item.content}}</text>
		    </view>
		    <view class="btns">
		      <view class="l">
		        <text>{{item.human_date}}</text>
		      </view>
		      <view class="r">
		        <view class="ask btn" @click="goPage('/pages/lawyer/lawyer?uuid='+item.lawyer.uuid)">
		          <text>咨询Ta</text>
		        </view>
		        <view class="like btn" @click="like(item.id, 1, index)" v-if="item.is_liked==0">
		          <u-icon name="thumb-up"></u-icon>
		          <text>点赞 ({{item.like_num}})</text>
		        </view>
            <view class="like btn text-red" @click="like(item.id, 0, index)" v-else>
              <u-icon name="thumb-up-fill"></u-icon>
              <text>取消 ({{item.like_num}})</text>
            </view>
		      </view>
		    </view>
		  </view>
		</view>
    
    <u-empty text="等待律师回答" v-if="list.length==0" mode="list"></u-empty>
    
	</view>
</template>

<script>
  import { doLike } from '../../api/api.js';
  import { chooseBest } from '../../api/ask.js';
  import Util from '../../utils/util.js';
	export default {
		name: 'Answer',
		props: {
			list: {
				type: Array,
				default () {
					return []
				}
			},
      status: {
        type: Number,
        default(){
          return 0
        }
      },
      ismy: {
        type: Boolean,
        default(){
          return false
        }
      }
		},
    data(){
      return {
        newList: []
      }
    },
		methods: {
			onClick() {
				this.$emit('click')
			},
      choose(id){
        var that = this;
        //todo 直接修改status报错，同下方like
        uni.showLoading({
          title:'none',
          mask: true
        });
        chooseBest(id).then(res=>{
          Util.Tips({title:res.msg}, '/pages/ask/evaluate?id='+id);
        }).catch(err => {
          uni.hideLoading();
        	return that.$util.Tips({
        		title: err
        	});
        });
      },
      goPage(url, type='navigate'){
        Util.goPage(url, type);
      },
      like(id, type, index){
        var that = this;
        doLike({mid:id, mtable: 'answer', type: type}).then(res=>{
          //Util.Tips({title:res.msg});
          //that.list[index].is_liked=type;
          let items = that.list;
          console.log(items[index])
          items[index].is_liked =type;
          if(type){
            items[index].like_num++;
          }else{
            items[index].like_num--;
          }
          // that.list = items;
          that.$emit('change', items);
        }).catch(err => {
        	return that.$util.Tips({
        		title: err
        	});
        });
      }
		}
	}
</script>

<style lang="scss" scoped>
	.answer{
	  padding: 30rpx 0;
	  overflow: hidden;
	  background: #fff;
	  .item{
      position: relative;
	    padding: 35rpx 30rpx;
      overflow: hidden;
	    border-bottom: 1px solid #e6e6e6;
	    display: flex;
	    flex-flow: row nowrap;
	    .avatar{
	      width: 60rpx;
	      height: 60rpx;
	      overflow: hidden;
	      image{
	        width: 60rpx;
	      }
	    }
	    .info{
	      flex: 1;
	      margin-left: 15rpx;
	      .name{
	        width: 100%;
	        height: 60rpx;
	        line-height: 30rpx;
	        display: flex;
	        flex-flow: column;
	        justify-content: space-between;
	        .t{
	          
	        }
	        .d{
	          font-size: $uni-font-size-sm;
	          color: $ss-font-dark;
	        }
	      }
	      .desc{
	        padding: 25rpx 0;
	        line-height: 40rpx;
	        font-size: $uni-font-size-lg;
	      }
	      .btns{
	        height: 40rpx;
	        line-height: 40rpx;
	        .l{
	          width: 40%;
	          float: left;
	          color: $ss-font-dark;
	        }
	        .r{
	          width: 55%;
	          float: right;
	          .btn{
	            width: 120rpx;
	            float: right;
	            font-size: $uni-font-size-sm;
	            height: 40rpx;
	            margin-left: 10rpx;
	            border-radius: 5rpx;
	            display: flex;
	            flex-flow: row nowrap;
	            justify-content: center;
	          }
	          .like{
	            border: 1px solid #c3c3c3;
	            border-radius: 8rpx;
	            color: #c3c3c3;
	          }
            .text-red{
	            border: 1px solid #c27474;
	            border-radius: 8rpx;
	            color: #c27474;
            }
	          .ask{
	            border: 1px solid $ss-blue;
	            border-radius: 8rpx;
	            color: $ss-blue;
	          }
	        }
	      }
	    }
	  }
	}
  
  .tags{
  	position: absolute;
  	right: -50rpx;
  	top: 18rpx;
  	height: 50rpx;
  	line-height: 50rpx;
  	background-color: $ss-blue;
  	color: #fff;
  	padding: 0 80rpx;
  	font-size: $uni-font-size-sm;
  	margin-right: -28rpx;
  	
  	transform:rotate(45deg); 
  }
  
  .tags2{
  	position: absolute;
  	right: 0;
  	top: 10rpx;
  	height: 50rpx;
  	line-height: 50rpx;
  	background-color: #999;
  	color: #fff;
  	padding: 0 20rpx;
  	font-size: $uni-font-size-sm;
  }
</style>