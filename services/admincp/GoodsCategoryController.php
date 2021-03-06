<?php

class Admincp_GoodsCategoryController extends Admincp_Controller_Action
{
	public function init()
	{
		$this->_auth();
	}

	public function doDefault()
	{
		$this->redirect('action=list');
	}

	public function doList()
	{
		$select = M('Goods_Category')->select()
			->where('parent_id = ?', $this->_request->cid ? $this->_request->cid : 0)
			->order('rank ASC, id ASC');
		
		$view = $this->_initView();
		$view->datalist = $select->fetchRows();
		$view->types = M('Goods_Type')->fetchRows();
		$view->render('goods/category/list.php');
	}

	public function doAdd()
	{
		if ($this->_request->success) {
			return $this->_notice(array(
				'title' => '分类添加成功！',
				'links' => array(
					array('继续添加', '&success'),
					array('返回列表', '&action=list')
				),
				'autoback' => array('自动返回上一页', '&success'),
			), 'success');
		}

		if ($this->_request->isPost()) {
			M('Goods_Category')->insert($this->_request->getPosts());
			$this->redirect('&success=1');
		}

		$view = $this->_initView();
		$view->types = M('Goods_Type')->fetchRows();
		$view->render('goods/category/input.php');
	}

	public function doEdit()
	{	
		$data = M('Goods_Category')->getById((int)$this->_request->id);
		if ($this->_request->isPost()) {
			M('Goods_Category')->updateById($this->_request->getPosts(), (int)$this->_request->id);
			$this->redirect(isset($this->_request->ref) ? base64_decode($this->_request->ref) : 'action=list');
		}

		$path = $data['path'] . ',' . $data['id'];

		$view = $this->_initView();
		$view->data = $data;
		$view->types = M('Goods_Type')->fetchRows();
		$view->render('goods/category/input.php');
	}

	public function doGoodsCount()
	{
		if ($this->_request->success) {
			return $this->_notice(array(
				'title' => '计算完成',
				'links' => array(
					array('返回分类列表', 'action=list')
				),
			), 'success');
		}

		$page = isset($this->_request->page) ? (int)$this->_request->page : 0;
		$pagesize = 10;
		$list = M('Goods_Category')->select()
			->order('level DESC')
			->limit(($page*$pagesize),$pagesize)
			->fetchRows();
		if(!$list->total()){
			$this->redirect('&success=1');
		}
		foreach($list as $val){
			$ids = M('Goods_Category')->getChildIds($val['id']);
			$count = M('Goods')->count('category_id in ('.$ids.') AND is_selling = 1 AND is_checked = 2');
			$val['goods_num'] = $count;
			$val->save();
		}
		echo '计算中...';
		echo '<script type="text/javascript">location.href="?controller=goods_category&action=goods_count&page='.($page+1).'";</script>';
		echo Suco_Db::dump();
	}

	public function doMakeCache()
	{
		if ($this->_request->success) {
			return $this->_notice(array(
				'title' => '缓存更新成功',
				'links' => array(
					array('返回分类列表', 'action=list')
				),
			), 'success');
		}

		$categories = M('Goods_Category')->select()
			->order('rank ASC, id ASC')
			->fetchRows()
			->toTreeList();
			
		foreach ($categories as $row) {
			$tmp = M('Goods_Category')->select('name')->where('id IN ('.$row['path_ids'].') OR id = '.(int)$row['id'])->order('level ASC')->fetchCols('name');
			$data[$row['id']] = array_merge($row, array('path_text' => implode(' > ', $tmp)));
		}

		//创建缓存
		echo Suco_Cache_File::save($data, 0, 'all_cates');
		$this->redirect('&success=1');
	}

	public function doGetJson()
	{
		echo M('Goods_Category')->select('id, parent_id, name, level')
			->order('id ASC, rank ASC')
			->fetchOnKey('id')
			->toJson();
	}
}
