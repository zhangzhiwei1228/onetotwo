<?php

class Admincp_GoodsController extends Admincp_Controller_Action
{
	public function init()
	{
		$this->_auth();
	}

	public function doSearch()
	{
		$view = $this->_initView();
		$view->render('goods/search.php');
	}

	public function doList()
	{
		$select = M('Goods')->alias('g')
			->paginator(20, $this->_request->page);

		switch ($this->_request->t) {
			case 'quantity_warning':
				$select->where('g.is_selling = 1 AND g.quantity != -1 AND (g.quantity = 0)')->order('g.quantity ASC');
				break;
			case 'approval_pending':
				$select->where('g.is_checked = 0');
				break;
			case 'not_approved':
				$select->where('g.is_checked = 1');
				break;
			case 'onsale':
				$select->where('g.is_selling = 1 AND g.is_checked = 2 AND (g.expiry_time = 0 OR g.expiry_time > ?)', time());
				break;
			case 'offsale':
				$select->where('(g.is_selling = 0 AND g.is_checked = 2 OR (g.expiry_time != 0 AND g.expiry_time < ?))', time());
				break;
			case 'promotion':
				$select->where('(g.is_selling = 1 AND g.is_checked = 2 AND g.is_promotion = 1)');
				break;
		}

		if ($this->_request->cid) {
			$ids = M('Goods_Category')->getChildIds((int)$this->_request->cid);
			$select->where('g.category_id IN ('.($ids ? $ids : 0).')');
		}
		if ($this->_request->pid) {
			$select->where('g.id = ?', (int)$this->_request->pid);
		}
		if ($this->_request->code) {
			$select->where('g.code = ?', (string)$this->_request->code);
		}
		if ($this->_request->start_time) {
			$select->where('g.create_time >= ?', strtotime($this->_request->start_time));
		}
		if ($this->_request->end_time) {
			$select->where('g.create_time <= ?', strtotime($this->_request->end_time));
		}
		if ($this->_request->q) {
			//全文索引
			/*
			$keywords = segment($this->_request->q);
			$keywords = explode(' ', $keywords);
			foreach ((array)$keywords as $i => $val) {
				$keywords[$i] = '+'.$val.'*';
			}
			$keyword = implode(' ', (array)$keywords);

			$select->rightJoin(M('Goods_Match')->getTableName().' AS gm', 'gm.goods_id = g.id')
				->match('gm.title', $keyword, 'IN BOOLEAN MODE')
				->columns('g.*');*/
			$select->where('(g.code LIKE ? OR g.title LIKE ? OR g.tags LIKE ?)', '%'.$this->_request->q.'%');
		}
		$select->order('g.create_time DESC');

		$view = $this->_initView();
		$view->category = M('Goods_Category')->getById((int)$this->_request->cid);
		$view->datalist = $select->fetchRows()
			->hasmanyPromotions()
			->hasmanyCategory()
			->hasmanySku();
		$view->render('goods/list.php');
	}

	public function doCopy()
	{
		$data = M('Goods')->getById((int)$this->_request->id)->toArray();
		$attr = M('Goods_Attribute')->select()
			->where('goods_id = ?', $data['id'])
			->fetchRows()
			->toArray();
		$skus = M('Goods_Sku')->select()
			->where('goods_id = ?', $data['id'])
			->fetchRows()
			->toArray();

		unset($data['id']);
		$data['title'] .= ' - 副本';

		$data['ref_img'] = json_decode($data['ref_img'], 1);
		
		foreach($attr as $row) {
			unset($row['id'], $row['goods_id']);
			$data['attributes'][] = $row;
		}

		foreach($skus as $row) {
			unset($row['id'], $row['goods_id']);
			$data['skus'][] = $row;
		}

		$gid = M('Goods')->insert($data);
		$this->redirect($_SERVER['HTTP_REFERER']);
	}

	public function doRecycle()
	{
		$select = M('Goods')->select()
			->paginator(20, $this->_request->page)
			->where('is_trash = 1');

		$view = $this->_initView();
		$view->datalist = $select->fetchRows();
		$view->render('goods/recycle.php');
	}

	public function doSku()
	{
		$select = M('Goods')->alias('g')
			->rightJoin(M('Goods_Sku')->getTableName().' AS gs', 'g.id = gs.goods_id')
			->columns('gs.*, gs.thumb, g.code AS goods_code, g.thumb AS goods_thumb, g.title, g.package_unit, g.package_lot_unit, g.package_quantity')
			->paginator(20, $this->_request->page);

		if ($this->_request->q) {
			$select->where('(gs.code LIKE ? OR g.code LIKE ? OR g.title LIKE ?)', '%'.$this->_request->q.'%');
		}
		if ($this->_request->cid) {
			$ids = M('Goods_Category')->getChildIds((int)$this->_request->cid);
			$select->where('g.category_id IN ('.($ids ? $ids : 0).')');
		}

		switch($this->_request->sortby) {
			case 'cost_price_asc';
				$select->order('cost_price ASC');
				break;
			default:
				$select->order('goods_id DESC, id DESC');
				break;
		}


		$view = $this->_initView();
		$view->datalist = $select->fetchRows();
		$view->render('goods/sku.php');
	}

	public function doDetail()
	{
		$data = M('Goods')->getById((int)$this->_request->id);
		if (!$data->exists()) {
			throw new Suco_Controller_Dispatcher_Exception('Not found.');
		}

		$view = $this->_initView();
		$view->data = $data;
		$view->render('goods/detail.php');
	}

	public function doToggleStatus()
	{
		$fields = array('is_new', 'is_hot', 'is_rec', 'is_selling');
		if (in_array($this->_request->t, $fields)) {
			$field = $this->_request->t;
			$data[$field] = abs($this->_request->v - 1);
			M('Goods')->updateById($data, (int)$this->_request->id);
		} elseif ($this->_request->t == 'is_checked') {
			$data['is_checked'] = $this->_request->v;
			M('Goods')->updateById($data, (int)$this->_request->id);
		}
		$this->redirect(isset($this->_request->ref) ? base64_decode($this->_request->ref) : 'action=list');
	}

	public function doBatch()
	{
		switch($_POST['act']) {
			case 'delete':
				foreach ((array)$_POST['ids'] as $id) {
					M('Goods')->deleteById((int)$id);
				}
				break;
			case 'move':
				if ($_POST['cid']) {
					foreach ((array)$_POST['ids'] as $id) {
						M('Goods')->updateById('category_id = '.(int)$_POST['cid'], (int)$id);
					}
				}
				break;
			case 'onsale':
				foreach ((array)$_POST['ids'] as $id) {
					M('Goods')->updateById('is_selling = 1, is_checked = 2', (int)$id);
				}
				break;
			case 'offsale':
				foreach ((array)$_POST['ids'] as $id) {
					M('Goods')->updateById('is_selling = 0', (int)$id);
				}
				break;
			case 'update_sku':
				foreach ((array)$_POST['data'] as $id => $data) { $i++;
					M('Goods_Sku')->updateById($data, (int)$id);
					$updateGoods[] = $data['goods_id'];
				}

				$updateGoods = array_unique($updateGoods);
				foreach($updateGoods as $goodsId) {
					$sku = M('Goods_Sku')->select('
						MIN(selling_price) min_price, 
						MAX(selling_price) AS max_price,
						SUM(quantity) AS qty')
						->where('goods_id = ?', $goodsId)
						->fetchRow()
						->toArray();
					
					M('Goods')->updateById(array(
						'min_price' => $sku['min_price'],
						'max_price' => $sku['max_price'],
						'quantity' => $sku['qty']
					),(int)$goodsId);
				}
				break;
		}
		$this->redirect($_SERVER['HTTP_REFERER']);
	}
}
