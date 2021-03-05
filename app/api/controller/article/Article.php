<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/6/3
 * Time: 11:01
 */

namespace app\api\controller\article;

use app\api\controller\Base;
use app\models\article\ArticleCate;
use app\models\article\Article as ArticleModel;
use app\models\article\ArticleContent;
use app\Request;
use sensen\services\UtilService;
use think\facade\Db;

class Article extends Base
{
    /**
     * 获取文章分类
     * @param Request $request
     * @return mixed
     */
    public function getArticleCate(Request $request)
    {
        $cates = ArticleCate::where(['status'=>1, 'delete_time'=>0, 'pid'=>0, 'lawfirm_id'=>0])->select()->toArray();

        $cate = [];
        foreach ($cates as $k=>$v){
            $tmp['newsid'] = $v['id'];
            $tmp['id'] = 'tab'.str_pad($k+1, 2, '0', STR_PAD_LEFT);
            $tmp['name'] = $v['title'];
            $cate[] = $tmp;
        }
        return app('json')->successful(compact('cate'));
    }

    /**
     * 获取文章列表
     * @param Request $request
     * @return mixed
     */
    public function getArticleList(Request $request)
    {
        list($cid, $page, $limit, $lawfirm_id) = UtilService::getMore([
            ['cid',0],
            ['page',1],
            ['limit',10],
            ['lawfirm_id',0],
        ],$request, true);

        $lists = ArticleModel::getCateArticle($cid, $page, $limit, $lawfirm_id)->toArray();

        foreach ($lists as &$v){
            if($v['thumb']){
                $v['article_type'] = 1;
            }else{
                $v['article_type'] = 0;
            }
            if($v['thumb'] && $v['flag']=='t'){
                $v['article_type'] = 4;
            }
        }

        //获取置顶文章
        $topWhere = ['flag'=>'t', 'status'=>1, 'delete_time'=>0];
        if($lawfirm_id){
            $topWhere['lawfirm_id'] = $lawfirm_id;
        }
        if($cid){
            $topWhere['cid'] = $cid;
        }

        $topArticle = ArticleModel::where($topWhere)->limit(5)->select();
        $topArticle = $topArticle?:[];

        return app('json')->successful(compact('lists', 'topArticle'));
    }

    /**
     * 获取文章详情
     * @param Request $request
     * @return mixed
     */
    public function getArticleDetail(Request $request)
    {
        list($id, $uid) = UtilService::getMore([
            ['id', 0],
            ['uid', 0]
        ],$request, true);

        $info = ArticleModel::get(['id'=>$id]);
        if(!$id || !$info){
            return app('json')->fail('文章不存在');
        }

        //增加点击量
        ArticleModel::bcInc($id, 'click', 1);

        $info['thumb'] = $info['thumb']?set_web_url($info['thumb']):set_web_url('/static/logo.png');

        //图片路径转为绝对路径
        $content = ArticleContent::get(['article_id'=>$id]);
        $info['content'] = updateContentUrl($content['content']);

        //获取作者
        $info['author'] = $info['author']?:sys_config('default_author', '潍坊市律师协会');
        $info['avatar'] = sys_config('logo');
        if($info['user_type']==2 && $info['user_id']){
            $info['author'] = $info['user_name'];
            $avatar = Db::name('user')->where('id', $info['user_id'])->value('avatar');
            $info['avatar'] = set_web_url($avatar);
        }

        //判断是否已收藏
        $info['is_favorite'] = 0;
        if($uid){
            $isFavorite = Db::name('favorite')->where(['user_id'=>$uid, 'mtable'=>'article', 'mid'=>$id])->find();
            if($isFavorite){
                $info['is_favorite'] = $isFavorite['id'];
            }
        }

        return app('json')->successful(compact('info'));
    }

    /**
     * 搜索
     * @param Request $request
     * @return array
     */
    public function doSearch(Request $request)
    {
        list($keyword) = UtilService::postMore([
            ['keyword', '']
        ], $request, true);

        if(!$keyword) return [];

        //按名称搜搜
        $lists = Db::name('article')->where(['delete_time'=>0, 'status'=>1])->where('title', 'like', "%$keyword%")->field('id, uuid, delete_time, status, title')->limit(30)->select();

        return app('json')->successful(compact('lists'));
    }



}