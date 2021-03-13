import request from "@/utils/request.js";

/**
 * 获取用户信息
 * 
*/
export function getUserInfo(){
  return request.get('user');
}

/**
 * 设置用户分享
 * 
*/
export function userShare(){
  return request.post('user/share');
}

/**
 * 验证码key
 */
export function getCodeApi() {
  return request.get("verify_code", {}, { noAuth: true });
}

/**
 * h5用户发送验证码
 * @param data object 用户手机号
 */
export function registerVerify(data) {
  return request.post("/register/verify", data, { noAuth : true });
}
