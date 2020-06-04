<?php

namespace view;

use Cache\Adapter\Redis\RedisCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;
use control\Control;
use control\ControlBootstrap;
use control\fields\Base;
use org\helper\HtmlHelper;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\facade\Cache;
use think\facade\Env;
use think\facade\Log;
use think\Response;
use think\response\Json;

/**
 * 数据列表组件
 *
 * Class TableList
 * @since v1.0.0
 * @author ranj
 * @package view
 */
class TableList extends Table
{
    /**
     * 业务状态+系统状态（数量）
     * 方便对数据进行横向筛选
     *
     * @var array
     */
    protected $states;

    /**
     * 获取批量操作按钮
     *
     * @var callable
     */
    protected $batchListCall;

    /**
     * 获取过滤条件字段
     *
     * @var callable
     */
    protected $filtersCall;

    /**
     * 获取列表列名字段信息
     *
     * @var callable
     */
    protected $columnsCall;

    /**
     * 获取单列数据的按钮配置信息
     *
     * @var callable
     */
    protected $itemActionCall;

    /**
     * 执行事件
     * 导入导出、删除等一系列事件
     *
     * @var callable
     */
    protected $call_event=null;

    /**
     * 执行查询单页数据
     *
     * @var callable
     */
    protected $call_search_items;

    /**
     * 执行数据总数查询
     * @var callable
     */
    protected $call_search_total;

    /**
     * 事件触发地址，默认为当前页面地址
     *
     * @var string
     */
    protected $url;

    /**
     * 声明当前列表是否可以拖动排序
     *
     * @var boolean
     */
    protected $sortable;

    /**
     * 声明当前数据的主键 的字段名
     *
     * @var string
     */
    protected $primaryKey;

    /**
     * 声明当前分页，每页数据量
     *
     * @var integer
     */
    protected $pageSize;

    /**
     * 声明额外的分页查询参数
     *
     * @var array
     */
    protected $query_args;

    /**
     * 表单渲染器
     *
     * @var Control
     */
    protected $control;

    /**
     * @param $viewKey string 前端组件统一取值的键
     *
     * @param $args array 初始化列表所需要的元素
     * states : array|function
     * batch : 顶部操作按钮组
     *          按钮事件key=>{
     *              title:按钮文字
     *              icon:按钮图标
     *              class:按钮自定义样式
     *              group: navigation(链接类型的按钮，点击跳转新页面) ，event(批量操作的事件按钮，点击触发(删除等)事件)
     *              url: (当group为 "navigation" 时)跳转链接
     *              type: (当group为 “event” 时)  divider:分割线 default: 按钮,
     *              filter:当前按钮事件的一些条件筛选
     *          }
     * filters ：列表的筛选条件
     *          筛选字段key=>{
     *              title: 字段名称
     *              type: 字段类型 可选 ：text select checkbox datetime ...
     *              ...
     *          }
     * columns : 数据列表列项
     *          列对应的数据字段=>{
     *              title: 列名
     *              sortable: 是否可排序
     *              primary: 标识是主键
     *          }
     * action : 单行数据，操作按钮事件key=>{
     *              title:按钮文字
     *              icon:按钮图标
     *              class:按钮自定义样式
     *              filter:当前按钮事件的一些条件筛选
     *          }
     */
    public function __construct($viewKey,$args){
        $args = parse_args($args,array(
            'primary'=>'id',
            'sortable'=>false,
            'states'=>null,
            'control'=>new ControlBootstrap(),
            //额外的查询参数
            'query_args'=>array(),
            'columns'=>null,
            'batch'=>null,
            'filter'=>null,
            'action'=>null,
            'pageSize'=>20,
            'url'=>null
        ));

        $this->primaryKey = $args['primary'];
        $this->sortable = $args['sortable'];
        $this->url = $args['url'];
        $this->viewKey = $viewKey;
        $this->pageSize = $args['pageSize'];
        $this->states = $args['states'];
        $this->batchListCall = $args['batch'];
        $this->filtersCall = $args['filter'];
        $this->columnsCall = $args['columns'];
        $this->itemActionCall = $args['action'];
        $this->query_args = $args['query_args'];
        $this->control = $args['control'];
    }

    /**
     * @return Control
     */
    public function getControl(){
        return $this->control;
    }

    public function getQueryArgs(){
        return array_is_empty($this->query_args)?array():$this->query_args;
    }

    public function getSortable(){
        return $this->sortable;
    }

    public function getPageSize(){
        return $this->pageSize?$this->pageSize:20;
    }

    public function getUrl($default=null){
        return $this->url?$this->url:$default;
    }

    public function isEnableAction(){
        return $this->itemActionCall?true:false;
    }

    public function getItemAction($item){
        $state = $this->getCurrentState();

        $actionList =  $this->itemActionCall?call_user_func_array($this->itemActionCall,array($item,$state,$this)):null;
        if(!$actionList){
            return array();
        }

        return $actionList;
    }


    /**
     * 监听导出，并执行
     * @param array $columns 导出字段,默认为取columns
     * @param string $filename 文件名(不含后缀和路径信息)
     * @param array $specialDataList
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function handleExport($columns=array(), $filename=null, $specialDataList=[]){
        $basePath =  Env::get('root_path'). "public";
        $now = strtotime(date('Y-m-d H:i:s'));
        $dataList = db_default("module_export")
            ->where("created_time","<=",$now-60*5)
            ->limit(0,30)
            ->select();
        //清除上传导出的文件缓存
        if($dataList){
            foreach ($dataList as $data){
                try{
                    if(@file_exists($basePath.$data['filename'])){
                        @unlink($basePath.$data['filename']);
                    }

                    db_default("module_export")
                        ->where("id",$data['id'])
                        ->delete();
                }catch (\Exception $e){

                }
            }
        }

        $filePath = "/upload/export/";
        if(!@is_dir($basePath. $filePath)){
            if(!@mkdir($basePath. $filePath,0777, true)){
                return errorJson(40112);
            }
        }

        $client = Cache::handler();
        $pool = new RedisCachePool($client);
        $simpleCache = new SimpleCacheBridge($pool);
        Settings::setCache($simpleCache);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        try{
            if(!count($columns)){
                $columns = $this->getColumns();
            }
            $columns = fields_spread($columns);
            $index = 1;
            foreach ($columns as $columnKey=>$column){
                if(empty($column['title'])){
                    $column['title']="";
                }
                $col = $index++;
                //设置单元格内容
                $worksheet->setCellValueByColumnAndRow($col, 1, $column['title']);
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $worksheet->getStyle($columnLetter)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                //$header[]= mb_convert_encoding($column['title'],'gb2312','utf-8');
            }

            if($specialDataList&&count($specialDataList)){
                $items = $specialDataList;
                $start = 1;
                foreach ($items as $item){
                    $index = 1;
                    $start++;

                    $ctrl = new ControlBootstrap();
                    foreach ($columns as $columnKey=>$column){
                        $columnIndex = $index++;
                        $row = $start;
                        $celVal = $ctrl->preview($columnKey,$column,$item,true);

                        //避免数字内容 被科学格式转换
                        if(is_string($celVal)&&preg_match("/^[\d\s]+$/",$celVal)){
                            $celVal = " ".$celVal;
                        }
                        if(is_numeric($celVal)){
                            $celVal ="{$celVal}";
                        }
                        $worksheet->setCellValueByColumnAndRow($columnIndex, $row,$celVal);
                    }
                }
            }else{
                $pageIndex = 0;
                $pageSize = 500;
                $totalCount = $this->doSearchTotal();
                $totalPage = ceil($totalCount/($pageSize*1.0));


                while ($pageIndex++<=$totalPage){
                    $items = $this->doSearchItems($pageIndex,$pageSize);
                    if(!$items){
                        continue;
                    }

                    $start = ($pageIndex-1)*$pageSize+1;
                    foreach ($items as $item){
                        $index = 1;
                        $start++;

                        $ctrl = new ControlBootstrap();
                        foreach ($columns as $columnKey=>$column){
                            $columnIndex = $index++;
                            $row = $start;
                            $celVal = $ctrl->preview($columnKey,$column,$item,true);

                            //避免数字内容 被科学格式转换
                            if(is_string($celVal)&&preg_match("/^[\d\s]+$/",$celVal)){
                                $celVal = " ".$celVal;
                            }
                            //$worksheet->getCellByColumnAndRow($columnIndex, $row)->getStyle()->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                            if(is_numeric($celVal)){
                                $celVal ="{$celVal}";
                            }
                            $worksheet->setCellValueByColumnAndRow($columnIndex, $row,$celVal);
                        }
                    }
                }
            }


            if($filename){
                $filename = str_replace("/","-",$filename);
                $filenameArray = explode('.',$filename);
                if($filenameArray&&count($filenameArray)){
                    $filename = $filenameArray[0];
                }
            }

            $filename = ($filename?:date('Ymd-His')). '.xlsx';
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save( $basePath.$filePath.$filename);
            db_default("module_export")
                ->insert(array(
                    'filename'=>$filePath.$filename,
                    'created_time'=>$now
                ));
            return successJson(array(
                'url'=>$filePath.$filename
            ));
        }
        catch (\Exception $e){
            Log::error($e);
            return errorJson(500,$e->getMessage());
        }
    }

    /**
     * 注册查询
     * @param $call
     */
    public function handleSearchTotal($call){
        $this->call_search_total =$call;
    }
    public function handleSearchItems($call){
        $this->call_search_items =$call;
    }
    /**
     * 注册事件
     * @param $call
     */
    public function handleEvent($call){
        $this->call_event =$call;
    }

    /**
     * 获取过滤参数列表
     *
     * @return array
     */
    public function getQueryFilters(){
        $filters = maybe_json_decode((request()->param("filters")),true);
        if(!$filters){
            $filters=array();
        }

        $filterQuery = array();
        $filterQuery['state'] = $this->getCurrentState();

        $filterList = $this->getFilters();
        if($filterList){
            $ctrl = new Control();
            foreach ($filterList as $filterKey=>$f){
                $filterQuery[$filterKey]  = null;
                try{
                    $ctrl->param($filterKey,$f,$filterQuery,function($k) use ($filters){
                        return isset($filters[$k])?$filters[$k]:null;
                    });
                }catch (\Exception $e){

                }
            }
        }

        return $filterQuery;
    }

    /**
     * 获取排序的请求参数列表
     * @return array
     */
    public function getQuerySorts(){
        $sorts = maybe_json_decode((request()->param("sorts")),true);
        if(!$sorts){
            $sorts=array();
        }

        $sorts = map_deep_call($sorts,'sanitize_text_field');
        $sortsQuery=array();
        $sortList = $this->getSortableColumns();
        if(!$sortList) {
            return $sortsQuery;
        }
        foreach ($sortList as $sortKey => $s) {
            $align = !empty($s['align'])?"{$s['align']}.":"";
            $sortStatus = isset($sorts[$sortKey]) ? $sorts[$sortKey] : 'sorting';
            switch ($sortStatus) {
                default:
                    break;
                case 'sorting_asc':
                    $sortsQuery[$align.$sortKey] = 'asc';
                    break;
                case 'sorting_desc':
                    $sortsQuery[$align.$sortKey] = 'desc';
                    break;
            }
        }
        return $sortsQuery;
    }


    public function doSearchTotal(){
        if(!$this->call_search_total){
            return 0;
        }

        $filterQuery = $this->getQueryFilters();
        return call_user_func_array($this->call_search_total,array($filterQuery,$this));
    }

    public function doSearchItems($pageIndex,$pageSize){
        if(!$this->call_search_items){
            return false;
        }

        $filterQuery = $this->getQueryFilters();
        $sortsQuery = $this->getQuerySorts();
        $start = ($pageIndex-1)*$pageSize;
        $end = $pageSize;

        return call_user_func_array($this->call_search_items,array($start,$end,$filterQuery,$sortsQuery,$this));
    }

    /**
     * @return bool|Json
     */
    public function doSearch(){
        if(!$this->call_search_items){
            return false;
        }

        if(!request()->isAjax()){
            return false;
        }

        $_ = request()->param("__");
        if($_!=$this->getViewKey("search_")){
            return false;
        }

        $pageSize = $this->getPageSize();
        $pageIndex = absint(request()->param('page_index', 1, FILTER_VALIDATE_INT));

        $items = $this->doSearchItems($pageIndex,$pageSize);

        $columns = $this->getColumns();
        $enableAction = $this->isEnableAction();
        ob_start();
        if($columns&&count($columns)){
            if(!$items||!count($items)){
                ?>
                <tr>
                    <td colspan="<?php echo count($columns)+($enableAction?1:0)/*操作栏*/+1/*checkbox*/+($this->getSortable()?1:0);?>">
                        <div class="text-center">
                            <p> <?php echo __('请尝试更改查询条件后重新查询或刷新页面！')?> </p>
                        </div>
                    </td>
                </tr>
                <?php
            }else{
                foreach ($items as $index=>$item){
                    $primaryColumn = $this->getPrimaryColumn();
                    ?>
                    <tr data-id="<?php echo $primaryColumn?esc_attr($item[$primaryColumn]):"";?>" id="<?php echo $primaryColumn?esc_attr($item[$primaryColumn]):"";?>" class="<?php echo $index%2?'even':'odd';?>">
                        <?php
                        $columnKey = $primaryColumn;
                        $value = isset($item[$columnKey])?$item[$columnKey]:null;
                        ?>
                        <td style="<?php echo $this->isEnablePrimaryCheckbox()?'':'display:none;'?>">
                            <label style="min-width: 20px;text-align: right;"><?php echo ($pageIndex-1)*$pageSize+$index+1;?>.</label>
                            <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                <input type="checkbox" name="<?php echo esc_attr($this->getViewKey());?>-<?php echo esc_attr($columnKey)?>" class="checkboxes" value="<?php echo esc_attr($value)?>">
                                <span></span>
                            </label>
                        </td>
                        <?php

                        foreach ($columns as $columnKey=>$column){
                            if(!isset($column['type'])){$column['type']='text';}
                            ?><td><?php
                            switch ($column['type']){
                                default:
                                    $ctrl = $this->getControl();
                                    echo $ctrl->preview($columnKey,$column,$item,false);
                                    break;
                                case 'select':
                                    $options = isset($column['options'])?$column['options']:[];
                                    if(!is_string($options)&&is_callable($options)){
                                        $options = call_user_func($options);
                                    }

                                    if(array_is_empty($options)){  $options=[]; }
                                    $value = isset($item[$columnKey])?$item[$columnKey]:(isset($item['default'])?$item['default']:null);

                                    if(!empty($column['action'])&&$primaryColumn){
                                        ?>
                                        <select class="form-control <?php echo $this->getViewKey()?>-column-changeable"
                                                data-column="<?php echo esc_attr($columnKey)?>"
                                                data-action="<?php echo esc_attr($column['action'])?>"
                                                data-id="<?php echo esc_attr($item[$primaryColumn])?>"
                                        >
                                            <?php foreach ($options as $opKey=>$option){
                                                ?><option value="<?php echo esc_attr($opKey)?>" <?php echo $opKey==$value?"selected":""?>><?php echo $option;?></option><?php
                                            }?>
                                        </select>
                                        <?php
                                        break;
                                    }
                                    $ctrl = $this->getControl();
                                    echo $ctrl->preview($columnKey,$column,$item,false);
                                    break;
                                case 'checkbox':
                                    $value = isset($item[$columnKey])?$item[$columnKey]:(isset($item['default'])?$item['default']:null);
                                    if(!empty($column['action'])&&$primaryColumn){
                                        ?><input class="make-switch <?php echo $this->getViewKey()?>-column-switch"
                                                 data-column="<?php echo esc_attr($columnKey)?>"
                                                 data-action="<?php echo esc_attr($column['action'])?>"
                                                 data-id="<?php echo esc_attr($item[$primaryColumn])?>"
                                                 data-on-text="是"
                                                 data-off-text="否"
                                                 data-size="small"
                                                 type="checkbox"
                                                 value="yes"
                                            <?php echo $value=='yes'?'checked':'';?>
                                        />
                                        <script type="text/javascript">
                                            $(document).bind("handle_<?php echo $this->getViewKey();?>_obj_<?php echo esc_attr($item[$primaryColumn])?>_column_<?php echo $columnKey?>_change",function(e,form){
                                                form.<?php echo $columnKey?> =$('#<?php echo $this->getViewKey()."-tr-".$item[$primaryColumn]."-td-".$columnKey;?>:checked').length>0?'yes':'no';
                                            });
                                        </script>
                                        <?php
                                        break;
                                    }
                                    $ctrl = $this->getControl();
                                    echo $ctrl->preview($columnKey,$column,$item,false);
                                    break;
                                case 'img':
                                    $value = isset($item[$columnKey])?$item[$columnKey]:(isset($item['default'])?$item['default']:null);
                                    $style = Base::customerStyleHtml(isset($column['style'])?$column['style']:array());
                                    ?><img  src="<?php echo esc_attr($value)?>" style="<?php echo esc_attr($style)?>" /><?php
                                    break;
                                case 'code':
                                    $value = isset($item[$columnKey])?$item[$columnKey]:(isset($item['default'])?$item['default']:null);
                                    ?><code><?php echo $value?></code><?php
                                    break;
                            }
                            ?></td><?php
                        }
                        if($enableAction){
                            ?> <td>

                            <?php
                            $actions = $this->getItemAction($item);
                            if(!array_is_empty($actions)){
                                foreach($actions as $actionKey=>$action){
                                    if(empty($action["title"])){
                                        $action["title"]="button";
                                    }
                                    if(!isset($action["type"])){
                                        $action["type"]="button";
                                    }
                                    if(!is_string($action["type"])&&is_callable($action['type'])){
                                        $res = call_user_func_array($action["type"],array($item,$action));
                                        if($res &&$res instanceof \think\Response){
                                            echo $res->getContent();
                                        }
                                        continue;
                                    }

                                    switch ($action["type"]) {
                                        case "navigation":
                                            $url = $action["url"] ? call_user_func($action["url"], $item) : "#";
                                            $class = empty($action["class"])?"btn btn-outline-info btn-xs":$action["class"];
                                            $icon = !empty($action["icon"]) ? $action["icon"] : null;
                                            ?>
                                            <a href="<?php echo esc_attr($url); ?>"
                                               class="<?php echo esc_attr($class)?>">
                                                <?php if ($icon) {
                                                    ?><i class="<?php echo $icon; ?>"></i><?php
                                                } ?>
                                                <span class="hidden-xs"> <?php echo $action["title"] ?> </span>
                                            </a>
                                            <?php
                                            break;
                                        case "button":
                                            if (!$primaryColumn) {
                                                break;
                                            }
                                            $title= !empty($action["title"])?$action["title"]:"button";
                                            $class = !empty($action["class"])?$action["class"]:"btn btn-danger";
                                            $actionFieldKey = esc_attr($this->getViewKey("action_".$actionKey."_".$item[$this->getPrimaryColumn()]."_"));
                                            $icon = !empty($action["icon"])?$action["icon"]:null;
                                            ?>
                                            <a href="javascript:void(0);"
                                               class="<?php echo esc_attr($class)?>"
                                               onclick="window.<?php echo $actionFieldKey ?>View.confirm();">
                                                <?php if ($icon) {
                                                    ?><i class="<?php echo $icon; ?>"></i><?php
                                                } ?>
                                                <span class="hidden-xs"> <?php echo $title; ?> </span>
                                            </a>
                                            <?php
                                            $this->generateActionDialogHtml($item,$action,$actionKey);
                                            break;
                                        case "group":
                                            $options = !empty($action["options"])?$action["options"]:array();
                                            if(array_is_empty($options)){
                                                break;
                                            }
                                            $icon = !empty($action["icon"]) ? $action["icon"] : null;
                                            ?>
                                            <div class="btn-group">
                                                <a class="btn btn-outline-success btn-xs dropdown-toggle"
                                                   href="javascript:void(0);" data-toggle="dropdown"
                                                   aria-expanded="false">
                                                    <?php if ($icon) {
                                                        ?><i class="<?php echo $icon; ?>"></i><?php
                                                    } ?>
                                                    <span class="hidden-xs"> <?php echo $action["title"] ?> </span>
                                                    <i class="fa fa-angle-down"></i>
                                                </a>
                                                <?php foreach ($options as $optionKey => $option) {
                                                    if (empty($option["title"])) {
                                                        $option["title"]="button";
                                                    }
                                                    if (!isset($option["type"])) {
                                                        $option["type"] = "button";
                                                    }
                                                    if (!is_string($option["type"])&&is_callable($option["type"])) {
                                                        $res = call_user_func_array($option["type"], array($option,""));
                                                        if($res &&$res instanceof \think\Response){
                                                            echo $res->getContent();
                                                        }
                                                        continue;
                                                    }
                                                    switch ($option["type"]) {
                                                        case "divider":
                                                        case "navigation":
                                                            break;
                                                        default:
                                                            $this->generateActionDialogHtml($item,$option,$optionKey);
                                                            break;
                                                    }
                                                }?>
                                                <ul class="dropdown-menu pull-right">
                                                    <?php
                                                    foreach ($options as $optionKey => $option) {
                                                        if (empty($option["title"])) {
                                                            $option["title"]="button";
                                                        }
                                                        if (!isset($option['type'])) {
                                                            $option['type'] = 'button';
                                                        }

                                                        if (!is_string($option["type"])&&is_callable($option['type'])) {
                                                            ?><li><?php
                                                            $res = call_user_func_array($option["type"], array($item,$option,"li"));
                                                            if($res &&$res instanceof \think\Response){
                                                                echo $res->getContent();
                                                            }
                                                            ?></li><?php
                                                            continue;
                                                        }
                                                        switch ($option["type"]) {
                                                            case "divider":
                                                                ?><li class="divider"></li><?php
                                                                break;
                                                            case "navigation":
                                                                $title= !empty($option["title"])?$option["title"]:"button";
                                                                $class = !empty($option["class"])?$option["class"]:"";
                                                                $url = !empty($option["url"])?call_user_func($option["url"],$item):"#";
                                                                ?><li>
                                                                <a href="<?php echo esc_url($url);?>"
                                                                   class="<?php echo esc_attr($class);?>" >
                                                                    <span class="hidden-xs"> <?php echo $title;?> </span>
                                                                </a>
                                                                </li><?php
                                                                break;
                                                            default:
                                                                $title= !empty($option["title"])?$option["title"]:"button";
                                                                $class = !empty($option["class"])?$option["class"]:"";
                                                                $actionFieldKey = esc_attr($this->getViewKey("action_".$optionKey."_".$item[$this->getPrimaryColumn()]."_"));
                                                                $icon = !empty($option["icon"])?$option["icon"]:null;
                                                                ?>
                                                                <li>
                                                                <a href="javascript:void(0);"
                                                                   class="<?php echo esc_attr($class);?>"
                                                                   onclick="window.<?php echo $actionFieldKey;?>View.confirm();">
                                                                    <?php if($icon){
                                                                        ?><i class="<?php echo $icon;?>"></i><?php
                                                                    }?>
                                                                    <span class="hidden-xs"> <?php echo $title;?> </span>
                                                                </a>
                                                                </li><?php
                                                                break;
                                                        }
                                                    } ?>
                                                </ul>
                                            </div>
                                            <?php
                                            break;
                                    }
                                }
                            }
                            ?> </td><?php
                        }

                        if($this->sortable){
                            ?><td><i class="sortbar fa fa-bars"></i></td><?php
                        }?>

                    </tr>


                    <?php
                }
            }
        }
        $itemsHtml = ob_get_clean();

        ob_start();
        $this->getStatesHtml();
        $stateHtml = ob_get_clean();

        $result=array(
            'errcode'=>0,
            'items'=>$items,
            'html'=>$itemsHtml,
            'states'=>$stateHtml
        );

        $result['paging'] = HtmlHelper::paging(array(
            'page_index'=>$pageIndex,
            'total_count'=>$this->isCanPaging()? $this->doSearchTotal():($items?count($items):0),
            'page_size'=>$pageSize
        ));

        return json($result);
    }

    private function generateActionDialogHtml($item,$action,$actionKey){
        $currentAction =$action;
        $currentKey = $actionKey;
        $model = $this;
        $modelName = $this->getViewKey();
        $title= !empty($currentAction["title"])?$currentAction["title"]:"button";
        $filter = !empty($currentAction["filter"])&&array_is($currentAction["filter"])?$currentAction["filter"]:array();
        $filterFields = !empty($filter["fields"])&&array_is($filter["fields"])?$filter["fields"]:array();
        $confirmTitle = !empty($filter["title"])?$filter["title"]:(__("确定").$title."?");
        $actionFieldKey = esc_attr($model->getViewKey("action_".$currentKey."_".$item[$this->getPrimaryColumn()]."_"));
        if(!array_is_empty($filterFields)){
            ?>
            <div class="modal fade" id="modal-<?php echo $actionFieldKey;?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"><?php echo $confirmTitle;?></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body d-flex flex-column">
                            <div class="form form-row-seperated">
                                <?php
                                $control = new ControlBootstrap();
                                foreach($filterFields as $filterFieldKey=>$filterField){
                                    $control->generateField("form_".$actionFieldKey,$filterFieldKey,$filterField);
                                }
                                ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('取消') ;  ?></button>
                            <button type="button" class="btn btn-danger" onclick="window.<?php echo $actionFieldKey;?>View.submit()"><?php echo __('确定') ;  ?></button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <script type="text/javascript">
                (function($){
                    window.<?php echo $actionFieldKey;?>View={
                        confirm:function(){
                            $("#modal-<?php echo $actionFieldKey;?>").modal("show");
                        },
                        submit:function(){
                            let res = window.<?php echo $modelName;?>View.getBatchBase("<?php echo $currentKey;?>");
                            let filters = $.parseJSON(res.filters );
                            $(document).trigger("handle_form_<?php echo $actionFieldKey;?>_submit",filters);
                            res.filters = JSON.stringify(filters);
                            res.ids = JSON.stringify(["<?php echo isset($item[$this->getPrimaryColumn()])?esc_attr($item[$this->getPrimaryColumn()]):''?>"]);
                            window.<?php echo $modelName;?>View.ajaxGo(res,function(response){
                                $("#modal-<?php echo $actionFieldKey;?>").modal("hide");
                                $(document).trigger("handle_<?php echo $modelName;?>_action",{
                                    request:res,
                                    response:response
                                });
                            });
                        }
                    };
                })(jQuery);
            </script>
            <?php
        }else{
            ?>
            <script type="text/javascript">
                (function($){
                    window.<?php echo $actionFieldKey;?>View={
                        confirm:function(){
                            htmlhelper.dialog.confirm({
                                title:"<?php echo esc_attr($confirmTitle);?>",
                                confirm:function(){
                                    let res = window.<?php echo $modelName;?>View.getBatchBase("<?php echo $currentKey;?>");
                                    res.ids = JSON.stringify(["<?php echo isset($item[$this->getPrimaryColumn()])?esc_attr($item[$this->getPrimaryColumn()]):''?>"]);
                                    window.<?php echo $modelName;?>View.ajaxGo(res,function(response){
                                        $(document).trigger("handle_<?php echo $modelName;?>_action",{
                                            request:res,
                                            response:response
                                        });
                                    });
                                }
                            });
                        }
                    };
                })(jQuery);
            </script>
            <?php
        }

    }

    public function getStatesHtml(){
        $current_state = $this->getCurrentState();
        $states = $this->getStates();
        $getParams = parse_args(request()->get(),$this->getQueryArgs());
        $module = request()->module();
        $controller = request()->controller();
        $action = request()->action();
        if(array_is_empty($states)){
            return;
        }

        if(!isset($states[$current_state])){
            $current_state = array_first(array_keys($states));
        }
        ?><ul class="nav nav-tabs" style="margin-bottom: 0;"><?php
        foreach ($states as $stateKey => $state) {
            $type = isset($state["type"]) ? $state["type"] : "navigation";
            if (!is_string($type) && is_callable($type)) {
                $res = call_user_func_array($type, array($state,$current_state));
                if($res &&$res instanceof Response){
                    echo $res->getContent();
                }
                continue;
            }

            $params = $getParams;
            $params["state"] = $stateKey;
            ?>
            <li role="presentation" class="<?php echo($stateKey == $current_state ? 'active' : ''); ?>">
                <a href="<?php echo esc_attr(\org\helper\UrlHelper::urlArrToStr(array($module, $controller, $action, $params))); ?>">
                    <?php echo $state["title"] ?>(<?php echo $state["count"] ?>)
                </a>
            </li>  <?php
        }
        ?></ul><?php
    }

    /**
     * 是否允许分页
     * @return bool
     */
    public function isCanPaging(){
        return $this->call_search_total?true:false;
    }

    public function doEvent(){
        if(!$this->call_event){
            return false;
        }
        if(!request()->isAjax()){
            return false;
        }

        $_ = request()->param("__");
        if($_!=$this->getViewKey("event_")){
            return false;
        }

        $eventKey = request()->param("__event__");
        $idList = isset($_REQUEST['ids'])?maybe_json_decode($_REQUEST['ids'],true):null;
        if(!$idList){
            $idList = array();
        }
        $filters =maybe_json_decode( request()->param('filters'));
        $idList = map_deep_call($idList,'sanitize_text_field');
        try{
            return call_user_func_array($this->call_event,array($eventKey,array_unique($idList),$filters));
        }catch (\Exception $e){
            Log::error($e);
            return errorJson(500,$e->getMessage());
        }
    }

    /**
     * 获取系统(业务)状态列表
     * @return array
     */
    public function getStates(){
        static $states;
        if($states){
            return $states;
        }
        $filters = $this->getQueryFilters();
        if(!$this->states){
            return array();
        }

        $stateList = null;
        if(!is_array($this->states)&&!is_string($this->states)&&is_callable($this->states)){
            $stateList = call_user_func_array($this->states,array($filters,$this->getCurrentState()));
        }elseif(is_array($this->states)){
            $stateList = $this->states;
        }

        $states=array();
        if(array_is_empty($stateList)){
            return $states;
        }

        foreach ($stateList as $key=>$item){
            $filterQuery = maybe_json_decode($this->getQueryFilters());
            foreach ($filterQuery as $k=>$v){
                $filterQuery[$k] = null;
            }
            $filterQuery['state'] = $key;
            if(!isset( $item['count'])){
                $item['count'] =$this->call_search_total? call_user_func_array($this->call_search_total,array($filterQuery,$this)):0;
            }

            $states[$key] = $item;
        }

        return $states;
    }

    /**
     * 获取当前业务(系统)状态
     *
     * @return string|NULL
     */
    public function getCurrentState(){
        return request()->param("state");
    }

    /**
     * 判断是否每行显示checkbox 选择框
     *
     * @return bool
     */
    public function isEnablePrimaryCheckbox(){
        return $this->getBatchList()&&$this->primaryKey;
    }

    /**
     * 获取批量操作按钮配置
     *
     * @return array
     */
    public function getBatchList(){
        $state = $this->getCurrentState();
        $batchList = $this->batchListCall?call_user_func_array($this->batchListCall,array($state,$this)):null;
        if(!$batchList||!count($batchList)){
            return array();
        }

        return $batchList;
    }

    /**
     * 获取筛选字段列
     *
     * @return array
     */
    public function getFilters(){
        $state = $this->getCurrentState();
        $filterList = $this->filtersCall?call_user_func_array($this->filtersCall,array($state,$this)):null;
        if(!$filterList||!count($filterList)){
            return array();
        }

        $filters  = array();
        foreach ($filterList as $k=>$v){
            if(empty($v['class'])||!is_array($v['class'])){
                $v['class'] = array();
            }

            $v['class'][]="form-filter";
            $v['class'][]="input-sm";
            $filters[$k] = $v;
        }
        return $filters;
    }

    /**
     * 获取数据列表字段列
     *
     * @return array
     */
    public function getColumns(){
        $state = $this->getCurrentState();
        $columns = $this->columnsCall?call_user_func_array($this->columnsCall,array($state,$this)):null;

        if(!$columns||!count($columns)){
            return array();
        }

        return $columns;
    }

    /**
     * 获取主键
     * @return string
     */
    public function getPrimaryColumn(){
        return $this->primaryKey;
    }

    /**
     * 获取可排序的字段列表
     *
     * @return array|NULL
     */
    public function getSortableColumns(){
        $columns = $this->getColumns();
        if(!$columns){
            return null;
        }

        $sortableList = array();
        foreach ($columns as $key=>$column){
            if(isset($column['sortable'])&&$column['sortable']){
                $sortableList[$key] = $column;
            }
        }

        return $sortableList;
    }

    /**
     * 加载模板输出
     * @access protected
     * @param  string $template 模板文件名
     * @param  array  $vars     模板输出变量
     * @param  array  $config   模板参数
     * @return Response
     */
    public function fetch($template = '', $vars = [], $config = []){
        $this->assign($vars);

        $res = $this->doSearch();
        if($res instanceof Response){
            return $res;
        }

        $res = $this->doEvent();
        if($res instanceof Response){
            return $res;
        }

        return Response::create($template, 'view')->assign($vars)->config($config);
    }


}