module.exports = {
	// #ifdef MP
	HTTP_REQUEST_URL: '',// 请求域名 格式： https://您的域名
	VUE_APP_WS_URL: ``,// 客服域名 格式： ws://您的域名：20003
	// #endif

	// #ifdef H5
	//H5接口是浏览器地址
	HTTP_REQUEST_URL: window.location.protocol + "//" + window.location.host,
	VUE_APP_WS_URL: `ws://${window.location.host}:20003`,
	// #endif

	HEADER: {
		'content-type': 'application/json',
		//#ifdef H5
		'Form-type': navigator.userAgent.toLowerCase().indexOf("micromessenger") !== -1 ? 'wechat' : 'h5',
		//#endif
		//#ifdef MP
		'Form-type': 'routine',
		//#endif
		//#ifdef APP-VUE
		'Form-type': 'app',
		//#endif
	},
	// 回话密钥名称 请勿修改此配置
	TOKENNAME: 'Authori-zation',
	// 缓存时间 0 永久
	EXPIRE: 0,
	//分页最多显示条数
	LIMIT: 10
};
