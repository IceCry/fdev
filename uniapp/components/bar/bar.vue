<template>
	<view>
		<view class="nav-h"></view>
		<view class="cu-bar tabbar bg-white foot">
      
      <view class="action text-blue" :class="item.isCircle===true ? 'add-action':''" :style="[currentTabIndex == index ? {'color': selectedColor} : {'color': color}]" @click="switchTab(index)" v-for="(item,index) in tabList" :key="index">
        <button class="cu-btn cuIcon-add bg-red shadow" v-if="item.isCircle===true"></button>
      	<view :class="item.icon" v-else><view class="cu-tag badge" v-if="item.badge>0">{{item.badge}}</view></view> {{item.text}}
      </view>
      
		</view>
	</view>
</template>

<script>
	export default {
		name: 'Bar',
    data() {
      return {
        tabList: [
          {
            icon: 'custom-icon custom-icon-shouye',
            text: '首页',
            badge: 0,
            path: '/pages/index/index',
            isCircle: false
          },
          {
            icon: 'custom-icon custom-icon-icon',
            text: '查找',
            badge: 0,
            path: '/pages/search/search',
            isCircle: false
          },
          {
            icon: 'custom-icon custom-icon-jia',
            text: '咨询',
            badge: 0,
            path: '/pages/ask/post',
            isCircle: true
          },
          {
            icon: 'custom-icon custom-icon-zhibo1',
            text: '直播',
            badge: 0,
            path: '/pages/live/live',
            isCircle: false
          },
          {
            icon: 'custom-icon custom-icon-wode1',
            text: '我的',
            badge: 0,
            path: '/pages/my/my',
            isCircle: false
          }
        ],
        currentTabIndex: this.current
      }
    },
		props: {
			current: {
				type: Number,
				default: 9
			},
      color:{
        type: String,
        default: '#7A7E83'
      },
      selectedColor:{
        type: String,
        default: '#2b6dff'
      }
		},
    onShow() {
      var tabIndex = uni.getStorageSync('tabIndex') || 0;
      this.currentTabIndex = tabIndex;
      console.log('tab', tabIndex)
    },
		methods: {
			switchTab(index) {
        var path = this.tabList[index].path;
        
        // this.currentTabIndex = index;
        
        //存储
        // uni.setStorageSync('tabIndex', index);
        console.log(index)
        this.$emit('click', index);
        uni.switchTab({
          url: path
        })
      }
		}
	}
</script>

<style>
  .selectedColor{
    color: #3cc51f;
  }
  .color{
    color: #7A7E83;
  }
</style>
