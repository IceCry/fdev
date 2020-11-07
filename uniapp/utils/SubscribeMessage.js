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
 * 认证审核结果通知
 */
export function openVerifySubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.verify
	]);
}

/**
 * 出价被超越通知 或中拍通知
 */
export function openBidSubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.exceed,
    tmplIds.shot,
    tmplIds.auction_order_cancel
	]);
}

/**
 * 发货通知
 */
export function openPaySubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.express,
		tmplIds.pay_success
	]);
}

/**
 * 保证金提现通知
 */
export function openWithdrawSubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.withdraw_success,
    tmplIds.withdraw_fail
	]);
}

/**
 * 线下保证金充值通知
 */
export function openRechargeSubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.recharge_success,
    tmplIds.recharge_fail
	]);
}


/**
 * 调起订阅界面
 * array tmplIds 模板id
 */
export function subscribe(tmplIds) {
	 let wechat = wx;
	return new Promise((reslove, reject) => {
		wechat.requestSubscribeMessage({
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
