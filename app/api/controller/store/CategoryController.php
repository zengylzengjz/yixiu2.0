<?php
namespace app\api\controller\store;


use app\models\store\StoreCategory;
use app\admin\model\store\StoreCategory as CategoryModel;
use app\Request;
use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;

class CategoryController
{

    public function category(Request $request)
    {
        // $cateogry = StoreCategory::with('children')->where('is_show',0)->order('sort desc,id desc')->where('pid',0)->select();

        //修改 lancercd
        $cateogry = StoreCategory::where('is_show',1)->order('sort desc,id desc')->where('pid',0)->select();
        return app('json')->success($cateogry->hidden(['add_time','is_show','sort','children.sort','children.add_time','children.pid','children.is_show'])->toArray());
        // return app('json')->$cateogry->toArray();
    }
    //修改 lancercd
    public function categoryList($pid='')
    {
      $pid=='' && Json::fail('缺少参数');

      $cateogry = StoreCategory::where('pid', $pid)->where('is_show', 1)->order('sort esc, id esc')->select();
      if($cateogry->isEmpty()){
        return Json::fail('Not Found');
      }
      return app('json')->success($cateogry->hidden(['add_time','is_show','sort','pic'])->toArray());
    }
    //修改 lancercd
    public function save(Request $request)
    {
        $data = Util::getMore([
            'pid',
            'cate_name',
            ['pic',[]],
            'sort',
            ['is_show',0]
        ],$request);
        $date['pic']=null;
        $data['is_show']=1;
        $data['sort']=0;
        $data['pic']=' ';
        if($data['pid'] == '') return Json::fail('请选择类');
        if(!$data['cate_name']) return Json::fail('请输入分类或型号名');
        if($data['sort'] <0 ) $data['sort'] = 0;
        $data['pic'] = $data['pic'][0];
        $data['add_time'] = time();
        CategoryModel::create($data);
        return Json::successful('添加成功!');
    }
}
