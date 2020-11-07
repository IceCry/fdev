import request from "@/utils/request.js";

/**
 * 获取用户信息
 * 
*/
export function getUserInfo(){
  return request.get('user');
}

/**
 * 我的关注
*/
export function getMyFollow(data)
{
  return request.get("my_follow",data);
}

/**
 * 我的收藏
*/
export function getMyFavorite(data)
{
  return request.get("my_favorite",data);
}

/**
 * 绑定手机号
*/
export function bindPhone(data)
{
  return request.post("bind_phone",data);
}

/**
 * 修改用户资料
*/
export function editUser(data)
{
  return request.post("edit_user",data);
}

/**
 * 修改头像
*/
export function avatar(data)
{
  return request.post("avatar",data);
}

/**
 * 获取地址
 * @param {Object} data
 */
export function getAddress()
{
  return request.get('address');
}

/**
 * 默认地址
 * @param {Object} data
 */
export function getDefaultAddress()
{
  return request.get('address_default');
}

/**
 * 添加修改地址
 * @param {Object} data
 */
export function saveAddress(data)
{
  return request.post('save_address', data);
}

/**
 * 获取指定地址信息
 * @param {Object} uuid
 */
export function getAddressDetail(uuid){
  return request.get('address_detail/'+uuid);
}

/**
 * 删除地址
*/
export function delAddress(id){
  return request.post('address_delete/'+id);
}

/**
 * 提交认证信息
 * @param {Object} data
 */
export function doVerify(data)
{
  return request.post('verify', data);
}

/**
 * 加入vip
 * @param {Object} data
 */
export function joinVip(data)
{
  return request.post('join_vip', data);
}














