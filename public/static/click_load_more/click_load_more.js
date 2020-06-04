;(function (win, $) {
    "use strict";

    let ClickLoadMore=function () {
        this.version = '1.0.0';

        this.box_el=null;                   // required
        this.url=null;                      // required
        this.page=null;
        this.page_size=12;                  // not required

        this.extra_where_fun=null;          // not required
        this.render_items_fun=null;         // required

        this.is_loading=false;
        this.is_load_all=false;
    };

    ClickLoadMore.prototype.init=function (obj) {
        let that=this;
        if(!that.is_obj(obj)||that.is_empty_obj(obj)||!obj.hasOwnProperty('flag')||!obj.hasOwnProperty('url')||
            !obj.hasOwnProperty('render_items_fun')) throw new Error('Parameter exception');

        let boxEl=$(obj.flag);
        if(boxEl.length<=0) throw new Error("[flag]Parameter exception");

        that.box_el=$(obj.flag);
        that.url=obj.url;
        that.page=1;
        that.page_size=obj.hasOwnProperty('page_size')?obj.page_size:12;
        that.extra_where_fun=obj.hasOwnProperty('extra_where_fun')?obj.extra_where_fun:null;
        that.render_items_fun=obj.render_items_fun;
        that.is_loading=false;
        that.is_load_all=false;

        // paging html structure init
        that.set_box_html();
        // search page 1
        that.search();

        that.set_event_bind();
    };

    ClickLoadMore.prototype.set_box_html=function(){
        let that=this;
        let normal = "查看更多";
        let loadAll = "已加载所有";
        let noData = "暂无数据";
        let loadError ="暂无数据";
        if(window.GLOBALS['UEditorLANG'] =="en-us"){
             normal = "View More";
             loadAll = "Load All";
             noData = "No Data";
             loadError ="No Data";
        }

        that.box_el.addClass('click-load-more-box');
        let boxElHtml='<div class="normal">'+normal+'</div><div class="loading"></div><div class="load-all">'+loadAll+'</div><div class="no-data">'+noData+'</div><div class="load-error">'+loadError+'</div>';
        that.box_el.html(boxElHtml);
    };

    ClickLoadMore.prototype.set_event_bind=function(){
        let that=this;

        that.box_el.on('click','.normal',function () {
            if(that.is_load_all||that.is_loading) return false;
            that.page=parseInt(that.page)+1;
            that.search();
        });
    };

    ClickLoadMore.prototype.is_obj=function(obj){
        return (typeof(obj) === "object") && (Object.prototype.toString.call(obj).toLowerCase() === "[object object]") && !obj.length;
    };

    ClickLoadMore.prototype.is_empty_obj=function(obj){
        let isObj=this.is_obj(obj);
        if(isObj&&obj&&Object.keys(obj).length<=0){
            return true;
        }
        return false
    };

    ClickLoadMore.prototype.search=function(){
        let that=this;
        if(that.is_load_all||that.is_loading) return false;

        let page=parseInt(that.page);
        let pageSize=parseInt(that.page_size);
        let requestParams={page:page,page_size:pageSize};
        if(that.extra_where_fun) requestParams.where=that.extra_where_fun();

        that.load_start();
        $.ajax({
            type: 'post',
            url: that.url,
            data: requestParams,
            dataType: 'json',
            success: function (res) {
                if(res.errcode>0){
                    that.load_error(res.errmsg);
                }else {
                    let data=res.data;
                    that.page=data.page;
                    that.page_size=data.page_size;
                    // judge load end/load all/no data
                    that.render_items_fun(data.items);
                    if(data.items.length>0){
                        that.load_end();
                    }else {
                        if(parseInt(that.page)===1){
                            that.no_data();
                        }else {
                            that.load_all();
                        }
                    }
                }
            },
            error: function (XMLHttpRequest) {
                that.load_error('unknown error');
            }
        });
    };

    ClickLoadMore.prototype.box_child_show=function(child_flag,txt){
        let that=this;
        let boxEl=that.box_el;
        boxEl.find('div').hide();
        let childEl=boxEl.find('.'+child_flag);
        if(child_flag==='load-error'){
            childEl.html(txt||'load error');
        }
        childEl.show();
    };

    ClickLoadMore.prototype.load_start=function(){
        let that=this;
        that.is_loading=true;
        that.box_child_show('loading');
    };

    ClickLoadMore.prototype.load_end=function(){
        let that=this;
        that.is_loading=false;
        that.box_child_show('normal');
    };

    ClickLoadMore.prototype.load_all=function(){
        let that=this;
        that.is_load_all=true;
        that.box_child_show('load-all');
    };

    ClickLoadMore.prototype.no_data=function(){
        let that=this;
        that.is_load_all=true;
        that.box_child_show('no-data');
    };

    ClickLoadMore.prototype.load_error=function(errmsg){
        console.log(123);
        let that=this;
        that.is_load_all=true;
        that.box_child_show('load-error',errmsg);
    };

    win.click_load_more = new ClickLoadMore();

})(window, jQuery);