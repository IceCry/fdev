import request from "@/utils/request.js";


/**
 * 获取主页数据 无需授权
*/
export function getIndexData()
{
  return request.get("index",{},{ noAuth : true});
}

/**
 * 退出登錄
*/
export function logout(){
  return request.get('logout');
}

/**
 * 分类
*/
export function getCategory()
{
  return request.get("category",{},{noAuth: true});
}

/**
 * 收藏 关注
*/
export function doFavorite(data)
{
  return request.post("favorite", data);
}

/**
 * 点赞
*/
export function doLike(data)
{
  return request.post("like", data);
}

/**
 * 关注
*/
export function doFollow(data)
{
  return request.post("follow", data);
}

/**
 * 评价 问题相关
*/
export function evaluate(data)
{
  return request.post("evaluate",data);
}

/**
 * 指定字段次数加
*/
export function setKeyInc(data)
{
  return request.post("set_key_inc",data);
}

/**
 * 获取热门关键词
*/
export function getHotKeyword()
{
  return request.get("hot_keyword",{},{ noAuth : true});
}

/**
 * 获取搜索结果
*/
export function getSearchResult(data)
{
  return request.post("search_result",data,{ noAuth : true});
}

/**
 * 获取城市数据
*/
export function getCityData(data)
{
  return request.get("city_data",data,{ noAuth : true});
}

/**
 * 获取订阅消息id
 */
export function getTplIds()
{
  return request.get('routine_tpl_ids', {}, { noAuth:true});
}

/**
 * 关于我们页面
 */
export function getAboutData()
{
  return request.get('about', {}, { noAuth:true});
}

/**
 * 联系客服
 */
export function getKfData()
{
  return request.get('kefu', {}, { noAuth:true});
}

/**
 * 获取联络处
 */
export function getOfficeData()
{
  return request.get('office', {}, { noAuth:true});
}

/**
 * 获取合作伙伴
 */
export function getPartnerData()
{
  return request.get('partner', {}, { noAuth:true});
}

/**
 * 获取消息通知
 */
export function getNoticeData(data)
{
  return request.get('notice', data);
}

/**
 * 消息详情
 */
export function getNoticeInfo(data)
{
  return request.get('notice_info', data);
}

/**
 * 消息删除
 */
export function noticeDelete(data)
{
  return request.post("notice_delete", data);
}

/**
 * 提交藏品征集
*/
export function submitCollect(data)
{
  return request.post("submit_collect",data);
}

/**
 * 获取藏品征集服务规则
 */
export function getCollectRule()
{
  return request.get('collect_rule', {}, { noAuth:true});
}

/**
 * 提交藏品寄售
*/
export function submitConsign(data)
{
  return request.post("submit_consign",data);
}

/**
 * 获取藏品寄售服务规则
 */
export function getConsignRule()
{
  return request.get('consign_rule', {}, { noAuth:true});
}

/**
 * 获取转账信息
 */
export function getBankInfo()
{
  return request.get('bank_info', {}, { noAuth:true});
}