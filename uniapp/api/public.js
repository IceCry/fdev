import request from "@/utils/request.js";

/**
 * 获取微信sdk配置
 * @returns {*}
 */
export function getWechatConfig() {
  return request.get(
    "wechat/config",
    { url: document.location.href },
    { noAuth: true }
  );
}

/**
 * 获取微信sdk配置
 * @returns {*}
 */
export function wechatAuth(code, spread, login_type) {
  return request.get(
    "wechat/auth",
    { code, spread, login_type },
    { noAuth: true }
  );
}

/**
 * 获取登录授权login
 * 
*/
export function getLogo()
{
  return request.get('routine/get_logo', {}, { noAuth : true});
}

/**
 * 小程序用户登录
 * @param data object 小程序用户登陆信息
 */
export function login(data) {
  return request.post("routine/auth", data, { noAuth : true });
}

/**
 * 分享
 * @returns {*}
 */
export function getShare() {
  return request.get("share", {}, { noAuth: true });
}

/**
 * 获取关注海报
 * @returns {*}
 */
export function follow() {
  return request.get("wechat/follow", {}, { noAuth: true });
}

/**
 * 获取图片base64
 * @retins {*}
 * */
export function imageBase64(image, code) {
  return request.post(
    "/image_base64",
    { image: image, code: code },
    { noAuth: true }
  );
}

/**
 * 验证码key
 */
export function getCodeKey() {
  return request.get("sms_code_key", {}, { login: false });
}

/**
 * 验证码key
 */
export function getSmsCode(data) {
  return request.post("sms_code", data, { login: false });
}