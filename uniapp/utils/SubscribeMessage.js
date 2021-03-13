import {
	SUBSCRIBE_MESSAGE
} from '../config/cache.js';

export function auth() {
	let tmplIds = {};
	let messageTmplIds = uni.getStorageSync(SUBSCRIBE_MESSAGE);
	tmplIds = messageTmplIds ? JSON.parse(messageTmplIds) : {};
	return tmplIds;
}

/**
 * 支付成功后订阅消息id
 * 订阅  确认收货通知 订单支付成功  新订单管理员提醒 
 */
export function openPaySubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.oreder_takever,
		tmplIds.order_pay_success,
		tmplIds.order_new,
	]);
}

//...

/**
 * 调起订阅界面
 * array tmplIds 模板id
 */
export function subscribe(tmplIds) {
	 let wecaht = wx;
	return new Promise((reslove, reject) => {
		wecaht.requestSubscribeMessage({
			tmplIds: tmplIds,
			success(res) {
				return reslove(res);
			},
			fail(res) {
				return reslove(res);
			}
		})
	});
}
