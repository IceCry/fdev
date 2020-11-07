module.exports = {
  // Socket链接 暂不做配置
  // WSS_SERVER_URL:'ws://192.168.1.123:2345',
  WSS_SERVER_URL:'wss://paimai.guoui.com/wss',
  // Socket调试模式
  SERVER_DEBUG:true,
  // 心跳间隔
  PINGINTERVAL:3000,
  // 请求头
  HEADER:{
    'content-type': 'application/json'
  },
}