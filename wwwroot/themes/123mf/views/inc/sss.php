<div class="nn-input">
        <a class="nn-new" href="<?=$this->url('default/goods/nav')?>">搜索导航表</a>
        <?php $this->_request->sbt = $this->_request->sbt ? $this->_request->sbt : 1;?>
        <form class="nn-big-input" action="<?=$this->url($this->_request->sbt==1?'default/goods/search':'default/shop/search')?>">
            <select name="sbt">
                <option value="1" <?=$this->_request->sbt==1?'selected':'selected'?>>商品</option>
                <option value="2" <?=$this->_request->sbt==2?'selected':''?>>商家</option>
            </select>
            <input class="nn-kk" type="text" name="q" value="<?=$this->_request->q?>">
            <input class="nn-dis" value="" type="submit">
        </form>
        <div class="nn-big-right"><a href="<?=$this->url('default/goods')?>"><img src="<?php echo static_file('mobile/img/nimg-03.png'); ?> " alt=""></a></div>
    </div>
    <script type="text/javascript">
        $('[name=sbt]').on('change', function(){
            var v = parseInt($(this).val());
            switch(v) {
                case 1:
                    $('.nn-big-input').prop('action', '<?=$this->url('default/goods/search')?>');
                    break;
                case 2:
                    $('.nn-big-input').prop('action', '<?=$this->url('default/shop/search')?>');
                    break;
            }
        });
    </script>