<?php

namespace view;

use Cache\Adapter\Redis\RedisCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;
use control\Control;
use control\ControlBootstrap;
use org\helper\UrlHelper;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SheetIOException;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\facade\Cache;
use think\facade\Env;
use think\facade\Log;
use think\Response;
use think\response\Json;
use think\response\Redirect;

/**
 * excel 导入组件
 *
 * Class TableUpload
 * @since v1.0.0
 * @author ranj
 * @package view
 */
class TableUpload extends Table
{
    /**
     * 表单渲染器
     *
     * @var Control
     */
    protected $control;


    //protected $example_url;
    /**
     * 获取excel映射的字段列表
     * @var callable
     */
    protected $columnCall;

    /**
     * 导入数据库操作
     * @var callable
     */
    private $uploadCall;

    /**
     * 导入数据库后的操作
     * @var callable
     */
    private $uploadCallAfter;

    /**
     * 导入数据库前额外的参数
     * @var callable
     */
    protected $extrasCall;

    /**
     * 文件上传前额外参数
     * @var callable
     */
    protected $prepareCall;

    /**
     * 数据导入业务流程描述
     * @var callable
     */
    protected $descriptionCall;

    /**
     * 生成模板文件名命名
     *
     * @var string
     */
    protected $template_name;

    public function __construct($viewKey, $args)
    {
        $args = parse_args($args, array(
            //excel模板地址
            // 'example_url'=>null,
            'control' => new ControlBootstrap(),
            //导入前的配置
            'prepare' => null,
            //导入后的配置
            'extras' => null,
            //文件字段
            'columns' => null,
            'template_name' => null,
            'description' => function () {
                ?>
                <p>1. 导入数据支持的文件格式：xlsx/xls/csv</p>
                <p>2. 请使用第一行存放字段名称</p>
                <p>3. 请不要使用合并单元格存放数据</p>
                <p>4. 请清除文件中的空行或空列，系统无法识别空行或空列后的数据</p>
                <p>5. 文件大小小于5M</p>
                <p>6. 建议导入数据数量不超过5000</p>
                <?php
            }
        ));

        $this->viewKey = $viewKey;
        $this->control = $args['control'];
        $this->columnCall = $args['columns'];
        $this->extrasCall = $args['extras'];
        $this->prepareCall = $args['prepare'];
        $this->template_name = $args['template_name'];
        // $this->example_url = $args['example_url'];
        $this->descriptionCall = $args['description'];
    }

    /**
     * 数据导入业务流程描述
     *
     * @return string
     */
    public function getDescription()
    {
        ob_start();
        if ($this->descriptionCall) {
            call_user_func($this->descriptionCall);
        };
        return ob_get_clean();
    }
//
//    public function getExampleSheetDownloadUrl(){
//        return $this->example_url;
//    }

    /**
     * 获取文件导入前，额外字段
     *
     * @return array
     */
    public function getPrepare()
    {
        $prepare = $this->prepareCall ? call_user_func($this->prepareCall) : null;
        return array_is_empty($prepare) ? array() : $prepare;
    }

    /**
     * 获取导入数据库前 额外字段信息
     * @param array $import 文件导入信息：excel文件名，excel字段信息
     * @return array
     */
    public function getExtra($import)
    {
        static $extras;
        if (!array_is($extras)) {
            $extras = $this->extrasCall ? call_user_func($this->extrasCall, $import) : null;
            $extras = array_is_empty($extras) ? array() : $extras;
        }
        return $extras;
    }

    /**
     * 获取excel映射的字段信息
     *
     * @param array $import 文件导入信息：excel文件名，excel字段信息
     * @return array
     */
    public function getColumns($import)
    {
        static $columns;
        if (!array_is($columns)) {
            $columns = $this->columnCall ? call_user_func($this->columnCall, $import) : null;
            $columns = array_is_empty($columns) ? array() : $columns;
        }
        return $columns;
    }

    /**
     * 获取表单渲染器
     *
     * @return Control
     */
    public function getControl()
    {
        return $this->control;
    }

    /**
     * 获取上传文件字段的key
     *
     * @return string
     */
    public function getFileKey()
    {
        return 'file';
    }

    /**
     * 执行页面加载
     *
     * @param $vars array()
     * @return Response
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    protected function handleLoad(&$vars)
    {
        switch (request()->get("step")) {
            default:
                break;
            case '2':
                $member = request()->current_user;
                $import_id = request()->get("import_id", 0, FILTER_VALIDATE_INT);
                $import = db_default("module_import")
                    ->where("id", $import_id)
                    ->where("status", 'prepare')
                    ->where("member_id", $member['id'])
                    ->find();
                if (!$import) {
                    return redirectTo500(404, "上传信息未找到", UrlHelper::get_location_uri(array(
                        'step' => 1
                    )));
                }
                $vars['import'] = $import;
                break;
            case '3':
                $member = request()->current_user;
                $import_id = request()->get("import_id", 0, FILTER_VALIDATE_INT);
                $import = db_default("module_import")
                    ->where("id", $import_id)
                    ->where("status", 'prepare')
                    ->where("member_id", $member['id'])
                    ->find();
                if (!$import) {
                    return redirectTo500(404, "上传信息未找到", UrlHelper::get_location_uri(array(
                        'step' => 1
                    )));
                }

                $vars['import'] = $import;
                $model = $this->getPreviewListModel($import);
                $model->assign($vars);
                break;
            case "4":
                $member = request()->current_user;
                $import_id = request()->get("import_id", 0, FILTER_VALIDATE_INT);
                $import = db_default("module_import")
                    ->where("id", $import_id)
                    ->where("status", 'finished')
                    ->where("member_id", $member['id'])
                    ->find();
                if (!$import) {
                    return redirectTo500(404, "上传信息未找到", UrlHelper::get_location_uri(array(
                        'step' => 1
                    )));
                }
                $vars['import'] = $import;
                $total = db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->whereIn('status', array('await', 'succeed', 'aborted', 'cancelled'))
                    ->count('id');
                $await = db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->whereIn('status', 'await')
                    ->count('id');
                $aborted = db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->whereIn('status', 'aborted')
                    ->count('id');
                $succeed = db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->where('status', 'succeed')
                    ->count('id');
                $cancelled = db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->whereIn('status', 'cancelled')
                    ->count('id');
                $vars['report'] = array(
                    'total' => $total,
                    'await' => $await,
                    'aborted' => $aborted,
                    'succeed' => $succeed,
                    'cancelled' => $cancelled
                );

                $model = $this->getPreviewListModel($import);
                $model->assign($vars);
                break;
        }
    }

    public function handleUpload($call)
    {
        $this->uploadCall = $call;
    }

    public function handleUploadAfter($call)
    {
        $this->uploadCallAfter = $call;
    }

    /**
     * @param $import
     * @return TableList
     * @throws Exception
     */
    private function getPreviewListModel($import)
    {
        $that = $this;
        $states = array(
            'all' => array(
                'title' => '全部',
                'count' => db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->where('status', '<>', 'cancelled')
                    ->count('id')
            ),
            'await' => array(
                'title' => '等待',
                'count' => db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->where('status', 'await')
                    ->count('id'),
            ),
            'aborted' => array(
                'title' => '失败',
                'count' => db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->where('status', 'aborted')
                    ->count('id'),
            ),
            'succeed' => array(
                'title' => '成功',
                'count' => db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->where('status', 'succeed')
                    ->count('id'),
            )
        );

        if ($import['status'] === 'finished') {
            $states['cancelled'] = array(
                'title' => '已取消',
                'count' => db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->where('status', 'cancelled')
                    ->count('id'),
            );
        }

        $columns = $this->getColumns($import);

        $model = new TableList('previewList', array(
            'primary' => 'id',
            'states' => $states,
            'query_args' => array(
                'tab' => 'list'
            ),
            'batch' => function ($state) use ($import) {
                if ($import['status'] === 'finished') {
                    return array();
                }

                switch ($state) {
                    case 'await':
                    case 'aborted':
                        return array(
                            'back' => array(
                                'title' => '上一步',
                                'icon' => 'fa fa-reply',
                                'class' => 'btn btn-danger',
                                'type' => 'navigation',
                                'url' => function () use ($import) {
                                    return UrlHelper::get_location_uri(array(
                                        'step' => 2,
                                        'import_id' => $import['id']
                                    ));
                                }
                            ),
                            'confirm' => array(
                                'title' => '开始导入',
                                'type' => function () {
                                    ?>
                                    <span class="btn btn-sm btn-m-0 control-width btn-submit"
                                          onclick="window.previewListView.showStartTask();">开始导入</span>
                                    <?php
                                }
                            ),
                            'actions' => array(
                                'title' => '批量操作',
                                'icon' => 'fa fa-share',
                                'class' => 'btn btn-default',
                                'type' => 'group',
                                'options' => array(
                                    'cancel' => array(
                                        'title' => '取消导入',
                                        'type' => 'button'
                                    )
                                )
                            )
                        );
                }
                return array(
                    'back' => array(
                        'title' => '上一步',
                        'icon' => 'fa fa-reply',
                        'class' => 'btn btn-danger',
                        'type' => 'navigation',
                        'url' => function () use ($import) {
                            return UrlHelper::get_location_uri(array(
                                'step' => 2,
                                'import_id' => $import['id']
                            ));
                        }
                    ),
                    'confirm' => array(
                        'title' => '开始导入',
                        'type' => function () {
                            ?>
                            <span class="btn btn-sm btn-m-0 control-width btn-submit"
                                  onclick="window.previewListView.showStartTask();">开始导入</span>
                            <?php
                        }
                    )
                );
            },
            'columns' => function ($state) use ($columns) {
                return array_merge(
                    $columns,
                    array(
                        'status' => array(
                            'title' => '状态',
                            'type' => 'select',
                            'options' => array(
                                'await' => '<span class="label label-sm label-warning">等待</span>',
                                'aborted' => '<span class="label label-sm label-danger">失败</span>',
                                'succeed' => '<span class="label label-sm label-success">成功</span>'
                            )
                        ),
                        'remark' => array(
                            'title' => '失败原因',
                            'type' => function ($item) {
                                ?>
                                <span style="color:red;"><?php echo $item['remark'] ?></span>
                                <?php
                            }
                        )
                    )
                );
            },
            'action' => function ($item, $state) use ($import) {
                if ($import['status'] === 'finished') {
                    return array();
                }

                switch ($item['status']) {
                    case 'await':
                    case 'aborted':
                        return array(
                            'detail' => array(
                                'title' => '编辑',
                                'icon' => 'fa fa-eye',
                                'class' => 'btn btn-xs btn-info',
                                'type' => function ($item) {
                                    ?><a href="javascript:void(0)"
                                         onclick="window.previewListEntryView.edit(<?php echo $item['id'] ?>)"
                                         class="btn btn-xs btn-info">
                                        <i class="fa fa-eye"></i>
                                        <span class="hidden-xs"> 编辑 </span>
                                    </a><?php
                                }
                            ),
                            'actions' => array(
                                'title' => '操作',
                                'icon' => 'fa fa-share',
                                'class' => 'btn btn-xs btn-default',
                                'type' => 'group',
                                'options' => array(
                                    'cancel' => array(
                                        'title' => '取消导入'
                                    )
                                )
                            )
                        );
                    default:
                        return array();
                }
            }
        ));
        $model->handleSearchTotal(function ($filters) use ($import) {
            $state = $filters['state'];
            $countQuery = db_default('module_import_item')
                ->where("import_id", $import['id']);

            if ($state && $state != 'all') {
                $countQuery->where("status", $state);
            } else {
                $countQuery->where("status", '<>', 'cancelled');
            }

            return $countQuery->count('id');
        });
        $model->handleSearchItems(function ($start, $end, $filters, $sorts) use ($import, $columns) {
            $state = $filters['state'];
            $itemQuery = db_default('module_import_item')
                ->where("import_id", $import['id']);

            if ($state && $state != 'all') {
                $itemQuery->where("status", $state);
            } else {
                $itemQuery->where("status", '<>', 'cancelled');
            }

            //排序
            if (count($sorts)) {
                $itemQuery->order($sorts);
            } else {
                $itemQuery->order(['id' => 'asc']);
            }

            $results = $itemQuery->limit($start, $end)->select();
            if (!$results || !count($results)) {
                return $results;
            }

            //{name=>姓名}
            $reflect = maybe_json_decode($import['reflect'], true);
            if (array_is_empty($reflect)) {
                $reflect = array();
            }

            $returns = array();
            foreach ($results as $item) {
                $entry = maybe_json_decode($item['entry'], true);
                //{姓名=>测试}
                $entry = array_is($entry) ? $entry : array();
                $control = new Control();
                foreach ($columns as $columnKey => $field) {
                    $fieldObj = $control->getField($columnKey, $field);

                    $excelTitle = $reflect[$columnKey];

                    if (isset($entry[$excelTitle])) {
                        $excelToData = $fieldObj->get('excelToData');
                        if ($excelToData) {
                            $item[$columnKey] = call_user_func($excelToData, $entry[$excelTitle]);
                        } else {
                            $item[$columnKey] = $entry[$excelTitle];
                        }
                    } else {
                        $item[$columnKey] = isset($field['default']) ? $field['default'] : '';
                    }
                }
                $returns[] = $item;
            }
            return $returns;
        });

        if ($import['status'] !== 'finished') {
            $model->handleEvent(function ($eventKey, $idList) use ($import) {
                //强制转化为int类型
                $idList = map_deep_call($idList, 'absint');

                try {
                    switch ($eventKey) {
                        case 'cancel':
                            foreach ($idList as $id) {
                                db_default("module_import_item")
                                    ->where('import_id', $import['id'])
                                    ->where('id', $id)
                                    ->whereIn('status', array('await', 'aborted'))
                                    ->update(array(
                                        'status' => 'cancelled'
                                    ));
                            }
                            return successJson([
                                'refresh' => true
                            ]);
                    }
                } catch (\Exception $e) {
                    Log::error($e);
                    return errorJson(500);
                }

                return false;
            });
        }

        return $model;
    }

    /**
     * 执行页面事件
     *
     * @return Response|FALSE
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \think\exception\PDOException
     */
    protected function handleEvent()
    {
        if (!request()->isPost()) {
            return false;
        }

        $member = request()->current_user;
        $expo = request()->current_expo;

        $__ = request()->post("__");
        switch ($__) {
            default:
                $import_id = request()->get("import_id", 0, FILTER_VALIDATE_INT);
                $import = db_default("module_import")
                    ->where("id", $import_id)
                    ->whereIn("status", array('prepare', 'finished'))
                    ->where("member_id", $member['id'])
                    ->find();
                if (!$import) {
                    return errorJson(404);
                }

                $model = $this->getPreviewListModel($import);
                $res = $model->doSearch();
                if ($res instanceof Response) {
                    return $res;
                }

                $res = $model->doEvent();
                if ($res instanceof Response) {
                    return $res;
                }
                return false;
            case 'upload':
                $member = request()->current_user;
                $file = request()->file($this->getFileKey());
                if ($file->getError()) {
                    return errorJson(500, $file->getError());
                }


                if (!$file->checkExt(array("xls", "xlsx", "csv")) || !$file->checkMime(array(
                        "application/vnd.ms-excel",
                        "application/vnd.msexcel",
                        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                        "text/csv",
                        "application/csv",
                        "application/excel",
                        "application/zip",
                        "text/plain"
                    ))) {
                    return errorJson(500, "请上传正确的excel格式的文件");
                }

                $client = Cache::handler();
                $pool = new RedisCachePool($client);
                $simpleCache = new SimpleCacheBridge($pool);
                Settings::setCache($simpleCache);

                $extension = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
                ini_set('max_execution_time', 60);
                try {
                    $objReader = IOFactory::createReader(ucfirst($extension));
                    $spreadsheet = $objReader->load($file->getRealPath());
                    $worksheet = $spreadsheet->getActiveSheet();
                    $highestRow = $worksheet->getHighestRow(); // 总行数
                    $highestColumn = $worksheet->getHighestColumn(); // 总列数
                    if ($highestRow < 2 || !$highestColumn) {
                        return errorJson(500, 'Excel表格中没有数据');
                    }

                    $dataColumnList = array();
                    $start = $this->excelColumnIndexToNumber('A');
                    $end = $this->excelColumnIndexToNumber($highestColumn);
                    for ($singleData = $start; $singleData <= $end; $singleData++) {
                        $title = $worksheet->getCellByColumnAndRow($singleData, 1)->getValue();
                        if ($title instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                            $title = $title->getPlainText();
                        }

                        $dataColumnList[] = $title;
                    }

                    $prepare = request()->post();
                    unset($prepare['__']);

                    $import_id = db_default("module_import")
                        ->insertGetId(array(
                            'columns' => maybe_json_encode($dataColumnList),
                            'member_id' => $member['id'],
                            'prepare' => maybe_json_encode($prepare),
                            'expo_id' => $expo ? $expo['id'] : 0,
                            'status' => 'prepare',
                            'name' => $file->getInfo('name'),
                            'file_size' => round($file->getSize() / 1024),
                            'created_time' => date('Y-m-d H:i:s')
                        ));
                    $columnList = array();
                    for ($row = 2; $row <= $highestRow; $row++) {
                        $line = array();
                        for ($singleData = $start; $singleData <= $end; $singleData++) {
                            $value = $worksheet->getCellByColumnAndRow($singleData, $row)->getValue();
                            if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                                $value = $value->getPlainText();
                            }
                            $line[$dataColumnList[$singleData - 1]] = $value;
                        }
                        if (!array_any($line, function ($item) {
                            return !empty($item);
                        })) {
                            continue;
                        }

                        $line = array_filter2($line, function ($item, $key) {
                            return !is_null_or_whitespace($key);
                        });

                        $columnList[] = array(
                            'import_id' => $import_id,
                            'entry' => maybe_json_encode($line),
                            'created_time' => date('Y-m-d H:i:s')
                        );

                        if (count($columnList) >= 1000) {
                            db_default("module_import_item")
                                ->data($columnList)
                                ->limit(200)
                                ->insertAll();
                            $columnList = array();
                        }
                    }

                    if (count($columnList)) {
                        db_default("module_import_item")
                            ->data($columnList)
                            ->insertAll();
                    }

                    return successJson(array(
                        'url' => UrlHelper::get_location_uri(array(
                            'step' => 2,
                            'import_id' => $import_id
                        ))
                    ));
                } catch (SheetIOException $e) {
                    Log::error($e);
                    return errorJson(500, $e->getMessage());
                } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
                    Log::error($e);
                    return errorJson(500, $e->getMessage());
                } catch (\Exception $e) {
                    Log::error($e);
                    return errorJson(500, $e->getMessage());
                }
            case 'do':
                $member = request()->current_user;
                $import_id = request()->get("import_id", 0, FILTER_VALIDATE_INT);
                $import = db_default("module_import")
                    ->where("id", $import_id)
                    ->where("member_id", $member['id'])
                    ->find();
                if (!$import) {
                    return errorJson(404);
                }

                $url = '';
                if ($import['status'] == 'finished') {
                    return json(array(
                        'errcode' => 20019,
                        'errmsg' => '导入已结束',
                        'data' => array(
                            'url' => UrlHelper::get_location_uri(array(
                                'step' => 4,
                                'import_id' => $import['id']
                            ))
                        )
                    ));
                }

                $import_item_list = db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->where('status', 'await')
                    ->limit(0, 200)
                    ->select();

                $control = $this->getControl();
                $extras = array();
                foreach ($this->getExtra($import) as $extraKey => $extra) {
                    try {
                        $control->param($extraKey, $extra, $extras);
                    } catch (\Exception $e) {
                        $extras[$extraKey] = isset($extra['default']) ? $extra['default'] : null;
                        //ignore
                    }
                }

                $columns = $this->getColumns($import);

                //{name=>姓名}
                $reflect = maybe_json_decode($import['reflect'], true);
                if (array_is_empty($reflect)) {
                    $reflect = array();
                }

                if ($import_item_list && count($import_item_list)) {
                    $dataColumnList = array();
                    foreach ($import_item_list as $importItem) {
                        $singleData = array(
                            'import_item_id' => $importItem['id']
                        );

                        $entry = maybe_json_decode($importItem['entry'], true);
                        //{姓名=>xxx}
                        $entry = !array_is_empty($entry) ? $entry : array();
                        try {
                            foreach ($columns as $columnKey => $column) {
                                $fieldObj = $control->getField($columnKey, $column);
                                if (!$fieldObj) {
                                    continue;
                                }

                                $fieldObj->param($singleData, function ($key) use ($reflect, $entry, $column, $fieldObj) {
                                    $entryKey = $reflect[$key];
                                    $excelToData = $fieldObj->get('excelToData');
                                    if ($excelToData) {
                                        return call_user_func($excelToData, $entry[$entryKey]);
                                    }
                                    return $entry[$entryKey];
                                });
                            }

                            Db::startTrans();
                            $do = false;
                            if ($this->uploadCall) {
                                $do = call_user_func_array($this->uploadCall, array($singleData, $extras, $importItem, $import, $columns));
                            }

                            $status = 'succeed';
                            $remark = null;
                            if ($do instanceof Json) {
                                $data = maybe_json_decode($do->getData());
                                if (isset($data['errcode']) && $data['errcode'] != 0) {
                                    $status = 'cancelled';
                                    $remark = isset($data['errmsg']) ? $data['errmsg'] : '系统内部异常！';
                                }
                            } else if ($do === false) {
                                $status = 'cancelled';
                                $remark = null;
                            }

                            db_default('module_import_item')
                                ->where('import_id', $import['id'])
                                ->where('id', $importItem['id'])
                                ->update(array(
                                    'status' => $status,
                                    'remark' => $remark
                                ));

                            Db::commit();
                            if ($status === 'succeed') {
                                $dataColumnList[] = $singleData;
                            }
                        } catch (\Exception $e) {
                            Db::rollback();
                            Log::error($e);
                            db_default('module_import_item')
                                ->where('import_id', $import['id'])
                                ->where('id', $importItem['id'])
                                ->update(array(
                                    'status' => 'aborted',
                                    'remark' => $e->getMessage()
                                ));
                        }
                    }

                    try {
                        if ($this->uploadCallAfter) {
                            call_user_func_array($this->uploadCallAfter, array($dataColumnList, $extras, $import));
                        }
                    } catch (\Exception $e) {
                        Log::error($e);
                        //ignore
                    }
                }

                $total = db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->whereIn('status', array('await', 'succeed', 'aborted'))
                    ->count('id');
                $done = db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->whereIn('status', array('succeed', 'aborted'))
                    ->count('id');
                $succeed = db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->where('status', 'succeed')
                    ->count('id');

                if ($done == $total && $succeed != $total) {
                    $url = UrlHelper::get_location_uri(array(
                        'step' => 3,
                        'import_id' => $import['id'],
                        'state' => 'aborted'
                    ));
                }

                if ($succeed == $total) {
                    db_default('module_import')
                        ->where('id', $import['id'])
                        ->where('status', 'prepare')
                        ->update(array(
                            'status' => 'finished'
                        ));
                    $url = UrlHelper::get_location_uri(array(
                        'step' => 4,
                        'import_id' => $import['id']
                    ));
                }

                return successJson(array(
                    'total' => $total,
                    'done' => $done,
                    'succeed' => $succeed,
                    'percent' => $total ? (round($succeed / ($total * 1.0), 2) * 100) : 0,
                    'url' => $url
                ));
            case 'preDo':
                $member = request()->current_user;
                $import_id = request()->get("import_id", 0, FILTER_VALIDATE_INT);
                $import = db_default("module_import")
                    ->where("id", $import_id)
                    ->where("member_id", $member['id'])
                    ->find();
                if (!$import) {
                    return errorJson(404);
                }
                $url = '';
                if ($import['status'] == 'finished') {
                    return json(array(
                        'errcode' => 20019,
                        'errmsg' => '导入已结束',
                        'data' => array(
                            'url' => UrlHelper::get_location_uri(array(
                                'step' => 4,
                                'import_id' => $import['id']
                            ))
                        )
                    ));
                }

                $total = db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->whereIn('status', array('await', 'succeed', 'aborted'))
                    ->count('id');
                $done = db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->whereIn('status', array('succeed', 'aborted'))
                    ->count('id');

                $succeed = db_default('module_import_item')
                    ->where('import_id', $import['id'])
                    ->where('status', 'succeed')
                    ->count('id');

                if ($done == $total && $succeed != $total) {
                    $url = UrlHelper::get_location_uri(array(
                        'step' => 3,
                        'import_id' => $import['id'],
                        'state' => 'aborted'
                    ));
                }

                if ($succeed == $total) {
                    db_default('module_import')
                        ->where('id', $import['id'])
                        ->where('status', 'prepare')
                        ->update(array(
                            'status' => 'finished'
                        ));
                    $url = UrlHelper::get_location_uri(array(
                        'step' => 4,
                        'import_id' => $import['id']
                    ));
                }

                return successJson(array(
                    'total' => $total,
                    'done' => $done,
                    'succeed' => $succeed,
                    'percent' => $total ? (round($succeed / ($total * 1.0), 2) * 100) : 0,
                    'url' => $url
                ));
            case 'update':
                $member = request()->current_user;
                $import_id = request()->get("import_id", 0, FILTER_VALIDATE_INT);
                $import = db_default("module_import")
                    ->where("id", $import_id)
                    ->where("status", 'prepare')
                    ->where("member_id", $member['id'])
                    ->find();
                if (!$import) {
                    return errorJson(404);
                }
                $import_item_id = request()->post("id", 0, FILTER_VALIDATE_INT);
                $import_item = db_default("module_import_item")
                    ->where("import_id", $import_id)
                    ->where("id", $import_item_id)
                    ->whereIn("status", array('await', 'aborted'))
                    ->find();
                if (!$import_item) {
                    return errorJson(404);
                }


                //{name=>姓名}
                $reflect = maybe_json_decode($import['reflect'], true);
                if (array_is_empty($reflect)) {
                    $reflect = array();
                }
                $control = $this->getControl();
                $entryData = array();

                try {
                    foreach ($this->getColumns($import) as $fieldKey => $field) {
                        $data = array();
                        $control->param($fieldKey, $field, $data);
                        $entryData[$reflect[$fieldKey]] = isset($data[$fieldKey]) ? $data[$fieldKey] : (isset($field['default']) ? $field['default'] : null);
                    }

                    db_default("module_import_item")
                        ->where("import_id", $import_id)
                        ->where("id", $import_item_id)
                        ->whereIn("status", array('await', 'aborted'))
                        ->update(array(
                            'status' => 'await',
                            'remark' => null,
                            'entry' => maybe_json_encode($entryData)
                        ));
                } catch (\Exception $e) {
                    Log::error($e);
                    return errorJson(500, $e->getMessage());
                }

                return successJson();
            case 'confirm':
                $member = request()->current_user;

                $import_id = request()->get("import_id", 0, FILTER_VALIDATE_INT);
                $import = db_default("module_import")
                    ->where("id", $import_id)
                    ->where("status", 'prepare')
                    ->where("member_id", $member['id'])
                    ->find();
                if (!$import) {
                    return errorJson(404);
                }

                $columns = $this->getColumns($import);
                try {
                    $reflect = array();
                    foreach ($columns as $fieldKey => $field) {
                        $title = request()->post($fieldKey);
                        if (empty($title)) {
                            throw new Exception("请填写正确字段：{$field['title']}");
                        }
                        $reflect[$fieldKey] = $title;
                    }

                    foreach ($reflect as $fieldKey => $title) {
                        if (array_first($reflect, function ($item, $key) use ($title, $fieldKey) {
                            return $fieldKey != $key && $title == $item;
                        })) {
                            throw new Exception("文件字段({$title})匹配到多个系统字段！");
                        }
                    }

                    db_default("module_import")
                        ->where("id", $import_id)
                        ->where("status", 'prepare')
                        ->update(array(
                            'reflect' => maybe_json_encode($reflect)
                        ));
                } catch (\Exception $e) {
                    Log::error($e);
                    return errorJson(500, $e->getMessage());
                }

                return successJson(array(
                    'url' => UrlHelper::get_location_uri(array(
                        'step' => 3,
                        'import_id' => $import_id
                    ))
                ));
            case 'template':
                $basePath = Env::get('root_path') . "public";
                $now = strtotime(date('Y-m-d H:i:s'));
                $dataList = db_default("module_export")
                    ->where("created_time", "<=", $now - 60 * 5)
                    ->limit(0, 30)
                    ->select();
                //清除上传导出的文件缓存
                if ($dataList) {
                    foreach ($dataList as $data) {
                        try {
                            if (@file_exists($basePath . $data['filename'])) {
                                @unlink($basePath . $data['filename']);
                            }

                            db_default("module_export")
                                ->where("id", $data['id'])
                                ->delete();
                        } catch (\Exception $e) {

                        }
                    }
                }

                $filePath = "/upload/export/";
                if (!@is_dir($basePath . $filePath)) {
                    if (!@mkdir($basePath . $filePath, 0777, true)) {
                        return errorJson(40112);
                    }
                }

                $client = Cache::handler();
                $pool = new RedisCachePool($client);
                $simpleCache = new SimpleCacheBridge($pool);
                Settings::setCache($simpleCache);
                $spreadsheet = new Spreadsheet();
                $worksheet = $spreadsheet->getActiveSheet();

                $prepare = request()->post();
                unset($prepare['__']);
                $import = array(
                    'member_id' => $member['id'],
                    'prepare' => maybe_json_encode($prepare),
                    'expo_id' => $expo ? $expo['id'] : 0
                );

                try {
                    $columns = $this->getColumns($import);

                    $index = 1;
                    foreach ($columns as $columnKey => $column) {
                        if (empty($column['title'])) {
                            $column['title'] = "";
                        }
                        $col = $index++;
                        //设置单元格内容
                        $worksheet->setCellValueByColumnAndRow($col, 1, $column['title']);
                        $columnLetter = Coordinate::stringFromColumnIndex($col);
                        $worksheet->getStyle($columnLetter)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                        //$header[]= mb_convert_encoding($column['title'],'gb2312','utf-8');
                    }

                    $filename = $this->template_name;
                    if ($filename) {
                        $filename = str_replace("/", "-", $filename);
                        $filenameArray = explode('.', $filename);
                        if ($filenameArray && count($filenameArray)) {
                            $filename = $filenameArray[0];
                        }
                    }

                    $filename = ($filename ?: "模板文件") . '.xls';
                    $writer = IOFactory::createWriter($spreadsheet, 'Xls');
                    $writer->save($basePath . $filePath . $filename);
                    db_default("module_export")
                        ->insert(array(
                            'filename' => $filePath . $filename,
                            'created_time' => $now
                        ));
                    return successJson(array(
                        'url' => $filePath . $filename
                    ));
                } catch (\Exception $e) {
                    Log::error($e);
                    return errorJson(500, $e->getMessage());
                }
        }
    }


    private function excelColumnIndexToNumber($index)
    {
        $charList = array_flip(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z']);
        $len = strlen($index);
        return ($len - 1) * 26 + $charList[$index[$len - 1]] + 1;
    }

    /**
     * 加载模板输出
     *
     * @param string $template 模板文件名
     * @param array $vars 模板输出变量
     * @param array $config 模板参数
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \think\exception\PDOException
     */
    public function fetch($template = '', $vars = [], $config = [])
    {
        $this->assign($vars);

        $res = $this->handleLoad($vars);
        if ($res instanceof Response) {
            return $res;
        }

        $res = $this->handleEvent();
        if ($res instanceof Response) {
            return $res;
        }

        return Response::create($template, 'view')->assign($vars)->config($config);
    }

    /**
     * 预览列表view
     * @param array $model
     */
    public static function preview($model)
    {
        echo fetch("common@table/upload/preview_list", [
            'previewList' => $model
        ])->getContent();
    }
}