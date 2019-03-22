<?php
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');

class Lib_Page
{
    private $_total_page;
    private $_current_page;
    private $_pre_page;
    private $_next_page;
    private $_url;
    public function __construct($total_page,$current_page,$url = '')
    {
        $this->_url = $url;
        $this->_total_page = $total_page - 1;
        $this->_current_page = $current_page;
        if($this->_current_page == 0){
            $this->_pre_page = false;
        }else{
            $this->_pre_page = $this->_current_page - 1;
        }
        if($this->_current_page == $this->_total_page){
            $this->_next_page = false;
        }else{
            $this->_next_page = $this->_current_page + 1;
        }
    }

    public function page(){
        $html = '';
        
		$page = $this->_total_page+1;
		$html .= "<span style='font-size:15px;margin-right: 25px;'>总页数：".$page." 页</span>";
		
        $first_url = $this->_url.'&p=0';
        $html .= "<a onclick='page_ajax(0)'><span>首页</span></a>";
        if($this->_pre_page === false){
            $pre_url = "javascript:return false;";
        }else{
            $pre_url = $this->_url.'&p='.$this->_pre_page;
            $html .= "<a onclick='page_ajax($this->_pre_page)'><span>上一页</span></a>";
        }

        $current_url = $this->_url.'&p='.$this->_current_page;
        $show_page = $this->_current_page + 1;
        $html .= "<a onclick='page_ajax($this->_current_page)'><span id='now_page'>{$show_page}</span></a>";

        if($this->_next_page === false){
            $next_url = "javascript:return false;";
        }else{
            $next_url = $this->_url.'&p='.$this->_next_page;
            $html .= "<a onclick='page_ajax($this->_next_page)'><span>下一页</span></a>";
        }
        $end_url = $this->_url.'&p='.$this->_total_page;
        $html .= "<a onclick='page_ajax($this->_total_page)'><span>尾页</span></a>";
        
        $html .= "<span><input type='number' id='jump_number' min='1' max='".$page."'>";
        $html .= "<button type='button' id='page_jump'>GO</button></span>";
        
        return $html;
    }
}