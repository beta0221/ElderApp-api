<?php
namespace App\Helpers;

trait ControllerTrait{

    /**
     * 把html裡面的img tag 寬度變成100%
     * @param string html
     * @return string
     */
    protected function resizeHtmlImg($html){

        $html = str_replace("style='width:100%'","",$html);
        $html = str_replace("<img","<img style='width:100%'",$html);

        return $html;
    }
    
}