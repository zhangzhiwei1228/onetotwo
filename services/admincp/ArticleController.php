<?php

class Admincp_ArticleController extends Admincp_Controller_Action
{
	public function init()
	{
		$this->_auth();
	}

	public function doList()
	{
		$pageSize = 20;
		$currentPage = isset($this->_request->page) ? $this->_request->page : $this->_request->page;

		$select = M('Article')->alias('a')
			->leftJoin(M('Article_Category')->getTableName() . ' AS b', 'a.category_id = b.id')
			->columns('a.*, b.name AS name')
			->order('a.update_time DESC')
			->paginator($pageSize, $currentPage);

		//按分类查找
		if ($this->_request->cid) {
			$ids = M('Article_Category')->getChildIds((int)$this->_request->cid);
			$select->where('a.category_id IN ('.($ids ? $ids : $this->_request->cid).')');
		}
		//按关键词查找
		if ($this->_request->q) {
			$keywords = explode(' ', $this->_request->q);
			foreach ($keywords as $i => $val) {
				$keywords[$i] = '+'.$val.'*';
			}
			$keyword = implode(' ', $keywords);
			$select->match('a.title', $keyword, 'IN BOOLEAN MODE');
		}
		//按作者查找
		if ($this->_request->author) {
			$select->where('a.author LIKE ?', "%{$this->_request->author}%");
		}
		//按来源查找
		if ($this->_request->source) {
			$select->where('a.source LIKE ?', "%{$this->_request->source}%");
		}
		//按时间查找
		if ($this->_request->begin_time) {
			$select->where('a.create_time >= ?', strtotime($this->_request->begin_time));
		}
		if ($this->_request->end_time) {
			$select->where('a.create_time <= ?', strtotime($this->_request->end_time) + (3600 * 24));
		}
		//按来源查找
		if (isset($this->_request->is_checked)) {
			$select->where('a.is_checked = ?', $this->_request->is_checked);
		}
		//按来源查找
		if (isset($this->_request->is_best)) {
			$select->where('a.is_best = ?', $this->_request->is_best);
		}

		$view = $this->_initView();
		$view->category = M('Article_Category')->getById((int)$this->_request->cid);
		$view->categories = M('Article_Category')->select()->fetchRows()->toTreeList();
		$view->datalist = $select->fetchRows();
		$view->render('article/list.php');
	}

	public function doDetail()
	{
		$data = M('Article')->getById((int)$this->_request->id);
		if (!$data->exists()) {
			throw new Suco_Controller_Dispatcher_Exception('Not found.');
		}

		$view = $this->_initView();
		$view->data = $data;
		$view->prevdata = M('Article')->select()->where('id > ?', $data['id'])->order('id ASC')->fetchRow();
		$view->nextdata = M('Article')->select()->where('id < ?', $data['id'])->order('id DESC')->fetchRow();
		$view->paths = M('Article_Category')->getPath((int)$data['cid']);
		$view->render('article/detail.php');
	}

	public function doAdd()
	{
		if ($this->_request->isPost()) {
			M('Article')->insert(array_merge($this->_request->getPosts(), $this->_request->getFiles()));
			$this->redirect(isset($this->_request->ref) ? base64_decode($this->_request->ref) : 'action=list&cid=' . $_POST['cid']);
		}

		$view = $this->_initView();
		$view->categories = M('Article_Category')->toTreeList(M('Article_Category')->select()->fetchRows()->toArray());
		$view->render('article/input.php');
	}

	public function doEdit()
	{
		$data = M('Article')->getById((int)$this->_request->id);
		if (!$data->exists()) {
			throw new Suco_Controller_Dispatcher_Exception('Not found.');
		}

		if ($this->_request->isPost()) {
			M('Article')->updateById(array_merge($this->_request->getPosts(), $this->_request->getFiles()), (int)$this->_request->id);
			$this->redirect(isset($this->_request->ref) ? base64_decode($this->_request->ref) : 'action=list');
		}

		$view = $this->_initView();
		$view->data = $data;
		$view->categories = M('Article_Category')->toTreeList(M('Article_Category')->select()->fetchRows()->toArray());
		$view->render('article/input.php');
	}

	public function doBatch()
	{
		switch($_POST['act']) {
			case 'delete':
				foreach ((array)$_POST['ids'] as $id) {
					M('Article')->deleteById((int)$id);
				}
				break;
			case 'move':
				if ($_POST['cid']) {
					foreach ((array)$_POST['ids'] as $id) {
						M('Article')->updateById('category_id = '.(int)$_POST['cid'], (int)$id);
					}
				}
				break;
		}
		$this->redirect($_SERVER['HTTP_REFERER']);
	}

}