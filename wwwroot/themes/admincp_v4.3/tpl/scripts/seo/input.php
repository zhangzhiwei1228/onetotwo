<?php
if(!defined('APP_KEY')) { exit('Access Denied'); }
$this->title = ($this->_request->getActionName() == 'add' ? '添加' : '修改'). '规则';
$ref = isset($this->_request->ref) ? base64_decode($this->_request->ref) : $this->url('action=list');
$this->head()->setTitle($this->title);
?>

<div class="sui-box">
	<div class="sui-page-header">
		<h1><?=$this->head()->getTitle()?></h1>
	</div>
	<form method="post" enctype="multipart/form-data" class="form-horizontal sui-dataitem">
		<div class="form-group">
			<label class="control-label col-sm-2"> URL: </label>
			<div class="col-sm-4"> <textarea name="match" class="form-control" rows="2"><?=$this->data['match']?></textarea>
				<p class="help-block">可支持正则匹配</p>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2"> 标题: </label>
			<div class="col-sm-4"> <textarea name="title" class="form-control" rows="2"><?=$this->data['title']?></textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2"> 关键词: </label>
			<div class="col-sm-4"> <textarea name="meta_keywords" class="form-control" rows="2"><?=$this->data['meta_keywords']?></textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2"> 描述: </label>
			<div class="col-sm-4"> <textarea name="meta_description" class="form-control" rows="4"><?=$this->data['meta_description']?></textarea>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-10 col-sm-offset-2">
				<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> 保存</button>
				<button type="button" class="btn" onclick="window.location='<?=$ref?>'">取消</button>
			</div>
		</div>
	</form>
</div>