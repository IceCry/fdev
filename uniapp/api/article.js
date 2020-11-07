import request from "@/utils/request.js";

/**
 * 获取文章分类
*/
export function getArticleList(data)
{
  return request.get("article_list", data, { noAuth : true});
}

/**
 * 获取文章详情
*/
export function getArticleDetail(data)
{
  return request.get("article", data, { noAuth : true});
}

/**
 * 搜索文章
*/
export function searchArticle(data)
{
  return request.post("search_article", data, { noAuth : true});
}