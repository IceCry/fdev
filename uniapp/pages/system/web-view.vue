<template>
	<view class="content">
		<web-view :src="src"></web-view>
	</view>
</template>

<script>
  export default {
    data() {
      return {
				src: ''
			}
		},
    onLoad(option) {
      let src = decodeURIComponent(option.src);
      console.log(src)
			this.src = src;
    },
    methods: {
			
    }
	}
</script>

<style lang="scss" scoped>
  .content{
  }
	.services{
		overflow: hidden;
		.item{
      background: #fff;
			width: 750rpx;
			height: 120rpx;
			padding: 20rpx 45rpx;
      margin: 25rpx 0;
			display: flex;
			overflow: hidden;
			justify-content: left;
			.thumb{
				width: 80rpx;
				height: 80rpx;
				image{
					width: 80rpx;
					height: 80rpx;
					border-radius: 50%;
				}
			}
			.intro{
				width: 580rpx;
				padding-left: 25rpx;
				line-height: 80rpx;
				.title{
					font-weight: 700;
					font-size: $uni-font-size-lg;
				}
			}
		}
    .on{
      color: $ss-blue;
    }
	}
  .btn{
    width: 100%;
    height: 80rpx;
    line-height: 80rpx;
    position: fixed;
    bottom: 0;
    left: 0;
    button{
      background: $ss-bg-red;
      color: #fff;
      border-radius: 0;
    }
  }
</style>
